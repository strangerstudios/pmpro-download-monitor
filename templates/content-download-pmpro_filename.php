<?php
/**
 * PMPro custom template output for a download via the [download] shortcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
global $current_user;
if ( function_exists( 'pmpro_hasMembershipLevel' ) ) {
	if ( !pmpro_has_membership_access( $dlm_download->id ) ) 
	{
		$download_membership_levels = pmprodlm_getDownloadLevels($dlm_download);
		
		if ( $dlm_download->exists() ) {
			?>
			<a class="download-link filetype-icon <?php echo 'filetype-' . $dlm_download->get_the_filetype(); ?>" href="<?php 
				if(count($download_membership_levels[0]) > 1)
					echo pmpro_url('levels');
				else
					echo pmpro_url("checkout", "?level=" . $download_membership_levels[0][0], "https");
			?>"><?php $dlm_download->the_filename(); ?></a> 
			<?php _e('Membership Required','pmprodlm'); ?>: <?php echo $download_membership_levels[1]; ?>
			<?php	
		} 
		else 
		{
			?>
			[<?php _e( 'Download not found', 'download-monitor' ); ?>]
			<?php
		}
	}
	else
	{
		?>
		<a class="download-link filetype-icon <?php echo 'filetype-' . $dlm_download->get_the_filetype(); ?>"
		   title="<?php if ( $dlm_download->has_version_number() ) {
			   printf( __( 'Version %s', 'download-monitor' ), $dlm_download->get_the_version_number() );
		   } ?>" href="<?php $dlm_download->the_download_link(); ?>" rel="nofollow">
			<?php $dlm_download->the_filename(); ?>
			(<?php printf( _n( '1 download', '%d downloads', $dlm_download->get_the_download_count(), 'download-monitor' ), $dlm_download->get_the_download_count() ) ?>)
		</a>
		<?php
	}
}