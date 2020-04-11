<?php

return [
    'dbhost' => '127.0.0.1',
    'dbname' => 'example',
    'dbuser' => 'example',
    'dbpass' => 'example',

    // used for login to app
    // to generate password, run in command line:
    // php -r "echo password_hash('your password', PASSWORD_BCRYPT) . PHP_EOL;";
    // @todo #31 ask for password during lxc creaation
    'secret' => '$2y$10$3UGy985/CCHtS.SKqpjP0u3KAjsU/zpzK7cYACuD2Su5KM2BAFjd2'
];
