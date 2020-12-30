<?php

class specialLogic
{
	static function updateJobSpecial( $db, $table, $param ){
		
		//$table = $db->searchTable($table,"special","!","%".$param["pid"]."%");
		$_SESSION["tempSQL"]=$table->getString();
		$row=$db->getRow($table);
		
		//まず完全一致を一括で消す
		$table = $db->searchTable($table,"special","=","");
		$updateCount=$db->getRow($table);

		if($updateCount>0) $db->setTableDataUpdate($table,"special",$param["pid"],true);

		self::updateQuery($param["pid"],$param);
		//特集ページ追加指定の場合は特集ページ編集画面へジャンプ
		//var_dump($updateCount);
		//exit;
		header("Location:index.php?app_controller=page&p=".systemUtil::getTableData("page", $param["pid"], "name")."&add=".($row-$updateCount));
	}

	function updateQuery($pid,$param){
		unset($param['pid']);
		$query = http_build_query($param);
		$db = GMList::getDB("page");
		$rec = $db->selectRecord($pid);
		$db->setData($rec,"query",$query);
		$db->updateRecord($rec);
	}
}