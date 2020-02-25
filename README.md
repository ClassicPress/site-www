# www.classicpress.net

This repository contains the files for https://www.classicpress.net/.

## Local development

Download the site files and contact a site administrator (probably James) for a
recent database dump.

Load the database dump into a MySQL database on your computer.  The site is
configured using a `.env` file, so you can copy `.env.example` to `.env` and
fill in your database values there.

You will need to
[install `composer`](https://getcomposer.org/download/)
and run `composer install` in order to download the library that makes the
`.env` file work properly.

Then update the site URL, for example using
[WP-CLI](https://wp-cli.org/):

```
wp search-replace https://www.classicpress.net http://www.classicpress.local
```

Finally add a local administrative user, for example:

```
wp user create admin admin@local.host --role=administrator --user_pass=changeme
```
