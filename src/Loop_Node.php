<?php

namespace Rarst\Meadow;

use Twig_Node;
use Twig_Compiler;

/**
 * Compiles loop nodes into WP loop with optional query node for secondary loops.
 */
class Loop_Node extends Twig_Node {

	/**
	 * @param Twig_Compiler $compiler
	 */
	public function compile( Twig_Compiler $compiler ) {

		$compiler->addDebugInfo( $this );

		if ( $this->hasNode( 'query' ) ) {
			$compiler
					->write( '$loop = new WP_Query(' )
					->subcompile( $this->getNode( 'query' ) )
					->raw( ");\n" )
					->write( 'while( $loop->have_posts() ) : $loop->the_post();' . "\n" ); // TODO nested loops
		}
		else {
			$compiler->write( 'while( have_posts() ) : the_post();' . "\n" );
		}

		$compiler
				->subcompile( $this->getNode( 'body' ) )
				->write( 'endwhile;' . "\n" );
	}
}