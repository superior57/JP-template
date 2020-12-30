<?php

	//★クラス //

	/**
		@brief   既定のデータ編集処理のモデル。
	*/
	class AppEditModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief 編集内容確認に関する処理を実行する。
		*/
		function doConfirm() //
		{
			global $gm;

			$this->rec = $this->db->setRecord( $this->rec , $_POST );

			$this->sys->editProc( $gm , $this->rec , $this->loginUserType , $this->loginUserRank , true );
		}

		/**
			@brief 編集処理を実行する。
		*/
		function doEdit() //
		{
			global $gm;
			global $CONF_FEED_ENABLE;
			global $CONF_FEED_TABLES;
			global $PASSWORD_MODE;
			global $THIS_TABLE_IS_USERDATA;

			if( !$this->canEdit() ) //入力内容に問題がある場合
				{ return; }

			$this->rec = $this->db->setRecord( $this->rec , $_POST );

			if( !$this->sys->registCompCheck( $gm , $this->rec , $this->loginUserType , $this->loginUserRank , true ) ) //入力内容に問題がある場合
				{ return; }

			$oldRec = $this->db->selectRecord( $_GET[ 'id' ] );

			$this->sys->editProc( $gm , $this->rec , $this->loginUserType , $this->loginUserRank );

			if( $THIS_TABLE_IS_USERDATA[ $_GET[ 'type' ] ] )
			{
				$newPass = $this->db->getData( $this->rec , 'pass' );
				$oldPass = $this->db->getData( $oldRec , 'pass' );

				if( $newPass != SystemUtil::decodePassword( $oldPass ) )
					{ $this->db->setData( $this->rec , 'pass' , SystemUtil::encodePassword( $newPass , $PASSWORD_MODE ) ); }
				else
					{ $this->db->setData( $this->rec , 'pass' , SystemUtil::encodePassword( $oldPass , $PASSWORD_MODE ) ); }
			}

			foreach( $this->db->colName as $column ) //全てのカラムを処理
			{
				$data       = $this->db->getData( $this->rec , $column );
				$updateData = Extension::Database_updateExtension( $this->db->colExtend[ $column ], $data );

				if( $updateData != $data ) //データに変化があった場合
					{ $this->db->setData( $this->rec , $column , $updateData ); }
			}

			$this->db->updateRecord( $this->rec );
			$this->sys->editComp( $gm , $this->rec , $oldRec , $this->loginUserType , $this->loginUserRank );

			if( $CONF_FEED_ENABLE && in_array( $this->type , $CONF_FEED_TABLES ) ) //feed更新設定が有効な場合
				{ SystemUtil::async( 'FeedApi' , 'update' , array(  'targetType' => $this->type ) ); }

			$this->succeededEdit = true;
		}

		/**
			@brief 前の画面に戻る。
		*/
		function goBack() //
			{ $this->rec = $this->db->setRecord( $this->rec , $_POST ); }

		/**
			@brief クエリを更新する。
		*/
		function updateQuery() //
		{
			global $CONFIG_SQL_FILE_TYPES;

			foreach( $this->db->colName as $column ) //全てのカラムを処理
			{
				$useOriginData = false;

				if( isset( $_POST[ $column ] ) ) //入力がある場合
				{
					if( null == $_POST[ $column ] ) //nullが入力されている場合
					{
						if( 'password' == $this->db->colType[ $column ] ) //パスワードカラムの場合
							{ $useOriginData = true; }
					}
				}
				else //入力がない場合
				{
					if( in_array( $this->db->colType[ $column ] , $CONFIG_SQL_FILE_TYPES ) ) //ファイルカラムの場合
					{
						if( 'true' != $_POST[ $column . '_DELETE' ] ) //削除要求がない場合
							{ $useOriginData = true; }
					}
					else if( !isset( $_POST[ $column . '_CHECKBOX' ] ) ) //複数選択カラムではない場合
						{ $useOriginData = true; }
				}

				if( $useOriginData ) //レコードデータを使用する場合
				{
					if( 'password' == $this->db->colType[ $column ] )
						{ $_POST[ $column ] = SystemUtil::decodePassword( $this->db->getData( $this->rec , $column ) ); }
					else
						{ $_POST[ $column ] = $this->db->getData( $this->rec , $column ); }
				}
				else //クエリを使用する場合
					{ $_POST[ $column ] = GUIManager::replaceString( $_POST[ $column ] , $this->db->colExtend[ $column ] , $this->db->colType[ $column ] ); }
			}
		}

		/**
			@brief 入力内容を検証する。
		*/
		function verifyInput() //
		{
			global $gm;

			if( $this->sys->registCheck( $gm , true , $this->loginUserType , $this->loginUserRank ) ) //入力に問題がない場合
				{ $this->noInputError = true; }
		}

		/**
			@brief トークンの有効性を検証する。
		*/
		function verifyToken() //
			{ ConceptSystem::CheckAuthenticityToken()->OrThrow( 'IllegalTokenAccess' ); }

		//■データ取得 //

		/**
			@brief  編集可能な状態か確認する。
			@retval true  編集可能である場合。
			@retval false 編集可能ではない場合。
		*/
		function canEdit() //
			{ return $this->noInputError; }

		/**
			@brief  編集処理が成功したか確認する。
			@retval true  成功した場合。
			@retval false 失敗した場合。
		*/
		function succeededEdit() //
			{ return $this->succeededEdit; }

		//■コンストラクタ・デストラクタ //

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			global $gm;

			parent::__construct();

		    if( 'admin' == $this->loginUserType ) //管理者でログインしている場合
				{ $tableType = 'all'; }

			$this->type = $_GET[ 'type' ];
			$this->sys  = SystemUtil::getSystem( $this->type );
			$this->gm   = $gm[ $this->type ];
			$this->db   = $this->gm->getDB();
			$this->rec  = System::setPageRecord( $this->db , $tableType );

			ConceptSystem::CheckTableEditUser( $this->db , $this->rec )->OrThrow( 'IllegalAccess' );

			System::$checkData = new CheckData( $gm , false , $this->loginUserType , $this->loginUserRank , $this->type );
		}

		//■変数 //
		var     $type          = null;  ///<ターゲットタイプ。
		var     $sys           = null;  ///<Systemインスタンス。
		var     $gm            = null;  ///<GMインスタンス。
		var     $db            = null;  ///<DBインスタンス。
		private $noInputError  = false; ///<入力に問題がない場合はtrue。
		private $canEdit       = false; ///<編集処理が実行可能な場合はtrue。
		private $succeededEdit = false; ///<編集処理に成功した場合はtrue。
	}
