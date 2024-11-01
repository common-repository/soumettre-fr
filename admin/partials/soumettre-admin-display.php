<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://soumettre.fr/
 * @since      1.0.0
 *
 * @package    Soumettre
 * @subpackage Soumettre/admin/partials
 */
?>

<?php
$soumettre_nonce = wp_create_nonce();
set_transient( 'soumettre_nonce', $soumettre_nonce, HOUR_IN_SECONDS );
$soumettre_url = "https://soumettre.fr";

if ( true === SOUMETTRE_DEBUG ) {
	$soumettre_url = "http://soumettre.test";
}

$soumettre_api_key    = get_option( 'soumettre_api_key', '' );
$soumettre_api_secret = get_option( 'soumettre_api_secret', '' );
$soumettre_spot_id    = get_option( 'soumettre_spot_id', '' );
$soumettre_email      = get_option( 'soumettre_email', '' );
?>

<div class="wrap">
    <div class="soumettre-logo">
        <img src="<?php echo SOUMETTRE_IMAGES; ?>logo-soumettre-450x129.png"
             alt="<?php _ex( 'Soumettre.fr logo', 'Image alt on logo', 'soumettrefr' ); ?>" width="360px"
             height="103px">
    </div>

    <div class="soumettre-settings-container">
        <div class="soumettre-settings">
            <h1><?php _e( "Plugin settings", 'soumettrefr' ) ?></h1>
            <h2><?php _e( "Soumettre.fr gateway", 'soumettrefr' ); ?></h2>
			<?php if ( '' === $soumettre_api_key || '' === $soumettre_api_secret ) : ?>
                <div class="soumettre-conntected soumettre-block soumettre-error">
                    <h2>
                        <span class="dashicons dashicons-admin-plugins"></span> <?php _e( 'Connect your website now', 'soumettrefr' ); ?>
                    </h2>
                    <p><?php _e( "Your are not connected with Soumettre.fr API yet. You must already have an account on our website.", 'soumettrefr' ); ?></p>

                    <form method="get" name="soumettre-connect" action="<?php echo $soumettre_url; ?>/remote/auth">
                        <input type="hidden" name="site" value="<?php echo get_home_url(); ?>">
                        <input type="hidden" name="site_api_path"
                               value="<?php echo get_rest_url() . 'soumettre/v' . SOUMETTRE_REST_API_VERSION . '/'; ?>">
                        <input type="hidden" name="redirect_url"
                               value="<?php echo admin_url( 'admin.php?page=soumettre-options' ); ?>">
                        <input type="hidden" name="plugin_version" value="<?php echo SOUMETTRE_VERSION ?>">
                        <input type="hidden" name="nonce" value="<?php echo $soumettre_nonce; ?>">
						<?php submit_button( __( 'Connect my website to Soumettre.fr', 'soumettrefr' ) ); ?>
                    </form>
                </div>
			<?php else : ?>
                <div class="soumettre-connetected soumettre-block">
                    <h2>
                        <span class="dashicons dashicons-plugins-checked"></span> <?php _e( 'Your website is connected with Soumettre.fr', 'soumettrefr' ); ?>
                    </h2>
                    <ul>
						<?php if ( '' !== $soumettre_email ): ?>
                        <li>
                            <strong><?php _e( 'Email:', 'soumettrefr' ); ?></strong>
							<?php echo $soumettre_email; ?>
                        <li>
                        <?php else: ?>
                        <li>
                            <strong><?php _e( 'API key:', 'soumettrefr' ); ?></strong>
		                    <?php echo $soumettre_api_key; ?>
                        <li>

                        <li>
                            <strong><?php _e( 'API secret:', 'soumettrefr' ); ?></strong>
		                    <?php echo $soumettre_api_secret; ?>
                        <li>
                        <?php endif; ?>

                        <li>
                            <strong><?php _e( 'ID:', 'soumettrefr' ); ?></strong>
							<?php echo $soumettre_spot_id; ?>
                        <li>

                    </ul>
                    <p><a href="<?php echo esc_url( admin_url( 'admin-post.php?action=disconnect_soumettre' ) ); ?>"
                          class="soumettre-remove-gateway-link"><?php _e( 'disconnect', 'soumettrefr' ); ?></a></p>
                </div>
			<?php endif; ?>
            <form method="post" action="options.php" id="<?php echo $this->plugin_name ?>-form">
				<?php
				settings_fields( $this->plugin_name );
				do_settings_sections( $this->plugin_name );
				submit_button();
				?>
            </form>
        </div>

        <aside class="soumettre-settings-aside">
            <div class="soumettre-block commissions">
				<?php
				$soumettre_commission = get_option( 'soumettre_commission' );
				if ( false == ! $soumettre_commission && is_array( $soumettre_commission ) ) :
					?>
                    <h2><span class="dashicons dashicons-megaphone"></span> <?php _e( "Commissions", 'soumettrefr' ); ?>
                    </h2>
                    <ul>
						<?php if ( isset( $soumettre_commission['soumettre'] ) && $soumettre_commission['soumettre'] ) : ?>
                            <li><?php _e( "Your commission: ", 'soumettrefr' ); ?>
                                <span class="soumettre-money-amount">
                                    <?php echo number_format_i18n( floatval( $soumettre_commission['soumettre'] ), 2 ); ?>&nbsp;€
                                </span>
                            </li>
						<?php endif; ?>

						<?php if ( isset( $soumettre_commission['catalog'] ) && $soumettre_commission['catalog'] ) : ?>
                            <li><?php _e( "Catalog commission: ", 'soumettrefr' ); ?>
                                <span class="soumettre-money-amount">
                                    <?php echo number_format_i18n( floatval( $soumettre_commission['catalog'] ), 2 ); ?>&nbsp;€
                                </span>
                            </li>
						<?php else : ?>
                            <li>
								<?php
								/* translators: %s: Link url */
								printf( __( "You have not added your website to our catalog yet. <a href='%s' target='_blank'>Click here to add it now</a>.", 'soumettrefr' ), "{$soumettre_url}/user/website/" . get_option( 'soumettre_spot_id' ) );
								?>
                            </li>
						<?php endif; ?>
                    </ul>
				<?php else : ?>
                    <h2>
                        <span class="dashicons dashicons-megaphone"></span> <?php _e( "State of the website", 'soumettrefr' ); ?>
                    </h2>
                    <p><?php _e( "Waiting for validation", 'soumettrefr' ); ?></p>
				<?php endif; ?>
            </div>

            <div class="soumettre-block">
                <h2><span class="dashicons dashicons-admin-links"></span> <?php _e( 'Useful links', 'soumettrefr' ); ?>
                </h2>
                <ul class="links">
                    <li><a href="https://soumettre.fr/user/dashboard" target="_blank" class="external-link"
                           rel="noopener"><?php _e( 'My Soumettre.fr account', 'soumettrefr' ); ?></a></li>
                    <li><a href="https://soumettre.fr/user/api-token" target="_blank" class="external-link"
                           rel="noopener"><?php _e( 'My API keys', 'soumettrefr' ); ?></a></li>
					<?php if ( $soumettre_spot_id ) : ?>
                        <li>
                            <a href="<?php echo esc_url( "https://soumettre.fr/user/partner/website/$soumettre_spot_id" ); ?>"
                               target="_blank" class="external-link"
                               rel="noopener"><?php _e( 'Manage my website', 'soumettrefr' ); ?></a></li>
					<?php endif; ?>
                </ul>
            </div>
        </aside>

    </div>

</div>
