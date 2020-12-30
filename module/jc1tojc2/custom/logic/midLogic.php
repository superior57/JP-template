<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class midImportLogic extends ImportLogic
{
	var $type = 'mid';
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
			$data['foreign_flg'] = TRUE;
			$data['foreign_address'] = $base['ad_inter_info'];
			$data['work_place_label'] = $base['ad_inter_info'];
		}
		else
		{
			$data['work_place_zip1'] = $base['zip1'];
			$data['work_place_zip2'] = $base['zip2'];
			$data['work_place_adds'] = $base['adds'];
			$data['work_place_add_sub'] = $base['add_sub'];
			$data['work_place_add_sub2'] = $base['add_sub2'];
			$data['work_place_add_sub3'] = $base['add_sub3'];
			$data['work_place_label'] = $base['address_label'];
		}


		$data['name'] = $base['title'];
		$data['category'] = $base['job_type'];
		$data['work_style'] = $base['job_form'];
		$data['transport'] = $base['station_label'];
		$data['work_detail'] = $base['work_info'];
		$data['addition'] = $base['job_addition'];


		//画像セット
		$imageList = array();
		if( strlen($base['image']) > 0 ) { $imageList[] = array( 'img'=>$base['image'], 'text'=>"" ); }
		for( $i=1; $i<=3; $i++ )
		{
			if( strlen($base['photo0'.$i.'_img']) > 0 )
			{ $imageList[] = array( 'img'=>$base['photo0'.$i.'_img'], 'text'=>$base['photo0'.$i.'_title'] ); }
		}

		$no=1;
		foreach( $imageList as $image )
		{
			if( $no > 3 ) { break; }
			$data['image'.$no] = $image['img'];
			$data['image'.$no.'_comment'] = $image['text'];
			$no++;
		}

		// 応募資格セット
		$req = array();
		for( $i=1; $i<=5; $i++ )
		{ if( strlen($base['req0'.$i]) > 0 ){ $req[] = $base['req0'.$i]; } }
		$data['apply_license'] = implode( "\n", $req );

		$data['treatment'] = $base['deal'];
		$data['work_time'] = $base['whour'];
		$data['apply_detail'] = $base['interview_method_label'];
		$data['job_pr'] = $base['catchcopy'];
		$data['charger_name'] = $base['interviewer'];

		$data['lat'] = ConvartTable::getLatLon($base['add_sub'], 'lat');
		$data['lon'] = ConvartTable::getLatLon($base['add_sub'], 'lon');

		$publish = 'on';
		if( !SystemUtil::convertBool( $base['publish'] ) ) { $publish = 'off'; }
	 	$data['publish'] = $publish;

		$activate = 4;
		if( !SystemUtil::convertBool( $base['open'] ) ) { $activate = 1; }
	 	$data['activate'] = $activate;

		$data['limits'] = $base['limit_time_apply'];

		$apply_pos = false;
		if( !SystemUtil::convertBool( $base['apply_pos'] ) ) { $apply_pos = true; }
	 	$data['apply_pos'] = $apply_pos;

		$attention = false;
		if( $base['attention_time'] > time() ) { $attention = TRUE; }
	 	$data['attention'] = $attention;

		 return $data;
	 }

}
