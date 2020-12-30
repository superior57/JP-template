<?php

	//★クラス //

	/**
		@brief 既定の一般ページのビュー。
	*/
	class AppPageView extends AppBaseView //
	{
		//■処理 //

		/**
			@brief     ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function setBuffer( $iModel ) //
		{
			global $mobile_flag;
			global $sp_flag;
			global $terminal_type;
			global $sp_mode;
			global $page_path;
			global $FileBase;

			$rec             = $iModel->db->getRecord( $iModel->table , 0 );
			$disableTerminal = $iModel->db->getData( $rec , 'link_terminal' );

			// 検索エンジンから&sp=trueがついたURLをPCでクリックした場合は、SPソースを表示
			$enablePC     = ( false === strpos( $disableTerminal , 'pc' ) );
			$enableMobile = ( $mobile_flag && false === strpos( $disableTerminal , 'mobile' ) );
			$enableSP     = ( false === strpos( $disableTerminal , 'smartphone' ) );

			if( $sp_flag && ( $_GET[ 'sp' ] || $sp_mode ) ) //スマートフォン版の使用が要求されている場合
			{
				if( $enableSP ) //スマートフォンの表示を許可する場合
				{
					$templatePath = $page_path . $iModel->db->getData( $rec , 'id' ) . '.sp.dat';

					if( !$FileBase->file_exists( $templatePath ) ) //ファイルがない場合
						{ $templatePath = $page_path . $iModel->db->getData( $rec , 'name' ) . '.sp.dat'; }

					$templatePath = $FileBase->getfilepath($templatePath);
				}
				else //端末が有効でない場合
					{ $templatePath = Template::getLabelFile( 'ERROR_PAGE_DESIGN' ); }
			}
			else if( $mobile_flag && ( $_GET[ 'mobile' ] || $terminal_type ) ) //モバイル版の使用が要求されている場合
			{
				if( $enableMobile ) //携帯の表示を許可する場合
				{
					$templatePath = $page_path . $iModel->db->getData( $rec , 'id' ) . '.mob.dat';

					if( !$FileBase->file_exists( $templatePath ) ) //ファイルがない場合
						{ $templatePath = $page_path . $iModel->db->getData( $rec , 'name' ) . '.mob.dat'; }

					$templatePath = $FileBase->getfilepath($templatePath);
				}
				else //端末が有効でない場合
					{ $templatePath = Template::getLabelFile( 'ERROR_PAGE_DESIGN' ); }
			}
			else if( $enablePC ) //モバイル版の使用が要求されていない場合
			{
				$templatePath = $page_path . $iModel->db->getData( $rec , 'id' ) . '.dat';

				if( !$FileBase->file_exists( $templatePath ) ) //ファイルがない場合
					{ $templatePath = $page_path . $iModel->db->getData( $rec , 'name' ) . '.dat'; }

				$templatePath = $FileBase->getfilepath($templatePath);
			}
			else //端末が有効でない場合
				{ $templatePath = Template::getLabelFile( 'ERROR_PAGE_DESIGN' ); }

			$this->buffer = $iModel->gm->getString( $templatePath , $rec , null );
		}

		function getBuffer(){
			return $this->buffer;
		}

		function drawPage( $iModel ) //
		{
			$this->setBuffer($iModel);
			$this->drawContentsWithHeadFoot( $iModel , $this->buffer );
		}

		function drawContentsWithHeadFoot( $iModel , $iContents ) //
		{
			global $gm;
			$rec = $iModel->db->getFirstRecord($iModel->table);
			$name = $iModel->db->getData($rec,"name");

			if($iModel->loginUserType =="admin"){
				print System::getHead( $gm , $iModel->loginUserType , $iModel->loginUserRank );
				print $iContents;
				print System::getFoot( $gm , $iModel->loginUserType , $iModel->loginUserRank );
			}else{
				switch($name){
					default:
						print System::getHead( $gm , $iModel->loginUserType , $iModel->loginUserRank );
						print $iContents;
						print System::getFoot( $gm , $iModel->loginUserType , $iModel->loginUserRank );
						break;
				}
			}

		}

		/**
			@brief     プレビューページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawPreviewPage( $iModel ) //
		{
			global $loginUserType;
			global $template_path;
			global $sp_mode;

			$iModel->loginUserType = $_GET[ 'authority' ];
			$loginUserType         = $_GET[ 'authority' ];

			if( $_GET[ 'sp' ] )
			{
				$template_path = './template/sp/';
				$sp_mode       = true;
			}

			$this->drawPage( $iModel );
		}

		/**
			@brief     プレビュー一覧ページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawPreviewListPage( $iModel ) //
		{
			$row = $iModel->db->getRow( $iModel->table );
			$str = '';

			for( $i = 0 ; $row > $i ; ++$i )
			{
				$rec        = $iModel->db->getRecord( $iModel->table , $i );
				$authoritys = $iModel->db->getData( $rec , 'authority' );
				$authoritys = explode( '/' , $authoritys );

				foreach( $authoritys as $authority )
				{
					$iModel->gm->setVariable( 'authority' , $authority );
					$str .= Template::getTemplateString( $iModel->gm , $rec , $iModel->loginUserType , $iModel->loginUserRank , 'page' , 'PAGE_LIST' );
				}
			}

			$iModel->gm->setVariable( 'page_list' , $str );

			ob_start();

			Template::drawTemplate( $iModel->gm , null , $iModel->loginUserType , $iModel->loginUserRank , 'page' , 'PAGE_LIST_FORMAT' );

			$this->drawContentsWithHeadFoot( $iModel , ob_get_clean() );
		}

		/**
			@brief     エラーページを出力する。
			@param[in] $iModel modelインスタンス。
		*/
		function drawErrorPage( $iModel ) //
			{ $this->drawContentsWithHeadFoot( $iModel , $iModel->gm->getString( Template::getLabelFile( 'ERROR_PAGE_DESIGN' ) , null , null ) ); }

		private $buffer = null;
	}
