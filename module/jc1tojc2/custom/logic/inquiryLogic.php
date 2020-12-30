<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class InquiryImportLogic extends ImportLogic
{
	var $type = 'inquiry';
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

		$data['note'] = $base['main'];
		$data['supported'] = true;

		return $data;
	}
}
