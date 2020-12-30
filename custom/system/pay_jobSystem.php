<?php
class pay_jobSystem extends System{

	function drawRegistForm(&$gm, $rec, $loginUserType, $loginUserRank){
		global $LOGIN_ID;
		$this->setErrorMessage($gm[ $_GET['type'] ]);

		if(!empty($_GET["label"]))
			{ $query["label"] = $_GET["label"]; }
		if(!empty($_GET["target_id"]))
			{ $query["target_id"] = $_GET["target_id"]; }

		if( 'normal' == WS_SYSTEM_SYSTEM_FORM_ACTON )
		{
			$action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ]."&".http_build_query((array)$query);
		}
		else if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
		{
			$action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ]."&".http_build_query((array)$query);
		}
		else
		{ $action = ' ';
		}

		$label = $this->getDesign("REGIST_FORM_PAGE_DESIGN");

		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				if($gm[$_GET['type']]->maxStep >= 2)
					Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , $label . $_POST['step'] , $action );
				else
					Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , $label , $action );
		}
	}

	function drawRegistCheck( &$gm, $rec, $loginUserType, $loginUserRank )
	{
		if(!empty($_GET["label"]))
			{ $query["label"] = $_GET["label"];	}
		if(!empty($_GET["target_id"]))
			{ $query["target_id"] = $_GET["target_id"]; }

		if( 'normal' == WS_SYSTEM_SYSTEM_FORM_ACTON )
		{
			$action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ]."&".http_build_query((array)$query);
		}
		else if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
		{
			$action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ]."&".http_build_query((array)$query);
		}
		else
		{ $action = ' ';
		}

		$label = $this->getDesign("REGIST_CHECK_PAGE_DESIGN");

		switch(  $_GET['type']  )
		{
			default:
				// 汎用処理
				Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , $label , $action );
		}
	}

	function registCheck( &$gm, $edit, $loginUserType, $loginUserRank )
	{
		global $LOGIN_ID;
		// チェック処理
		parent::$checkData->generalCheck($edit);
		$data = parent::$checkData->getData();

		if($data["charges"] == "ul_term"){
			parent::$checkData->checkNull("target_id", array());
			if($data["label"]=="fresh" && !empty($data["target_id"])){
				parent::$checkData->checkFreshLimit("target_id");
			}

			parent::$checkData->checkIntable("target_id",array($data["label"]."_term"));
			if(in_array($data["target_id"],array("MT000","FT000"))){
				parent::$checkData->addError("target_id_in_table",null,"target_id");
			}
		}else{
			if(in_array($data["label"],array("mid","fresh"))){
				$chargedUserLimit = (pay_jobLogic::getUserTerm($LOGIN_ID,$data["label"]) == "time");
				$availableInTime = pay_jobLogic::isAvailable($LOGIN_ID,$data["label"]);

				if($chargedUserLimit && $availableInTime){
					parent::$checkData->addError("charges_intime_userlimit",null,"charges");
				}elseif(pay_jobLogic::getUserTerm($LOGIN_ID,$data["label"]) == "job" && $data["charges"] == "job" ){
					parent::$checkData->addError("charges_dup_job_charges",null,"charges");
				}
			}
		}

		// エラー内容取得
		return parent::$checkData->getCheck();
	}

	function drawRegistComp( &$gm, $rec, $loginUserType, $loginUserRank )
	{
		$label = $this->getDesign("REGIST_COMP_PAGE_DESIGN");
		Template::drawTemplate( $gm[ $_GET['type'] ] , $rec , $loginUserType , $loginUserRank , $_GET['type'] , $label );
	}

	function registComp( &$gm, &$rec, $loginUserType, $loginUserRank ){
		global $LOGIN_ID;

		$db = $gm[$_GET["type"]]->getDB();
		switch($_GET["label"]){
			case "attention":
				$target_type = $db->getData($rec,"target_type");
				$target_id = $db->getData($rec,"target_id");
				$limits =$db->getData($rec,"limits");
				Job::updateAttention($target_id,$limits);
				MailLogic::noticeAttentionRequest($rec);
				MailLogic::noticeAttentionAddsUser($target_type,$target_id);
				break;
			case "mid":
			case "fresh":
				cUserLogic::setFlg($db->getData($rec,"owner"),$db->getData($rec,"label"),true);
				MailLogic::noticeUserLimitRequest($rec);
				break;
		}
	}
	function registProc(&$gm, &$rec, $loginUserType, $loginUserRank, $check = false){
		global $LOGIN_ID;
		$db = $gm[$_GET["type"]]->getDB();
		$db->setData($rec,"owner",$LOGIN_ID);
		$taxRate = SystemUtil::getSystemData("tax");
		parent::registProc($gm, $rec, $loginUserType, $loginUserRank, $check);

		//従量課金指定時の処理
		if($_POST["charges"] == "job"){
			$db->setData($rec,"limits",0);
			$db->setData($rec,"money",0);
			$db->setData($rec,"pay_flg",true);
			$db->setData($rec,"pay_time",time());
			$db->setData($rec,"target_id","");
			return;
		}
		$label = $db->getData($rec,"label");
		switch($label){
			case "mid":
				$db->setData($rec,"target_type","mid_term");
				$money = SystemUtil::getTableData("mid_term", $db->getData($rec,"target_id"), "cost");
				$money = $money + ceil($money*$taxRate/100);

				$term = SystemUtil::getTableData("mid_term", $db->getData($rec,"target_id"), "term");
				$addSec = 60 * 60 * 24 * $term;
				$limit = pay_jobLogic::getNewLimits($rec,$label,$addSec);

				if(pay_jobLogic::isFirstPayment("mid",$LOGIN_ID))
					$db->setData($rec,"status","1st");
				else
					$db->setData($rec,"status","2nd");

				break;
			case "fresh":
				$db->setData($rec,"target_type","fresh_term");
				$money = SystemUtil::getTableData("fresh_term", $db->getData($rec,"target_id"), "cost");
				$money = $money + ceil($money*$taxRate/100);

				if($db->getData($rec,"target_id") == "FT001"){
					$limit = mktime(23,59,59,3,31,SystemUtil::getYearly());
				}elseif($db->getData($rec,"target_id") == "FT002"){
					$limit = mktime(23,59,59,3,31,SystemUtil::getYearly("next"));
				}

				if(pay_jobLogic::isFirstPayment("fresh",$LOGIN_ID))
					$db->setData($rec,"status","1st");
				else
					$db->setData($rec,"status","2nd");

				break;
			case "attention":
				if(substr($db->getData($rec,"target_id"), 0,2)=="JN")
					{ $db->setData($rec,"target_type","fresh"); }
				elseif(substr($db->getData($rec,"target_id"), 0,1) == "J")
					{ $db->setData($rec,"target_type","mid"); }
				else
					{ throw new Exception(); }
				$money = SystemUtil::getTableData("at_term", h($_POST["at_term"]), "cost");
				$money = $money + ceil($money*$taxRate/100);

				$term = SystemUtil::getTableData("at_term", h($_POST["at_term"]), "term");
				$addSec = 60*60*24*$term;
				$limit = pay_jobLogic::getNewLimits($rec,$label,$addSec);

				break;
		}

		$db->setData($rec,"limits",$limit);
		$db->setData($rec,"money",$money);
		$db->setData($rec,"pay_flg",true);
		$db->setData($rec,"pay_time",time());
	}



	function infoProc( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		// 簡易情報変更（情報ページからの内容変更処理）
		if(  isset( $_POST['post'] ) ){
			switch($loginUserType){
				case "admin":
					$db		 = $gm[ $_GET['type'] ]->getDB();
					for( $i=0; $i<count($db->colName); $i++ ){
						if(isset($_POST[ $db->colName[$i]])){
							$db->setData( $rec, $db->colName[$i], $_POST[ $db->colName[$i] ] );
						}
					}
					$db->updateRecord( $rec );
					break;
			}
		}
	}

	function searchProc(&$gm, &$table, $loginUserType, $loginUserRank){
		global $LOGIN_ID;

		$type = SearchTableStack::getType();

		$db = $gm[ $type ]->getDB();
		switch($loginUserType){
			case"cUser":
				$table = $db->searchTable($table,"owner","=",$LOGIN_ID);
				$table = $db->searchTable($table,"pay_flg","=",true);

				if( isset($_GET['limit']) ){
					switch($_GET['limit'][0]){
						case 'on':
							$onTable  = $db->searchTable( $table , 'limits' , '>' , 0 );
							$onTable  = $db->searchTable( $onTable , 'limits' , '>' , time() );
							$offTable = $db->searchTable( $table , 'limits' , '=' , 0 );
							$table    = $db->orTable( $onTable , $offTable );
							break;
						case 'off':
							$table  = $db->searchTable( $table , 'limits' , '<' , time() );
							break;
					}
				}

				if($_GET["max"]=="label"){
					$tA = $db->getMaxTable("shadow_id","label",$table);
					$tA = $db->searchTable($tA, "label","in","fresh/mid");
					$table = $db->outerJoinTableSubQuerySQL("inner ", $table, $tA, "pay_job2", $db->tableName . ".shadow_id = pay_job2.max");
					
				}elseif($_GET["max"]=="attention"){
					$tA = $db->getMaxTable("shadow_id","target_id",$table);
					$tA = $db->searchTable($tA, "label","=","attention");
					$table = $db->outerJoinTableSubQuerySQL("inner ", $table, $tA, "pay_job2", $db->tableName . ".shadow_id = pay_job2.max");
					
				}else{
					$tableA[] = $db->searchTable($table,"id","!","MT000");
					$tableA[] = $db->searchTable($table,"id","!","FT000");
					$tableA[] = $db->searchTable($table,"money","!",0);
					$table = $db->orTableM($tableA);
				}
		}
	}

	/**
	 * 契約情報取り消し処理。
	 */
	function deleteComp( &$gm, &$rec, $loginUserType, $loginUserRank )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		// **************************************************************************************

		$type = SearchTableStack::getType();

		$db = $gm[ $type ]->getDB();

		$owner=$db->getData($rec,"owner");
		$id=$db->getData($rec,"id");
		$pay_flg=$db->getData($rec,"pay_flg");
		$target_type=$db->getData($rec,"target_type");
		$target_id=$db->getData($rec,"target_id");

		if($pay_flg) $payType=$db->getData($rec,"label");
		else $payType="exit";

		switch($payType)
		{
			case 'attention':
				$table = $db->getTable();
				$table = $db->searchTable($table,"owner","=",$owner);
				$tableSub = $db->getTable();
				$tableSub = $db->searchTable($tableSub,"owner","=",$owner);
				$tableSub = $db->searchTable($tableSub, "label","=","attention");
				//削除対象以外で最新の決済ログ（同じタイプの同じIDに限定）
				$tableSub = $db->searchTable($tableSub, "id","!",$id);
				$tableSub = $db->searchTable($tableSub, "target_type","=",$target_type);
				$tableSub = $db->searchTable($tableSub, "target_id","=",$target_id);
				$tableSub = $db->searchTable($tableSub, "limits","!",0);

				$tA = $db->getMaxTable("shadow_id","target_id",$tableSub);
				$table = $db->outerJoinTableSubQuerySQL("inner ", $table, $tA, "pay_job2", $db->tableName . ".shadow_id = pay_job2.max");
				$row = $db->getRow($table);
				
				//該当求人レコードを取得
				$db_job = $gm[ $target_type ]->getDB();
				$rec_job = $db_job->selectRecord($target_id);

				//削除対象ログのおすすめ指定求人に過去の決済履歴がある場合のみ
				if($row>0){
					$before_rec=$db->getRecord($table,0);
					$before_limits=$db->getData($before_rec,"limits");
					$db_job->setData($rec_job,"attention_time",$before_limits); //直前契約の期間データで上書き
				}else{
					$db_job->setData($rec_job,"attention_time",0); //直前契約の期間データで上書き
					$db_job->setData($rec_job,"attention",false);
				}

				$db_job->updateRecord($rec_job);
				break;
			default:
		}

		if($payType != "exit")
		{
			MailLogic::noticePaymentCancel($rec);
		}


		parent::deleteComp( $gm, $rec, $loginUserType, $loginUserRank );
	}

	function getDesign( $design )
	{
		switch($_GET['label'])
		{
		case 'attention':  $design = $design.'_ATTENTION'; break;
		case 'mid':  $design = $design.'_MID'; break;
		case 'fresh':  $design = $design.'_FRESH'; break;
		}

		return $design;
	}
}