<?php

	/**
		@brief   テンプレート設定情報管理クラス。
		@details テンプレートに関する設定を管理します。
		@author  松木 昌平
		@version 1.01
		@date    2010/8/19
		@ingroup Information
	*/
	class TemplateInfo
	{
		//■初期化

		/**
			@brief テンプレート設定を初期化する。
		*/
		private static function Initialize()
		{
			if( !self::$Initialized ) //初期化されていない場合
			{
				self::ImportGlobalVarConfigs();

				self::$Initialized = true;
			}
		}

		//■取得

		/**
			@brief  システムが生成するフォームの形式を取得する。
			@retval buffer   テンプレートに自動的に埋め込まれる場合
			@retval variable コマンドコメントで任意の位置に出力する場合
		*/
		static function GetAutoFormType()
		{
			self::Initialize();

			return self::$AutoFormType;
		}

		/**
			@brief     テンプレートの標準ファイル名を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@exception LogicException           標準化できないテンプレートのラベルを指定した場合。
			@param[in] $iLabelName_ テンプレートのラベル名。
			@return    テンプレートの標準ファイル名。
		*/
		static function GetDefaultFileName( $iLabelName_ )
		{
			self::Initialize();

			Concept::IsString( $iLabelName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( array_key_exists( $iLabelName_ , self::$DefaultFileNames ) )->OrThrow( 'Logic' );

			return self::$DefaultFileNames[ $iLabelName_ ];
		}

		/**
			@brief     テンプレートの標準格納先ディレクトリ名を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@param[in] $iUserType_   テンプレートを使用するユーザー種別。
			@param[in] $iTargetName_ テンプレートのターゲット名。
			@param[in] $iLabelName_  テンプレートのラベル名。
			@return    テンプレートの標準格納先ディレクトリ名。
		*/
		static function GetDefaultDirName( $iUserType_ , $iTargetName_ , $iLabelName_ )
		{
			self::Initialize();

			Concept::IsString( $iLabelName_ )->OrThrow( 'InvalidArgument' );

			switch( $iLabelName_ )
			{
				case 'HEAD_DESIGN'            :
				case 'HEAD_DESIGN_ADMIN_MODE' :
				case 'FOOT_DESIGN'            :
					{ return 'other/common/'; }

				case 'TOP_PAGE_DESIGN'        :
				case 'OTHER_PAGE_DESIGN'      :
				{
					Concept::IsString( $iUserType_ )->OrThrow( 'InvalidArgument' );

					return 'other/user/' . $iUserType_ . '/';
				}

				case 'INCLUDE_DESIGN' :
				{
					Concept::IsString( $iUserType_ )->OrThrow( 'InvalidArgument' );

					return 'other/include/' . $iUserType_ . '/';
				}

				case 'LOGIN_PAGE_DESIGN'          :
				case 'LOGIN_FALED_DESIGN'         :
				case 'ACTIVATE_DESIGN_HTML'       :
				case 'ACTIVATE_FALED_DESIGN_HTML' :
				case 'ERROR_PAGE_DESIGN'          :
				case 'SEARCH_PAGE_CHANGE_DESIGN'  :
				case 'REGIST_FALED_DESIGN'  :
					{ return 'other/common/'; }

				case 'ACTIVATE_MAIL'      :
				case 'ACTIVATE_COMP_MAIL' :
				case 'REGIST_COMP_MAIL'   :
				{
					Concept::IsString( $iUserType_ )->OrThrow( 'InvalidArgument' );

					return 'other/mail_contents/' . $iUserType_ . '/';
				}

				case 'REGIST_ERROR_DESIGN' :
				{
					Concept::IsString( $iLabelName_ )->OrThrow( 'InvalidArgument' );

					return $iTargetName_ . '/';
				}

				default :
				{
					Concept::IsString( $iLabelName_ , $iUserType_ )->OrThrow( 'InvalidArgument' );

					return $iTargetName_ . '/' . $iUserType_ . '/';
				}
			}
		}

		/**
			@brief     テンプレートの標準レイアウトラベル名を取得する。
			@exception InvalidArgumentException 引数に不正な値を指定した場合。
			@exception LogicException           標準化できないテンプレートのラベルを指定した場合。
			@param[in] $iLabelName_ テンプレートのラベル名。
			@return    テンプレートの標準レイアウトラベル名。
		*/
		static function GetDefaultLayoutName( $iLabelName_ )
		{
			self::Initialize();

			Concept::IsString( $iLabelName_ )->OrThrow( 'InvalidArgument' );
			Concept::IsTrue( array_key_exists( $iLabelName_ , self::$DefaultLayoutNames ) )->OrThrow( 'Logic' );

			return self::$DefaultLayoutNames[ $iLabelName_ ];
		}

		/**
			@brief  非オーナーテンプレートを表すビット値を取得する。
			@return ビット値。
		*/
		static function GetOUtsiderTemplateBit()
		{
			self::Initialize();

			return self::$OutsiderTemplateBit;
		}

		/**
			@brief  オーナーテンプレートを表すビット値を取得する。
			@return ビット値。
		*/
		static function GetOwnerTemplateBit()
		{
			self::Initialize();

			return self::$OwnerTemplateBit;
		}

		/**
			@brief  テンプレートファイルの格納ディレクトリパスを取得する。
			@return ディレクトリパス。
		*/
		static function GetTemplateDir()
		{
			self::Initialize();

			if( UserInfo::isMobile() ) //携帯からアクセスしている場合
				{ return self::$MobileTemplateDir; }
			else //その他の端末からアクセスしている場合
				{ return self::$TemplateDir; }
		}

		//■特殊

		/**
			@brief     グローバル変数から設定値をインポートする。
			@attention 移行が完了するまでの仮機能です。
		*/
		private static function ImportGlobalVarConfigs()
		{
			global $template_path;
			global $FORM_TAG_DRAW_FLAG;

			self::$TemplateDir  = $template_path;
			self::$AutoFormType = $FORM_TAG_DRAW_FLAG;
		}

		//■変数
		private static $Initialized          = false;              ///<初期化フラグ
		private static $OwnerTemplateBit     = 1;                  ///<非オーナー値
		private static $OutsiderTemplateBit  = 2;                  ///<オーナー値
		private static $AutoFormType          = 'variable';        ///<システムフォームの形式
		private static $TemplateDir          = 'template/pc/';     ///<テンプレートファイルの格納ディレクトリ
		private static $MobileTemplateDir    = 'template/mobile/'; ///<モバイル用テンプレートファイルの格納ディレクトリ

		private static $DefaultFileNames = ///<テンプレートの標準ファイル名一覧
		Array(
			'HEAD_DESIGN'                => 'Head.html' ,
			'HEAD_DESIGN_ADMIN_MODE'     => 'HeadAdminMode.html' ,
			'TOP_PAGE_DESIGN'            => 'Index.html' ,
			'FOOT_DESIGN'                => 'Foot.html' ,
			'LOGIN_PAGE_DESIGN'          => 'Login.html' ,
			'LOGIN_FALED_DESIGN'         => 'LoginFailed.html' ,
			'ACTIVATE_DESIGN_HTML'       => 'Activate.html' ,
			'ACTIVATE_FALED_DESIGN_HTML' => 'ActivateFailed.html' ,
			'ERROR_PAGE_DESIGN'          => 'Error.html' ,
			'REGIST_FORM_PAGE_DESIGN'    => 'Regist.html' ,
			'REGIST_CHECK_PAGE_DESIGN'   => 'RegistCheck.html' ,
			'REGIST_COMP_PAGE_DESIGN'    => 'RegistComp.html' ,
			'REGIST_ERROR_DESIGN'        => 'RegistError.html' ,
			'REGIST_FALED_DESIGN'        => 'RegistFailed.html' ,
			'EDIT_FORM_PAGE_DESIGN'      => 'Edit.html' ,
			'EDIT_CHECK_PAGE_DESIGN'     => 'EditCheck.html' ,
			'EDIT_COMP_PAGE_DESIGN'      => 'EditComp.html' ,
			'DELETE_CHECK_PAGE_DESIGN'   => 'DeleteCheck.html' ,
			'DELETE_COMP_PAGE_DESIGN'    => 'DeleteComp.html' ,
			'SEARCH_FORM_PAGE_DESIGN'    => 'Search.html' ,
			'SEARCH_RESULT_DESIGN'       => 'SearchResult.html' ,
			'SEARCH_NOT_FOUND_DESIGN'    => 'SearchFailed.html' ,
			'SEARCH_LIST_PAGE_DESIGN'    => 'List.html' ,
			'SEARCH_PAGE_CHANGE_DESIGN'  => 'SearchPageChange.html' ,
			'INFO_PAGE_DESIGN'           => 'Info.html' ,
			'ACTIVATE_MAIL'              => 'Activate.txt' ,
			'ACTIVATE_COMP_MAIL'         => 'ActivateComp.txt' ,
			'REGIST_COMP_MAIL'           => 'RegistComp.txt'
		);

		private static $DefaultLayoutNames = ///<レイアウトの標準ファイル名一覧
		Array(
			'TOP_PAGE_DESIGN'            => 'index' ,
			'LOGIN_PAGE_DESIGN'          => 'other' ,
			'LOGIN_FALED_DESIGN'         => 'error' ,
			'ACTIVATE_DESIGN_HTML'       => 'other' ,
			'ACTIVATE_FALED_DESIGN_HTML' => 'error' ,
			'ERROR_PAGE_DESIGN'          => 'error' ,
			'REGIST_FORM_PAGE_DESIGN'    => 'input' ,
			'REGIST_CHECK_PAGE_DESIGN'   => 'inputCheck' ,
			'REGIST_COMP_PAGE_DESIGN'    => 'inputComp' ,
			'REGIST_FALED_DESIGN'        => 'other' ,
			'EDIT_FORM_PAGE_DESIGN'      => 'input' ,
			'EDIT_CHECK_PAGE_DESIGN'     => 'inputCheck' ,
			'EDIT_COMP_PAGE_DESIGN'      => 'inputComp' ,
			'DELETE_CHECK_PAGE_DESIGN'   => 'inputCheck' ,
			'DELETE_COMP_PAGE_DESIGN'    => 'inputComp' ,
			'SEARCH_FORM_PAGE_DESIGN'    => 'search' ,
			'SEARCH_RESULT_DESIGN'       => 'searchResult' ,
			'SEARCH_NOT_FOUND_DESIGN'    => 'search' ,
			'INFO_PAGE_DESIGN'           => 'info' ,
			'OTHER_PAGE_DESIGN'          => 'other'
		);
	}

?>