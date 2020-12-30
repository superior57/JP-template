<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class nUserImportLogic extends ImportLogic
{
	var $type = 'nUser';
	var $check_name = 'id';
	var $id_update = false;

	/**base
	 * 元データから新フォーマット用のデータを生成する
	 *
	 * @param base パラメータを生成する元データ
	 * @return 生成したデータ
	 */
	function createParam( $base )
	{
		$db = GMList::getDB($this->type);

		$data['kana'] = $base['ruby'];
		$data['nick_name'] = $base['id'];
		$data['mobile_tel'] = $base['tel2'];
		$data['login'] = $base['logout'];

		$data['receive_notice'] = true;
		$data['review_notice'] = true;
		$data['view_mode'] = "mid";
		$data['guide'] = true;
		$data['information'] = true;
		$data['edit_comp'] = false;
		if( $base['activate'] == 2 ) { $data['activate'] = 1; }  

		return $data;
	}

}
