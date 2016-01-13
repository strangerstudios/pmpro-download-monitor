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
			<p><a class="aligncenter download-button" href="<?php 
				if(count($download_membership_levels[0]) > 1)
					echo pmpro_url('levels');
				else
					echo pmpro_url("checkout", "?level=" . $download_membership_levels[0][0], "https");
			?>">
				<?php printf( __( 'Download &ldquo;%s&rdquo;', 'download-monitor' ), $dlm_download->get_the_title() ); ?>
				<small><?php _e('Membership Required','pmprodlm'); ?>: <?php echo $download_membership_levels[1]; ?></small>
			</a></p>
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
		<p><a class="aligncenter download-button" href="<?php $dlm_download->the_download_link(); ?>" rel="nofollow">
			<?php printf( __( 'Download &ldquo;%s&rdquo;', 'download-monitor' ), $dlm_download->get_the_title() ); ?>
			<small><?php $dlm_download->the_filename(); ?> &ndash; <?php printf( _n( 'Downloaded 1 time', 'Downloaded %d times', $dlm_download->get_the_download_count(), 'download-monitor' ), $dlm_download->get_the_download_count() ) ?> &ndash; <?php $dlm_download->the_filesize(); ?></small>
		</a></p>
		<?php
	}
}