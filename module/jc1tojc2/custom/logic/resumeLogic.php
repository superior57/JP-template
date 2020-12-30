<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class ResumeImportLogic extends ImportLogic
{
	var $type = 'resume';
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

		$data['owner'] = $base['id']; //nUserテーブルを元に変換するため
		$data['birth_date_year'] = $base['birthyear'];
		$data['birth_date_month'] = $base['birthmonth'];
		$data['birth_date_day'] = $base['birthday'];
		$data['birth_date'] = mktime(0,0,0,$base['birthmonth'],$base['birthday'], $base['birthyear']);
		$data['sex'] = ConvartTable::getSex($base['sex']);
		$data['school'] = $base['ed_back'];
		$data['hope_job_category'] = $base['wish_job_type'];
		$data['hope_work_style'] = $base['wish_job_form'];
		$data['license'] = $base['qualification'];
		$data['history'] = $base['career'];
		$data['remarks'] = $base['pr'];

		$wish_adds_id = array();
		$wish_adds_label = array();
		for( $i=1; $i<=5; $i++ )
		{
			$id = "";
			$label = "";
			if( strlen( $base['wish_adds0'.$i]) > 0 )
			{
				$id = $base['wish_adds0'.$i];
				$label = ConvartTable::getAddsName($base['wish_adds0'.$i]);
			}
			if( strlen( $base['wish_add_sub0'.$i]) > 0 )
			{
				$id .= ','.$base['wish_add_sub0'.$i];
				$label .= ConvartTable::getAddSubName($base['wish_add_sub0'.$i]);
			}
			if( strlen($id) > 0 ) { $wish_adds_id[] = $id; }
			if( strlen($label) > 0 ) { $wish_adds_label[] = $label; }
		}
		$data['hope_work_place'] = implode( '/', $wish_adds_id );
		$data['hope_work_place_label'] = implode( "\n", $wish_adds_label );

		$data['label'] = "標準";
		$data['recent_state'] = "その他";
		$data['spouse'] = false;
		$data['publish'] = 'on';

		return $data;
	}

}
