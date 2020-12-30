<?php

class mod_articleApi
{

	function edit( &$param )
	{
		global $loginUserType;
		global $loginUserRank;
		global $LOGIN_ID;
		global $ACTIVE_NONE;
		global $ACTIVE_ACCEPT;
		global $ACTIVE_DENY;
		global $HOME;
		global $gm;

		$_GET['type'] = $param["type"] ;
		$_GET['id'] = $param["id"] ;
		$_POST = $param;

		$sys = SystemUtil::getSystem( articleLogic::$type );
		if( $loginUserType == 'admin' ){ $table_type = 'all'; }
		$db	 = $gm[ $param['type'] ]->getDB();
		$rec = System::setPageRecord( $db, $table_type );
		ConceptSystem::CheckTableEditUser($db,$rec)->OrThrow("IllegalAccess");

		System::$checkData = new CheckData( $gm, true, $loginUserType, $loginUserRank, articleLogic::$type );

		// 入力内容確認
		$check1	 = $sys->registCheck( $gm, true, $loginUserType, $loginUserRank );
		$update = [];
		foreach( $rec as $col => $val ) {
			// formにないデータを補う
			if( isset($param[$col]) ) { $updata[$col] = $param[$col]; }
			else { $updata[$col] = $rec[$col]; }
		}
		if( !count($updata) ){ exit(); }

		$rec = $db->setRecord( $rec, $updata );
		$check2	 = $sys->registCompCheck( $gm, $rec ,$loginUserType, $loginUserRank, true);

		if( $check1 && $check2 )
		{
			$old_rec = $db->selectRecord($param['id']);
			$rec = array_merge($old_rec,$rec);

			$sys->editProc( $gm, $rec, $loginUserType, $loginUserRank );

			$db->updateRecord( $rec );
			$data['status'] = "success";
			$sys->editComp( $gm, $rec, $old_rec, $loginUserType, $loginUserRank );

			$old_activate = $db->getData($old_rec,'activate');
			$new_activate = $db->getData($rec,'activate');
			if( ($old_activate != $new_activate) || ($new_activate == $ACTIVE_ACCEPT) ){
				$sys->FeedProc();
			}

			$data['id']  = $param["id"];
			self::returnJsonData($data);
			return ;

		}
		else
		{
			// エラー内容を調整
			$err = SystemBase::$checkData->error_msg;
			$errMsg = $err['is_error'];
			unset($err['is_error']);

			$err_data = $err;
			$err_data['check'] = $errMsg;

			self::returnJsonData($err_data);
			return ;
		}
	}

	/*
	 *  parts追加フォームを返す
	 */
	function addPartsForm( &$param ){
		global $LOGIN_ID;
		global $loginUserType;
		global $loginUserRank;
		global $gm;

		$res = [];
		$Gm = GMList::getGM(articleLogic::$type);
		if( $loginUserType != 'nobody' && strlen($LOGIN_ID) ) {
			if( isset($param['parts_type']) ) {
				$design = Template::getTemplate( $loginUserType, $loginUserRank, 'article', 'PARTS_ADD_FORM_DESIGN' );
				$res['form'] = $Gm->getString( $design, null, $param['parts_type'].'_input' );
			}
		}
		self::returnJsonData($res);
		return ;
	}

	function registParts( &$param ){
		global $LOGIN_ID;
		global $loginUserType;
		$erroMsg = Template::getTemplate( $loginUserType, '4', articleLogic::$type, 'REGIST_ERROR_DESIGN' );
		$res = self::apiError();

		$check = article_partsLogic::checkPartsData($param);
		if( !$check ) {
			self::returnJsonData($res);
			return ;
		}

		$db  = GMList::getDB(articleLogic::$sub_type );
		$rec = $db->getNewRecord( );

		$mehod = $param['part_type'].'SetParts';
		$check = article_partsLogic::{$mehod}($db, $rec, $param, $erroMsg );

		if( $check['status'] == 'error' ) {
			$res['status'] = 'error';
			$res['error'] = $check['errorMsg'];
		}
		else {
			$db->setData($rec,'original_id', $param['original_id']);
			$db->setData($rec,'regist', time());
			$db->addRecord($rec);

			articleLogic::setSearchWord( $param['original_id'], $rec );

			$design = Template::getTemplate( $loginUserType, $loginUserRank, 'article', 'PARTS_DESIGN' );
			$res['html'] = article_partsLogic::draw(array('rec'=>$rec),$design, true);
			$res['status'] = 'success';
			$res['jump'] = 'reload';
		}
		self::returnJsonData($res);
		return ;
	}

	function updateParts( &$param ){
		global $LOGIN_ID;
		global $loginUserType;
		$erroMsg = Template::getTemplate( $loginUserType, '4', articleLogic::$type, 'REGIST_ERROR_DESIGN' );

		$res = self::apiError();

		$check = article_partsLogic::checkPartsData($param , true);
		if( !$check ) {
			self::returnJsonData($res);
			return ;
		}

		$db  = GMList::getDB(articleLogic::$sub_type );
		if( isset($param['parts_id']) && strlen($param['parts_id']) )
		{
			$db    = GMList::getDB(articleLogic::$sub_type );
			$rec = $db->selectRecord($param['parts_id']);
			$pid = $db->getData($rec,'id');
			if(strlen($pid) < 1) {
				self::returnJsonData($res);
				return ;
			}
		}
		else {
			self::returnJsonData($res);
			return ;
		}

		$mehod = $param['part_type'].'SetParts';
		$check = article_partsLogic::{$mehod}($db, $rec, $param, $erroMsg );
		if( $check['status'] == 'error' ) {
			$res['status'] = 'error';
			$res['error'] = $check['errorMsg'];
		}
		else {
			$db->setData($rec,'edit', time());
			$db->updateRecord($rec);

			articleLogic::setSearchWord( $param['original_id'], $rec );

			$design = Template::getTemplate( $loginUserType, $loginUserRank, 'article', 'PARTS_DESIGN' );
			$res['html'] = article_partsLogic::draw(array('rec'=>$rec),$design, true);
			$res['status'] = 'success';
			$res['jump'] = 'reload';
		}
		self::returnJsonData($res);
		return ;
	}

	function deleteParts( &$param ){
		global $LOGIN_ID;
		global $loginUserType;

		$res = self::apiError();

		$check = article_partsLogic::checkPartsData($param , true);
		if( !$check ) {
			self::returnJsonData($res);
			return ;
		}

		$db  = GMList::getDB(articleLogic::$sub_type );
		if( isset($param['parts_id']) && strlen($param['parts_id']) )
		{
			$db    = GMList::getDB(articleLogic::$sub_type );
			$rec = $db->selectRecord($param['parts_id']);
			$pid = $db->getData($rec,'id');
			if(strlen($pid) < 1) {
				self::returnJsonData($res);
				return ;
			}
		}
		else {
			self::returnJsonData($res);
			return ;
		}

		if( $db->deleteRecord($rec) ) {
			$_tmp['id'] = $pid;
			$odb = GMList::getDB(articleLogic::$type);
			$orec = $odb->selectRecord($param['original_id']);
			$search = $odb->getData($orec,'search');

			$res['status'] = 'success';
			$res['jump'] = 'reload';

			$new = articleLogic::SearchWord($search, $_tmp);

			$db->setData($rec, 'search', $new);
			$db->updateRecord($rec);
		}

		self::returnJsonData($res);
		return ;

		
		$check = article_partsLogic::{$mehod}($db, $rec, $param );
		if( $check['status'] == 'error' ) {
			$res['status'] = 'error';
			$res['error'] = $check['errorMsg'];
		}
		else {
			$db->setData($rec,'edit', time());
			$db->updateRecord($rec);

			articleLogic::setSearchWord( $param['original_id'], $rec );

			$design = Template::getTemplate( $loginUserType, $loginUserRank, 'article', 'PARTS_DESIGN' );
			$res['html'] = article_partsLogic::draw(array('rec'=>$rec),$design, true);
		}
		self::returnJsonData($res);
		return ;
	}

	function updatePartsList( &$param ){
		global $LOGIN_ID;
		global $loginUserType;

		$res = self::apiError();

		if( isset($param['id']) && strlen($param['id']) )
		{
			$db    = GMList::getDB(articleLogic::$type );
			$rec = $db->selectRecord($param['id']);

			$owner = $db->getData($rec,'owner');
			if( $owner == $LOGIN_ID || $loginUserType == 'admin') {
				$db->setData($rec,'parts',$param['parts']);
				$db->updateRecord($rec);
				$res['status'] = 'success';
			}
		}
		self::returnJsonData($res);
		return ;
	}

	function apiError() {
		global $HOME;
		return array(
			'status' => 'error',
			'jump'   => 'home',
			'url'    => $HOME,
		);
	}

	/*
	 * 指定されたURLを確認
	 */
	function getHttpStatusCode($param)
	{
		if(!isset($param['url'])){
			$data['status'] = 'error';
			$data['Code'] = '';
			self::returnJsonData($data);
			return ;
		}

		$chkRes = articleLogic::HttpCheck($param['url']);
		self::returnJsonData($chkRes);
		return ;
	}
	
	/*
	 * 指定されたURLが画像か確認
	 */
	function checkImageURL($param)
	{		
		if(!isset($param['url']))
		{
			$data['status'] = 'error';
			$data['Code'] = '';
			self::returnJsonData($data);
			return ;
		}

		if(articleLogic::checkUrlImg( $param['url']))
		{ // URL先の画像情報が取得できたとき
			$data['status'] = 'success';
			$data['Code'] = '200';
			$data['img'] = $param['url'];
			self::returnJsonData($data);
			return ;
		}

		$data['status'] = 'error';
		$data['Code'] = 'no_img_info';
		echo json_encode($data);
	}

	function saveUrlImage($param)
	{
		global $FileBase;
		
		if(!isset($param['url']))
		{
			$data['status'] = 'error';
			$data['Code'] = '';
			self::returnJsonData($data);
			return ;
		}

		$check = false;
		$param['url'] = SystemUtil::checkWebp($param['url']);
		if(articleLogic::checkUrlImg( $param['url'])) { $check = true; }
		if(!$check)
		{ // URL先に画像情報が取得できなかったとき
			$data['status'] = 'error';
			$data['Code'] = 'no_img_info';
			self::returnJsonData($data);
			return ;
		}

		$directory = 'file/parts/tmp/';
		if(!is_dir($directory)) { mkdir( $directory, 0777, true ); chmod( $directory, 0777 );}

		$file_path = pathinfo($param['url']);
		$img_data = file_get_contents($param['url'],false, articleLogic::sslConTextSetting($param['url']));
		if(strlen($img_data) < 1 )
		{ // URL先に画像データが取得できなかったとき
			$data['status'] = 'error';
			$data['Code'] = 'no_img_info';
			self::returnJsonData($data);
			return ;
		}
		$size = getimagesize($param['url']);
		$ext = "";
		$img_type = array('1'=>'gif','2'=>'jpg','3'=>'png', '4'=>'wbmp');
		if(isset($size[2]) && isset($img_type[$size[2]])) $ext = ".".$img_type[$size[2]];
		$save_file = $directory.hash('md5',$file_path['filename'].microtime()).$ext;
		file_put_contents( $save_file,$img_data);
		//$FileBase->upload($save_file,$save_file);
		$data['status'] = 'success';
		$data['Code']   = '200';
		$data['path']   = $save_file;
		// 「/」のエスケープの可否がバージョンによって異なるため、json_encodeは使わない
		$json_str ="{";
		foreach($data as $key => $value) {
			$temp[] = '"'.$key.'":"'.$value.'"';
		}
		$json_str .= implode(",",$temp);
		$json_str .="}";
		echo $json_str;
	}

	/* 
	 * リンク記述をリンクに置換
	 */
	function convertLink($param)
	{
		$text = $param['text'] ? $param['text'] : "";
		if( strlen($text) > 0 )
		{
			$text = SystemUtil::convertLink($text);
		}
		echo $text;
	}

	function addTag($param) {
		global $LOGIN_ID;
		global $loginUserType;

		$res = self::apiError();
		$edit_check = true;

		$oId = isset($param['original_id']) ? $param['original_id'] : "";
		$word = isset($param['word']) ? $param['word'] : "";

		if( strlen($oId)<1 || strlen((string)$word) <1 )
		{
			$edit_check = false ;
			$res['msg'] = '編集画面にアクセスし直してください。';
		}

		if( strlen($oId) ){
			$db    = GMList::getDB(articleLogic::$type );
			$rec = $db->selectRecord($oId);

			if( !$loginUserType == 'admin' && $LOGIN_ID != $db->getData($rec, 'owner') )
			{ $edit_check = false ; }
		}

		if( !$edit_check ) {
			self::returnJsonData($res);
			return ;
		}

		$res = article_tagLogic::update($oId, $word);
		self::returnJsonData($res);
		return ;
	}

	function delTag($param) {
		global $LOGIN_ID;
		global $loginUserType;

		$res = self::apiError();
		$edit_check = true;

		$oId = isset($param['original_id']) ? $param['original_id'] : "";
		$word = isset($param['word']) ? $param['word'] : "";

		if( strlen($oId)<1 || strlen((string)$word) <1 )
		{
			$edit_check = false ;
			$res['msg'] = '削除するタグを確認してください。';
		}

		if( strlen($oId) ){
			$db    = GMList::getDB(articleLogic::$type );
			$rec = $db->selectRecord($oId);

			if( !$loginUserType == 'admin' && $LOGIN_ID != $db->getData($rec, 'owner') )
			{ $edit_check = false ; }
		}

		if( !$edit_check ) {
			self::returnJsonData($res);
			return ;
		}

		$res = article_tagLogic::update($oId, $word, 'delete');
		self::returnJsonData($res);
		return ;
	}
	
	/*
	 *  アプリで検索されたときの情報をjsonで返す
	 *  @param q          : 検索キーワード
	 *  @param tag        : 検索タグ
	 ** tagとqがあった場合、tagでの検索が優先される
	 ** tagとqがない場合、トップに表示されている新着と同じ内容を表示
	 *　@param resultNum  : 表示件数
	 *　@param page       : ページ番号
	 * 
	 */
	function search($param)
	{
		global $loginUserType;
		global $loginUserRank;
		global $pagejumpNum;
		global $resultNum;
	
		$tmp_get = $_GET;
		$_GET = array();

		$_GET['type'] = articleLogic::$type;
		$_GET['run'] = "true";
		//	検索条件を整理
		if( isset($param['tag']) && strlen($param['tag'])>0) {
			$_GET['tag'] = $param['tag'];
			$keyword = $param['tag'];
		}
		elseif( isset($param['q']) ){
			$_GET['activate'] = @$param['q']?:"";
			$_GET['activate_PAL'] = array('group keyword name description');
			$keyword = $_GET['activate'];
		}

		$resultNum = $param['resultNum'] ? (int)$param['resultNum'] : 20;
		
		$model = new AppSearchModel();
		$model->doSearch();

		$_gm   = SystemUtil::getGMforType( $_GET['type'] );
		$db    = $model->db;
		$table = $model->table;
		$row   = $db->getRow($table);

		$recs = array();
		$recs['keyword'] = $keyword;

		if( $row > 0 )
		{
			if( $param['page']-1 >= 0)
			{
				$page = (int)$param['page']-1;
				$jsonDataPage = $param['page']; // jsonデータで返す際の値
			}
			else{
				$page = 0;
				$jsonDataPage = 1; // jsonデータで返す際の値
			}
			$model->sys->searchApi($_gm, $table, $data, $recs['total'], $recs['row'], $page);
			$max_page = $recs['total'] % $resultNum > 0 ? (int)($recs['total']/ $resultNum) + 1 : (int)($recs['total']/ $resultNum) ; 
			$recs['max_page'] = $max_page;
			$recs['page']     = $jsonDataPage;

			foreach($data as $key =>$rec)
			{
				articleLogic::formatInfoData($rec);
				$recs['data'][] = $rec;
			}
		}
		else{
			$recs['total'] = 0;
			$recs['row']   = 0;
			$recs['data']  = array();
		}
		$_GET = $tmp_get;

		echo json_encode($recs);
	}

	/*
	 *  詳細情報をjsonで返す
	 *   @param id         : 記事ID（必須）
	 *   @param getParts   : 記事に登録されているアイテムの取得フラグ
	 *    * getPartsがあった場合に有効
	 *   @param resultNum  : アイテムの表示件数
	 *   @param page       : ページ番号
	 *
	 */
	function info($param)
	{
		global $HOME;
		global $loginUserType;
		global $loginUserRank;
		global $resultNum;
		
		$tmp_get = $_GET;
		$_GET = $param;

		$_GET['type'] = articleLogic::$type;
		$_GET['id'] = $param[ 'id' ];

		$notId = array(
			'status' => 'error',
			'note' => "Not ID",
		);
		
		if( !isset($param[ 'id' ]) )
		{
			self::returnJsonData($notId);
			return;
		}

		$_gm   = SystemUtil::getGMforType( $_GET['type'] );
		$db    = $_gm->getDB();
		$table = $db->searchTable( $db->getTable(), 'id', '=', $param['id']);
		if( !$db->existsRow($table))
		{
			self::returnJsonData($notId);
			return;
		}

		$model = new AppInfoModel();
		$model->verifyViewAuthority();
		if( $model->canView() ) //閲覧できる場合
		{
			$_gm   = SystemUtil::getGMforType( $_GET['type'] );
			$db    = $model->db;
			$table = $model->table;
			$rec   = $model->rec;

			$data = $rec;
			articleLogic::formatInfoData($data);
			$data['url'] = $HOME.'info.php?type='.articleLogic::$type.'&id='.$param['id'];

			echo json_encode($data);
			
		}
		else {
			$deny = array(
				'status' => 'error',
				'note' => "Not Access",
			);
			self::returnJsonData($deny);
			return;
		}
	}

	function returnJsonData($res) {
		header('content-type: application/json; charset=utf-8');
		echo json_encode($res);
		return ;
	}
}