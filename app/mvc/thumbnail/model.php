<?php

	//★クラス //

	/**
		@brief   既定のサムネイル中継APIのモデル。
	*/
	class AppThumbnailModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief サムネイルを生成する。
		*/
		function makeThumbnail() //
		{
			global $THUMBNAIL_OPTIONS;

			$src      = ( isset( $_GET[ 'src' ] ) ? $_GET[ 'src' ] : '' );
			$width    = ( isset( $_GET[ 'width' ] ) ? ( int )( $_GET[ 'width' ] ) : '' );
			$height   = ( isset( $_GET[ 'height' ] ) ? ( int )( $_GET[ 'height' ] ) : '' );
			$trimming = ( isset( $_GET[ 'trimming' ] ) ? 'true' == strtolower( $_GET[ 'trimming' ] ) : null );

            if( !is_file($src) && strpos($src,"file/") !== 0)  //ファイルが存在せず、且つfile/ディレクトリ内のファイルでない場合(index.phpとか)
                { return ;}

			if( 0 === strpos( $src , 'http' ) ) //パスがhttpから始まっている場合
				{ return; }

			if( $THUMBNAIL_OPTIONS[ 'maxWidth' ] < $width || $THUMBNAIL_OPTIONS[ 'maxHeight' ] < $height ) //サイズ指定が異常に大きい場合
				{ return; }

			$this->src = mod_Thumbnail::Create( $src , $width , $height , $trimming );
		}

		/**
			@brief  サムネイルの生成に成功したか確認する。
			@retval true  成功した場合。
			@retval false 失敗した場合。
		*/
		function hasThumbnail() //
			{ return ( $this->src ? true : false ); }

		//■変数 //
		var $src = null; ///<サムネイル画像のパス。
	}
