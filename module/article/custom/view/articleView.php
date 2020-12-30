<?php
class articleView extends command_base
{

	function drawTagListEdit( &$gm, &$rec, $args )
	{
		global $IMAGE_NOT_FOUND_SRC;

		$tags = $args[0] ? json_decode($args[0]) : array() ;
		$html = "";
		foreach( $tags as $key => $data) {

		}
		$this->addBuffer($html);
	}


	/**
	 * 登録内容に応じてイメージを表示
	 *
	 * $param width 表示サイズ
	 * $param height 表示サイズ
	 * $param noimgpath
	 */
	function drawImage( &$gm, &$rec, $args )
	{
		global $IMAGE_NOT_FOUND_SRC;

		$width = $args[0];
		$height = $args[1];

		$noImgSrc = $IMAGE_NOT_FOUND_SRC;
		if( isset($args[2]) && file_exists($args[2])) {
			$noImgSrc = $args[2];
		}

		$noImage = '<img\ alt=""\ width="'.$width.'"\ height="'.$height.'"\ src="'.$noImgSrc.'"\ />';

		$db = $gm->getDB();
		$id = $db->getData( $rec, 'id' );
		$image = $db->getData( $rec, 'image' );
		$image2 = article_partsLogic::getPartsImage($rec);

		// 記事機能に限り、サムネのトリミング設定を「余白なし」で固定
		if( strlen($image) > 0 )
		{ $cc = '<!--# object image image size '.$width.' '.$height.' not '.$noImage.' trimming true #-->'; }
		else if( strlen($image2) > 0 )
		{ $cc = '<!--# object image  src '.$image2.' size '.$width.' '.$height.' not '.$noImage.' trimming true #-->'; }
		else
		{ $cc = '<!--# object image  src '.$noImgSrc.' size '.$width.' '.$height.' trimming true #-->'; }

		$buffer = $gm->getCCResult( $rec , $cc );
		$this->addBuffer( $buffer );
	}

}
