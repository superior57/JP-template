<?php
class dl_bill extends commonDL{

	function billsInfo(){
		global $SYSTEM_CHARACODE;
		global $loginUserType;
		global $LOGIN_ID;
		ob_end_clean();
		ob_start();

		$gm = $this->gm[$this->type];
		$db = $gm->getDB();

		$rec = $db->selectRecord($this->param['id']);
		if(!$rec) return;

		$owner = $db->getData($rec,"owner");

		if($loginUserType != "admin" && !($loginUserType == "cUser" && $LOGIN_ID == $owner ))
		{ $this->drawDownloadError();}


		$owner = $db->getData($rec,"owner");
		$demand_s = (int)$db->getData($rec,"demand_s");
		$demand_e = (int)$db->getData($rec,"demand_e");

		$pGM = GMList::getGM("pay_job");
		$pDB = $pGM->getDB();;

		$pTable = $pDB->getTable();
		$pTable = $pDB->searchTable($pTable,"owner","=",$owner);
		$pTable = $pDB->searchTable($pTable,"is_billed","=",true);
		$pTable = $pDB->searchTable($pTable,"regist","b",$demand_s,$demand_e);

		$row = $pDB->getRow($pTable);

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename=billsInfo.csv');
		header('Content-Type: application/json;charset='.$SYSTEM_CHARACODE);

		$out = fopen('php://output', 'w');
		$elHeader[] = "決済ID";
		if($loginUserType == "admin"){
			$elHeader[] = "企業ID";
			$elHeader[] = "企業名";
		}
		$elHeader[] = "決済種別";
		$elHeader[] = "関連情報";
		$elHeader[] = "申込日";
		$elHeader[] = "金額(円)";
		$this->setCSVLabel($out,$elHeader);

		for($i = 0;$i<$row;$i++){
			$pRec = $pDB->getRecord($pTable,$i);
			$owner = $pDB->getData($pRec,"owner");
			$tType = $pDB->getData($pRec,"target_type");
			$tID = $pDB->getData($pRec,"target_id");
			$name = SystemUtil::getTableData("cUser",$owner, "name");
			if(empty($name))
				$name = SystemUtil::getDeleteTableData("cUser",$owner, "name")."(退会済)";

			switch($pDB->getData($pRec,"target_type")){
				case "mid_term":
				case "fresh_term":
					if(!empty($tID))$label = str_replace(array("mid","fresh"), array( "中途採用(期限)","新卒採用(期限)"),$pDB->getData($pRec,"label"));
					else			$label = str_replace(array("mid","fresh"), array( "中途採用(従量)","新卒採用(従量)"),$pDB->getData($pRec,"label"));
					break;
				default:
					$label = str_replace(array("attention","scout","apply","employment"), array("おすすめ掲載","スカウト","応募","採用"),$pDB->getData($pRec,"label"));
			}

			$array["決済ID"] = $pDB->getData($pRec,"id");
			if($loginUserType == "admin"){
				$array["企業ID"] = $owner;
				$array["企業名"] = $name;
			}
			$array["決済種別"] = $label;

			if(empty($tID)){
				$relation = str_replace(array("mid_term","fresh_term"), array( "求人毎課金（従量課金契約）","求人毎課金（従量課金契約）"),$pDB->getData($pRec,"target_type"));
			}else{
				$relation = SystemUtil::getTableData($tType,$tID,"name");
			}

			$array["関連情報"] = $relation;
			$array["申込日"] = SystemUtil::mb_date("Y年m月d日 H時i分",$db->getData($pRec,"regist"));
			$array["金額"] = $pDB->getData($pRec,"money");

			mb_convert_variables("sjis",$SYSTEM_CHARACODE,$array);
			fputcsv($out,$array);
			$array = array();
		}
		fclose($out);
		ob_end_flush();


	}

	function billList(){
		global $SYSTEM_CHARACODE;
		global $loginUserType;
		global $loginUserRank;
		ob_end_clean();
		ob_start();

		$gm = $this->gm[$this->type];
		$db = $gm->getDB();

		$table = $this->search($this->param);
		$this->sys->searchProc( $this->gm , $table , $loginUserType , $loginUserRank );

		if(!$db->existsRow($table) && $loginUserType == "admin")
		{ $this->drawDownloadError();}
		$row = $db->getRow($table);

		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment;filename=billList.csv');
		header('Content-Type: application/json;charset='.$SYSTEM_CHARACODE);


		$out = fopen('php://output', 'w');
		$elHeader = array("請求ID","企業ID","企業名","金額(円)","振込通知","入金確認","入金確認日","集計日","請求対象","請求日");
		
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

			$array["請求ID"] = $db->getData($rec,"id");
			$array["企業ID"] = $owner;
			$array["企業名"] = SystemUtil::getTableData("cUser", $owner, "name");
			$array["金額"] = $db->getData($rec,"money");
			$noticeFlg = $db->getData($rec,"notice") ? "通知済":"未通知";
			$array["振込通知"] = $noticeFlg;
			$payFlg = $db->getData($rec,"pay_flg") ? "確認済":"未確認";
			$array["入金確認"] = $payFlg;
			if($db->getData($rec,"pay_time") == 0)
			{ $pay_time = "----"; }
			else
			{ $pay_time = SystemUtil::mb_date("Y年m月d日 H時i分",$db->getData($rec,"pay_time")); }
			$array["入金確認日"] = $pay_time;
			$array["集計日"] = SystemUtil::mb_date("Y年m月d日 H時i分",$db->getData($rec,"regist"));

			$demand_s = SystemUtil::mb_date("Y年m月d日",$db->getData($rec,"demand_s"));
			$demand_e = SystemUtil::mb_date("Y年m月d日",$db->getData($rec,"demand_e"));
			$array["請求対象"] = "{$demand_s} ～ $demand_e";
			$billdate = SystemUtil::mb_date("Y年m月d日",$db->getData($rec,"billdate"));
			$array["請求日"] = "$billdate";

			mb_convert_variables("sjis",$SYSTEM_CHARACODE,$array);
			fputcsv($out,$array);
			$array = array();
		}
		fclose($out);
		ob_end_flush();
	}
}