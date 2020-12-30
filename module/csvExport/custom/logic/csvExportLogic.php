<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of csvExportLogic
 *
 * @author Yuji Noizumi <noizumi@websquare.co.jp>
 */
class csvExportLogic {

	function exportCsv($table){
		global $ACTIVE_NONE;
		global $SYSTEM_CHARACODE;
		global $loginUserType;

		if($loginUserType != 'admin' && $loginUserType != 'cUser'){
			return;
		}

		ob_end_clean();

		// データレコード
		$db = GMList::getDB($_GET['type']);

		if(isset($_SERVER['PATH_INFO'])){
			$outfile = str_replace('/', '', $_SERVER['PATH_INFO']);
		}else{
			
			$outfile = $db->tablePlaneName . date('YmdHis') . '.csv';
		}

		header("Content-Type: application/octet-stream");
		header('Content-Disposition: attachment;filename="'.$outfile.'"');
		header("Content-Transfer-Encoding: binary");
		header('Cache-Control: max-age=0');

		$handle = fopen('php://output','w');

		// ヘッダ出力
		$colName = $db->colName;
		array_push($colName, $db->tablePlaneName);
		fputcsv($handle,$colName);

		// 変換処理用 (DBテーブル名又は日付フォーマット、処理プログラム、データ取得カラム名)
		$converter = array(
			'category'=>			array('table'=>'items_type','program'=>'self::id2name','column'=>'name'),
			'work_style'=>			array('table'=>'items_form','program'=>'self::id2name','column'=>'name'),
			'work_place_adds'=>		array('table'=>'adds',		'program'=>'self::id2name','column'=>'name'),
			'work_place_add_sub'=>	array('table'=>'add_sub',	'program'=>'self::id2name','column'=>'name'),
			'traffic1_adds'=>		array('table'=>'adds',		'program'=>'self::id2name','column'=>'name'),
			'traffic1_line'=>		array('table'=>'line',		'program'=>'self::id2name','column'=>'name'),
			'traffic1_station'=>	array('table'=>'station',	'program'=>'self::id2name','column'=>'name'),
			'traffic2_adds'=>		array('table'=>'adds',		'program'=>'self::id2name','column'=>'name'),
			'traffic2_line'=>		array('table'=>'line',		'program'=>'self::id2name','column'=>'name'),
			'traffic2_station'=>	array('table'=>'station',	'program'=>'self::id2name','column'=>'name'),
			'traffic3_adds'=>		array('table'=>'adds',		'program'=>'self::id2name','column'=>'name'),
			'traffic3_line'=>		array('table'=>'line',		'program'=>'self::id2name','column'=>'name'),
			'traffic3_station'=>	array('table'=>'station',	'program'=>'self::id2name','column'=>'name'),
			'traffic4_adds'=>		array('table'=>'adds',		'program'=>'self::id2name','column'=>'name'),
			'traffic4_line'=>		array('table'=>'line',		'program'=>'self::id2name','column'=>'name'),
			'traffic4_station'=>	array('table'=>'station',	'program'=>'self::id2name','column'=>'name'),
			'traffic5_adds'=>		array('table'=>'adds',		'program'=>'self::id2name','column'=>'name'),
			'traffic5_line'=>		array('table'=>'line',		'program'=>'self::id2name','column'=>'name'),
			'traffic5_station'=>	array('table'=>'station',	'program'=>'self::id2name','column'=>'name'),
			'addition'=>			array('table'=>'job_addition','program'=>'self::id2name','column'=>'name'),
			'regist'=>				array('table'=>'Y/m/d H:i:s','program'=>'self::timestamp','column'=>''),
			'edit'=>				array('table'=>'Y/m/d H:i:s','program'=>'self::timestamp','column'=>''),
			'attention_time'=>		array('table'=>'Y/m/d H:i:s','program'=>'self::timestamp','column'=>''),
			'delete_date'=>			array('table'=>'Y/m/d H:i:s','program'=>'self::timestamp','column'=>''),
		);

		$row = $db->getRow($table);
		$total = 0;
		$count = $row;
		for($i=0; $i<$row; $i++){
			// mid
			$rec = $db->getRecord($table, $i);

			$out = array_fill_keys($db->colName,'');
			foreach($db->colName as $key){
				$out[$key] = $db->getData($rec, $key);
				if(isset($converter[$key])){
					$cvt = $converter[$key];
					$out[$key] = call_user_func($cvt['program'], $cvt['table'], $out[$key], $cvt['column']);
				}
			}

			$buf = mb_convert_variables('sjis-win', $SYSTEM_CHARACODE, $out);
			fputcsv($handle,$out);
		}

		fclose($handle);
		exit(0);
	}

	private function id2name($table, $data, $column){
		$ids = explode('/', $data);
		$names = array();
		foreach($ids as $id){
			$names[] = SystemUtil::getTableData($table, $id, $column);
		}
		$data = implode('/',$names);
		return $data;
	}
	private function timestamp($format,$data,$dummy=NULL){
		if(empty($data)){
			$data = '';
		}else{
			$data = date($format, $data);
		}
		return $data;
	}
}
