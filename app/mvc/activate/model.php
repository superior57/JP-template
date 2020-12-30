<?php

	//★クラス //

	/**
		@brief 既定のユーザー認証のモデル。
	*/
	class AppActivateModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief アクティベート処理を実行する。
		*/
		function doActivate() //
		{
			global $gm;
			global $CONF_FEED_ENABLE;
			global $CONF_FEED_TABLES;

			$table = $this->db->getTable();
			$table = $this->db->searchTable( $table , 'id' , '=' , $_GET[ 'id' ] );
			$table = $this->db->limitOffset( $table , 0 , 1 );
			$row   = $this->db->getRow( $table );

			if( 0 >= $row ) //ユーザーが見つからない場合
				{ return; }

			$this->rec  = $this->db->getRecord( $table , 0 );
			$id         = $this->db->getData( $this->rec , 'id' );
			$mail       = $this->db->getData( $this->rec , 'mail' );
			$md5        = md5( $id . $mail );

			if( $_GET[ 'md5' ] != $md5 ) //md5が一致しない場合
				{ return; }

			$this->succeededActivate = $this->sys->activateAction( $gm , $this->rec , $this->loginUserType , $this->loginUserRank );

			if( $this->succeededActivate ) //アクティベートに成功した場合
			{
				if( $CONF_FEED_ENABLE && in_array( $this->type , $CONF_FEED_TABLES ) ) //feed更新設定が有効な場合
					{ SystemUtil::async( 'FeedApi' , 'update' , array( 'targetType' => $this->type ) ); }
			}
		}

		//■データ取得 //

		/**
			@brief アクティベートが成功したか確認する。
			@retval true  成功した場合。
			@retval false 失敗した場合。
		*/
		function succeededActivate() //
			{ return $this->succeededActivate; }

		/**
			@brief コンストラクタ。
		*/
		function __construct() //
		{
			parent::__construct();

			$this->type = $_GET[ 'type' ];
			$this->sys  = SystemUtil::getSystem( $this->type );
			$this->db   = GMList::getDB( $this->type );
		}

		var     $type             = null;  ///<ターゲットタイプ。
		var     $sys              = null;  ///<Systemインスタンス。
		var     $db               = null;  ///<DBインスタンス。
		private $succeedeActivate = false; ///<アクティベートに成功した場合はtrue。
	}
