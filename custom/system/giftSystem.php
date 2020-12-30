<?php
class giftSystem extends System{

	function drawEditForm( &$gm, &$rec, $loginUserType, $loginUserRank ){

		$db = $gm[$_GET["type"]]->getDB();
//		$activate = $db->getData($rec,"activate");
//		Concept::IsFalse($activate != 0)->OrThrow("IllegalAccess");

		if(!empty($_GET["md5"]))
			{ $query["md5"] = $_GET["md5"]; }

		if( 'normal' == WS_SYSTEM_SYSTEM_FORM_ACTON ){
			$action = 'index.php?app_controller=edit&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ]."&".http_build_query((array)$query);
		}else if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON ){
			$action = 'index.php?app_controller=edit&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ]."&".http_build_query((array)$query);
		}else{
			$action = ' ';
		}

		$this->setErrorMessage( $gm[ $_GET['type'] ] );

		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'EDIT_FORM_PAGE_DESIGN' , $action , Template::getOwner() );
		}

	}

	function drawEditCheck( &$gm, &$rec, $loginUserType, $loginUserRank )
	{

		if(!empty($_GET["md5"])){
			$query["md5"] = $_GET["md5"];
		}

		if( 'normal' == WS_SYSTEM_SYSTEM_FORM_ACTON ){
			$action = 'index.php?app_controller=edit&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ]."&".http_build_query((array)$query);
		}else if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON ){
			$action = 'index.php?app_controller=edit&type=' . $_GET[ 'type' ] . '&id=' . $_GET[ 'id' ]."&".http_build_query((array)$query);
		}else{
			$action = ' ';
		}

		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'EDIT_CHECK_PAGE_DESIGN' , $action , Template::getOwner() );
		}
	}

	function registCheck( &$gm, $edit, $loginUserType, $loginUserRank ){
		// チェック処理
		self::$checkData->generalCheck($edit);
		$data = self::$checkData->getData();

		if(SystemUtil::existsModule('nobody')){

			$checkList = array("bank_name","branch_name","account_type","account_cd","account_name");
			$nullCheck = $checkList;
			$regexCheck = array("account_cd");
			$bankAccountCheck = array("account_name");

			$hash = $_GET["md5"];
			if(nobodyLogic::giftActivateCheck($_GET["id"],$hash)){
				foreach($checkList as $list){
					if(in_array($list, $nullCheck))	self::$checkData->checkNull($list, array());
					if(in_array($list, $bankAccountCheck))	self::$checkData->checkBankAccount($list, array());
					if(in_array($list, $regexCheck)){
						if( isset( $_POST[ $list ]) && $_POST[$list] != null ){
							if( !preg_match( "/^[\d]+$/",$_POST[$list]) ){
								self::$checkData->addError( $list. '_REGEX', null, $list );
							}
						}
					}
				}
			}
		}
		return self::$checkData->getCheck();
	}

    function infoProc(&$gm, &$rec, $loginUserType, $loginUserRank)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $ACTIVE_ACCEPT;
        global $ACTIVE_DENY;
        // **************************************************************************************

        // 簡易情報変更（情報ページからの内容変更処理）
        if (isset($_POST['post'])) {
            if ($loginUserType == 'admin') {
                $db = $gm[$_GET['type']]->getDB();
                for ($i = 0; $i < count($db->colName); $i ++) {
                    if (isset($_POST[$db->colName[$i]])) {
                        $db->setData($rec, $db->colName[$i], $_POST[$db->colName[$i]]);
                    }
                }
                $db->updateRecord($rec);
                if ($db->getData($rec, 'activate') == $ACTIVE_ACCEPT) {
                    $db->setData($rec, "payment_time", time());
                    $db->updateRecord($rec);

                    $id = $db->getData($rec, "entry_id");
                    entryLogic::paymentGift($id);
                    MailLogic::noticeGiftPay($rec);
                } elseif ($db->getData($rec, 'activate') == $ACTIVE_DENY) {
                    MailLogic::noticeGiftPay($rec);
                }
            }
        }
    }

    function editProc(&$gm, &$rec, $loginUserType, $loginUserRank, $check = false){
		$db = $gm[$_GET["type"]]->getDB();
		switch($loginUserType){
			case "admin":
				switch($db->getData($rec,"activate")){
					case 4:
						$db->setData($rec,"payment_time",time());
						break;
					default:
						$db->setData($rec,"payment_time",0);
						break;
				}
				break;
			case "nUser":
			case "nobody":
				$db->setData($rec,"publish",true);
				$db->setData($rec,"activate",1);
				$db->setData($rec,"regist",time());
				break;
		}
		parent::editProc($gm, $rec, $loginUserType, $loginUserRank, $check);

	}

	function editComp( &$gm, &$rec, &$old_rec, $loginUserType, $loginUserRank ){
	    // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
	    global $ACTIVE_ACCEPT;
	    global $ACTIVE_DENY;
	    // **************************************************************************************
	    
		if($loginUserType == "nobody"){
			$db = $gm[$_GET["type"]]->getDB();
			$addList = array("bank_name","branch_name","account_type","account_cd","account_name");
			foreach ($addList as $key)
				{ $data[$key] = $_POST[$key]; }
			$data["id"] = $db->getData($rec,"owner");
			$data["regist"] = time();
			bankAccountLogic::userRegistInit($data);
		} elseif ($loginUserType == 'admin') {
		    $db = $gm[$_GET['type']]->getDB();
		    $activate = $db->getData($rec, 'activate');
		    
		    if ($activate == $ACTIVE_ACCEPT) {
				$id = $db->getData($rec, 'entry_id');
		        entryLogic::paymentGift($id);
		        MailLogic::noticeGiftPay($rec);
		    } elseif ($activate == $ACTIVE_DENY) {
		        MailLogic::noticeGiftPay($rec);
		    }
		}
	}

	function searchProc(&$gm, &$table, $loginUserType, $loginUserRank){
		global $LOGIN_ID;

		$db = $gm[$_GET["type"]]->getDB();

		switch($loginUserType){
			case "admin":
				$table = $db->searchTable($table,"activate","!",0);
				break;
			case "nUser":
				$eDB = GMList::getDB("entry");
				$table = $db->joinTable($table,"gift","entry","entry_id","id");

				// 不採用状態以外のレコードを検索
				$table = $db->joinTableSearch($eDB,$table,"status","!","FAILE");

				$table = $db->searchTable($table,"owner","=",$LOGIN_ID);
				break;
		}
	}
}