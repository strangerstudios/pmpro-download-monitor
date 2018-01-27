<?php
/**
 * PMPro custom template output for a download via the [download] shortcode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
global $current_user;
if ( function_exists( 'pmpro_hasMembershipLevel' ) ) {
	if ( !pmpro_has_membership_access( $dlm_download->get_id() ) ) 
	{
		$download_membership_levels = pmprodlm_getDownloadLevels($dlm_download);

		if ( $dlm_download->exists() ) {
			?>
			<aside class="download-box">
				<?php $dlm_download->the_image(); ?>	
				<div
					class="download-count"><?php printf( _n( '1 download', '%d downloads', $dlm_download->get_the_download_count(), 'download-monitor' ), $dlm_download->get_the_download_count() ) ?></div>
			
				<div class="download-box-content">
			
					<h1><?php $dlm_download->the_title(); ?></h1>
			
					<?php $dlm_download->the_short_description(); ?>
			
					<a class="download-button" href="<?php 
						if(count($download_membership_levels[0]) > 1)
							echo pmpro_url('levels');
						else
							echo pmpro_url("checkout", "?level=" . $download_membership_levels[0][0], "https");
					?>">
						<?php _e('Membership Required','pmprodlm'); ?>
						<small><?php echo $download_membership_levels[1]; ?></small>
					</a>
				</div>
			</aside>
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
		<aside class="download-box">
			<?php $dlm_download->the_image(); ?>	
			<div
				class="download-count"><?php printf( _n( '1 download', '%d downloads', $dlm_download->get_the_download_count(), 'download-monitor' ), $dlm_download->get_the_download_count() ) ?></div>
		
			<div class="download-box-content">
		
				<h1><?php $dlm_download->the_title(); ?></h1>
		
				<?php $dlm_download->the_short_description(); ?>
		
				<a class="download-button" title="<?php if ( $dlm_download->has_version_number() ) {
					printf( __( 'Version %s', 'download-monitor' ), $dlm_download->get_the_version_number() );
				} ?>" href="<?php $dlm_download->the_download_link(); ?>" rel="nofollow">
					<?php _e( 'Download File', 'download-monitor' ); ?>
					<small><?php $dlm_download->the_filename(); ?> &ndash; <?php $dlm_download->the_filesize(); ?></small>
				</a>
		
			</div>
		</aside>
		<?php
	}
}