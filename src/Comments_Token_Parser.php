<?php
namespace Rarst\Meadow;

use Twig_Token;

class Comments_Token_Parser extends \Twig_TokenParser {

	/**
	 * Parses a token and returns a node.
	 *
	 * @param Twig_Token $token
	 *
	 * @return Comments_Node
	 */
	public function parse( Twig_Token $token ) {

		$nodes  = array();
		$parser = $this->parser;
		$stream = $parser->getStream();

		$stream->expect( Twig_Token::BLOCK_END_TYPE );
		$nodes['callback'] = $parser->subparse( array( $this, 'decide_comments_end' ), true );
		$stream->expect( Twig_Token::BLOCK_END_TYPE );

		return new Comments_Node( $nodes, array(), $token->getLine(), $this->getTag() );
	}

	/**
	 * @return string
	 */
	public function getTag() {

		return 'comments';
	}

	/**
	 * @param Twig_Token $token
	 *
	 * @return bool
	 */
	public function decide_comments_end( Twig_Token $token ) {
		return $token->test( 'endcomments' );
	}
} 