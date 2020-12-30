<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class Mail_sendImportLogic extends ImportLogic
{
	var $type = 'mailSend';
	var $check_name = 'id';
	var $id_update = false;
	var $listId = array( 'nUser'=>'ML000001', 'cUser'=>'ML000002' );

	/**
	 * 元データから新フォーマット用のデータを生成する
	 *
	 * @param base パラメータを生成する元データ
	 * @return 生成したデータ
	 */
	function createParam( $base )
	{
		$db = GMList::getDB($this->type);

		$data['through_cnt'] = $base['send_cnt'];
		$data['success_cnt'] = $base['send_cnt'];
		$data['total_cnt'] = $base['send_cnt'];
		$data['list_id'] = $this->listId[$base['user_type']];

		$data['mail_type'] = 'guide';

		return $data;
	}



}