<?php
class CategoryView extends command_base{
	/**
	 * 職種セレクトボックスを表示
	 *
	 */
	function drawSelectboxCategory( &$gm, $rec, $args )
	{
		$prefectursCol	 = $args[0];
		$sortFlg		 = $args[1] == 'TRUE';
		$countFlg		 = $args[2] == 'TRUE';

		$CC		 = Category::getSortCategorySelectCC($prefectursCol, $sortFlg, $countFlg);

		$buffer	 = $gm->getCCResult( $rec, $CC );

		$this->addBuffer( $buffer );
	}
}