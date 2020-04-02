#!/bin/bash
# Project development environment initialization
#
# This script prepares all that needed to development:
#   - install contaier;
#   - installs packages;
#   - services, database
#
# Author: Alexandr Gorlov
# Coauthor: Kirill Artemov

# Any subsequent(*) commands which fail will cause the shell script to exit immediately
set -e

LXC_NAME="lipid-example"
#LXC_PATH="/var/lib/lxc/${LXC_NAME}"
LXC_PATH="${HOME}/.local/share/lxc/${LXC_NAME}"
#LXC_ROOT="/var/lib/lxc/${LXC_NAME}/rootfs"
LXC_ROOT="${HOME}/.local/share/lxc/${LXC_NAME}/rootfs"

DB_NAME="example"

if [ ${UID} = "0" ]; then
  echo "Hey, run this script not from root:"
  echo "  $ ./initenv.sh"
  exit 255;
fi

MY_GROUP=`id -gn`
GID=`id -g`

if lxc-ls -f | grep "${LXC_NAME}"; then
  read -p "Container '${LXC_NAME}' already exists. Destroy it, and create new one from scratch (y/n)? " CONT
  if [ "$CONT" = "y" ]; then
    lxc-destroy -n "${LXC_NAME}" -f;
  else
    echo "Exiting...";
    exit 1;
  fi

fi

# Container creation
echo "Creationg container: ${LXC_NAME}"
lxc-create -t download -n "${LXC_NAME}" -- -d ubuntu -r bionic -a amd64

echo "Starting ${LXC_NAME}...";
lxc-start -n ${LXC_NAME}

# Recreate default user, his login, uid, gid equal to host user
lxc-attach -n ${LXC_NAME} -- userdel -r ubuntu
lxc-attach -n ${LXC_NAME} -- groupadd -g ${GID} ${MY_GROUP}
lxc-attach -n ${LXC_NAME} -- useradd -s /bin/bash --gid ${GID} -G sudo --uid ${UID} -d /www ${USER}

lxc-stop -n ${LXC_NAME}


# Lxc config tweaks
{
  echo ""
  echo "# Map Host project directory to /www"
  echo "lxc.mount.entry = ${PWD} www noncae bind,create=dir,rw 0 0"
} >> "${LXC_PATH}/config"

# Comment default lxc.idmap...
sed -i '/^lxc.idmap/s/^/#/g' "${LXC_PATH}/config"

# map this user alexandr (uid=1000) on user ubuntu (uid=1000) in container
{
  echo ""
  echo "lxc.idmap = u 0 100000 1000" # Uid  0 maps to 100000 in container, 1 -> 100001 till count 1000
  echo "lxc.idmap = g 0 100000 1000"
  echo "lxc.idmap = u ${UID} ${UID} 1" # Uid 1000 -> 1000 in container (one)
  echo "lxc.idmap = g ${GID} ${GID} 1"
  echo "lxc.idmap = u $((UID+1)) 10$((UID+1)) $((65535-UID))"
  echo "lxc.idmap = g $((UID+1)) 10$((UID+1)) $((65535-UID))"
} >> "${LXC_PATH}/config"

lxc-start -n ${LXC_NAME}

# wait untill it starts
until [[ `lxc-ls -f | grep "${LXC_NAME}" | grep "RUNNING" | grep "10.0.3"` ]]; do sleep 1; done;
echo `lxc-ls -f`



# Packages installation
echo "Packages installation...";

## Predefined variables to install postfix
## https://blog.bissquit.com/unix/debian/postfix-i-dovecot-v-kontejnere-docker/
{
  echo "postfix postfix/main_mailer_type string Internet site"
  echo "postfix postfix/mailname string mail.domain.tld"
} >> "${LXC_ROOT}/tmp/postfix_silent_install.txt"

lxc-attach -n "${LXC_NAME}" -- debconf-set-selections /tmp/postfix_silent_install.txt

lxc-attach -n "${LXC_NAME}" -- apt update
lxc-attach -n "${LXC_NAME}" -- sh -c "DEBIAN_FRONTEND=noninteractive apt install -q -y \
    locales-all php7.2-cli php7.2-mysql php7.2-mbstring php7.2-xml php7.2-curl \
    php7.2-fpm php7.2-zip wget nginx mc mariadb-server sphinxsearch composer postfix \
    php7.2-soap"
lxc-attach -n "${LXC_NAME}" -- su - ${USER} -c "cd /www && composer install"

# /etc/hosts
{
  echo "# ${LXC_NAME}"
  echo "127.0.0.1 dbhost"
} | lxc-attach -n "${LXC_NAME}" -- bash -c "cat - >> /etc/hosts"


# nginx configuration
{
cat <<EOFNGINX
server {
    listen 80;
    server_name ${LXC_NAME};
    fastcgi_read_timeout 600;
    access_log /var/log/nginx/access.log;
    error_log  /var/log/nginx/error.log;
    client_max_body_size 50m;
    set_real_ip_from 10.0.3.1; # set if container is behind proxy (on host machine)
    # @todo #175 поместить root в /www/www или /www/public
    root /www;
    gzip off;
    location / {
        try_files \$uri @php72;
        index index.php;
    }
    location /adminer {
        allow 127.0.0.1;
        allow 10.0.3.1;
        deny all;

        root /usr/share/adminer/;
        fastcgi_pass unix:///run/php/php7.2-fpm.sock;
        fastcgi_index  latest.php;
        fastcgi_param  SCRIPT_FILENAME  \$document_root/latest.php;
        include        fastcgi_params;
    }
    location @php72 {
        root /www;
        fastcgi_pass unix:///run/php/php7.2-fpm.sock;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME  \$document_root/index.php;
        include        fastcgi_params;
    }
}
EOFNGINX
} | lxc-attach -n "${LXC_NAME}" -- bash -c "cat - >> /etc/nginx/sites-available/${LXC_NAME}"

lxc-attach -n "${LXC_NAME}" -- ln -s /etc/nginx/sites-available/${LXC_NAME} /etc/nginx/sites-enabled/
lxc-attach -n "${LXC_NAME}" -- rm /etc/nginx/sites-enabled/default

# change www-data in container to this user
lxc-attach -n "${LXC_NAME}" -- sed -i "/^user www-data/s/user www-data/user ${USER}/"\
      "/etc/nginx/nginx.conf"
lxc-attach -n "${LXC_NAME}" -- sed -i "/^user = www-data/s/user = www-data/user = ${USER}/"\
    "/etc/php/7.2/fpm/pool.d/www.conf"
lxc-attach -n "${LXC_NAME}" -- sed -i "/^group = www-data/s/group = www-data/group = ${MY_GROUP}/"\
    "/etc/php/7.2/fpm/pool.d/www.conf"
lxc-attach -n "${LXC_NAME}" -- sed -i "/^listen.owner = www-data/s/listen.owner = www-data/listen.owner = ${USER}/"\
    "/etc/php/7.2/fpm/pool.d/www.conf"
lxc-attach -n "${LXC_NAME}" -- sed -i "/^listen.group = www-data/s/listen.group = www-data/listen.group = ${MY_GROUP}/"\
    "/etc/php/7.2/fpm/pool.d/www.conf"

# Php configuration customization
{
  echo "php_admin_value[upload_max_filesize] = 50M"
  echo "php_admin_value[post_max_size] = 50M"
} | lxc-attach -n "${LXC_NAME}" -- bash -c "cat - >> /etc/php/7.2/fpm/pool.d/www.conf"


lxc-attach -n "${LXC_NAME}" -- systemctl restart php7.2-fpm
lxc-attach -n "${LXC_NAME}" -- systemctl restart nginx

# Databases initialization
echo "Databases initialization"
lxc-attach -n "${LXC_NAME}" -- mysqladmin create ${DB_NAME}

cat ${DB_NAME}.sql | lxc-attach -n "${LXC_NAME}" -- mysql ${DB_NAME} -uroot
echo "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_NAME}'@'%' IDENTIFIED BY '${DB_NAME}';" |\
  lxc-attach -n "${LXC_NAME}" -- mysql ${DB_NAME} -uroot


#lxc-attach -n "${LXC_NAME}" -- sh -c "cd /www && /usr/bin/php ./migrate.php"

# Sphinxsearch

#cat sphinx.conf > "${LXC_ROOT}/etc/sphinxsearch/sphinx.conf"
#echo "START=yes" > "${LXC_ROOT}/etc/default/sphinxsearch"
#lxc-attach -n "${LXC_NAME}" -- indexer --all
#lxc-attach -n "${LXC_NAME}" -- systemctl restart sphinxsearch


# Crontab
{
  echo "#*/5 * * * *     cd /www && /usr/bin/php ./scripts/sync_boxes.php"
  echo "#*/30 * * * *    indexer --all --rotate"
} | lxc-attach -n "${LXC_NAME}" -- crontab -u${USER} -


# Adminer
lxc-attach -n "${LXC_NAME}" -- \
    mkdir -p /usr/share/adminer
lxc-attach -n "${LXC_NAME}" -- \
    sh -c 'cd /usr/share/adminer && wget https://www.adminer.org/latest.php'

# Help messages
LXC_IP=`lxc-info -n ${LXC_NAME} -iH`

echo
echo "======================= ALL DONE! ==========================="
echo
echo "Open in browser: http://${LXC_IP}"
echo
echo "To start adminer run:"
echo
echo "    http://${LXC_IP}/adminer"
echo
echo "For production, or to open app for all, forward port:"
echo "    $ sudo iptables -t nat -A PREROUTING -i eth0 -p tcp --dport 8000 -j DNAT --to-destination ${LXC_IP}:80"
