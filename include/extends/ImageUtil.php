<?php
class ImageUtil{

	/**
	 * 画像を指定されたサイズで表示するタグを生成する
	 *
	 * @param img リサイズする画像のファイルパスです。
	 * @param newWidth リサイズ後の横幅です。
	 * @param newHeight リサイズ後の縦幅です。
	 * @param ratio リサイズ後の比率を維持するか指定します。
	 * @param small 指定サイズより小さかった場合そのままのサイズで表示するか指定します。 
	 */
	function getImageTag( $img, $newWidth, $newHeight, $ratio = 'variable', $small = false )
	{
		if( !file_exists( $img ) )	{ return 'イメージは登録されていません'; }
		list($width, $height) = getimagesize($img);

		// 指定サイズより小さい時はオリジナルサイズで表示する場合
		if( $small && $newWidth > $width && $newHeight > $height )
		{ $newWidth  = $width; $newHeight = $height;  }

		switch($ratio)
		{
			case 'variable':
				// 縦横リサイズ後のサイズに近い方を元に比率を維持
				if( (double)$width/$newWidth > (double)$height/$newHeight )
				{ $newHeight = ($height/$width)*$newWidth; }
				else
				{ $newWidth	 = ($width/$height)*$newHeight; }
				break;
			case 'width':
				// 横幅を元に比率を維持
				$newHeight = ($height/$width)*$newWidth;
				break;
			case 'height':
				// 縦幅を元に比率を維持
				$newWidth	 = ($width/$height)*$newHeight;
				break;
			case 'fix':
			default:
				// 比率を無視して指定サイズで表示
				break;
		}
		return '<img src="'. $img .'" width="'.$newWidth.'" height="'.$newHeight.'" border="0">';

	}



	/**
	 * 指定された画像を指定されたサイズに変更して保存します。
	 *
	 * @param img リサイズする画像のファイルパスです。
	 * @param fileName リサイズ後の画像のファイルパスです。
	 * @param newWidth リサイズ後の横幅です。
	 * @param newHeight リサイズ後の縦幅です。
	 * @param ratio リサイズ後の比率を維持するか指定します。
	 */
	function resizeImage( $img, $fileName, $newWidth, $newHeight, $ratio = true  )
	{
		if( !file_exists( $img ) )	{ throw new InternalErrorException(  'ファイルが存在しません ->'. $img  ); }
		list($width, $height,$type) = getimagesize($img);

		if($ratio)
		{// 比率を維持する場合
			if( (double)$width/$newWidth > (double)$height/$newHeight )
			{ $newHeight = ($height/$width)*$newWidth; }
			else
			{ $newWidth	 = ($width/$height)*$newHeight; }
		}

		// リサイズ画像の生成
		$outImage		 =  @imagecreatetruecolor( (int)$newWidth, (int)$newHeight );
		switch( $type )
		{
			case '1':// gif
				$image	 = @imagecreatefromgif( $img );
				imagecopyresized($outImage,$image,0,0,0,0,$newWidth,$newHeight,$width,$height);
				imagegif( $outImage, $fileName );
				break;
			case '2':// jpg
				$image	 = @imagecreatefromjpeg( $img );
				imagecopyresized($outImage,$image,0,0,0,0,$newWidth,$newHeight,$width,$height);
				imagejpeg( $outImage, $fileName );
				break;
			case '3':// png
				$image	 = @imagecreatefrompng( $img );
				imagecopyresized($outImage,$image,0,0,0,0,$newWidth,$newHeight,$width,$height);
				imagepng( $outImage, $fileName );
				break;
		}

		return $fileName;
	}

}
?>