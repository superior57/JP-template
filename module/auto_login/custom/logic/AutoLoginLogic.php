<?php
class AutoLoginLogic {

	static function getType(){
		return self::$type;
	}

	/*
	 * クッキーにキーをセット
	 */
	static function setcookie($remember_key){
		global $REMEMBER_COOKIE;
		setcookie($REMEMBER_COOKIE,$remember_key, time() + 60*60*24*7*2);
	}

	/*
	 * クッキーからキーを削除
	 */
	static function deletecookie($remember_key){
		global $REMEMBER_COOKIE;
		setcookie($REMEMBER_COOKIE,$remember_key, time() -3600);
	}

	/*
	 * 認証キーを登録
	 */
	static function registKey($owner_id){

		$db = GMList::getDB(self::getType());
		$rec = $db->getNewRecord();

		$cookieKey = SystemUtil::getAuthenticityToken();

		$db->setData( $rec , 'owner' , $owner_id );
		$db->setData( $rec , 'cookiekey' , $cookieKey );
		$db->setData( $rec , 'ip' , $_SERVER["REMOTE_ADDR"] );
		$db->setData( $rec , 'regist' , time() );
		$db->setData( $rec , 'limittime' , time() + 60*60*24*7*2 );
		$db->addRecord($rec);
		self::setcookie($cookieKey);
	}

	/*
	 * ログアウト時など認証キーを削除
	 */
	static function deleteKey(){
		global $REMEMBER_COOKIE;

		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,'cookiekey','=',$_COOKIE[$REMEMBER_COOKIE]);

		if($db->getRow($table) == 0){ return; }

		$rec = $db->getRecord($table,0);
		$db->setData( $rec , 'cookiekey' , null);
		$db->setData( $rec , 'ip' , null );
		$db->setData( $rec , 'edit' , time() );
		$db->setData( $rec , 'limittime' , 0 );
		$db->deleteRecord($rec);
		self::deletecookie($REMEMBER_COOKIE);
	}

	/*
	 * 次回アクセス時等、自動ログインの期限、クッキー情報をチェック
	 */

	static function  checkValidate(){
		global $REMEMBER_COOKIE;

		if(!$_COOKIE[$REMEMBER_COOKIE]){return false;}

		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,'cookiekey','=',$_COOKIE[$REMEMBER_COOKIE]);
		$table = $db->searchTable($table,'limittime','>',time());
		$table = $db->searchTable($table,'ip','=',$_SERVER["REMOTE_ADDR"]);

		return $db->getRow($table);
	}

	/*
	 * クッキー情報からユーザーIDを収得
	 * */
	static function getId(){
		global $REMEMBER_COOKIE;

		$db = GMList::getDB(self::getType());
		$table = $db->getTable();
		$table = $db->searchTable($table,'cookiekey','=',$_COOKIE[$REMEMBER_COOKIE]);
		$table = $db->searchTable($table,'ip','=',$_SERVER["REMOTE_ADDR"]);
		$table = $db->searchTable($table,'delete_key','=',false);

		if($db->getRow($table) == 0){return;}

		$rec = $db->getRecord($table, 0);

		return $db->getData($rec,'owner');
	}

	function getUserType($userID){
		global $TABLE_NAME;
		global $THIS_TABLE_IS_USERDATA;
		global $NOT_LOGIN_USER_TYPE;

		for($i=0; $i<count($TABLE_NAME); $i++){
			if(  $THIS_TABLE_IS_USERDATA[ $TABLE_NAME[$i] ]  ){
				$id = SystemUtil::getTableData($TABLE_NAME[$i], $userID, "id");
				if(isset($id) && strlen($id)){
					return $TABLE_NAME[$i];
				}
			}
		}
		return $NOT_LOGIN_USER_TYPE;
	}

	private static $type = 'auto_login';
}