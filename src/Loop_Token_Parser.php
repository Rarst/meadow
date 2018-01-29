<?php

namespace Rarst\Meadow;

use Twig_Token;

class Loop_Token_Parser extends \Twig_TokenParser {

	/**
	 * Parses a token and returns a node.
	 *
	 * @param Twig_Token $token
	 *
	 * @return Loop_Node
	 */
	public function parse( Twig_Token $token ) {

		$nodes  = array();
		$parser = $this->parser;
		$stream = $parser->getStream();

		if ( ! $stream->getCurrent()->test( Twig_Token::BLOCK_END_TYPE ) ) {
			$nodes['query'] = $parser->getExpressionParser()->parseExpression();
		}

		$stream->expect( Twig_Token::BLOCK_END_TYPE );
		$nodes['body'] = $parser->subparse( array( $this, 'decide_loop_end' ), true );
		$stream->expect( Twig_Token::BLOCK_END_TYPE );

		return new Loop_Node( $nodes, array(), $token->getLine(), $this->getTag() );
	}

	/**
	 * @return string
	 */
	public function getTag() {

		return 'loop';
	}

	/**
	 * @param Twig_Token $token
	 *
	 * @return bool
	 */
	public function decide_loop_end( Twig_Token $token ) {
		return $token->test( 'endloop' );
	}
}