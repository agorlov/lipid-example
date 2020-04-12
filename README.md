# Personal Notes

example appilcation

Example of application based on ``agorlov/lipid`` lib.
With template engine ``Twig``.


### Features

- login/logout
- add/edit/view note
- remove note
- paste image to attach it to note (upload it)
- upload file
- search note
- @todo #8 notes list with pages


### Installation

1. Create database ``lipidexample``
2. Grant access:
```sql
GRANT ALL PRIVILEGES ON example.* TO example@'%' IDENTIFIED BY 'example';
```
2. Init it with ``example.sql`` data
3. Update ``creds.php`` (if they differ from default)
4. Start app: ``$ composer serv``
