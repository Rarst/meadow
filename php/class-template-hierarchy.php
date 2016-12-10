<?php
namespace Rarst\Meadow;

/**
 * Augment native template hierarchy with non-PHP template processing.
 */
class Template_Hierarchy {

	public $template_types = array(
		'404',
		'search',
		'taxonomy',
		'frontpage',
		'home',
		'attachment',
		'single',
		'page',
		'singular',
		'category',
		'tag',
		'author',
		'date',
		'archive',
		'commentspopup',
		'paged',
		'index',
	);

	protected $mime_type;

	public function enable() {

		add_action( 'template_redirect', array( $this, 'template_redirect' ) );

		foreach ( $this->template_types as $type ) {
			add_filter( "{$type}_template", array( $this, 'query_template' ) );
		}
	}

	public function disable() {

		remove_action( 'template_redirect', array( $this, 'template_redirect' ) );

		foreach ( $this->template_types as $type ) {
			remove_filter( "{$type}_template", array( $this, 'query_template' ) );
		}

		if ( ! empty($this->mime_type) ) {
			remove_filter( "{$this->mime_type[0]}_template", array( $this, 'query_template' ) );
			remove_filter( "{$this->mime_type[1]}_template", array( $this, 'query_template' ) );
			remove_filter( "{$this->mime_type[0]}{$this->mime_type[1]}_template", array( $this, 'query_template' ) );
		}
	}

	public function template_redirect() {

		if ( is_attachment() ) {
			global $posts;

			if ( ! empty( $posts ) && isset( $posts[0]->post_mime_type ) )
				$this->mime_type = explode( '/', $posts[0]->post_mime_type );

			add_filter( "{$this->mime_type[0]}_template", array( $this, 'query_template' ) );
			add_filter( "{$this->mime_type[1]}_template", array( $this, 'query_template' ) );
			add_filter( "{$this->mime_type[0]}{$this->mime_type[1]}_template", array( $this, 'query_template' ) );
		}
	}

	/**
	 * @param string $fallback
	 *
	 * @return string
	 */
	public function query_template( $fallback ) {

		$type      = substr( current_filter(), 0, - 9 ); // trim '_template' from end
		$templates = array();

		switch ( $type ) {
			case 'taxonomy':
				$term = get_queried_object();

				if ( $term ) {
					$taxonomy    = $term->taxonomy;
					$templates[] = "taxonomy-{$taxonomy}-{$term->slug}.twig";
					$templates[] = "taxonomy-{$taxonomy}.twig";
				}

				$templates[] = 'taxonomy.twig';
				break;

			case 'frontpage':
				$templates = array( 'front-page.twig' );
				break;

			case 'home':
				$templates = array( 'home.twig', 'index.twig' );
				break;

			case 'single':
				$object = get_queried_object();

				if ( $object )
					$templates[] = "single-{$object->post_type}.twig";

				$templates[] = 'single.twig';
				break;

			case 'page':
				$page_id  = get_queried_object_id();
//				$template = get_page_template_slug();
				$pagename = get_query_var( 'pagename' );

				if ( ! $pagename && $page_id ) {
					// If a static page is set as the front page, $pagename will not be set. Retrieve it from the queried object
					$post     = get_queried_object();
					$pagename = $post->post_name;
				}

				// TODO page templates
//				if ( $template && 0 === validate_file( $template ) )
//					$templates[] = $template;

				if ( $pagename )
					$templates[] = "page-{$pagename}.twig";

				if ( $page_id )
					$templates[] = "page-{$page_id}.twig";

				$templates[] = 'page.twig';
				break;

			case 'category':
			case 'tag':
				$term = get_queried_object();

				if ( $term ) {
					$templates[] = "{$type}-{$term->slug}.twig";
					$templates[] = "{$type}-{$term->term_id}.twig";
				}

				$templates[] = "{$type}.twig";
				break;

			case 'author':
				$author = get_queried_object();

				if ( $author ) {
					$templates[] = "author-{$author->user_nicename}.twig";
					$templates[] = "author-{$author->ID}.twig";
				}

				$templates[] = 'author.twig';
				break;

			case 'archive':
				$post_types = array_filter( (array) get_query_var( 'post_type' ) );

				if ( count( $post_types ) == 1 ) {
					$post_type   = reset( $post_types );
					$templates[] = "archive-{$post_type}.twig";
				}

				$templates[] = 'archive.twig';
				break;

			default:
				$templates = array( "{$type}.twig" );
		}

		$template = $this->locate_template( $templates );

		if ( empty( $template ) ) {
			$template = $fallback;
		}

		return apply_filters( 'meadow_query_template', $template, $type );
	}

	/**
	 * Broken out for easier inheritance to customize lookup logic.
	 *
	 * @param array|string $templates
	 *
	 * @return string
	 */
	public function locate_template( $templates ) {

		return locate_template( $templates );
	}
}
