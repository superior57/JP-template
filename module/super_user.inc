<?php

// super user

/**********       権限カテゴリの定義          **********/

	$EDIT_TYPE                            = 'suCategory';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'super_user/su_category.csv';
	$TDB[ $EDIT_TYPE ]                    = 'super_user/su_category.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'SUC';
	$ID_LENGTH[ $EDIT_TYPE ]              = 5;

/**********       権限の定義          **********/

	$EDIT_TYPE                            = 'super_user';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'super_user/super_user.csv';
	$TDB[ $EDIT_TYPE ]                    = 'super_user/super_user.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'SU';
	$ID_LENGTH[ $EDIT_TYPE ]              = 5;

/**********       権限条件の定義          **********/

	$EDIT_TYPE                            = 'suMatch';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'super_user/su_match.csv';
	$TDB[ $EDIT_TYPE ]                    = 'super_user/su_match.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'SUM';
	$ID_LENGTH[ $EDIT_TYPE ]              = 7;

/**********       権限条件の定義          **********/

	$EDIT_TYPE                            = 'suItem';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'super_user/su_item.csv';
	$TDB[ $EDIT_TYPE ]                    = 'super_user/su_item.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'SUI';
	$ID_LENGTH[ $EDIT_TYPE ]              = 6;



/////////////////////mod tag

class mod_super_user extends command_base
{
	static $originalLoginUserType	= null;
	static $originalLoginID			= null;

	function drawTemplate( &$_gm , $_rec , $_args ){
		global $LOGIN_ID;
		global $loginUserType;
		global $loginUserRank;
		global $SUPER_USER_COLUMN;

		if( $loginUserType == 'admin' ){
			if( !isset(self::$originalLoginUserType) || !strlen(self::$originalLoginUserType) ){
				//adminのメニューを表示
				$cate_name = $_args[0];
				$HTML = Template::getTemplate( $loginUserType , $loginUserRank , $cate_name , 'SUPER_USER_MENU' );
				$this->addBuffer( $_gm->getString( $HTML ) );
				return;
			}
			$user_type = self::$originalLoginUserType;
			$login_user_id = self::$originalLoginID;
		}else{
			$user_type = $loginUserType;
			$login_user_id = $LOGIN_ID;
		}

		if( !isset($SUPER_USER_COLUMN[ $user_type ]) || !isset($_args[0])){
			return;
		}

		$su_id = SystemUtil::getTableData( $user_type, $login_user_id, $SUPER_USER_COLUMN[ $user_type ] );
		$item_list = explode( '/', SystemUtil::getTableData( 'super_user', $su_id, 'items' ) );
		if( !count($item_list)){return;}

		$cate_name = $_args[0];//SystemUtil::getTableData( 'suCategory', $_args[0], 'name' );
		$HTML = Template::getTemplate( $user_type , $loginUserRank , $cate_name , 'SUPER_USER_MENU' );

		$iDB = GMList::getDB( 'suItem' );
		$iTable = $iDB->searchTable( $iDB->getTable(), 'id', 'in', $item_list );

		$parts_list = $iDB->getDataList( $iTable, 'parts' );

		if( count($parts_list) ){
			foreach( $parts_list as $parts ){
				$this->addBuffer( $_gm->getString( $HTML , null , $parts ) );
			}
		}
	}

	function sudo( &$db, &$rec ){
		global $loginUserType;
		global $LOGIN_ID;
		global $SUPER_USER_COLUMN;

		global $path;
		global $script;

		if( !isset($SUPER_USER_COLUMN[ $loginUserType ]) || !isset($rec)){
			return;
		}

		$su_id = $db->getData( $rec, $SUPER_USER_COLUMN[ $loginUserType ] );

		if( !strlen( $su_id ) ){ return; }

		$suDB = GMList::getDB( 'super_user' );
		$iDB = GMList::getDB( 'suItem' );
		$mDB = GMList::getDB( 'suMatch' );

		//itemテーブルとmatchテーブルの結合
//		$mTable = $mDB->joinTable( $mDB->getTable(), 'suMatch', 'suItem', 'items', 'id');
		$mTable = $mDB->joinTableLike( $mDB->getTable(), 'suItem', 'suMatch', 'id', 'items' );

		//itemテーブルとsuper_userテーブルの結合
		$mTable = $mDB->joinTableLike( $mTable, 'suItem', 'super_user', 'id', 'items' );

		$mTable = $mDB->searchTable( $mTable, 'script', '=', str_replace( '.php', '', $script ) );
		$mTable = $mDB->joinTableSearch( $suDB, $mTable, 'id', '=', $su_id );

		$mRow = $mDB->getRow( $mTable );

		if($mRow){
			$check = false;
			for( $i=0 ; $i<$mRow ; $i++ ){
				$rec = $mDB->getRecord( $mTable, $i );
				$mType		= $mDB->getData( $rec, 'type' );
				$mRegex		= $mDB->getData( $rec, 'regex' );

				if( strlen( $mType ) && ( !isset($_GET['type']) || $mType != $_GET['type'] ) ){
					continue;
				}else if( strlen($mRegex) && ( !isset( $_SERVER['QUERY_STRING'] ) || !preg_match( '/'.$mRegex.'/', $_SERVER['QUERY_STRING'] ) ) ){
					continue;
				}
				$check = true;
				break;
			}

			if($check){
				self::$originalLoginUserType = $loginUserType;
				self::$originalLoginID = $LOGIN_ID;

				$loginUserType = 'admin';
				$LOGIN_ID = 'ADMIN';

			}
		}
	}


	function drawSelectItemsCheckbox( &$gm, $rec , $args ){
		global $loginUserType;
		global $loginUserRank;

		$suDB = $gm->getDB();
		$items = explode( '/', $suDB->getData( $rec, 'items' ) );

		$HTML = Template::getTemplate( $loginUserType , $loginUserRank , 'su_item_select_parts' , 'EXTENSION_PART_DESIGN' );

		$cGM = GMList::getGM( 'suCategory' );
		$iGM = GMList::getGM( 'suItem' );

		$cDB = $cGM->getDB();
		$iDB = $iGM->getDB();


		$cTable = $cDB->getTable();
		$cRow = $cDB->getRow( $cTable );

		if( $cRow ){
			$this->addBuffer( $cGM->getString( $HTML, null, 'c_head' ) );
			for( $i=0; $cRow > $i ; $i++ ){
				$cRec = $cDB->getRecord( $cTable, $i );
				$cID = $cDB->getData( $cRec, 'id' );

				$iTable = $iDB->searchTable( $iDB->getTable(), 'category', '=', $cID );
				$iRow = $iDB->getRow( $iTable );
				$items_html = Array();
				for($j=0;$j<$iRow;$j++){
					$iRec = $iDB->getRecord( $iTable, $j );
					$iID = $iDB->getData( $iRec, 'id' );
					$suffix = "";
					if( array_search( $iID, $items ) !== FALSE ){
						$suffix = '_checked';
					}
					$items_html[] = $iGM->getString( $HTML, $iRec, 'i_list_edit'.$suffix );
				}
				$cGM->setVariable( 'i_list', join( $items_html ) );
				$this->addBuffer( $cGM->getString( $HTML, $cRec, 'c_list' ) );
			}
			$this->addBuffer( $cGM->getString( $HTML, null, 'c_foot' ) );
		}
		$this->addBuffer( $cGM->getString( $HTML, null, 'c_not_found' ) );
	}
	function drawItemsList4Category( &$gm, $rec , $args ){
		global $loginUserType;
		global $loginUserRank;

		$suDB = $gm->getDB();
		$items = explode( '/', $suDB->getData( $rec, 'items' ) );

		$HTML = Template::getTemplate( $loginUserType , $loginUserRank , 'su_item_select_parts' , 'EXTENSION_PART_DESIGN' );

		$cGM = GMList::getGM( 'suCategory' );
		$iGM = GMList::getGM( 'suItem' );

		$cDB = $cGM->getDB();
		$iDB = $iGM->getDB();


		$cTable = $cDB->getTable();
		$cRow = $cDB->getRow( $cTable );

		if( $cRow ){
			$this->addBuffer( $cGM->getString( $HTML, null, 'c_head' ) );
			for( $i=0; $cRow > $i ; $i++ ){
				$cRec = $cDB->getRecord( $cTable, $i );
				$cID = $cDB->getData( $cRec, 'id' );

				$iTable = $iDB->searchTable( $iDB->getTable(), 'category', '=', $cID );
				$iTable = $iDB->searchTable( $iTable, 'id', 'in', $items );
				$iRow = $iDB->getRow( $iTable );
				if( $iRow ){
					$items_html = Array();
					for($j=0;$j<$iRow;$j++){
						$iRec = $iDB->getRecord( $iTable, $j );
						$iID = $iDB->getData( $iRec, 'id' );
						$items_html[] = $iGM->getString( $HTML, $iRec, 'i_list' );
					}
					$cGM->setVariable( 'i_list', join( $items_html ) );
					$this->addBuffer( $cGM->getString( $HTML, $cRec, 'c_list' ) );
				}
			}
			$this->addBuffer( $cGM->getString( $HTML, null, 'c_foot' ) );
		}
		$this->addBuffer( $cGM->getString( $HTML, null, 'c_not_found' ) );
	}

}