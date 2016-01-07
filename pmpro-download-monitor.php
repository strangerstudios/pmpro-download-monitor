<?php
/*
Plugin Name: Paid Memberships Pro - Download Monitor Integration Add On
Plugin URI: http://www.paidmembershipspro.com/pmpro-download-monitor/
Description: Require membership for downloads when using the Download Monitor plugin.
Version: .1
Author: Stranger Studios
Author URI: http://www.strangerstudios.com

/*
 * Add Require Membership box to dlm_download CPT
 */
function pmprodlm_page_meta_wrapper() {
	add_meta_box( 'pmpro_page_meta', 'Require Membership', 'pmpro_page_meta', 'dlm_download', 'side' );
}
function pmprodlm_cpt_init() {
	if ( is_admin() )
		add_action( 'admin_menu', 'pmprodlm_page_meta_wrapper' );
}
add_action( "init", "pmprodlm_cpt_init", 20 );

/*
 * Require Membership on the Download
*/
function pmprodlm_can_download( $download, $version ) {
	if ( function_exists( 'pmpro_hasMembershipLevel' ) ) {
		if ( ! pmpro_has_membership_access( $version->post->ID ) ) {
			$download = false;
		}
	}
	return $download;
}
add_filter( 'dlm_can_download', 'pmprodlm_can_download', 10, 2 );

function pmprodlm_shortcode_download_content( $content, $download_id, $atts ) {
	global $current_user;
	if ( function_exists( 'pmpro_hasMembershipLevel' ) ) {
		if ( !pmpro_has_membership_access( $download_id ) ) 
		{
			$hasaccess = pmpro_has_membership_access($download_id, NULL, true);
			if(is_array($hasaccess))
			{
				//returned an array to give us the membership level values
				$post_membership_levels_ids = $hasaccess[1];
				$post_membership_levels_names = $hasaccess[2];
				$hasaccess = $hasaccess[0];
			}
			if(empty($post_membership_levels_ids))
				$post_membership_levels_ids = array();
			if(empty($post_membership_levels_names))
				$post_membership_levels_names = array();
		
			 //hide levels which don't allow signups by default
			if(!apply_filters("pmpro_membership_content_filter_disallowed_levels", false, $post_membership_levels_ids, $post_membership_levels_names))
			{
				foreach($post_membership_levels_ids as $key=>$id)
				{
					//does this level allow registrations?
					$level_obj = pmpro_getLevel($id);
					if(!$level_obj->allow_signups)
					{
						unset($post_membership_levels_ids[$key]);
						unset($post_membership_levels_names[$key]);
					}
				}
			}
		
			$post_membership_levels_names = pmpro_implodeToEnglish($post_membership_levels_names, 'or');
			
			$download = new DLM_Download( $download_id );
			if ( $download->exists() ) {
				$content .= '<a href="' . pmpro_url('levels') . '">' . $download->get_the_title() . '</a>';
				$content .= ' (' . __('Membership Required','pmprodlm') . ': ' . $post_membership_levels_names . ')';
				$content = apply_filters("pmprodlm_shortcode_download_content_filter", $content);
			} else {
				$content = '[' . __( 'Download not found', 'download-monitor' ) . ']';
			}
		}
	}
	return $content;
}
add_filter('dlm_shortcode_download_content', 'pmprodlm_shortcode_download_content', 10, 3);

/*
Function to add links to the plugin row meta
*/
function pmprodlm_plugin_row_meta($links, $file) {
	if(strpos($file, 'pmpro-download-monitor.php') !== false)
	{
		$new_links = array(
			'<a href="' . esc_url('http://www.paidmembershipspro.com/add-ons/plus-add-ons/pmpro-download-monitor/')  . '" title="' . esc_attr( __( 'View Documentation', 'pmpro' ) ) . '">' . __( 'Docs', 'pmpro' ) . '</a>',
			'<a href="' . esc_url('http://paidmembershipspro.com/support/') . '" title="' . esc_attr( __( 'Visit Customer Support Forum', 'pmpro' ) ) . '">' . __( 'Support', 'pmpro' ) . '</a>',
		);
		$links = array_merge($links, $new_links);
	}
	return $links;
}
add_filter('plugin_row_meta', 'pmprodlm_plugin_row_meta', 10, 2);