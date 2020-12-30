<?php
class dl_entry extends commonDL{

	function __construct($param){
		parent::__construct($param);
	}

	function entryList(){
		global $SYSTEM_CHARACODE;
		global $loginUserType;
		global $loginUserRank;
		global $HOME;
		ob_end_clean();
		ob_start();

		$gm = $this->gm[$this->type];
		$db = $gm->getDB();

		$table = $this->search($this->param);
		$this->sys->searchProc( $this->gm , $table , $loginUserType , $loginUserRank );

		if(! $db->existsRow($table) || ! in_array($loginUserType, array("admin","cUser")))
			{ $this->drawDownloadError(); }

		$row = $db->getRow($table);

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename=entryList.csv');
		header('Content-Type: application/json;charset='.$SYSTEM_CHARACODE);

		$out = fopen('php://output', 'w');
		$elHeader = array("応募ID","求人種別","求人ID","求人名","ユーザーID","ユーザー名","ニックネーム","進捗状況","履歴書URL","応募日時");
		$this->setCSVLabel($out,$elHeader);

		for($i = 0;$i<$row;$i++){
			$rec = $db->getRecord($table,$i);
			$entry_user = $db->getData($rec,"entry_user");
			$userType = SystemUtil::getUserType($entry_user);

			$name = SystemUtil::getTableData($userType,$entry_user, "name");
			if(!isset($name)||!strlen($name))
				$name = "退会ユーザー";

			$nick_name = SystemUtil::getTableData($userType,$entry_user, "nick_name");
			if(!isset($nick_name)||!strlen($nick_name))
				$nick_name = "退会ユーザー";

			$items_id = $db->getData($rec,"items_id");

			$array["応募ID"] = $db->getData($rec,"id");
			$array["求人種別"] = str_replace(array("mid","fresh"), array("中途採用","新卒採用"),$db->getData($rec,"items_type"));
			$array["求人ID"] = $items_id;
			$array["求人名"] = SystemUtil::getTableData(SystemUtil::getJobType($items_id), $items_id, "name");
			$array["ユーザーID"] = $entry_user;
			$array["ユーザー名"] = $name;
			$array["ニックネーム"] = $nick_name;
			$array["進捗状況"] = SystemUtil::getTableData("entry_progress", $db->getData($rec,"status"), "name");
			$array["履歴書ID"] = $HOME.SystemUtil::getTableData("message", $db->getData($rec,"message_id"), "file");
			$array["応募日時"] = SystemUtil::mb_date("Y年m月d日 H時i分",$db->getData($rec,"regist"));

			mb_convert_variables("sjis",$SYSTEM_CHARACODE,$array);
			fputcsv($out,$array);
			$array = array();
		}
		fclose($out);
		ob_end_flush();
	}

	function getType(){
		return "entry";
	}
}