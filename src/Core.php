<?php

namespace Rarst\Meadow;
use Pimple\Container;

/**
 * Main plugin class.
 */
class Core extends Container {

	/**
	 * @param array $values Optional array of services/options.
	 */
	public function __construct( $values = array() ) {

		global $wp_version;

		$defaults                     = [];
		$defaults['twig.options']     = [];
		$defaults['twig.directories'] = [];

		// This needs to be lazy or theme switchers and alike explode it.
		$defaults['twig.loader'] = function ( $meadow ) {

			$stylesheet_dir  = get_stylesheet_directory();
			$template_dir    = get_template_directory();
			$calculated_dirs = array(
				$stylesheet_dir,
				$template_dir,
				plugin_dir_path( __DIR__ ) . 'src/twig',
			);

			// Enables explicit inheritance from parent theme in child.
			if ( $stylesheet_dir !== $template_dir ) {
				$calculated_dirs[] = \dirname( $template_dir );
			}

			$directories = array_unique(
				array_merge(
					$calculated_dirs,
					$meadow['twig.directories']
				)
			);

			return new \Twig_Loader_Filesystem( $directories );
		};

		$defaults['twig.undefined_function'] = array( __CLASS__, 'undefined_function' );
		$defaults['twig.undefined_filter']   = array( __CLASS__, 'undefined_filter' );

		$defaults['twig.environment'] = function ( $meadow ) {
			$environment      = new \Twig_Environment( $meadow['twig.loader'], $meadow['twig.options'] );
			$meadow_extension = new Extension();
			$environment->addExtension( $meadow_extension );
			$environment->registerUndefinedFunctionCallback( $meadow['twig.undefined_function'] );
			$environment->registerUndefinedFilterCallback( $meadow['twig.undefined_filter'] );

			if ( \defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$debug_extension = new \Twig_Extension_Debug();
				$environment->addExtension( $debug_extension );
				$environment->enableDebug();
			}

			return $environment;
		};

		if ( version_compare( rtrim( $wp_version, '-src' ), '4.7', '>=' ) ) {

			$defaults['hierarchy'] = function () {
				return new Type_Template_Hierarchy();
			};
		} else {

			trigger_error( 'Pre–WP 4.7 implementation of Meadow hierarchy is deprecated and will be removed in 1.0.', E_USER_DEPRECATED );

			$defaults['hierarchy'] = function () {
				/** @noinspection PhpDeprecationInspection */
				return new Template_Hierarchy();
			};
		}

		parent::__construct( array_merge( $defaults, $values ) );
	}

	/**
	 * Handler for undefined functions in Twig to pass them through to PHP and buffer echoing versions.
	 *
	 * @param string $function_name Name of the function to handle.
	 *
	 * @return bool|\Twig_Function
	 */
	public static function undefined_function( $function_name ) {

		if ( \function_exists( $function_name ) ) {
			return new \Twig_Function(
				$function_name,
				function () use ( $function_name ) {

					ob_start();
					$return = \call_user_func_array( $function_name, \func_get_args() );
					$echo   = ob_get_clean();

					return empty( $echo ) ? $return : $echo;
				},
				array( 'is_safe' => array( 'all' ) )
			);
		}

		return false;
	}

	/**
	 * Handler for fallback to WordPress filters for undefined Twig filters in template.
	 *
	 * @param string $filter_name Name of the filter to handle.
	 *
	 * @return bool|\Twig_Filter
	 */
	public static function undefined_filter( $filter_name ) {

		return new \Twig_Filter(
			$filter_name,
			function () use ( $filter_name ) {

				return apply_filters( $filter_name, func_get_arg( 0 ) );
			},
			array( 'is_safe' => array( 'all' ) )
		);
	}

	public function enable() {

		/** @var Template_Hierarchy $hierarchy */
		$hierarchy = $this['hierarchy'];
		$hierarchy->enable();
		add_filter( 'template_include', [ $this, 'template_include' ], 100 );
		add_filter( 'get_search_form', array( $this, 'get_search_form' ), 9 );
	}

	public function disable() {

		/** @var Template_Hierarchy $hierarchy */
		$hierarchy = $this['hierarchy'];
		$hierarchy->disable();
		remove_filter( 'template_include', [ $this, 'template_include' ], 100 );
		remove_filter( 'get_search_form', array( $this, 'get_search_form' ), 9 );
	}

	/**
	 * @param string $template Template found by loader.
	 *
	 * @return string|bool
	 */
	public function template_include( $template ) {

		if ( '.twig' === substr( $template, - 5 ) ) {
			/** @var \Twig_Environment $twig */
			$twig = $this['twig.environment'];

			echo $twig->render( basename( $template ), apply_filters( 'meadow_context', array() ) );

			die();
		}

		return $template;
	}

	/**
	 * @param string $form Form markup.
	 *
	 * @return string
	 */
	public function get_search_form( $form ) {

		// Because first time it's an action.
		if ( ! empty( $form ) ) {
			/** @var \Twig_Environment $twig */
			$twig = $this['twig.environment'];

			return $twig->render( 'searchform.twig' );
		}

		return $form;
	}
}