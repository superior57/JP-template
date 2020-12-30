<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of nuser_csvApi
 *
 * @author Yuji Noizumi <noizumi@websquare.co.jp>
 */
class mod_csvExportApi {

	function uploadCsv( $param ){
		global $loginUserType;

		if ($loginUserType != 'admin' && $loginUserType != 'cUser') {
			return;
		}

		$_POST = $param;
		include_once 'custom/head_main.php';

		ConceptSystem::CheckAuthenticityToken()->OrThrow('IllegalTokenAccess');

		$result = Array();
		$result['preview'] = '';
		$result['src'] = '';
		$saveFiles = array();

		$directory = 'file/upload/';
		if (!is_dir($directory)) {
			mkdir($directory, 0777, true);
			chmod($directory, 0777);
		} //ディレクトリが存在しない場合は作成

		$replace = '';
		if (isset($_POST['replace'])) {
			$replace = $_POST['replace'];
			if (!preg_match('/' . preg_quote($directory) . '/', $replace)) {
				$replace = '';
			}
		}
		if (!empty($replace)) {
			@unlink($replace);
		}
		foreach ($_FILES as $data) {
			$ext = preg_replace('/^.*\.(.*)$/', '$1', $data['name']);
			$saveName = $directory . time() . '_' . rand() . '.' . $ext;

			if (isset($data['is_big'])) {
				rename($data['tmp_name'], $saveName);
			} else {
				move_uploaded_file($data['tmp_name'], $saveName);
			}
			chmod($saveName,0666);
			$result['src'] = $saveName;
			$result['preview'] .= '';
		}
		$result['token'] = SystemUtil::getAuthenticityToken();
		$result['type'] = $param['type'];

		print json_encode($result);
		return;
	}

	/**
	 * 
	 * 課題：企業ユーザーインポート時、データ更新の場合は owner チェックが必要
	 * 
	 * @global type $SYSTEM_CHARACODE
	 * @global type $loginUserType
	 * @param type $param
	 * @return type
	 */
	function importCsv( $param ){
		global $SYSTEM_CHARACODE;
		global $loginUserType;
		global $LOGIN_ID;

		if ($loginUserType != 'admin' && $loginUserType != 'cUser') {
			return;
		}

		$_POST = $param;
		include_once 'custom/head_main.php';
		ConceptSystem::CheckAuthenticityToken()->OrThrow('IllegalTokenAccess');

		set_time_limit(0);

		$error = false;
		$result['preview'] = '';
		$csvFile = $param['f'];
		$fp = fopen($csvFile, 'rb');
		if ($fp === false) {
			$error = true;
			$result['preview'] .= 'ファイルが開けませんでした<br/>';
		}
		$line_count = 0;
		$new_count = 0;
		$update_count = 0;
		$type = $param['type'];
		$db = GMList::getDB($type);

		$result['preview'] = '';
		while (!feof($fp) && !$error) { //全ての行を処理
			$data = fgetcsv($fp);
			mb_convert_variables($SYSTEM_CHARACODE, 'sjis-win', $data);

			if (!$data) { //行が空の場合
				continue;
			}
			$line_count++;
			if($line_count == 1){ // 1行目はカラム名を想定
				$tbl_name = array_pop($data);
				if($type != $tbl_name && strtolower($tbl_name) != 'nocheck'){
					$error = true;
					$result['preview'] .= $type.'用のファイルではありません<br/>';
					array_push($data, $tbl_name);
				}
				$colNames = $data;
				$id_index = array_search('id', $colNames);
				continue;
			}

			if(strlen($data[$id_index])>0){
				$rec = $db->selectRecord($data[2]);	// 更新データの場合
			}else{
				$rec = $db->getNewRecord();			// 新規データの場合
			}
			if($rec != NULL){
				foreach($db->colName as $colName){
					$idx = array_search($colName, $colNames);
					if($idx !== FALSE){
						if($loginUserType == 'cUser' && $colName == 'owner'){
							if($LOGIN_ID != $data[$idx]){
								$result['preview'] .= $line_count.':他社用データ取込不可<br />';
								continue 2;
							}
						}
						$db->setData($rec, $colName, $data[$idx]);
					}
				}
				if(strlen($data[$id_index])>0){
					$db->updateRecord($rec);	// 更新データの場合
					$update_count++;
				}else{
					$db->addRecord($rec);		// 新規データの場合
					$new_count++;
				}
			}
		}

		fclose($fp);
		if (preg_match('/file\/upload/', $csvFile)) {
			unlink($csvFile);
		}

		if($line_count>0){
			$line_count--; // ヘッダ分減算
		}
		if ($error) {
			if ($line_count > 1) {
				$result['preview'] .= ($line_count - 1) . '行目まで取り込みました。<br />';
			}
			$result['preview'] .= $line_count . '行目でエラーが発生しました。';
			if (!empty($data)) {
				$result['preview'] .= '<br />' . implode(',', $data);
			}
		} else {
			$buf =<<< EOD
{$line_count}行取り込みました。<br />
新規：{$new_count} 更新：{$update_count}<br />
<input type="button" value="ページ更新" onclick="location.reload();">
EOD;
			$result['preview'] .= $buf;
		}
		$result['token'] = SystemUtil::getAuthenticityToken();
		print json_encode($result);
		return;
	}
}
