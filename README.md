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

## Pull requests

We accept pull requests for this website via GitHub. Many of the guidelines at https://github.com/ClassicPress/ClassicPress/blob/develop/.github/CONTRIBUTING.md also apply here, for example:

 - Please be sure to test your PR on a local development installation as described above, or at least using the browser development tools.
 - Please include screenshots of the site before and after your changes as well as an explanation of what has been changed and why.
 - Some parts of the above guidelines are specific to ClassicPress core and do not apply here, like the instructions for backporting changes from WP and running the automated tests.

Please always use a **separate branch** for each change, to avoid issues with your `master` branch not being reset or updated correctly in between different pull requests. If you're not sure how to do this, you can use the GitHub web interface to edit files, open a new branch, and propose your changes as a pull request.

## CSS/JS changes

If you make any CSS or JS changes, you need to update version number in the functions.php to clear cache:

```
function cp_susty_get_asset_version() {
    return '20210919';
}
```

Use the date when the changes were made as the version number in the format `YYYYMMDD`. If there are multiple changes in one day, you can add a decimal to increment the version number (ex: `20210919.1`, `20200919.2`).