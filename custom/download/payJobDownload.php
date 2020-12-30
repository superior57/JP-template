<?php
class dl_pay_job extends commonDL{

	function __construct($param){
		parent::__construct($param);
	}

	function paymentList(){
		global $SYSTEM_CHARACODE;
		global $loginUserType;
		global $loginUserRank;
		ob_end_clean();
		ob_start();

		$gm = $this->gm[$this->type];
		$db = $gm->getDB();

		$table = $this->search($this->param);
		$this->sys->searchProc( $this->gm , $table , $loginUserType , $loginUserRank );

		if(! $db->existsRow($table) || $loginUserType != "admin")
			{ $this->drawDownloadError();}
		$row = $db->getRow($table);

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename=paymentList.csv');
		header('Content-Type: application/json;charset='.$SYSTEM_CHARACODE);


		$out = fopen('php://output', 'w');
		$elHeader = array("決済ID","企業ID","企業名","ターゲットタイプ","ターゲットID","識別子","金額(円)","利用期限","申込日");
		$this->setCSVLabel($out,$elHeader);

		for($i = 0;$i<$row;$i++){
			$rec = $db->getRecord($table,$i);
			$owner = $db->getData($rec,"owner");
			$tID = $db->getData($rec,"target_id");
			$name = SystemUtil::getTableData("cUser",$owner, "name");
			if(!isset($name)||!strlen($name))
				$name = "退会ユーザー";

			switch($db->getData($rec,"target_type")){
				case "mid_term":
				case "fresh_term":
					if(!empty($tID))$label = str_replace(array("mid","fresh"), array( "中途採用(期限)","新卒採用(期限)"),$db->getData($rec,"label"));
					else			$label = str_replace(array("mid","fresh"), array( "中途採用(従量)","新卒採用(従量)"),$db->getData($rec,"label"));
					break;
				default:
					$label = str_replace(array("attention","scout","apply","employment"), array("おすすめ掲載","スカウト","応募","採用"),$db->getData($rec,"label"));
			}

			$array["決済ID"] = $db->getData($rec,"id");
			$array["企業ID"] = $owner;
			$array["企業名"] = SystemUtil::getTableData("cUser", $owner, "name");
			$array["ターゲットタイプ"] = $db->getData($rec,"target_type");
			$array["ターゲットID"] = $db->getData($rec,"target_id");
			$array["識別子"] = $label;
			$array["金額"] = $db->getData($rec,"money");
			if($db->getData($rec,"limits") == 0)
				{ $limits = "無期限"; }
			else
				{ $limits = SystemUtil::mb_date("Y年m月d日 H時i分",$db->getData($rec,"limits")); }
			$array["利用期限"] = $limits;

			$array["申込日"] = SystemUtil::mb_date("Y年m月d日 H時i分",$db->getData($rec,"regist"));

			mb_convert_variables("sjis",$SYSTEM_CHARACODE,$array);
			fputcsv($out,$array);
			$array = array();
		}
		fclose($out);
		ob_end_flush();
	}
}