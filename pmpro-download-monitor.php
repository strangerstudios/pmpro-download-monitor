<?php
/**
 * Plugin Name: Paid Memberships Pro - Download Monitor Integration Add On
 * Plugin URI: https://www.paidmembershipspro.com/add-ons/pmpro-download-monitor/
 * Description: Require membership for downloads when using the Download Monitor plugin.
 * Version: 1.0.1
 * Author: Paid Memberships Pro
 * Author URI: https://www.paidmembershipspro.com
 * Text Domain: pmpro-download-monitor
 * Domain Path: /languages
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add the dlm_download CPT to the list of PMPro restrictable post types.
 *
 * @since 1.0
 *
 * @param array $post_types Array of post types that PMPro can restrict.
 * @return array Modified array of restrictable post types.
 */
function pmprodlm_restrictable_post_types( $post_types ) {
	$post_types[] = 'dlm_download';
	return array_unique( $post_types );
}
add_filter( 'pmpro_restrictable_post_types', 'pmprodlm_restrictable_post_types' );

/**
 * Get the membership level IDs and names required for a download.
 *
 * Filters out levels that do not allow signups unless overridden
 * by the pmpro_membership_content_filter_disallowed_levels filter.
 *
 * @since 0.1
 *
 * @param DLM_Download $dlm_download The Download Monitor download object.
 * @return array {
 *     Array with two elements.
 *
 *     @type int[]  $0 Array of membership level IDs.
 *     @type string $1 Human-readable level names joined with "or".
 * }
 */
function pmprodlm_getDownloadLevels($dlm_download)
{
	$hasaccess = pmpro_has_membership_access($dlm_download->get_id(), NULL, true);
	if(is_array($hasaccess))
	{
		//returned an array to give us the membership level values
		$download_membership_levels_ids = $hasaccess[1];
		$download_membership_levels_names = $hasaccess[2];
		$hasaccess = $hasaccess[0];
	}
	if(empty($download_membership_levels_ids))
		$download_membership_levels_ids = array();
	if(empty($download_membership_levels_names))
		$download_membership_levels_names = array();

	 //hide levels which don't allow signups by default
	if(!apply_filters("pmpro_membership_content_filter_disallowed_levels", false, $download_membership_levels_ids, $download_membership_levels_names))
	{
			foreach($download_membership_levels_ids as $key=>$id)
			{
				//does this level allow registrations?
				$level_obj = pmpro_getLevel($id);
				if(empty($level_obj) || !$level_obj->allow_signups)
				{
					unset($download_membership_levels_ids[$key]);
					unset($download_membership_levels_names[$key]);
				}
			}
	}

	$download_membership_levels_names = pmpro_implodeToEnglish($download_membership_levels_names, 'or');
	
	return array($download_membership_levels_ids, $download_membership_levels_names);
}

/**
 * Check if the current user has membership access to a download.
 *
 * Hooked to dlm_can_download to block direct download access
 * for users without the required membership level.
 *
 * @since 0.1
 *
 * @param bool         $can_download Whether the user can download the file.
 * @param DLM_Download $download     The Download Monitor download object.
 * @return bool Whether the user can download the file.
 */
function pmprodlm_can_download( $can_download, $download ) {
	if ( !$can_download ) {
		return $can_download;
	}

	if ( function_exists( 'pmpro_hasMembershipLevel' ) ) {
		$download_id = ( is_object( $download ) && method_exists( $download, 'get_id' ) ) ? $download->get_id() : 0;
		if ( empty( $download_id ) ) {
			return $can_download;
		}

		//check for membership
		if ( !pmpro_has_membership_access($download_id) ) {
			$can_download = false;
		}
	}
	return $can_download;
}
add_filter( 'dlm_can_download', 'pmprodlm_can_download', 10, 2 );

/**
 * Swap DLM download templates for membership-restricted versions.
 *
 * When a download requires membership and the current user does not have access,
 * this replaces the standard DLM template with a style-matched restricted template.
 * Supports default, title, box, button, and filename template variants.
 *
 * Legacy pmpro_* template names are normalized and trigger a _doing_it_wrong notice.
 *
 * @since 0.1
 *
 * @param string $template The path to the template file.
 * @param string $slug     The template slug (e.g. 'content-download').
 * @param string $name     The template name/variant (e.g. 'box', 'button').
 * @param array  $args     Template arguments including the download object.
 * @return string The path to the original or restricted template file.
 */
function pmprodlm_dlm_get_template_part( $template, $slug, $name, $args = array() ) {
	// Only handle download content templates, not no-access, tc-form, etc.
	if ( 'content-download' !== $slug ) {
		return $template;
	}

	// Only proceed if PMPro is active and we have a download to check.
	if ( ! function_exists( 'pmpro_hasMembershipLevel' ) ) {
		return $template;
	}

	// DLM passes the download as either 'dlm_download' or 'download' depending on the caller.
	$dlm_download = isset( $args['dlm_download'] ) ? $args['dlm_download'] : ( isset( $args['download'] ) ? $args['download'] : null );
	if ( empty( $dlm_download ) || ! is_object( $dlm_download ) || ! method_exists( $dlm_download, 'get_id' ) ) {
		return $template;
	}

	// Normalize name: strip pmpro_ prefix, treat plain 'pmpro' as default.
	$variant = str_replace( 'pmpro_', '', $name );
	if ( 'pmpro' === $variant ) {
		$variant = '';
	}

	// Flag misuse of internal pmpro_ template names in shortcode/block attributes.
	if ( $name !== $variant ) {
		$suggested = ! empty( $variant ) ? 'template="' . $variant . '"' : 'the default template';
		_doing_it_wrong(
			'[download] template attribute',
			sprintf(
				/* translators: 1: old template name, 2: suggested replacement */
				esc_html__( 'The "%1$s" template should no longer be used directly. Use %2$s instead.', 'pmpro-download-monitor' ),
				esc_html( $name ),
				esc_html( $suggested )
			),
			'1.0'
		);
	}

	// If user has access, return the DLM template (corrected for deprecated names).
	if ( pmpro_has_membership_access( $dlm_download->get_id() ) ) {
		// If the name was normalized from a deprecated pmpro_* name, DLM will have
		// resolved the wrong template file. Re-resolve using the corrected variant.
		if ( $name !== $variant ) {
			$template_file = 'content-download' . ( ! empty( $variant ) ? '-' . $variant : '' ) . '.php';
			$theme_template = locate_template( 'download-monitor/' . $template_file );
			if ( ! empty( $theme_template ) ) {
				$template = $theme_template;
			} elseif ( defined( 'DLM_PLUGIN_FILE' ) && file_exists( plugin_dir_path( DLM_PLUGIN_FILE ) . 'templates/' . $template_file ) ) {
				$template = plugin_dir_path( DLM_PLUGIN_FILE ) . 'templates/' . $template_file;
			}
		}
		return $template;
	}

	// User doesn't have access. Swap to a style-matched restricted template.
	$pmpro_templates_dir = trailingslashit( dirname( __FILE__ ) ) . 'templates/';

	// Look for a style-matched restricted template, fall back to default.
	if ( ! empty( $variant ) && file_exists( $pmpro_templates_dir . 'content-download-pmpro_' . $variant . '.php' ) ) {
		$template = $pmpro_templates_dir . 'content-download-pmpro_' . $variant . '.php';
	} else {
		$template = $pmpro_templates_dir . 'content-download-pmpro.php';
	}

	return $template;
}
add_filter( 'dlm_get_template_part', 'pmprodlm_dlm_get_template_part', 10, 4 );

/**
 * Display the PMPro no-access message on the DLM no-access page.
 *
 * @since 0.1
 *
 * @param DLM_Download $download The Download Monitor download object.
 */
function pmprodlm_dlm_no_access_after_message($download) {
	// Make sure PMPro v3.1+ is installed.
	if ( ! function_exists( 'pmpro_get_no_access_message' ) ) {
		return;
	}

	// Check if the user has access to this download.
	$hasaccess = pmpro_has_membership_access($download->get_id(), NULL, true);
	if ( ! is_array( $hasaccess ) || ! empty( $hasaccess[0] ) ) {
		// Invalid $hasaccess or the user has access to this post.
		return;
	}

	// The user does not have access to this post. Show the "no access" message.
	echo wp_kses_post( pmpro_get_no_access_message( '', $hasaccess[1], $hasaccess[2] ) );
}
add_action('dlm_no_access_after_message', 'pmprodlm_dlm_no_access_after_message', 10, 2);

/**
 * Add documentation and support links to the plugin row meta.
 *
 * @since 0.1
 *
 * @param array  $links Array of existing plugin row meta links.
 * @param string $file  Path to the plugin file relative to the plugins directory.
 * @return array Modified array of plugin row meta links.
 */
function pmprodlm_plugin_row_meta($links, $file) {
	if(strpos($file, 'pmpro-download-monitor.php') !== false)
	{
		$new_links = array(
			'<a href="' . esc_url('https://www.paidmembershipspro.com/add-ons/pmpro-download-monitor/')  . '" title="' . esc_attr__( 'View Documentation', 'pmpro-download-monitor' ) . '">' . esc_html__( 'Docs', 'pmpro-download-monitor' ) . '</a>',
			'<a href="' . esc_url('https://www.paidmembershipspro.com/support/') . '" title="' . esc_attr__( 'Visit Customer Support Forum', 'pmpro-download-monitor' ) . '">' . esc_html__( 'Support', 'pmpro-download-monitor' ) . '</a>',
		);
		$links = array_merge($links, $new_links);
	}
	return $links;
}
add_filter('plugin_row_meta', 'pmprodlm_plugin_row_meta', 10, 2);
