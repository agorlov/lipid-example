# Personal Notes

example appilcation

Example of application based on ``agorlov/lipid`` lib.
With template engine ``Twig``.


### Features

- login/logout
- add note
- remove note
- upload image
- upload file
- search note

### Installation

1. Create database ``lipidexample``
2. Grant access:
```sql
GRANT ALL PRIVILEGES ON lipidexample.* TO lipidexample@'%' IDENTIFIED BY 'lipidexample';
```
2. Init it with ``example.sql`` data
3. Update ``creds.php`` (if they differ from default)
4. Start app: ``$ composer serv``
