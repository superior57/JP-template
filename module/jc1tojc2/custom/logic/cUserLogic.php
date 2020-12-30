<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class cUserImportLogic extends ImportLogic
{
	var $type = 'cUser';
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

		$data['foreign_flg'] = false;
		if( $base['ad_inter'] == '海外' )
		{
			$data['foreign_flg'] = true;
			$data['foreign_address'] = $base['ad_inter_info'];
		}

		$data['owner_name'] = $base['cap'];

		if(  strlen($base['birth_year']) > 0 )
		{
			$data['establish_date'] = $base['birth_year'].'年';
			if(  strlen($base['birth_month']) > 0 ) { $data['establish_date'] .= $base['birth_month'].'月'; }
		}

		$data['employee'] = $base['number'];
		$data['work_detail'] = $base['app'];
		$data['sub_mail'] = $base['mail2'];
		$data['login'] = $base['logout'];

		$data['lat'] = ConvartTable::getLatLon($base['add_sub'], 'lat');
		$data['lon'] = ConvartTable::getLatLon($base['add_sub'], 'lon');

		$data['inquiry'] = 'on';
		$data['receive_notice'] = true;
		$data['guide'] = true;
		$data['information'] = true;
		$data['charging_mid'] = true;
		$data['charging'] = true;
		$data['edit_comp'] = true;
		if( $base['activate'] == 2 ) { $data['activate'] = 1; }  

		return $data;
	}

}
