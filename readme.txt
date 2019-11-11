=== Paid Memberships Pro - Download Monitor Integration Add On ===
Contributors: strangerstudios
Tags: paid memberships pro, pmpro, membership, memberships, download monitor, restrict downloads
Requires at least: 3.5
Tested up to: 4.9.2
Stable tag: .2.2

Require membership for downloads when using the Download Monitor plugin.

== Description ==

The Download Monitor Integration Add On for Paid Memberships Pro adds a "Require Membership" meta box to the "Edit Download" page, allowing you to easily toggle the membership level(s) that can access the download. 

When using the [download] shortcode, you can now use the templates: "pmpro", "pmpro_box", "pmpro_button", "pmpro_filename", "pmpro_title" to show the non-member a link to the membership levels page and a list of the levels that are required to download the file.

Requires Download Monitor (https://wordpress.org/plugins/download-monitor/) and Paid Memberships Pro installed and activated.

= Official Paid Memberships Pro Add On =

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

When using the [download] shortcode you can optionally specify the appropriate "pmpro" template. Available templates include: "pmpro", "pmpro_box", "pmpro_button", "pmpro_filename", "pmpro_title".

If you do not specify a template, the output of the [download] shortcode can be filtered for a non-member by using the filter: pmprodlm_shortcode_download_content_filter. This will alter the message shown to a visitor that is not logged in or a logged in user that doesn't meet membership requirements.

== Changelog ==
= .2.2 =
* BUG FIX: Fixing issue where the download was not found if no template was specified.
* ENHANCEMENT: Adding filter 'pmprodlm_shortcode_download_show_membership_required_filter' to optionally hide the "Membership Required" portion of the download shortcode output.

= .2.1 =
* BUG FIX: Using the get_id() method to get the id of downloads now that the ->id property is private.

= .2 =
* ENHANCEMENT/FIX: Would show ':' if membership was required but level was inaccessible (signup allowed = no)

= .1 =
* Added unique templates for the output of the various download/downloads shortcodes.
* Added pmprodlm_shortcode_download_content_filter filter for non-member download shortcode output.
* Initial release.
