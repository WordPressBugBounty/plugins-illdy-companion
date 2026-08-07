<?php
/*
 * Plugin Name:       Illdy Companion
 * Plugin URI:        https://colorlib.com/wp/themes/illdy/
 * Description:       Illdy Companion is a companion for Illdy theme.
 * Version:           2.3.0
 * Author:            Colorlib
 * Author URI:        https://colorlib.com/
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Text Domain:       illdy-companion
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ILLDY_COMPANION', '2.3.0' );
define( 'ILLDY_COMPANION_ASSETS_DIR', plugins_url( '/assets/', __FILE__ ) );

/**
 * Load the Dashboard Widget
 */
require_once plugin_dir_path( __FILE__ ) . 'inc/epsilon-dashboard/class-epsilon-dashboard.php';

/**
 * The helper method to run the class
 *
 * @return Epsilon_Dashboard
 */
function illdy_companion_dashboard_widget() {
	$epsilon_dashboard_args = array(
		'widget_title' => esc_html__( 'From our blog', 'illdy-companion' ),
		'feed_url'     => array( 'https://colorlib.com/wp/feed/' ),
	);
	return Epsilon_Dashboard::instance( $epsilon_dashboard_args );
}

/*
 * Deferred to `init`. This call was previously made while the plugin file was still
 * being parsed, and its first act is to translate the widget title. Resolving a string
 * that early makes WordPress 6.7+ load the text domain just-in-time and emit
 * "_load_textdomain_just_in_time was called incorrectly" for the illdy-companion
 * domain. Epsilon_Dashboard only hooks wp_dashboard_setup, which runs much later, so
 * nothing is missed by waiting.
 */
add_action( 'init', 'illdy_companion_dashboard_widget' );

/**
 * Whether Illdy (or a child of it) is the active theme.
 *
 * The widgets call illdy_get_image_id_from_image_url(), which the theme defines, so the
 * plugin's main file must stay behind this guard.
 *
 * @return bool
 */
function illdy_companion_theme_is_active() {
	$current_theme  = wp_get_theme();
	$current_parent = $current_theme->parent();

	return 'Illdy' === $current_theme->get( 'Name' )
		|| ( $current_parent && 'Illdy' === $current_parent->get( 'Name' ) );
}

if ( illdy_companion_theme_is_active() ) {

	require_once plugin_dir_path( __FILE__ ) . 'illdy-main.php';

} else {

	add_action( 'admin_notices', 'illdy_companion_admin_notice', 99 );
	function illdy_companion_admin_notice() {
	?>
		<div class="notice-warning notice">
			<p>
			<?php
			/* translators: 1: opening link tag to the Illdy theme on WordPress.org, 2: closing link tag. */
			$illdy_companion_notice = __( 'In order to use the <strong>Illdy Companion</strong> plugin you have to also install the %1$sIlldy Theme%2$s', 'illdy-companion' );

			printf(
				wp_kses( $illdy_companion_notice, array( 'strong' => array() ) ),
				'<a href="' . esc_url( 'https://wordpress.org/themes/illdy/' ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);
			?>
			</p>
		</div>
		<?php
	}
}
