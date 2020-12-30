<?php
set_time_limit( 0 );
include "./custom/head_main.php";
include "./convertTDB/init.php";

if($_SERVER["REQUEST_METHOD"] !="POST"){
	print "nUsercsvからresumeテーブルにデータを流し込みます。<br />";
	print "先にnUserデータは流し込みましたか？";
	print "<form action=\"\" method=\"POST\" ><input type=\"submit\" /></form>";
}else{
/*

	$gm = GMList::getGM("resume");
	$db = $gm->getDB();

	$file = new CSV( "./eanimal_export/eanimal_export/tdb/fresh.csv" );
	while( $a = $file->readRecord() ){
	mb_convert_variables("UTF-8","shift-jis",$a);
	$rec = $db->getNewRecord();

	foreach($array as $key => $val){
		switch($val){
			case "license";
			$salary = strip_tags($a[$key]);
			$db->setData($rec,$val,$salary.",");
			break;
			case "hope_work_place";
			$salary = str2adds($a[$key]);
			$db->setData($rec,$val,$salary.",");
			break;
			case "hope_salary";
			$salary = str_replace(array("不問","0","〜100万","〜200万","〜300万","〜400万","〜500万","〜600万","〜700万","〜800万","〜900万","〜1000万"),array(0,0,1000000,2000000,3000000,4000000,5000000,6000000,7000000,8000000,9000000,10000000),$a[$key]);
			$db->setData($rec,$val,$salary);
			break;
			case "hope_salary_type";
			$db->setData($rec,$val,"年俸");
			break;
			case "publish";
			$db->setData($rec,$val,"on");
			break;
			case "label";
			$db->setData($rec,$val,"標準");
			break;
			case "sex";
			$sex = str_replace(array("男","女"),array("m","f"),$a[$key]);
			$db->setData($rec,$val,$sex);
			break;
			case "hope_work_style";
			$hws = str2work_style($a[$key]);
			$db->setData($rec,$val,$hws);
			break;
			case "hope_job_category";
			$hjc = str2jobCate($a[$key]);
			$db->setData($rec,$val,$hjc);
			break;
			case "spouse";
			$spouse = str_replace(array("無し","有り　"),array(false,true),$a[$key]);
			$db->setData($rec,$val,$spouse);
			break;
			case "adds_id":
			$db->setData($rec,$val,SystemUtil::getTableData("nUser", $db->getData($rec,"owner"), "adds"));
			break;
			default:
			$db->setData($rec,$val,$a[$key]);
			break;
			}
		}
		$db->addRecord($rec);
	}
	*/
}

function str2adds($str){
	$db = GMList::getDB("adds");
	$table = $db->getTable();
	$table = $db->searchTable($table,"name","=",$str);
	$rec = $db->getFirstRecord($table);
	return $db->getData($rec,"id");
}

function str2work_style($str){
	$db = GMList::getDB("items_form");
	$table = $db->getTable();
	$table = $db->searchTable($table,"name","=",$str);
	$rec = $db->getFirstRecord($table);
	return $db->getData($rec,"id");
}

function str2jobCate($str){
	$db = GMList::getDB("items_type");
	$table = $db->getTable();
	$table = $db->searchTable($table,"name","=",$str);
	$rec = $db->getFirstRecord($table);
	return $db->getData($rec,"id");
}
