<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class EntryImportLogic extends ImportLogic
{
	var $type = 'entry';
	var $check_name = 'id';
	var $id_update = false;

	/**
	 * 元データから新フォーマット用のデータを生成する
	 *
	 * @param base パラメータを生成する元データ
	 * @return 生成したデータ
	 */
	function createParam( $base )
	{
		$db = GMList::getDB($this->type);

		$data['items_id'] = $base['job_id'];
		$data['items_owner'] = $base['cuser_id'];
		$data['transport'] = $base['station_label'];
		$data['work_detail'] = $base['work_info'];
		$data['addition'] = $base['job_addition'];

		$user = $base['nuser_id'];
		$userType = 'nUser';
		if( $user == 'unknown' )
		{
			$user = $base['nuser_id'] = $this->registerNobody($base);
			$userType = 'nobody';
		}
		$data['entry_user'] = $user;

		$status = $base['apply_state'];
		if( $status == "SUCCE" ) { $status = "SUCCESS"; }
		$data['status'] = $status;

		$message_id = $this->registMessage( $base, $userType );
		$data['message_id'] = $message_id;

		$data['items_type'] = 'mid';

		return $data;
	}

	function registMessage( $base, $userType )
	{
		$gm = GMList::getGM('message');
		$db = $gm->getDB();
		$rec = $db->getNewRecord();

		$data['owner'] = $base['nuser_id'];
		$data['destination'] = $base['cuser_id'];
		$data['message'] = $base['main'];
		$data['read_flg'] = $base['readf'];
		$data['mailtype'] = "entry";

		$threadId = threadLogic::getThreadID($base['cuser_id'],$base['nuser_id']);
		$data['thread_id'] = $threadId;
		$data['owner_type'] = $userType;
		$data['sub'] = '求人応募';
		$data['sender_del'] = false;
		$data['receiver_del'] = false;

		$fileType = 'nobody';
		$fileId = $base['nuser_id'];
		if( $userType == 'nUser' )
		{
			$fileType = 'resume';
			$fileId = ConvartTable::getResumeId($base['nuser_id']);
		}
		$file = "index.php?app_controller=info&type=".$fileType."&id=".$fileId;
		$data['file'] = $file;

		foreach( $gm->colName as $col )
		{// 同名カラムが存在する初期値をセット
			if( isset($base[$col]) && strlen($base[$col]) > 0 ) { $db->setData( $rec, $col, $base[$col] ); }
		}
		// 生成したデータをセット
		foreach( $data as $col => $value ){ $db->setData( $rec, $col, $value ); }

		$db->addRecord($rec);

		return $db->getData( $rec, 'id' );
	}

	/**
	 * 未登録ユーザーの場合nobodyにデータを登録してIDを返す
	 *
	 * @param base 初期化データ
	 * @return noodyID
	 */
	function registerNobody( $base )
	{
		$gm = GMList::getGM('nobody');
		$db = $gm->getDB();
		$rec = $db->getNewRecord();

		$data['kana'] = $base['ruby'];
		$data['mobile_tel'] = $base['tel2'];

		$data['birth_date_year'] = $base['birthyear'];
		$data['birth_date_month'] = $base['birthmonth'];
		$data['birth_date_day'] = $base['birthday'];
		$data['birth_date'] = mktime(0,0,0,$base['birthmonth'],$base['birthday'], $base['birthyear']);
		$data['sex'] = ConvartTable::getSex($base['sex']);
		$data['school'] = $base['ed_back'];
		$data['license'] = $base['qualification'];
		$data['history'] = $base['career'];

		$db->setData($rec, "publish", "on");

		foreach( $gm->colName as $col )
		{// 同名カラムが存在する初期値をセット
			if( isset($base[$col]) && strlen($base[$col]) > 0 ) { $db->setData( $rec, $col, $base[$col] ); }
		}
		// 生成したデータをセット
		foreach( $data as $col => $value ){ $db->setData( $rec, $col, $value ); }

		$db->addRecord($rec);

		return $db->getData( $rec, 'id' );
	}

}
