<?php
class mailSendSystem extends System{

	function drawRegistCheck(&$gm, $rec, $loginUserType, $loginUserRank){
		$this->setErrorMessage($gm[ $_GET['type'] ]);

		$db = $gm[$_GET["type"]]->getDB();
		$userType = $db->getData($rec,"user_type");

		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , 'REGIST_CHECK_PAGE_DESIGN' , SystemUtil::GetFormTarget( 'registCheck' )."&user_type=".$userType );
		}
	}

	function registProc(&$gm, &$rec, $loginUserType, $loginUserRank, $check = false){
		$db	 = $gm[ $_GET['type'] ]->getDB();

		if($db->getData($rec,"reserve_flag")=="TRUE"){
			$reserve_y = $db->getData($rec,"reserve_y");
			$reserve_m = $db->getData($rec,"reserve_m");
			$reserve_d = $db->getData($rec,"reserve_d");
			$reserve_h = $db->getData($rec,"reserve_h");
			$reserve_i = $db->getData($rec,"reserve_i");
			$reserve_time = mktime($reserve_h,$reserve_i,0,$reserve_m,$reserve_d,$reserve_y);
			$db->setData($rec,"reserve_time",$reserve_time);
		}
		$list_id = $db->getData($rec,"list_id");
		$lRow = DMList::getUserCnt($list_id);
		$db->setData($rec,"total_cnt",$lRow);

		parent::registProc($gm, $rec, $loginUserType, $loginUserRank, $check);
	}


	function searchProc(&$gm, &$table, $loginUserType, $loginUserRank){

		$db	 = $gm[ $_GET['type'] ]->getDB();

		if(isset($_GET["keyword"]))
			$table = $db->searchConcat($table,array("sender_mail","sender_name","sub","main"),$_GET["keyword"]);

		parent::searchProc($gm, $table, $loginUserType, $loginUserRank);
	}

	function getSearchPageChange( &$gm, $table, $loginUserType, $loginUserRank, $row, $pagejumpNum, $resultNum, $phpName, $param )
	{
		$_GET["user_type"] = "";
		return parent::getSearchPageChange($gm, $table, $loginUserType, $loginUserRank, $row, $pagejumpNum, $resultNum, $phpName, $param);
	}
}