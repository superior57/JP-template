<?php
//管理者用各検索ページの一括変更処理
class mod_specialSettingApi extends apiClass{

	function splitUpdate($param){
		// 実行時間の制限を無効にする
		set_time_limit( 0 );
		$db = GMList::getDB($param["type"]);
		$result=$db->sql_query($_SESSION["tempSQL"]);
		
		if( !$result ){
			exit("getRecord() : SQL MESSAGE ERROR. \n");
		}
		
		if($db->sql_num_rows($result) != 0){
			$updateCount=0;
			for($i=$param["start"];$i<($param["start"]+$param["limit"])&&$i<$db->sql_num_rows($result);$i++){
			
				$rec = $db->sql_fetch_assoc( $result, $i);
				
				if($db->getData($rec,"special")){
					$special=explode("/",$db->getData($rec,"special"));
					if(!in_array($param["pid"],$special)) $special[]=$param["pid"];
					$db->setData($rec,"special",implode("/",$special));
				}else{
					$db->setData($rec,"special",$param["pid"]);
				}
				$db->updateRecord($rec);
				++$updateCount;
			}
			echo $updateCount;
		}
		
	
	}
	
	function splitDelete($param){
		// 実行時間の制限を無効にする
		set_time_limit( 0 );
		global $loginUserType;
		if($loginUserType != "admin") return;

		$type = $param["type"];
		$val=$param["pid"];

		if($val){
		
			//まず完全一致を一括で消す
			$db = GMList::getDB($type);
			$table = $db->getTable();
			$table = $db->searchTable($table,"special","=",$val);
			$updateCount=$db->getRow($table);
			if($updateCount>0) $db->setTableDataUpdate($table,"special","",true);
		
		
			$db = GMList::getDB($type);
			$table = $db->getTable();
			$table = $db->searchTable($table,"special","like","%".$val."%");
			$row=$db->getRow($table);
			
			if($row>0){
				for($i=0;$i<$row&&$i<$param["limit"];$i++){
	
					$rec=$db->getRecord($table,$i);
					$special=explode("/",$db->getData($rec,"special"));
	
					if(in_array($val,$special)) unset($special[array_search($val,$special)]);
					$db->setData($rec,"special",implode("/",$special));
					$db->updateRecord($rec);
					++$updateCount;
				}
			}
			echo $updateCount;
		}
	
	}

	//inquiry 対応フラグ変更
	function changeSpecial($param){
		global $loginUserType;
		if($loginUserType != "admin") return;

		$type = $param["type"];
		$id = explode("/", $param["id"]);
		$val=$param["val"];

		if($val){
			$db = GMList::getDB($type);
			$table = $db->getTable();
			$table = $db->searchTable($table,"id","in",$id);
			$row=$db->getRow($table);

			$_SESSION["tempSQL"]=$table->getString();

			for($i=0;$i<$row;$i++){

				$rec=$db->getRecord($table,$i);
				if($db->getData($rec,"special")){
					$special=explode("/",$db->getData($rec,"special"));
					if(!in_array($val,$special)) $special[]=$val;
					$db->setData($rec,"special",implode("/",$special));
				}else{
					$db->setData($rec,"special",$val);
				}
				$db->updateRecord($rec);

			}

			//特集ページ追加指定の場合は特集ページ編集画面へジャンプ
			echo "index.php?app_controller=page&p=".systemUtil::getTableData("page", $val, "name")."&add=".$row;
		}
	}



	//common データ削除
	function deleteSpecial($param){
		global $loginUserType;
		if($loginUserType != "admin") return;

		$type = $param["type"];
		$id = explode("/", $param["id"]);
		$val=$param["val"];

		if($val){
			$db = GMList::getDB($type);
			$table = $db->getTable();
			$table = $db->searchTable($table,"id","in",$id);
			$row=$db->getRow($table);

			for($i=0;$i<$row;$i++){

				$rec=$db->getRecord($table,$i);
				$special=explode("/",$db->getData($rec,"special"));

				if(in_array($val,$special)) unset($special[array_search($val,$special)]);
				$db->setData($rec,"special",implode("/",$special));
				$db->updateRecord($rec);
			}

		}
	}

	function deleteSpecialAll($param){
		global $loginUserType;
		if($loginUserType != "admin") return;

		$type = $param["type"];
		$val=$param["val"];

		if($val){
			$db = GMList::getDB($type);
			$table = $db->getTable();
			$table = $db->searchTable($table,"special","like",$val);
			$row=$db->getRow($table);

			for($i=0;$i<$row;$i++){

				$rec=$db->getRecord($table,$i);
				$special=explode("/",$db->getData($rec,"special"));

				if(in_array($val,$special)) unset($special[array_search($val,$special)]);
				$db->setData($rec,"special",implode("/",$special));
				$db->updateRecord($rec);
			}

		}
	}
}