<?php
/**
 * The demo content importer's UI.
 *
 * This renders as a tab on the theme's About Illdy screen (Appearance → About Illdy →
 * Import Demo Content), registered through the theme's `illdy_welcome_tabs` filter and
 * `illdy_welcome_tab_import` action. If the theme does not offer that screen — an older
 * Illdy, or a child theme that removed it — the same panel is registered as a standalone
 * page instead, so the importer is never unreachable.
 *
 * What the plugin owns either way is the part that matters: its own nonce and its own
 * AJAX endpoint. It used to hand the theme a blob of HTML and ride a generic
 * class/method dispatcher belonging to the old welcome screen.
 *
 * The import itself is unchanged. Every step still runs through
 * Illdy_Companion_Import_Data, so what lands in the database is exactly what it always was.
 *
 * @package illdy-companion
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Illdy_Companion_Importer_Page' ) ) {

	class Illdy_Companion_Importer_Page {

		/**
		 * Menu slug, also the `page` query arg.
		 */
		const SLUG = 'illdy-import-demo-content';

		/**
		 * Nonce action for the import request.
		 */
		const NONCE = 'illdy_companion_import_demo';

		/**
		 * Capability required to see the page and to run an import.
		 *
		 * Matches what the old welcome-screen endpoint enforced, so this is no weaker
		 * than the flow it replaces.
		 */
		const CAP = 'manage_options';

		/**
		 * Tab id on the theme's About Illdy screen.
		 */
		const TAB = 'import';

		public function __construct() {
			add_action( 'wp_ajax_illdy_companion_import_demo', array( $this, 'handle_import' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

			/*
			 * Deferred: plugins are loaded before themes, so Illdy_Welcome does not exist
			 * yet at this point and testing for it here would always fall through to the
			 * standalone page. after_setup_theme is the first hook that runs with the
			 * theme's functions.php parsed, and is still far ahead of admin_menu.
			 */
			add_action( 'after_setup_theme', array( $this, 'register_ui' ) );
		}

		/**
		 * Attaches the importer either as a tab on About Illdy or as its own page.
		 */
		public function register_ui() {
			if ( self::theme_has_welcome_screen() ) {
				add_filter( 'illdy_welcome_tabs', array( $this, 'register_tab' ) );
				add_action( 'illdy_welcome_tab_' . self::TAB, array( $this, 'render_panel' ) );

				return;
			}

			add_action( 'admin_menu', array( $this, 'register_page' ) );
		}

		/**
		 * Whether the active theme offers the About Illdy screen to hang a tab on.
		 *
		 * @return bool
		 */
		public static function theme_has_welcome_screen() {
			return class_exists( 'Illdy_Welcome' );
		}

		/**
		 * The admin-page hook suffix the importer renders on, whichever mode it is in.
		 *
		 * @return string
		 */
		public static function hook_suffix() {
			if ( self::theme_has_welcome_screen() ) {
				return Illdy_Welcome::hook_suffix();
			}

			return 'appearance_page_' . self::SLUG;
		}

		/**
		 * Adds the importer tab to the theme's About screen.
		 *
		 * Inserted directly after Getting Started, which is where step 2 points.
		 *
		 * @param array $tabs Tab id => label.
		 *
		 * @return array
		 */
		public function register_tab( $tabs ) {
			if ( ! is_array( $tabs ) ) {
				return $tabs;
			}

			$label = esc_html__( 'Import Demo Content', 'illdy-companion' );
			$after = 'getting-started';

			if ( ! array_key_exists( $after, $tabs ) ) {
				$tabs[ self::TAB ] = $label;

				return $tabs;
			}

			$out = array();
			foreach ( $tabs as $id => $tab_label ) {
				$out[ $id ] = $tab_label;

				if ( $after === $id ) {
					$out[ self::TAB ] = $label;
				}
			}

			return $out;
		}

		/**
		 * Fallback: registers a standalone page when the theme has no About screen.
		 */
		public function register_page() {
			add_theme_page(
				esc_html__( 'Import Demo Content', 'illdy-companion' ),
				esc_html__( 'Import Demo Content', 'illdy-companion' ),
				self::CAP,
				self::SLUG,
				array( $this, 'render' )
			);
		}

		/**
		 * Loads the importer script only where the panel actually renders.
		 *
		 * @param string $hook_suffix Current admin page.
		 */
		public function enqueue( $hook_suffix ) {
			if ( self::hook_suffix() !== $hook_suffix ) {
				return;
			}

			/*
			 * On the About screen the panel is one tab among several, so there is no
			 * point loading this on the others.
			 */
			if ( self::theme_has_welcome_screen() ) {
				$requested = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( self::TAB !== $requested ) {
					return;
				}
			}

			wp_enqueue_script(
				'illdy-companion-importer',
				ILLDY_COMPANION_ASSETS_DIR . 'js/importer.js',
				array( 'jquery' ),
				ILLDY_COMPANION,
				true
			);

			wp_localize_script(
				'illdy-companion-importer',
				'illdyCompanionImporter',
				array(
					'ajaxurl' => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
					'nonce'   => wp_create_nonce( self::NONCE ),
					'i18n'    => array(
						/* translators: shown in a browser confirm() dialog before the import runs. */
						'confirm'   => __( "Importing will overwrite your current Customizer settings and front page widgets.\n\nThis cannot be undone. Continue?", 'illdy-companion' ),
						'running'   => __( 'Importing…', 'illdy-companion' ),
						'button'    => __( 'Import Demo Content', 'illdy-companion' ),
						'noneFound' => __( 'Select at least one item to import.', 'illdy-companion' ),
						'success'   => __( 'Demo content imported.', 'illdy-companion' ),
						'failure'   => __( 'The import did not complete. Nothing further was changed.', 'illdy-companion' ),
					),
				)
			);
		}

		/**
		 * The steps offered on the page, in the order they must run.
		 *
		 * Keys are validated against Illdy_Companion_Import_Data::get_import_steps() on
		 * the server, so this list is presentation only.
		 *
		 * @return array Step id => label.
		 */
		private function steps() {
			return array(
				'set_static_frontpage' => __( 'Set the front page to a static page', 'illdy-companion' ),
				'import_customizer'    => __( 'Import Customizer settings', 'illdy-companion' ),
				'import_widgets'       => __( 'Import front page widgets', 'illdy-companion' ),
			);
		}

		/**
		 * Whether a demo import has already been recorded on this site.
		 *
		 * @return bool
		 */
		private function already_imported() {
			$state = get_option( 'illdy_show_required_actions' );

			return is_array( $state ) && ! empty( $state['illdy-req-import-content'] );
		}

		/**
		 * Renders the standalone page. Only used when the theme has no About screen.
		 */
		public function render() {
			if ( ! current_user_can( self::CAP ) ) {
				wp_die( esc_html__( 'You do not have permission to import demo content.', 'illdy-companion' ) );
			}
			?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Import Demo Content', 'illdy-companion' ); ?></h1>
				<?php $this->render_panel(); ?>
			</div>
			<?php
		}

		/**
		 * Renders the importer itself.
		 *
		 * Called directly as the `illdy_welcome_tab_import` handler, and from render()
		 * in the standalone fallback, so it emits no page chrome of its own.
		 */
		public function render_panel() {
			if ( ! current_user_can( self::CAP ) ) {
				echo '<div class="notice notice-error inline"><p>' . esc_html__( 'Your account cannot import demo content.', 'illdy-companion' ) . '</p></div>';

				return;
			}
			?>
			<div class="illdy-import-panel">
				<h3><?php esc_html_e( 'Set the front page up like the demo', 'illdy-companion' ); ?></h3>

				<p><?php esc_html_e( 'This creates the Front Page and Blog pages, points Settings → Reading at them, fills in the Customizer settings, and adds the front page widgets. It is optional: everything it does can be done by hand.', 'illdy-companion' ); ?></p>

				<?php if ( $this->already_imported() ) : ?>
					<div class="notice notice-info inline">
						<p><?php esc_html_e( 'Demo content has already been imported on this site. Running it again replaces your current settings with the demo ones.', 'illdy-companion' ); ?></p>
					</div>
				<?php endif; ?>

				<div class="notice notice-warning inline">
					<p>
						<strong><?php esc_html_e( 'This overwrites existing settings.', 'illdy-companion' ); ?></strong>
						<?php esc_html_e( 'Customizer options and front page widgets are replaced by the demo values. Your posts, pages and media are not touched.', 'illdy-companion' ); ?>
					</p>
				</div>

				<form id="illdy-companion-import-form" method="post" onsubmit="return false;">
					<h4><?php esc_html_e( 'What to import', 'illdy-companion' ); ?></h4>

					<fieldset>
						<legend class="screen-reader-text"><?php esc_html_e( 'What to import', 'illdy-companion' ); ?></legend>
						<ul class="illdy-import-steps">
							<?php foreach ( $this->steps() as $illdy_step_id => $illdy_step_label ) : ?>
								<li>
									<label>
										<input type="checkbox" class="illdy-import-step" value="<?php echo esc_attr( $illdy_step_id ); ?>" checked>
										<?php echo esc_html( $illdy_step_label ); ?>
									</label>
								</li>
							<?php endforeach; ?>
						</ul>
					</fieldset>

					<p class="submit">
						<button type="button" class="button button-primary" id="illdy-run-import">
							<?php esc_html_e( 'Import Demo Content', 'illdy-companion' ); ?>
						</button>
						<span class="spinner" style="float:none;vertical-align:middle;"></span>
					</p>

					<div id="illdy-import-result" role="status" aria-live="polite"></div>
				</form>

				<p>
					<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>">
						<?php esc_html_e( 'Open the Customizer', 'illdy-companion' ); ?>
					</a>
				</p>
			</div>
			<?php
		}

		/**
		 * AJAX: runs the requested import steps.
		 */
		public function handle_import() {
			if ( ! current_user_can( self::CAP ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to import demo content.', 'illdy-companion' ) ), 403 );
			}

			check_ajax_referer( self::NONCE, 'nonce' );

			// Sanitised at the point of access so no unsanitised value ever exists.
			$requested = isset( $_POST['steps'] ) && is_array( $_POST['steps'] )
				? array_map( 'sanitize_text_field', wp_unslash( $_POST['steps'] ) )
				: array();

			/*
			 * Intersect with the canonical list rather than trusting the request: the step
			 * name is used to pick a method to call, so it must come from the allowlist.
			 * array_values() keeps it a plain list, and the order comes from the allowlist
			 * so the front page is created before the Customizer settings reference it.
			 */
			$allowed = Illdy_Companion_Import_Data::get_import_steps();
			$steps   = array_values( array_intersect( $allowed, $requested ) );

			if ( empty( $steps ) ) {
				wp_send_json_error( array( 'message' => __( 'Select at least one item to import.', 'illdy-companion' ) ), 400 );
			}

			$result = Illdy_Companion_Import_Data::process_sample_content( $steps );

			if ( 'ok' === $result ) {
				wp_send_json_success(
					array(
						'message' => __( 'Demo content imported.', 'illdy-companion' ),
					)
				);
			}

			wp_send_json_error(
				array(
					'message' => __( 'The import did not complete. Nothing further was changed.', 'illdy-companion' ),
				),
				500
			);
		}
	}

	new Illdy_Companion_Importer_Page();
}
