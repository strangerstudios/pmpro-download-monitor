<?php
/*
Plugin Name: Paid Memberships Pro - Download Monitor Integration Add On
Plugin URI: http://www.paidmembershipspro.com/pmpro-download-monitor/
Description: Require membership for downloads when using the Download Monitor plugin.
Version: .2.1
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
			if(!$level_obj->allow_signups)
			{
				unset($download_membership_levels_ids[$key]);
				unset($download_membership_levels_names[$key]);
			}
		}
	}

	$download_membership_levels_names = pmpro_implodeToEnglish($download_membership_levels_names, 'or');
	
	return array($download_membership_levels_ids, $download_membership_levels_names);
}

/*
 * Require Membership on the Download
*/
function pmprodlm_can_download( $download, $version ) {
	$download_id = $version->post->ID;
	if ( function_exists( 'pmpro_hasMembershipLevel' ) ) {
		//need to setup post global
		global $post;
		$post = get_post($download_id);

		//check for membership
		if ( !pmpro_has_membership_access($download_id) ) {
			$download = false;
		}
	}
	return $download;
}
add_filter( 'dlm_can_download', 'pmprodlm_can_download', 10, 2 );

function pmprodlm_dlm_get_template_part( $template, $slug, $name ) {	
	if($name == 'pmpro')
	{
		$template = trailingslashit( dirname(__FILE__) ) . "templates/content-download-pmpro.php";
	}
	elseif(strpos($name, "pmpro_") !== false)
	{
		$template = trailingslashit( dirname(__FILE__) ) . "templates/content-download-" . $name . ".php";
	}
	return $template;
}
add_filter('dlm_get_template_part', 'pmprodlm_dlm_get_template_part', 10, 3);

function pmprodlm_shortcode_download_content( $content, $download_id, $atts ) {
	global $current_user;
	if(empty($atts['template']) && (function_exists( 'pmpro_hasMembershipLevel' )) ) {
		if ( !pmpro_has_membership_access( $download_id ) ) 
		{
			$dlm_download = new DLM_Download( $download_id );
			if ( $dlm_download->exists() ) 
			{
				$download_membership_levels = pmprodlm_getDownloadLevels($dlm_download);	
				$content .= '<a href="';
				if(count($download_membership_levels[0]) > 1)
					$content .= pmpro_url('levels');
				else
					$content .= pmpro_url("checkout", "?level=" . $download_membership_levels[0][0], "https");
				$content .= '">' . $dlm_download->get_the_title() . '</a>';
				$content .= ' (' . __('Membership Required','pmprodlm') . ': ' . $download_membership_levels[1] . ')';
				$content = apply_filters("pmprodlm_shortcode_download_content_filter", $content);
			} 
			else 
			{
				$content = '[' . __( 'Download not found', 'download-monitor' ) . ']';
			}
		}
	}
	return $content;
}
add_filter('dlm_shortcode_download_content', 'pmprodlm_shortcode_download_content', 10, 3);

function pmprodlm_dlm_no_access_after_message($download) {
	global $current_user;
	if ( function_exists( 'pmpro_hasMembershipLevel' ) ) {
		if ( !pmpro_has_membership_access( $download->get_id() ) ) 
		{
			$hasaccess = pmpro_has_membership_access($download->get_id(), NULL, true);
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
		
			$pmpro_content_message_pre = '<div class="pmpro_content_message">';
			$pmpro_content_message_post = '</div>';
			$content = '';
			$sr_search = array("!!levels!!", "!!referrer!!");
			$sr_replace = array(pmpro_implodeToEnglish($post_membership_levels_names), urlencode(site_url($_SERVER['REQUEST_URI'])));
			//get the correct message to show at the bottom
			if($current_user->ID)
			{
				//not a member
				$newcontent = apply_filters("pmpro_non_member_text_filter", stripslashes(get_option("pmpro_nonmembertext")));
				$content .= $pmpro_content_message_pre . str_replace($sr_search, $sr_replace, $newcontent) . $pmpro_content_message_post;
			}
			else
			{
				//not logged in!
				$newcontent = apply_filters("pmpro_not_logged_in_text_filter", stripslashes(get_option("pmpro_notloggedintext")));
				$content .= $pmpro_content_message_pre . str_replace($sr_search, $sr_replace, $newcontent) . $pmpro_content_message_post;
			}
		}
	}
	echo $content;	
}
add_action('dlm_no_access_after_message', 'pmprodlm_dlm_no_access_after_message', 10, 2);

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