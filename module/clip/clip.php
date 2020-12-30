<?php

	class mod_clip extends command_base
	{
	
		function getCount( &$gm, $rec, $args ){
			List($itemsID) = $args;
			$db = GMList::getDB("clip");
			$table = $db->getTable();
			$table = $db->joinTable($table,"clip","nUser","user_id","id");
			$table = $db->searchTable($table,"c_id","=",$itemsID);
			$this->addBuffer($db->getRow($table));
		}
		
		/**
		 * 新規レコードを登録
		 *
		 * @param user_id クリップの所有ユーザID。
		 * @param c_type クリップ対象のテーブル名。
		 * @param c_id クリップ対象ID。
		 */
	    function regist( $param )
		{
			$user_id = $this->getUserId();
			$c_type = $param["c_type"];
			$c_id = $param["c_id"];
			
			$gm		 = SystemUtil::getGMforType( 'clip' );
			$db		 = $gm->getDB();
			
			$rec	 = $this->getRecord( $user_id, $c_type, $c_id );
			if( !isset($rec) )
			{
				$rec	 = $db->getNewRecord();
				$db->setData( $rec, 'id', md5($user_id.$c_id) );
				$db->setData( $rec, 'user_id', $user_id );
				$db->setData( $rec, 'c_type', $c_type );
				$db->setData( $rec, 'c_id', $c_id );
	
				$db->addRecord( $rec );
			}
			
			if($param["jump"] != "") SystemUtil::innerLocation( $param["jump"] );
			
	    }
		
		/**
		 * レコードを削除
		 *
		 * @param user_id クリップの所有ユーザID。
		 * @param c_type クリップ対象のテーブル名。
		 * @param c_id クリップ対象ID。
		 */
	    function delete( $param )
		{
			$user_id = $this->getUserId();
			$c_type = $param["c_type"];
			$c_id = $param["c_id"];
	
			$gm		 = SystemUtil::getGMforType( 'clip' );
			$db		 = $gm->getDB();
			
			$rec	 = $this->getRecord( $user_id, $c_type, $c_id );
			if( isset($rec) ) { $db->deleteRecord( $rec ); }
	
			if($param["jump"] != "") SystemUtil::innerLocation( $param["jump"] );
	    }
		
		/**
		 * レコードを取得
		 *
		 * @param user_id クリップの所有ユーザID。
		 * @param c_type クリップ対象のテーブル名。
		 * @param c_id クリップ対象ID。
		 * @return クリップレコード。
		 */
	    function getRecord( $user_id, $c_type, $c_id )
		{
			$gm		 = SystemUtil::getGMforType( 'clip' );
			$db		 = $gm->getDB();
			
			$table	 = $db->getTable();
			$table	 = $db->searchTable( $table, 'user_id' , '=' , $user_id );
			$table	 = $db->searchTable( $table, 'c_type' , '=' , $c_type );
			$table	 = $db->searchTable( $table, 'c_id' , '=' , $c_id );
			
			$rec	 = null;
			if( $db->getRow($table) > 0 ) { $rec	 = $db->getRecord( $table, 0 ); }
			
			return $rec;
	    }
		
		
		      
		/**
		 * 登録・削除ボタンを表示
		 * notLoginがfalseの場合voidデザインに処理を分岐
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 */
	    function drawButton( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $LOGIN_ID;
			global $NOT_LOGIN_USER_TYPE;
			// **************************************************************************************
	
			// レコードが存在しない場合登録ボタン、既に存在する場合削除ボタンを表示
			$user_id	 = $this->getUserId(); 
			$c_type		 = $args[0]; 
			$c_id		 = $args[1]; 
			
			$rec		 = $this->getRecord( $user_id, $c_type, $c_id );
			$type		 = 'regist';
			if(isset($rec)) { $type		 = 'delete'; }
			$gm->setVariable( 'ID', $c_id );
			$gm->setVariable( 'TYPE', $c_type );
	
			//if( Conf::getData( 'job', 'use_clip' ) != 'on' && $loginUserType == $NOT_LOGIN_USER_TYPE ) { $type = 'void'; }
	
			$design	 = Template::getLabelFile( 'CLIP_BUTTON' );
			$button	 = $gm->getString( $design , $rec , $type );
					
			$this->addBuffer( $button );
		
	    }
		
			
		/**
		 * ユーザID
		 * notLoginがtrueの場合未ログイン時には一時IDを返し識別可能となる
		 *
		 * @return userIdを返す。
		 */
		function getUserId()
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $LOGIN_ID;
			global $loginUserType;
			global $NOT_LOGIN_USER_TYPE;
			// **************************************************************************************
			$id	 = "none";
			if( $loginUserType != $NOT_LOGIN_USER_TYPE) { $id	 = $LOGIN_ID; }
			/*else if( Conf::getData( 'job', 'use_clip' ) == 'on' )
			{
				$id	 = $_SESSION['clipid'];
				if(!isset($_SESSION['clipid']))
				{
					$code = md5(time().$_SERVER['REMOTE_ADDR']);
					$_SESSION['clipid'] = $code;
					$id	 = $code;
				}
			}*/
	
			return $id;
		}

    /**
     * 企業ユーザー向け 検討中リストの件数を描画する
     *
     * @return void
     */
    function drawCount4c()
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        global $LOGIN_ID;
        // **************************************************************************************

        if ($loginUserType != 'cUser') {
            return;
        }

        // ログイン中の企業ユーザーの検討中リスト
        $db = GMList::getDB('clip');
        $table = $db->getTable();
        $table = $db->searchTable($table, 'user_id', '=', $LOGIN_ID);
        $table = $db->searchTable($table, 'c_type', '=', 'resume');
        $idList = $db->getDataList($table, 'c_id');

        // 検討中リストから削除済みの履歴書を除外
        $rDB = GMList::getDB('resume');
        $rTable = $rDB->getTable();
        if (isset($idList)) {
            $rTable = $rDB->searchTable($rTable, 'id', 'in', $idList);
        } else {
            $rTable = $rDB->getEmptyTable();
        }

        $this->addBuffer($rDB->getRow($rTable));
    }

	}
