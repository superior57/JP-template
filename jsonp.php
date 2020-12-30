<?php
	include_once "./custom/head_main.php";

	$type		= $_GET["type"];
	$ids	= explode(",",$_GET["id"]);
	$ids	= array_unique((array)$ids);
	$ids	= array_values($ids);

	$db		 = $gm[ $type ]->getDB();

	foreach($ids as $key => $id){
		$rec = $db->selectRecord($id);
		if(!$rec){continue;}

		foreach($gm[ $type ]->colName as $col){
			if($col == "price")
				$data["Body"][$key][$col] = number_format($db->getData($rec,$col));
			elseif($col == "category")
				$data["Body"][$key][$col] = systemUtil::getTableData( "items_type", $db->getData($rec,$col), 'name' );
			elseif($col == "work_place_adds")
				$data["Body"][$key][$col] = systemUtil::getTableData( "adds", $db->getData($rec,$col), 'name' );
			else
				$data["Body"][$key][$col] = $db->getData($rec,$col);

		}
	}
	$json = json_encode($data);

	print $type."Data(".$json.");";
