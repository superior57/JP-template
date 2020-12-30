<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class ClipImportLogic extends ImportLogic
{
	var $type = 'clip';
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

		$c_type = ConvartTable::getTypeList( $base['c_type'] );
		$data['c_type'] = $c_type;

		if( $c_type == 'resume' ){ $data['c_id'] = ConvartTable::getResumeId( $base['c_id'] ); }

		return $data;
	}

}
