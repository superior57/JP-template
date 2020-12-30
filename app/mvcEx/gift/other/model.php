<?php

	//★クラス //

	/**
		@brief   既定の静的ページのモデル。
	*/
	class AppgiftOtherModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief 静的ページを検索する。
		*/
		function searchPage() //
		{
			global $template_path;

			if( $_GET[ 'page' ] ) //pageクエリが指定されている場合
			{
				if( preg_match( '/\W/' , $_GET[ 'page' ] ) ) //英数字以外が含まれている場合
					{ return; }

				$templatePath = $template_path . 'other/' . $_GET[ 'page' ] . '.html';
			}
			else if( $_GET[ 'key' ] ) //keyクエリが指定されている場合
			{
				$type         = ( $_GET[ 'type' ] ? '_' . $_GET[ 'type' ] : '' );
				$templatePath = Template::getTemplate( $this->loginUserType , $this->loginUserRank , $_GET[ 'key' ] , 'OTHER_PAGE_DESIGN' . $type );
			}
			else //指定がない場合
				{ return; }

			if( !is_file( $templatePath ) ) //ファイルが見つからない場合
				{ return; }

			$this->staticTemplatePath = $templatePath;
		}

		//■データ取得 //

		/**
			@brief  ページが見つかったか確認する。
			@retval true  ページが見つかった場合。
			@retval false ページが見つからない場合。
		*/
		function hasSearchResult() //
			{ return ( $this->staticTemplatePath ? true : false ); }

		//■変数 //
		var $staticTemplatePath = null;  ///<静的ページのパス。
	}
