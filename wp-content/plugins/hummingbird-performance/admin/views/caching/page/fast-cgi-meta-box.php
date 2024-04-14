<?php
/**
 * FastCGI caching meta box.
 *
 * @package Hummingbird
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<p><?php esc_html_e( 'Hummingbird stores static HTML copies of your pages and posts to decrease page load time.', 'wphb' ); ?></p>

<?php
$notice = esc_html__( "Hummingbird has detected that you have static server cache enabled on your server, so Hummingbird's page caching has been disabled. You will still be able to clear cache on page/post updates within Hummingbird.", 'wphb' );
$this->admin_notices->show_inline( $notice, 'info' );
