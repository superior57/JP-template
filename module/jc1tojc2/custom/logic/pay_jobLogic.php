<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class Pay_jobImportLogic extends ImportLogic
{
	var $type = 'pay_job';
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

		$data['owner'] = $base['id']; //cUserテーブルを元に変換するため
		$data['target_type'] = 'mid_term';
		$data['target_id'] = '';
		$data['label'] = 'mid';
		$data['status'] = '2nd';
		$data['money'] = 0;
		$data['pay_flg'] = true;
		$data['pay_time'] = $base['regist'];
		$data['notice'] = true;
		$data['is_billed'] = true;
		$data['limits'] = $base['limit_time'];
		$data['regist'] = $base['regist'];

		return $data;
	}

}
