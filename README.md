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

## Pull Requests

* If you only have one PR open at a time then you can use the master branch on your fork.
* If you want to have multiple PRs open at once (which sometimes happens without being planned for) then each one will need its own branch.
* Best practice is to use a separate branch for each change but if you are only doing one change at a time it doesn't matter much.
* Make sure to reset to `upstream/master` or the "latest official" code before starting a branch or PR.

## CSS/JS Changes

If you make any CSS or JS changes, you need to update version number in the functions.php to clear cache:

```
function cp_susty_get_asset_version() {
    return '20200917.1';
}
```

Use the date when the changes were made as the version number in the format `YYYYMMDD`. If there are multiple changes in one day, increment decimal version number (ex: 20200917.1, 20200917.2).