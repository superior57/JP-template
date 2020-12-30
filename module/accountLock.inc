<?php

	//★設定 //

	//accountLockテーブルの設定
	$EDIT_TYPE                            = 'accountLock';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_IS_NOHTML[ $EDIT_TYPE ]   = true;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'module/accountLock.csv';
	$TDB[ $EDIT_TYPE ]                    = 'module/accountLock.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'ACL';
	$ID_LENGTH[ $EDIT_TYPE ]              = 16;

	//accountLockConfigテーブルの設定
	$EDIT_TYPE                            = 'accountLockConfig';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_IS_NOHTML[ $EDIT_TYPE ]   = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'module/accountLockConfig.csv';
	$TDB[ $EDIT_TYPE ]                    = 'module/accountLockConfig.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'ACLC';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;

	//★クラス //

	/**
		@brief アカウントロックに関連する処理。
	*/
	class accountLockLogic //
	{
		//■データ取得 //

		/**
			@brief  このクラスが処理するテーブル名を取得する。
			@return テーブル名。
		*/
		function getType() //
			{ return 'accountLock'; }

		/**
			@brief     ログインIDからユーザー種別を取得する。
			@param[in] $iLoginID_ ログインID。
			@retval    ユーザー種別 該当するユーザーが見つかった場合
			@retval    null         該当するユーザーが見つからなかった場合
			@attention この関数は accountLockConfig の影響を受けません。\n
			           アカウントロックの対象外のユーザー種別であっても、該当するログインIDであれば値を返します。
			@remarks   この関数から非ユーザーテーブルのユーザー種別が返ることはありません。
		*/
		function getUserType( $iLoginID_ ) //
		{
			if( !$iLoginID_ ) //ログインIDが空の場合
				{ return null; }

			global $TABLE_NAME;
			global $THIS_TABLE_IS_USERDATA;
			global $LOGIN_KEY_COLUM;

			foreach( $TABLE_NAME as $tableName ) //全てのテーブルを処理
			{
				if( !$THIS_TABLE_IS_USERDATA[ $tableName ] ) //ユーザーテーブルではない場合
					{ continue; }

				//該当するログインIDを持つユーザーを探す
				$db    = GMList::getDB( $tableName );
				$table = $db->getTable();
				$table = $db->searchTable( $table , $LOGIN_KEY_COLUM[ $tableName ] , '=' , $iLoginID_ );
				$row   = $db->getRow( $table );

				if( !$row ) //ユーザーが見つからなかった場合
					{ continue; }

				return $tableName;
			}

			return null;
		}

		/**
			@brief ログイントライ回数が規制条件を超えているか確認する。
			@retval true  規制条件を超えている場合。
			@retval false 規制条件を超えていない、またはログインIDが送信されていない場合。
		*/
		function isTryOver() //
		{
			global $LOGIN_KEY_FORM_NAME;

			if( !array_key_exists( $LOGIN_KEY_FORM_NAME , $_POST ) ) //ログインIDが送信されていない場合
				{ return false; }

			$loginID  = $_POST[ $LOGIN_KEY_FORM_NAME ];
			$userType = self::getUserType( $loginID );

			if( !$userType ) //ユーザー種別が取得できない場合
				{ return false; }

			if( !accountLockConfigLogic::isValidUserType( $userType ) ) //アカウントロック設定が有効でない場合
				{ return false; }

			$db    = GMList::getDB( self::getType() );
			$table = $db->getTable();
			$table = $db->searchTable( $table , 'login_id' , '=' , $loginID );
			$row   = $db->getRow( $table );

			if( !$row ) //ログイントライ履歴が見つからない場合
				{ return false; }

			$rec      = $db->getRecord( $table , 0 );
			$unlockTime = $db->getData( $rec , 'unlock_time' );

			if( !$unlockTime || time() >= $unlockTime ) //ロック解除時間を経過している場合
				{ return false; }

			return true;
		}

		//■処理 //

		/**
			@brief ログイントライ履歴を更新する。
		*/
		function addTryCount() //
		{
			global $LOGIN_KEY_FORM_NAME;

			if( !array_key_exists( $LOGIN_KEY_FORM_NAME , $_POST ) ) //ログインIDが送信されていない場合
				{ return; }

			$loginID  = $_POST[ $LOGIN_KEY_FORM_NAME ];
			$userType = self::getUserType( $loginID );

			if( !$userType ) //ユーザー種別が特定できない場合
				{ return; }

			$db    = GMList::getDB( self::getType() );
			$table = $db->getTable();
			$table = $db->searchTable( $table , 'login_id' , '=' , $loginID );
			$row   = $db->getRow( $table );

			if( !$row ) //履歴が見つからない場合
			{
				$rec = $db->getNewRecord();

				$db->setData( $rec , 'id' , SystemUtil::getNewID( $db , self::getType() ) );
				$db->setData( $rec , 'login_id' , $loginID );

				$tryTimes = Array();
			}
			else //履歴が見つかった場合
			{
				$rec      = $db->getRecord( $table , 0 );
				$tryTime  = $db->getData( $rec , 'try_time' );
				$tryTimes = explode( '/' , $tryTime );
			}

			if( 256 <= count( $tryTimes ) ) //履歴の保持数が最大数を超える場合
				{ array_shift( $tryTimes ); }

			array_push( $tryTimes , time() );

			$db->setData( $rec , 'try_time' , implode( '/' , $tryTimes ) );
			$db->setData( $rec , 'unlock_token' , md5( rand() ) );
			$db->setData( $rec , 'onetime_password' , substr( md5( rand() ) , 0 , 8 ) );

			$cDB         = GMList::getDB( 'accountLockConfig' );
			$cRec        = accountLockConfigLogic::getConfigRec( $userType );
			$maxTryTime  = $cDB->getData( $cRec , 'max_try_time' );
			$maxTryCount = $cDB->getData( $cRec , 'max_try_count' );
			$lockTime    = $cDB->getData( $cRec , 'lock_time' );
			$beginTime   = time() - $maxTryTime;

			$tryCount = 0;

			foreach( $tryTimes as $tryTime ) //ログイントライ時間の履歴を処理
			{
				if( $beginTime <= $tryTime ) //カウント開始時間より後にログインしている場合
					{ ++$tryCount; }
			}

			if( $maxTryCount <= $tryCount ) //ロック条件を満たしている場合
				{ $db->setData( $rec , 'unlock_time' , time() + $lockTime * 60 ); }

			if( !$row ) //履歴が見つからない場合
				{ $db->addRecord( $rec ); }
			else //履歴が見つかった場合
				{ $db->updateRecord( $rec ); }

			$isOver = self::isTryOver();

			if( $isOver && 'admin' == $userType ) //管理者のアカウントがロックされた場合
			{
				$template = Template::getLabelFile( 'ACCOUNT_LOCK_ALERT_MAIL' );

				$sysDB   = GMList::getDB( 'system' );
				$sysRec  = $sysDB->selectRecord( 'ADMIN' );
				$sysMail = $sysDB->getData( $sysRec , 'mail_address' );

				$gm = GMList::getGM( self::getType() );

				Mail::send( $template , $sysMail , $sysMail , $gm , $rec );
			}
		}
	}

	/**
		@brief アカウントロックの設定に関連する処理。
	*/
	class accountLockConfigLogic //
	{
		//■データ取得 //

		/**
			@brief  このクラスが処理するテーブル名を取得する。
			@return テーブル名。
		*/
		function getType() //
			{ return 'accountLockConfig'; }

		/**
			@brief     ユーザーのアカウントロック設定が有効か確認する。
			@exception InvalidArgumentException $iUserType_ に無効な値を指定した場合。
			@param[in] $iUserType_ ユーザー種別。
			@retval    true  ユーザーがアカウントロックを使用する場合。
			@retval    false ユーザーがアカウントロックを使用しない、または設定が見つからない場合。
		*/
		function isValidUserType( $iUserType_ ) //
		{
			if( !$iUserType_ ) //ユーザー種別が空の場合
				{ throw new InvalidArgumentException( '引数 $iUserType_ は無効です' ); }

			$db    = GMList::getDB( self::getType() );
			$table = $db->getTable();
			$table = $db->searchTable( $table , 'user_type' , '=' , $iUserType_ );
			$row   = $db->getRow( $table );

			if( !$row ) //設定が見つからない場合
				{ return false; }

			$rec   = $db->getRecord( $table , 0 );
			$isUse = $db->getData( $rec , 'is_use' );

			return $isUse;
		}

		/**
			@brief     ユーザーのアカウントロック設定を取得する。
			@exception InvalidArgumentException $iUserType_ に無効な値を指定した場合。
			@exception RuntimeException                     設定が見つからない場合。
			@param[in] $iUserType_ ユーザー種別。
			@return    該当ユーザー種別に対するアカウントロック設定。
		*/
		function getConfigRec( $iUserType_ ) //
		{
			if( !$iUserType_ ) //ユーザー種別が空の場合
				{ throw new InvalidArgumentException( '引数 $iUserType_ は無効です' ); }

			$db    = GMList::getDB( self::getType() );
			$table = $db->getTable();
			$table = $db->searchTable( $table , 'user_type' , '=' , $iUserType_ );
			$row   = $db->getRow( $table );

			if( !$row ) //設定が見つからない場合
				{ throw new RuntimeException( 'getConfigRec を完了できません[' . $iUserType_ . ']' ); }

			$rec = $db->getRecord( $table , 0 );

			return $rec;
		}
	}
