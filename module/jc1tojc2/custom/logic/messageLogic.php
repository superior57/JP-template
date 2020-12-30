<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class MessageImportLogic extends ImportLogic
{
	var $type = 'message';
	var $check_name = 'id';
	var $id_update = false;

	var $userTypeList = array();

	/**
	 * 元データから新フォーマット用のデータを生成する
	 *
	 * @param base パラメータを生成する元データ
	 * @return 生成したデータ
	 */
	function createParam( $base )
	{
		$db = GMList::getDB($this->type);

		$data['message'] = $base['main'];
		$data['read_flg'] = $base['readf'];

		$data['owner'] = $base['send_id'];
		$data['owner_type'] = $this->getUserType( $base['send_id'] );
		$data['destination'] = $base['receive_id'];
		$data['mailtype'] = "reply";

		$cUser = "";
		$nUser = "";
		$colList = array('send_id', 'receive_id');
		foreach( $colList as $col )
		{
			$type = $this->getUserType( $base[$col] );
			switch($type)
			{
			case 'cUser': $cUser = $base[$col]; break;
			case 'nUser': $nUser = $base[$col]; break;
			}
		}
		if( strlen($cUser) > 0 && strlen( $nUser ) > 0 )
		{
			$threadId = threadLogic::getThreadID($cUser,$nUser);
			$data['thread_id'] = $threadId;
		}

		return $data;
	}

	function getUserType($id)
	{
		if( isset($this->userTypeList[$id]) ) { return $this->userTypeList[$id]; }	

		$userType = '';
		if( substr($id, 0,1) == "N" )		 { $userType = "nUser"; }
		elseif( substr($id, 0,1) == "C" )	 { $userType = "cUser"; }

		$this->userTypeList[$id] = $userType;
		return $userType;
	}


}
