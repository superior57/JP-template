<?php

class listSystem extends System{

	function drawEditForm( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		$this->setErrorMessage( $gm[ $_GET['type'] ] );
		$db = GMList::getDB($_GET['type']);

		Concept::IsTrue($db->getData($rec,"is_editable"))->OrThrow("IllegalAccess");

		$rec = array_merge($rec,$_GET);	//検索条件を引き継ぐための処置
		parent::drawEditForm($gm, $rec, $loginUserType, $loginUserRank);

	}
	function drawDeleteCheck( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		$db = GMList::getDB($_GET['type']);

		Concept::IsTrue($db->getData($rec,"is_editable"))->OrThrow("IllegalAccess");
		parent::drawDeleteCheck( $gm, $rec, $loginUserType, $loginUserRank ); // 二重描画になるので編集する場合は削除
	}

	function deleteComp(&$gm, &$rec, $loginUserType, $loginUserRank){
		parent::deleteComp($gm, $rec, $loginUserType, $loginUserRank);

		$db = $gm[$_GET["type"]]->getDB();
		$userType = $db->getData($rec,"user_type");
		$id = $db->getData($rec,"id");

		$userDB = GMList::getDB($userType);
		$userTable = $userDB->getTable();
		$userTable = $userDB->searchTable($userTable,"list_id","=","%{$id}%");
		DMList::delete($userDB,$userTable,$id);
	}

	function registProc(&$gm, &$rec, $loginUserType, $loginUserRank, $check = false){

		$db = GMList::getDB($_GET['type']);
		$db->setData($rec,"is_editable",true);

		parent::registProc($gm, $rec, $loginUserType, $loginUserRank, $check);
	}

	function infoProc( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		// 簡易情報変更（情報ページからの内容変更処理）
		if(  isset( $_POST['post'] ) ){
			if( $loginUserType == 'admin' ){
				$db		 = $gm[ $_GET['type'] ]->getDB();
				for( $i=0; $i<count($db->colName); $i++ ){
					if(   isset(   $_POST[ $db->colName[$i] ]  )   ){
						$db->setData( $rec, $db->colName[$i], $_POST[ $db->colName[$i] ] );
					}
				}
				$db->updateRecord( $rec );
			}
		}
	}
}