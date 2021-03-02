#### v 2.0.1 / 2020-09-29
* FIXED: Fix missing semicolon

#### v 2.0.0 / 2020-09-29
* REMOVED: Google Search Console feature has been removed completely. See #112 for discussion. (#118)
* UPDATED: Replaced references to "WooCommerce" with "Classic Commerce" (#114) (props @simplycomputing)
* UPDATED: Update readme (#126)
* IMPROVED Minor CSS tweaks and accessibility improvements (#128)
* FIXED: Fix wrong field name in redirections cache (#119)
* FIXED: Fix display of CPSEO icon in admin menu (#123)
* FIXED: Fix display of icon and heading in CPSEO dashboard (#124)
* FIXED: Cleaned up some left over code (#122, #127)

#### v 1.0.0 / 2020-08-30
* FIXED: Fix for misaligned Classic SEO icon in top admin toolbar when viewing site #103
* FIXED: Remove empty title separator #104
* REMOVED: Remove legacy Rank Math help images #106
* UPDATED: Update language pot file #107
* UPDATED: Minor updates to help pages #108
* FIXED: Replace "ClassicPress-research" with "ClassicPress-plugins" #109

#### v 1.0.0-rc.1 / 2020-08-02
* IMPROVED: Clarify wording on Author Archives and Date Archives option settings (#85)
* IMPROVED: Add clear installation instructions to readme (#89) (props @nylen)
* REMOVED: Auto Update setting removed. This was a legacy setting from RM and hasd no effect in CPSEO (#86)
* FIXED: Corrected misaligned CPSEO icon in top admin bar and left-hand nav menu (#91)
* FIXED: Fix Search Console stats database query syntax which caused “Unknown column” error in some installations (#97) (props @nylen)
* FIXED: Fix a PHP notice (Undefined index: path) in Conditional::is_rest() (#98) (props @nylen)
* FIXED: Fix for Search Console jQuery error which prevented SC cache from being cleared in General Settings (#94)
* FIXED: Excludes .gitignore and .gitattributes release builds via git archive (#96) (props @nylen)
* FIXED: Clean up .gitignore (#92) (props @nylen)
* FIXED: Removes extraneous '%s' placeholder on Search Console settings page (#100)
* UPDATED: Minor CSS tweaks (#95), (#99)

#### v 1.0.0-beta4 / 2020-05-24
* NEW: Add new title separator characters (#71)
* NEW: Add '%' to CTR heading in Search Analytics view within Search Console (#72)
* NEW: Use native custom post types dashboard icon in Titles & Meta Setting (#75)
* IMPROVED: Output of SEO meta for pages created with page builders (#74)
* IMPROVED: Hide cpseo meta key data in custom fields on post edit page (#76)
* UPDATED: Help page content updated (#78)
* UPDATED: Translations (#79)
* FIXED: Dash menu not updating when module activated (#77)
* FIXED: Missing cpseo prefix in some settings (#81)
* FIXED: Removed broken link from title on Dashboard page (#73)

#### v 1.0.0-beta3 / 2020-04-03
* NEW: Add new Events schema properties to help with events that have been cancelled or postponed as a result of Covid-19
* UPDATED: Code Potent Update Manager updated to latest version

#### v 1.0.0-beta2 / 2020-02-14
* NEW: Add usage tracking notice and opt out (props xxsimoxx)
* FIX: Variables translations in help pages will now translate
* FIX: Fix for duplicate "view details" when GitHub updater is active (props xxsimoxx)
* FIX: Fix for PHP error when refreshing sitemap in Search Console

#### v 1.0.0-beta1 / 2020-02-08
* NEW: Integrated Code Potent's Update Manager
* NEW: Check for WP_CLI (props xxsimoxx)
* IMPROVED: Services rich snippets (added missing properties)
* Moved to new ClassicPress-plugins GitHub repository
* Renamed dashicon image
* Remove other superfluous images
* Translation file updates

#### v 0.7.0 / 2020-01-17
* NEW: Adds importer for The SEO Framework

#### v 0.6.0 / 2019-12-28
* UPDATED: Now fully supports [Classic Commerce](https://github.com/ClassicPress-plugins/classic-commerce)

#### v 0.5.3 / 2019-12-21
* FIX: Fix for wrong message shown on post edit page when content length was between 500 and 600 words
* TWEAK: Minor tweaks to AIOSEO import

#### v 0.5.2 / 2019-12-20
* FIX: Fix for "Undefined variable: sitemap_settings" in class-aioseo.php

#### v 0.5.1 / 2019-12-20
* IMPROVED: All In One SEO import

#### v 0.5.0 / 2019-12-18
* NEW: Added All In One SEO import
* IMPROVED: Yoast import

#### v 0.4.4 / 2019-12-06
* FIX: Second fix for issue #24 - keywords from Rank Math not being imported

#### v 0.4.3 / 2019-12-05
* FIX: Fix for issue #24 - import from Rank Math not working as expected

#### v 0.4.2 / 2019-11-29
* NEW: Added charts on Search Console overview page
* FIX: minor bug fixes

#### v 0.4.1 / 2019-11-28
* FIX: Fix for PHP error: "Fatal error : Cannot redeclare Classic_SEO::define_constants() in /home/xxx/public_html/xxx/xxx/wp-content/plugins/classicpress-seo/classicpress-seo.php on line 223"

#### v 0.4.0 / 2019-11-28
* NEW: Added support for Google Search Console

#### v 0.3.3 / 2019-11-25
* FIX: Fix for #20: Plugin is not automatically deactivated when using PHP < 7.0.

#### v 0.3.2 / 2019-11-20
* FIX: Fix for #17: PHP error "call_user_func_array() expects parameter 1 to be a valid callback, function on_login not found or invalid function name" 

#### v 0.3.1 / 2019-11-18
* FIX: Classic SEO icon size in admin menu

#### v 0.3.0 / 2019-11-18
* NEW: Plugin has new name. Now called Classic SEO as per [discussions on forums](https://forums.classicpress.net/t/plugin-theme-naming-conventions-when-to-use-classicpress-and-or-cp/1653/8). Note: plugin directory is still called "classicpress-seo" and the main PHP file is called "classicpress-seo.php". These will be changed in a future version.
* NEW: Added ability to set metabox position on post/page/product edit pages
* NEW: Added support for Pinterest meta tag
* NEW: Added "nosnippet", "max-snippet:\[number\]", "max-video-preview:\[number\]" and "max-image-preview:\[setting\]" Advanced Robots Meta settings. See https://webmasters.googleblog.com/2019/09/more-controls-on-search.html
* UPDATED: Schema Types updated including removal of "self-serving" review snippets
* Updated the Help page
* Plugin icon updated

#### v 0.2.2 / 2019-10-31
* Fixed CSS bug which prevented tab icons from displaying correctly on post edit page in admin
* Fixed bug that disabled the sitemap "Include Featured Images" feature
* Improved l8n checks
* Removed a couple of debug messages from assessor.js (forgot they were still there!)
* Removed a couple of unused files

#### v 0.2.1 / 2019-10-27)
* Fixed issue with breadcrumbs not displaying

#### v 0.2.0 / 2019-10-26
* Initial public release
