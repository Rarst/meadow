<?php
namespace Rarst\Meadow;

use Twig_Node;
use Twig_Compiler;

/**
 * Compiles comments node into wp_list_comments() call with markup in callback.
 */
class Comments_Node extends \Twig_Node {
	/**
	 * @param Twig_Compiler $compiler
	 */
	public function compile( Twig_Compiler $compiler ) {

		$compiler
			->addDebugInfo( $this )
			->write( '$callback = function() {' )
			->subcompile( $this->getNode( 'callback' ) )
			->write( '};' )
			->write( 'wp_list_comments( array( \'callback\' => $callback ) );' );
	}
} 