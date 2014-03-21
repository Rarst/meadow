<?php

namespace Rarst\Meadow;

/**
 * Meadow extension for Twig with WordPress specific functionality.
 */
class Extension extends \Twig_Extension {

	public function getName() {
		return 'meadow';
	}

	public function initRuntime( \Twig_Environment $environment ) {

	}

	public function getFunctions() {

		$options = array(
			'needs_environment' => true,
			'needs_context'     => true,
			'is_safe'           => array( 'all' )
		);

		$functions = array();

		foreach ( array( 'get_header', 'get_footer', 'get_sidebar', 'get_template_part', 'get_search_form' ) as $function ) {
			$functions[] = new \Twig_SimpleFunction( $function, array( $this, $function ), $options );
		}

		return $functions;
	}

	public function getGlobals() {

		global $wp_query;

		return compact( 'wp_query' );
	}

	public function getTokenParsers(  ) {

		return array(
			new Loop_Token_Parser()
		);
	}

	public function get_header( \Twig_Environment $env, $context, $name = null ) {

		try {
			$return = twig_include( $env, $context, $this->get_templates( 'header', $name ) );
			do_action( 'get_header', $name );
		} catch ( \Exception $e ) {
			ob_start();
			get_header( $name );
			$return = ob_get_clean();
		}

		return $return;
	}

	public function get_templates( $slug, $name = null ) {

		$templates = array();

		if ( ! empty( $name ) )
			$templates[] = "{$slug}-{$name}.twig";

		$templates[] = "{$slug}.twig";

		return $templates;
	}

	public function get_footer( \Twig_Environment $env, $context, $name = null ) {

		try {
			$return = twig_include( $env, $context, $this->get_templates( 'footer', $name ) );
			do_action( 'get_footer', $name );
		} catch ( \Exception $e ) {
			ob_start();
			get_footer( $name );
			$return = ob_get_clean();
		}

		return $return;
	}

	public function get_sidebar( \Twig_Environment $env, $context, $name = null ) {

		do_action( 'get_sidebar', $name );

		return twig_include( $env, $context, $this->get_templates( 'sidebar', $name ) );
	}

	public function get_template_part( \Twig_Environment $env, $context, $slug, $name = null ) {

		do_action( "get_template_part_{$slug}", $slug, $name );

		return twig_include( $env, $context, $this->get_templates( $slug, $name ) );
	}

	/**
	 * Skips rendering in native function in favor of Plugin->get_search_form() in filter
	 *
	 * @return string
	 */
	public function get_search_form() {

		return apply_filters( 'get_search_form', true );
	}
}