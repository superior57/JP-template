<?php

	//★クラス //

	/**
		@brief モデルの基底クラス。
	*/
	class AppBaseModel //
	{
		//■処理 //
		/**
			@brief エラーメッセージを出力する。
			@param[in] $iMessages エラーメッセージ配列。
		*/
		function drawErrorMessage( $iMessages ) //
		{
			foreach( $this->errors as $error => $state ) //全ての要素を処理
				{ print $iMessages[ $error ]; }
		}

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $loginUserType;
			global $loginUserRank;
			global $rec;
			global $gm;
			global $NOT_LOGIN_USER_TYPE;

			if( !$this->loginUserType ) //ユーザー種別が設定されていない場合
				{ $this->loginUserType = $loginUserType; }

			if( !$this->loginUserType ) //ユーザー認証レベルが設定されていない場合
				{ $this->loginUserRank = $loginUserRank; }

			if( !$this->rec ) //レコードデータが設定されていない場合
				{ $this->rec = ( isset( $rec ) ? $rec : null ); }

			if( !$this->gm ) //GMが設定されていない場合
			{
				if( isset( $_GET[ 'type' ] ) && array_key_exists( $_GET[ 'type' ] , $gm ) ) //type指定に一致するGMがある場合
					{ $this->gm = $gm[ $_GET[ 'type' ] ]; }
				else if( $NOT_LOGIN_USER_TYPE != $this->loginUserType ) //ログインしている場合
					{ $this->gm = $gm[ $this->loginUserType ]; }
				else //適切なGMが見つからない場合
					{ $this->gm = $gm[ 'system' ]; }
			}
		}

		//■変数 //
		var $loginUserType = null; ///<ユーザー種別。
		var $loginUserRank = null; ///<ユーザー認証レベル。
		var $rec           = null; ///<レコードデータ。
		var $gm            = null; ///<GMインスタンス。
		var $errors        = Array(); ///<エラー情報配列。
	}
