<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class AttentionImportLogic extends ImportLogic
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

		$data['target_id'] = $base['job'];
		$data['pay_time'] = $base['regist'];
		$data['limits'] = $this->getLimits($base['regist'],$base['term']);
		$data['money'] = $base['cost'];

		$data['target_type'] = 'mid';
		$data['label'] = 'attention';
		$data['pay_flg'] = true;
		$data['notice'] = true;
		$data['is_billed'] = true;

		return $data;
	}

	function getLimits( $regist, $term )
	{
		$y = date("Y",$regist);
		$m = date("n",$regist);
		$d = date("d",$regist);
		return mktime(23,59,59,$m,$d+$term,$y);
	}

}