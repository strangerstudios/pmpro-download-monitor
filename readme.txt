=== Paid Memberships Pro - Download Monitor Integration Add On ===
Contributors: strangerstudios, paidmembershipspro
Tags: paid memberships pro, pmpro, membership, memberships, download monitor, restrict downloads
Requires at least: 5.4
Tested up to: 7.1
Requires PHP: 5.6
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Require membership for downloads when using the Download Monitor plugin.

== Description ==

The Download Monitor Integration Add On for Paid Memberships Pro adds a "Require Membership" meta box to the "Edit Download" page, allowing you to easily toggle the membership level(s) that can access the download.

Requires Download Monitor (https://wordpress.org/plugins/download-monitor/) and Paid Memberships Pro installed and activated.

== Official Paid Memberships Pro Add On ==

This is an official Add On for [Paid Memberships Pro](https://www.paidmembershipspro.com), the most complete member management and membership subscriptions plugin for WordPress.

== Installation ==

= Prerequisites =
1. You must have Paid Memberships Pro and Download Monitor installed and activated on your site.

= Download, Install and Activate! =
1. Download the latest version of the plugin.
1. Unzip the downloaded file to your computer.
1. Upload the /pmpro-download-monitor/ directory to the /wp-content/plugins/ directory of your site.
1. Activate the plugin through the 'Plugins' menu in WordPress.

= How to Use =

1. After activation, navigate to "Downloads" to Edit or Add a New Download.
1. Check the box for each level that can access this download in the "Require Membership" meta box (below the Publish box in the right sidebar). 
1. Save your changes by clicking the "Update" button (or "Publish" if you are creating a new download).

== Changelog ==
= 1.0.1 - 2026-09-29 =
* SECURITY: Improved sanitization and escaping throughout the plugin to resolve Plugin Check security findings. #13 (@dparker1005)

= 1.0 - 2026-02-25 =
* BUG FIX/ENHANCEMENT: Download Monitor blocks now correctly filter output for restricted downloads, showing restricted templates instead of download links for non-members. #12 (@dparker1005)
* BUG FIX/ENHANCEMENT: Overhauled the template system for membership-restricted downloads. Templates now only render the restricted state and match Download Monitor v4.9.6 patterns including `DLM_Utils` attributes, Gutenberg `className` support, and `before/after` link hooks. #12 (@dparker1005)
* BUG FIX/ENHANCEMENT: Now using `pmpro_get_no_access_message()` for PMPro 3.1+ no-access messages on the Download Monitor no-access page. #10 (@dparker1005)
* BUG FIX/ENHANCEMENT: Now using the `pmpro_restrictable_post_types` filter for meta box registration instead of a custom init hook. #10 (@dparker1005)
* BUG FIX: Fixed `dlm_can_download` filter callback to match modern Download Monitor's filter signature (`$can_download`, `$download`) instead of the previous (`$download`, `$version`). #10 (@dparker1005)
* BUG FIX: Fixed handling when all required membership levels have signups disabled, now correctly linking to the levels page instead of building a broken checkout URL. #10 (@dparker1005)
* BUG FIX: Added null guard for deleted membership levels in `pmprodlm_getDownloadLevels` to prevent fatal errors. #10 (@dparker1005)
* BUG FIX: Updated plugin for localization. #11 (@dparker1005)
* BUG FIX: Added proper output escaping (`esc_html()`, `esc_url()`, `esc_html__()`, `esc_html_e()`, `esc_attr__()`) to all outputted strings. #11 (@dparker1005)
* BUG FIX: Updated deprecated `getOption` to `get_option`. #6 (@JarrydLong)
* DEPRECATED: This Add On now requires running PMPro v3.0+.
* DEPRECATED: The `pmprodlm_shortcode_download_content_filter` filter has been removed. The `dlm_get_template_part` filter now handles all download template filtering. #12 (@dparker1005)
* DEPRECATED: The `pmpro_*` template names (e.g., `template="pmpro_box"`) should no longer be used directly. Use standard Download Monitor template names (e.g., `template="box"`) instead. Using legacy names will trigger a `_doing_it_wrong` notice. #12 (@dparker1005)

= .2.1 =
* BUG FIX: Using the get_id() method to get the id of downloads now that the ->id property is private.

= .2 =
* ENHANCEMENT/FIX: Would show ':' if membership was required but level was inaccessible (signup allowed = no)

= .1 =
* Added unique templates for the output of the various download/downloads shortcodes.
* Added pmprodlm_shortcode_download_content_filter filter for non-member download shortcode output.
* Initial release.
