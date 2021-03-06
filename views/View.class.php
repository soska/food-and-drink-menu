<?php

/**
 * Base class for any view requested on the front end.
 *
 * @since 1.1
 */

class fdmView extends fdmBase {

	// Map types of content to the template which will render them
	public $content_map = array(
		'title'		=> 'content/title',
		'content'	=> 'content/content',
		'price'		=> 'content/price',
		'image'		=> 'content/image'
	);
	
	// Menu layout type default
	public $layout = 'classic';

	public $style = 'base';

	/**
	 * Initialize the class
	 * @since 1.1
	 */
	public function __construct( $args ) {

		// Parse the values passed
		$this->parse_args( $args );
		
		// Filter the content map so addons can customize what and how content
		// is output. Filters are specific to each view, so for this base view
		// you would use the filter 'fdm_content_map_fdmView'
		$this->content_map = apply_filters( 'fdm_content_map_' . get_class( $this ), $this->content_map );

	}

	/**
	 * Render the view and enqueue required stylesheets
	 *
	 * @note This function should always be overridden by an extending class
	 * @since 1.1
	 */
	public function render() {
		$this->set_error(
			array( 
				'type'		=> 'render() called on wrong class'
			)
		);
	}

	/**
	 * Load a template file for views
	 *
	 * First, it looks in the current theme's /fdm-templates/ directory. Then it
	 * will check a parent theme's /fdm-templates/ directory. If nothing is found
	 * there, it will retrieve the template from the plugin directory.

	 * @since 1.1
	 * @param string template Type of template to load (eg - menu, menu-item)
	 */
	function find_template( $template ) {

		$this->template_dirs = array(
			get_stylesheet_directory() . '/' . FDM_TEMPLATE_DIR . '/',
			get_template_directory() . '/' . FDM_TEMPLATE_DIR . '/',
			FDM_PLUGIN_DIR . '/' . FDM_TEMPLATE_DIR . '/'
		);
		
		$this->template_dirs = apply_filters( 'fdm_template_directories', $this->template_dirs );

		if ( isset( $this->layout ) && $this->layout != 'classic' ) {
			$template .= '-' . $this->layout;
		}

		foreach ( $this->template_dirs as $dir ) {
			if ( file_exists( $dir . $template . '.php' ) ) {
				return $dir . $template . '.php';
			}
		}

		return false;
	}

	/**
	 * Enqueue stylesheets
	 */
	public function enqueue_assets() {

		global $fdm_controller;
		
		$settings = get_option( 'food-and-drink-menu-settings' );
		if ( $settings['fdm-style'] == 'none' ) {
			return;
		}

		$enqueued = false;
		foreach ( $fdm_controller->styles as $style ) {
			if ( $this->style == $style->id ) {
				$style->enqueue_assets();
				$enqueued = true;
			}
		}
		
		// Fallback to basic style if the selected style does not exist
		// This can happen if they have a custom style defined in a theme, then
		// they switch themes. The setting will still be the custom style, but
		// no entry in $fdm_controller->styles will exist for that style.
		if ( !$enqueued && isset( $fdm_controller->styles['base'] ) ) {
			$fdm_controller->styles['base']->enqueue_assets();
		}
	}

}
