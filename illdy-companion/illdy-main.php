<?php
if ( ! defined( 'WPINC' ) ) {
	die;
}


// Include Illdy Companion Helper
require_once plugin_dir_path( __FILE__ ) . 'inc/class-illdy-companion-helper.php';

// Include Illdy Companion Importer
require_once plugin_dir_path( __FILE__ ) . 'inc/class-illdy-companion-import-data.php';

// The importer's admin page. Loaded after the importer itself, which it calls into.
require_once plugin_dir_path( __FILE__ ) . 'inc/class-illdy-companion-importer-page.php';

/**
 * Plugin companion widgets
 */
require_once plugin_dir_path( __FILE__ ) . 'widgets/class-illdy-widget-recent-posts.php';
require_once plugin_dir_path( __FILE__ ) . 'widgets/class-illdy-widget-skill.php';
require_once plugin_dir_path( __FILE__ ) . 'widgets/class-illdy-widget-project.php';
require_once plugin_dir_path( __FILE__ ) . 'widgets/class-illdy-widget-service.php';
require_once plugin_dir_path( __FILE__ ) . 'widgets/class-illdy-widget-counter.php';
require_once plugin_dir_path( __FILE__ ) . 'widgets/class-illdy-widget-person.php';
require_once plugin_dir_path( __FILE__ ) . 'widgets/class-illdy-widget-parallax.php';
require_once plugin_dir_path( __FILE__ ) . 'widgets/class-illdy-widget-testimonial.php';

if ( ! function_exists( 'illdy_companion_admin_scripts' ) ) {

	/**
	 * Function to enqueue admin resources - CSS/JS
	 */
	function illdy_companion_admin_scripts( $hook_suffix ) {

		/*
		 * These used to load on every single admin screen, roughly 50 KB of CSS and JS
		 * on pages that never reference any of it. Only the widgets screen needs them
		 * now — it hosts the icon picker and the media control. The Customizer is served
		 * by illdy_companion_customizer_scripts() below, and the demo importer page
		 * loads its own script from Illdy_Companion_Importer_Page.
		 */
		if ( 'widgets.php' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'illdy-companion-admin-css', ILLDY_COMPANION_ASSETS_DIR . 'css/admin.css', array(), ILLDY_COMPANION );
		wp_enqueue_style( 'font-awesome', ILLDY_COMPANION_ASSETS_DIR . 'css/font-awesome.min.css', array(), '4.5.0', 'all' );
		wp_enqueue_style( 'illdy-companion-iconpicker-css', ILLDY_COMPANION_ASSETS_DIR . 'css/jquery.fonticonpicker.css', array(), ILLDY_COMPANION );
		wp_enqueue_style( 'illdy-companion-iconpicker-theme-css', ILLDY_COMPANION_ASSETS_DIR . 'css/jquery.fonticonpicker.grey.min.css', array(), ILLDY_COMPANION );
		wp_enqueue_script( 'illdy-companion-iconpicker-js', ILLDY_COMPANION_ASSETS_DIR . 'js/iconpicker.min.js', array( 'jquery' ), ILLDY_COMPANION, true );
		wp_enqueue_script( 'illdy-widget-text-editor', ILLDY_COMPANION_ASSETS_DIR . 'js/widget-text-editor.js', array( 'jquery' ), ILLDY_COMPANION, true );

	}

	add_action( 'admin_enqueue_scripts', 'illdy_companion_admin_scripts' );

}

if ( ! function_exists( 'illdy_companion_customizer_scripts' ) ) {

	/**
	 * Function to enqueue admin resources - CSS/JS
	 */
	function illdy_companion_customizer_scripts() {

		wp_enqueue_style( 'illdy-companion-iconpicker-css', ILLDY_COMPANION_ASSETS_DIR . 'css/jquery.fonticonpicker.css', array(), ILLDY_COMPANION );
		wp_enqueue_style( 'font-awesome', ILLDY_COMPANION_ASSETS_DIR . 'css/font-awesome.min.css', array(), '4.5.0', 'all' );
		wp_enqueue_script( 'illdy-companion-iconpicker-js', ILLDY_COMPANION_ASSETS_DIR . 'js/iconpicker.min.js', array( 'jquery' ), ILLDY_COMPANION, true );
		wp_enqueue_style( 'illdy-companion-iconpicker-theme-css', ILLDY_COMPANION_ASSETS_DIR . 'css/jquery.fonticonpicker.grey.min.css', array(), ILLDY_COMPANION );

		/*
		 * admin.js is gone. Both of its handlers bound to welcome-screen markup — the
		 * "Advanced" toggle and the import button — and it read a nonce off the
		 * welcomeScreen object the theme localised. None of that exists any more; the
		 * importer page ships its own script.
		 */
	}

	add_action( 'customize_controls_enqueue_scripts', 'illdy_companion_customizer_scripts' );
}

if ( ! function_exists( 'illdy_companion_customize_register' ) ) {
	/**
	 * Function that adds back the customizer sections we were asked to remove from the theme
	 *
	 * @param $wp_customize
	 */
	function illdy_companion_customize_register( $wp_customize ) {

		// Set prefix
		$prefix = 'illdy';

		if ( ! $wp_customize->get_setting( $prefix . '_services_general_entry' ) ) {

			$wp_customize->add_setting(
				$prefix . '_services_general_entry', array(
					'sanitize_callback' => 'wp_kses_post',
					'default'           => __( 'In order to help you grow your business, our carefully selected experts can advise you in in the following areas:', 'illdy-companion' ),
					'transport'         => 'postMessage',
				)
			);

			if ( class_exists( 'Illdy_Control_Text_Editor' ) ) {

				$wp_customize->add_control(
					new Illdy_Control_Text_Editor(
						$wp_customize, $prefix . '_services_general_entry', array(
							'label'    => __( 'Entry', 'illdy-companion' ),
							'section'  => $prefix . '_panel_services',
							'priority' => 3,
							'type'     => 'illdy-text-editor',
						)
					)
				);

			} else {

				$wp_customize->add_control(
					$prefix . '_services_general_entry', array(
						'label'       => __( 'Entry', 'illdy-companion' ),
						'description' => __( 'Add the content for this section.', 'illdy-companion' ),
						'section'     => $prefix . '_panel_services',
						'priority'    => 3,
						'type'        => 'textarea',
					)
				);

			}
		}

		if ( ! $wp_customize->get_setting( $prefix . '_team_general_entry' ) ) {

			$wp_customize->add_setting(
				$prefix . '_team_general_entry', array(
					'sanitize_callback' => 'wp_kses_post',
					'default'           => __( 'Meet the people that are going to take your business to the next level.', 'illdy-companion' ),
					'transport'         => 'postMessage',
				)
			);

			if ( class_exists( 'Illdy_Control_Text_Editor' ) ) {

				$wp_customize->add_control(
					new Illdy_Control_Text_Editor(
						$wp_customize, $prefix . '_team_general_entry', array(
							'label'    => __( 'Entry', 'illdy-companion' ),
							'section'  => $prefix . '_panel_team',
							'priority' => 3,
							'type'     => 'illdy-text-editor',
						)
					)
				);

			} else {

				$wp_customize->add_control(
					$prefix . '_team_general_entry', array(
						'label'       => __( 'Entry', 'illdy-companion' ),
						'description' => __( 'Add the content for this section.', 'illdy-companion' ),
						'section'     => $prefix . '_panel_team',
						'priority'    => 3,
						'type'        => 'textarea',
					)
				);

			}
		}

		if ( ! $wp_customize->get_setting( $prefix . '_about_general_entry' ) ) {

			$wp_customize->add_setting(
				$prefix . '_about_general_entry', array(
					'sanitize_callback' => 'wp_kses_post',
					'default'           => __( 'It is an amazing one-page theme with great features that offers an incredible experience. It is easy to install, make changes, adapt for your business. A modern design with clean lines and styling for a wide variety of content, exactly how a business design should be. You can add as many images as you want to the main header area and turn them into slider.', 'illdy-companion' ),
					'transport'         => 'postMessage',
				)
			);

			if ( class_exists( 'Illdy_Control_Text_Editor' ) ) {

				$wp_customize->add_control(
					new Illdy_Control_Text_Editor(
						$wp_customize, $prefix . '_about_general_entry', array(
							'label'    => __( 'Entry', 'illdy-companion' ),
							'section'  => $prefix . '_panel_about',
							'priority' => 3,
							'type'     => 'illdy-text-editor',
						)
					)
				);

			} else {

				$wp_customize->add_control(
					$prefix . '_about_general_entry', array(
						'label'       => __( 'Entry', 'illdy-companion' ),
						'description' => __( 'Add the content for this section.', 'illdy-companion' ),
						'section'     => $prefix . '_panel_about',
						'priority'    => 3,
						'type'        => 'textarea',
					)
				);

			}
		}

		if ( ! $wp_customize->get_setting( $prefix . '_jumbotron_general_entry' ) ) {

			$wp_customize->add_setting(
				$prefix . '_jumbotron_general_entry', array(
					'sanitize_callback' => 'wp_kses_post',
					'default'           => __( 'lldy is a great one-page theme, perfect for developers and designers but also for someone who just wants a new website for his business. Try it now!', 'illdy-companion' ),
					'transport'         => 'postMessage',
				)
			);

			if ( class_exists( 'Illdy_Control_Text_Editor' ) ) {

				$wp_customize->add_control(
					new Illdy_Control_Text_Editor(
						$wp_customize, $prefix . '_jumbotron_general_entry', array(
							'label'    => __( 'Entry', 'illdy-companion' ),
							'section'  => $prefix . '_jumbotron_general',
							'priority' => 5,
							'type'     => 'illdy-text-editor',
						)
					)
				);

			} else {

				$wp_customize->add_control(
					$prefix . '_jumbotron_general_entry', array(
						'label'       => __( 'Entry', 'illdy-companion' ),
						'description' => __( 'The content added in this field will show below title.', 'illdy-companion' ),
						'section'     => $prefix . '_jumbotron_general',
						'priority'    => 5,
						'type'        => 'textarea',
					)
				);

			}
		}

		if ( ! $wp_customize->get_setting( $prefix . '_latest_news_general_entry' ) ) {

			$wp_customize->add_setting(
				$prefix . '_latest_news_general_entry', array(
					'sanitize_callback' => 'wp_kses_post',
					'default'           => __( 'If you are interested in the latest articles in the industry, take a sneak peek at our blog. You have nothing to loose!', 'illdy-companion' ),
					'transport'         => 'postMessage',
				)
			);

			if ( class_exists( 'Illdy_Control_Text_Editor' ) ) {

				$wp_customize->add_control(
					new Illdy_Control_Text_Editor(
						$wp_customize, $prefix . '_latest_news_general_entry', array(
							'label'    => __( 'Entry', 'illdy-companion' ),
							'section'  => $prefix . '_latest_news_general',
							'priority' => 3,
							'type'     => 'illdy-text-editor',
						)
					)
				);

			} else {

				$wp_customize->add_control(
					$prefix . '_latest_news_general_entry', array(
						'label'       => __( 'Entry', 'illdy-companion' ),
						'description' => __( 'Add the content for this section.', 'illdy-companion' ),
						'section'     => $prefix . '_latest_news_general',
						'priority'    => 3,
						'type'        => 'textarea',
					)
				);

			}
		}

		if ( ! $wp_customize->get_setting( $prefix . '_projects_general_entry' ) ) {

			$wp_customize->add_setting(
				$prefix . '_projects_general_entry', array(
					'sanitize_callback' => 'wp_kses_post',
					'default'           => __( 'You\'ll love our work. Check it out!', 'illdy-companion' ),
					'transport'         => 'postMessage',
				)
			);

			if ( class_exists( 'Illdy_Control_Text_Editor' ) ) {

				$wp_customize->add_control(
					new Illdy_Control_Text_Editor(
						$wp_customize, $prefix . '_projects_general_entry', array(
							'label'    => __( 'Entry', 'illdy-companion' ),
							'section'  => $prefix . '_panel_projects',
							'priority' => 3,
							'type'     => 'illdy-text-editor',
						)
					)
				);

			} else {

				$wp_customize->add_control(
					$prefix . '_projects_general_entry', array(
						'label'       => __( 'Entry', 'illdy-companion' ),
						'description' => __( 'Add the content for this section.', 'illdy-companion' ),
						'section'     => $prefix . '_panel_projects',
						'priority'    => 3,
						'type'        => 'textarea',
					)
				);

			}
		}

		if ( ! $wp_customize->get_setting( $prefix . '_contact_us_entry' ) ) {
			$wp_customize->add_setting(
				$prefix . '_contact_us_entry', array(
					'sanitize_callback' => 'wp_kses_post',
					'default'           => __( 'And we will get in touch as soon as possible.', 'illdy-companion' ),
					'transport'         => 'postMessage',
				)
			);

			if ( class_exists( 'Illdy_Control_Text_Editor' ) ) {

				$wp_customize->add_control(
					new Illdy_Control_Text_Editor(
						$wp_customize, $prefix . '_contact_us_entry', array(
							'label'    => __( 'Entry', 'illdy-companion' ),
							'section'  => $prefix . '_contact_us',
							'priority' => 3,
							'type'     => 'illdy-text-editor',
						)
					)
				);

			} else {

				$wp_customize->add_control(
					$prefix . '_contact_us_entry', array(
						'label'       => __( 'Entry', 'illdy-companion' ),
						'description' => __( 'Add the content for this section.', 'illdy-companion' ),
						'section'     => $prefix . '_contact_us',
						'priority'    => 3,
						'type'        => 'textarea',
					)
				);

			}
		}

	}

	// hook our function
	add_action( 'customize_register', 'illdy_companion_customize_register', 20 );
} // End if().

/**
 * Resolves an attachment id to its full-size URL for the widget media pickers.
 *
 * Previously this ran for any authenticated user with no nonce and no capability
 * check, letting a subscriber walk attachment ids and read back the URL of any
 * upload on the site, including media attached to private or draft posts.
 */
function illdy_get_attachment_image() {

	if ( ! current_user_can( 'upload_files' ) ) {
		wp_send_json_error( 'forbidden', 403 );
	}

	check_ajax_referer( 'illdy_get_attachment_media', 'nonce' );

	$id = isset( $_POST['attachment_id'] ) ? absint( wp_unslash( $_POST['attachment_id'] ) ) : 0;

	if ( ! $id ) {
		wp_die( '', '', array( 'response' => 400 ) );
	}

	$src = wp_get_attachment_image_src( $id, 'full', false );

	if ( ! empty( $src[0] ) ) {
		echo esc_url( $src[0] );
	}

	wp_die();
}
add_action( 'wp_ajax_illdy_get_attachment_media', 'illdy_get_attachment_image' );
