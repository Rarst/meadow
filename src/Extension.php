<?php

namespace Rarst\Meadow;

/**
 * Meadow extension for Twig with WordPress specific functionality.
 */
class Extension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface {

	public function getFunctions() {

		$options = array(
			'needs_environment' => true,
			'needs_context'     => true,
			'is_safe'           => array( 'all' )
		);

		$functions = array();

		foreach ( array( 'get_header', 'get_footer', 'get_sidebar', 'get_template_part', 'get_search_form', 'comments_template' ) as $function ) {
			$functions[] = new \Twig_Function( $function, array( $this, $function ), $options );
		}

		return $functions;
	}

	public function getGlobals() {

		global $wp_query;

		return compact( 'wp_query' );
	}

	public function getTokenParsers(  ) {

		return array(
			new Loop_Token_Parser(),
			new Comments_Token_Parser(),
		);
	}

	public function get_header( \Twig_Environment $env, $context, $name = null ) {

		return $this->get_template( $env, $context, 'header', $name );
	}

	public function get_templates( $slug, $name = null ) {

		$templates = array();

		if ( ! empty( $name ) ) {
			$templates[] = "{$slug}-{$name}.twig";
		}

		$templates[] = "{$slug}.twig";

		return $templates;
	}

	public function get_footer( \Twig_Environment $env, $context, $name = null ) {

		return $this->get_template( $env, $context, 'footer', $name );
	}

	public function get_sidebar( \Twig_Environment $env, $context, $name = null ) {

		return $this->get_template( $env, $context, 'sidebar', $name );
	}

	public function get_template_part( \Twig_Environment $env, $context, $slug, $name = null ) {

		try {
			$return = twig_include( $env, $context, $this->get_templates( $slug, $name ) );
			do_action( "get_template_part_{$slug}", $slug, $name );
		} catch ( \Twig_Error_Loader $e ) {
			ob_start();
			get_template_part( $slug, $name );
			$return = ob_get_clean();
		}

		return $return;
	}

	/**
	 * Skips rendering in native function in favor of Plugin->get_search_form() in filter
	 *
	 * @return string
	 */
	public function get_search_form() {

		return apply_filters( 'get_search_form', true );
	}

	protected function get_template( \Twig_Environment $env, $context, $type, $name = null ) {

		try {
			$return = twig_include( $env, $context, $this->get_templates( $type, $name ) );
			do_action( 'get_' . $type, $name );
		} catch ( \Twig_Error_Loader $e ) {
			ob_start();
			\call_user_func( 'get_' . $type, $name );
			$return = ob_get_clean();
		}

		return $return;
	}

	public function comments_template( \Twig_Environment $env, $context, $file = 'comments.twig', $separate_comments = false ) {

		try {
			$env->loadTemplate( $file );
		} catch ( \Twig_Error_Loader $e ) {
			ob_start();
			comments_template( '/comments.php', $separate_comments );

			return ob_get_clean();
		}

		add_filter( 'comments_template', array( $this, 'return_blank_template' ) );
		comments_template( '/comments.php', $separate_comments );
		remove_filter( 'comments_template', array( $this, 'return_blank_template' ) );

		return twig_include( $env, $context, $file );
	}

	public function return_blank_template() {

		return __DIR__ . '/blank.php';
	}
}