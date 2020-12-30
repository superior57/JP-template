<?PHP

	class article_partsLogic
	{
		private static $searcgGoogle = NULL;
		private static $searcgTwitter = NULL;
		private static $searcgPicpad = NULL;
		private static $imgExt = array('1'=>'gif','2'=>'jpg','3'=>'png', '4'=>'wbmp');


		function checkPartsData( $param , $edit=false) {
			global $loginUserType;
			global $LOGIN_ID;

			if( !isset($param['original_id']) || strlen($param['original_id']) < 1) {
				return false ;
			}

			if( !isset($param['part_type']) || strlen($param['part_type']) < 1) {
				return false ;
			}

			$db    = GMList::getDB(articleLogic::$type );
			$rec = $db->selectRecord($param['original_id']);

			if( !$loginUserType == 'admin' && $LOGIN_ID != $db->getData($rec, 'owner') )
			{
				return false;
			}

			return true;
		}

		function headSetParts( &$db, &$rec, $param, $errorfile ) {
			$check = [];
			$check['status'] = 'error';
			$oldRec = $rec;
			$_gm = GMList::getGM(articleLogic::$sub_type);
			$errMsg = "";

			if( !isset($param['body']) || strlen($param['body']) < 1 ) {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_head_body' );
			}

			if( !is_numeric($param['level']) ) {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_head_level' );
			}

			if( strlen($errMsg) ) {
				$check['status'] = 'error';
				$check['errorMsg'] = $errMsg;
			}
			else {
				$check['status'] = 'success';
				$_tmp['level'] = $param['level'];
				$body = array('body');
				foreach( $body as $col ) {
					$_tmp[$col] = GUIManager::replaceString( $param[$col] , array() , 'string' );
				}
				$db->setData($rec,'part_type', 'head');
				$db->setData($rec,'part_body', json_encode($_tmp) );
				$db->setData($rec,'original_id', $param['original_id']);
				$db->setData($rec,'edit', time());
			}

			return $check;
		}

		function textSetParts( &$db, &$rec, $param, $errorfile ) {
			$check = [];
			$check['status'] = 'error';
			$oldRec = $rec;
			$_gm = GMList::getGM(articleLogic::$sub_type);
			$errMsg = "";

			if( !isset($param['body']) || strlen($param['body']) < 1 ) {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_text_body' );
			}

			if( strlen($errMsg) ) {
				$check['status'] = 'error';
				$check['errorMsg'] = $errMsg;
			}
			else {
				$check['status'] = 'success';
				$body = array('body');
				foreach( $body as $col ) {
					$_tmp[$col] = GUIManager::replaceString( $param[$col] , array() , 'string' );
				}
				$db->setData($rec,'part_type', 'text');
				$db->setData($rec,'part_body', json_encode($_tmp) );
				$db->setData($rec,'original_id', $param['original_id']);
				$db->setData($rec,'edit', time());
			}

			return $check;
		}

		function linkSetParts( &$db, &$rec, $param, $errorfile  ) {
			global $FileBase;

			$check = [];
			$check['status'] = 'error';
			$oldRec = $rec;
			$_gm = GMList::getGM(articleLogic::$sub_type);
			$errMsg = "";

			if( !isset($param['body_url']) || strlen($param['body_url']) < 1 ) {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_link_body_url' );
			}

			if( !isset($param['body_title']) || strlen($param['body_title']) < 1 ) {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_link_body_title' );
			}

			$chk = articleLogic::HttpCheck($param['body_url']);
			if( $chk['status'] == 'error') {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_link_body_url_notaccess' );
			}

			if( !isset($param['body_img']) || strlen($param['body_img']) < 1 ) {
				$param['body_noimg'] = false;
				$_tmp['body_noimg'] = false;
			}

			if( $param['body_noimg'] == 'true' ) {
				$save = self::getUrlImage( artcleLogic::$upload_dir, $param['body_img']);
				if( isset($save['path']) && $FileBase->file_exists($param['body_img'])) {
					$_tmp['body_img'] = $save['path'];
				}
			}

			if( strlen($errMsg) ) {
				$check['status'] = 'error';
				$check['errorMsg'] = $errMsg;
			}
			else {
				$check['status'] = 'success';
				$body = array('body_url', 'body_title', 'body_comment', 'body_src', 'body_description');
				foreach( $body as $col ) {
					if( isset($param[$col]) ) {
						$_tmp[$col] = GUIManager::replaceString( $param[$col] , array() , 'string' );
					}
				}
				$db->setData($rec,'part_type', 'link');
				$db->setData($rec,'part_body', json_encode($_tmp) );
				$db->setData($rec,'original_id', $param['original_id']);
				$db->setData($rec,'edit', time());
			}
			
			return $check;
		}

		function imageSetParts( &$db, &$rec, $param, $errorfile  ) {
			global $FileBase;

			$check = [];
			$check['status'] = 'error';
			$oldRec = $rec;
			$_gm = GMList::getGM(articleLogic::$sub_type);
			$errMsg = "";

			$file = self::uploadComp($param['original_id'], 'body_image');
			if( !$FileBase->file_exists($file) )
			{
				if( strlen($param['body_url'])>0) {
					$data = self::getUrlImage(articleLogic::$upload_dir.$param['original_id'], $param['body_url']);
					if( isset($data['path']) ){ $file = $data['path']; }
				}
				else if( isset( $param['body_image_tmp']) && $FileBase->file_exists($param['body_image_tmp'])) {
					$file = $param['body_image_tmp'];
				}
			}

			if( strlen($file) && $FileBase->file_exists($file)) {
				$_tmp['body_image'] = $file;
			}
			else {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_image_body_image' );
			}

			if( isset($param['body_src_link']) && strlen($param['body_src_link'])) {
				$chk = articleLogic::HttpCheck($param['body_src_link']);
				if( $chk['status'] != 'success') {
					$errMsg = $_gm->partGetString( $errorfile , 'parts_image_body_src_link' );
				}
			}

			if( strlen($errMsg) ) {
				$check['status'] = 'error';
				$check['errorMsg'] = $errMsg;
			}
			else {

				$check['status'] = 'success';
				$body = array('body_title', 'body_alt', 'body_comment', 'body_src', 'body_src_link');
				foreach( $body as $col ) {
					if( isset($param[$col]) ) {
						$_tmp[$col] = GUIManager::replaceString( $param[$col] , array() , 'string' );
					}
				}
				$db->setData($rec,'part_type', 'image');
				$db->setData($rec,'part_body', json_encode($_tmp) );
				$db->setData($rec,'original_id', $param['original_id']);
				$db->setData($rec,'edit', time());
			}

			return $check;
		}

		function moveSetParts( &$db, &$rec, $param, $errorfile  ) {
			$check = [];
			$check['status'] = 'error';
			$oldRec = $rec;
			$_gm = GMList::getGM(articleLogic::$sub_type);
			$errMsg = "";

			$_tmp['move_type'] = "";
			if( isset($param['body_url']) && strlen($param['body_url']) > 1 ) {
				$urls = parse_url($param['body_url']);
				if( $urls != false ) {
					switch ( $urls['host'] ) {
						case 'm.youtube.com':
							$_tmp['move_code'] = str_replace('/', '', $urls['path'] );
							$_tmp['move_type'] = 'youtubu';
							break;
						case 'youtu.be':
						case 'www.youtube.com':
							$_tmp['move_code'] = str_replace('v=', '', $urls['query'] );
							$_tmp['move_type'] = 'youtubu';
							break;
					}
				}
			}

			if( $_tmp['move_type'] == "" ) {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_move_move_type' );
			}

			if( !isset($param['body_title']) || strlen($param['body_title']) < 1 ) {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_move_body_title' );
			}

			if( strlen($errMsg) ) {
				$check['status'] = 'error';
				$check['errorMsg'] = $errMsg;
			}
			else {
				$check['status'] = 'success';
				$body = array('body_url', 'body_title', 'body_comment', 'move_code');
				foreach( $body as $col ) {
					if( isset($param[$col]) ) {
						$_tmp[$col] = GUIManager::replaceString( $param[$col] , array() , 'string' );
					}
				}
				$db->setData($rec,'part_type', 'move');
				$db->setData($rec,'part_body', json_encode($_tmp) );
				$db->setData($rec,'original_id', $param['original_id']);
				$db->setData($rec,'edit', time());
			}

			return $check;
		}

		// 未実装
		function goodsSetParts( &$db, &$rec, $param, $errorfile  ) {
			global $FileBase;

			$check = [];
			$check['status'] = 'error';
			$oldRec = $rec;
			$_gm = GMList::getGM(articleLogic::$sub_type);
			$errMsg = "";

			if( strlen($param['body_img_url'])>0) {
				$data = self::getUrlImage(articleLogic::$upload_dir.$param['original_id'], $param['body_img_url']);
				if( isset($data['path']) ){ $file = $data['path']; }
			}
			else if( isset( $param['body_image_tmp']) && $FileBase->file_exists($param['body_image_tmp'])) {
				$file = $param['body_image_tmp'];
			}

			if( !strlen($param['body_title']) ) {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_goods_title' );
			}

			if( strlen($errMsg) ) {
				$check['status'] = 'error';
				$check['errorMsg'] = $errMsg;
			}
			else {
				$check['status'] = 'success';
				$_tmp['level'] = $param['head_level'];
				$body = array('body');
				foreach( $body as $col ) {
					$_tmp[$col] = GUIManager::replaceString( $param[$col] , array() , 'string' );
				}
				$db->setData($rec,'part_type', 'goods');
				$db->setData($rec,'part_body', json_encode($_tmp) );
				$db->setData($rec,'original_id', $param['original_id']);
				$db->setData($rec,'edit', time());
			}

			return $check;
		}

		function tweetSetParts( &$db, &$rec, $param, $errorfile  ) {
			$check = [];
			$check['status'] = 'error';
			$oldRec = $rec;
			$_gm = GMList::getGM(articleLogic::$sub_type);
			$errMsg = "";

			if( !isset($param['body_url']) || strlen($param['body_url']) < 1 ) {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_tweet_body_url' );
			}
			else {
				$twitter_api = "https://api.twitter.com/1/statuses/oembed.json?lang=ja&hide_thread=true&url=".rawurlencode($param['body_url']);

				$tweet = file_get_contents($twitter_api,false, articleLogic::sslConTextSetting($twitter_api));

				$res = strpos($http_response_header[0], '200');
				if ($res === false) {
					$errMsg = $_gm->partGetString( $errorfile , 'parts_tweet_api_error' );
				}
				else {
					$tweetResult = json_decode($tweet, true);
					if(isset($tweetResult['errors'])){
						$errMsg = $_gm->partGetString( $errorfile , 'parts_tweet_body_url_noaccess' );
					}
					else {
						$_tmp['body_tweet'] = $tweetResult['html'];
						$_tmp['body_data'] = $tweetResult;
					}
				}
			}

			if( strlen($errMsg) ) {
				$check['status'] = 'error';
				$check['errorMsg'] = $errMsg;
			}
			else {
				$check['status'] = 'success';
				$db->setData($rec,'part_type', 'tweet');
				$db->setData($rec,'part_body', json_encode($_tmp) );
				$db->setData($rec,'original_id', $param['original_id']);
				$db->setData($rec,'edit', time());
			}

			return $check;
		}

		function quoteSetParts( &$db, &$rec, $param, $errorfile  ) {
			$check = [];
			$check['status'] = 'error';
			$oldRec = $rec;
			$_gm = GMList::getGM(articleLogic::$sub_type);
			$errMsg = "";

			if( !isset($param['body_url']) || strlen($param['body_url']) < 1 ) {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_quote_body_url' );
			}

			if( !isset($param['body_title']) || strlen($param['body_title']) < 1 ) {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_quote_body_title' );
			}

			if( !isset($param['body_quote']) || strlen($param['body_quote']) < 1 ) {
				$errMsg = $_gm->partGetString( $errorfile , 'parts_quote_body_quote' );
			}

			if( strlen($errMsg) ) {
				$check['status'] = 'error';
				$check['errorMsg'] = $errMsg;
			}
			else {
				$check['status'] = 'success';
				$body = array('body_url', 'body_title', 'body_comment', 'body_quote' );
				foreach( $body as $col ) {
					if( isset($param[$col]) ) {
						$_tmp[$col] = GUIManager::replaceString( $param[$col] , array() , 'string' );
					}
				}
				$db->setData($rec,'part_type', 'quote');
				$db->setData($rec,'part_body', json_encode($_tmp) );
				$db->setData($rec,'original_id', $param['original_id']);
				$db->setData($rec,'edit', time());
			}
			return $check;
		}

		function htmlSetParts( &$db, &$rec, $param, $errorfile ) {
			global $loginUserType;

			$check = [];
			$check['status'] = 'error';
			$_gm = GMList::getGM(articleLogic::$sub_type);
			$errMsg = "";

			if( !isset($param['body_html']) || strlen($param['body_html']) < 1 ) {
				$errMsg .= $_gm->partGetString( $errorfile , 'parts_html_body_html' );
			}

			if( $loginUserType != 'admin' ) {
				$errMsg .= $_gm->partGetString( $errorfile , 'parts_html_error' );
			}

			if( strlen($errMsg) ) {
				$check['status'] = 'error';
				$check['errorMsg'] = $errMsg;
			}
			else {
				$check['status'] = 'success';
				$_tmp['body_html'] = $param['body_html'];
				$db->setData($rec,'part_type', 'html');
				$db->setData($rec,'part_body', json_encode($_tmp) );
				$db->setData($rec,'original_id', $param['original_id']);
				$db->setData($rec,'edit', time());
			}

			return $check;
		}

		function CheckParts( $param ) {
			$check = [];
			$check['status'] = 'error';
			return $check;
		}

		function draw( $data , $template, $edit=false) {
			$gm = GMList::getGM( articleLogic::$sub_type );
			$db = $gm->getDB();
			if(isset( $data['rec'])) {
				$rec = $data['rec'];
			}
			else if( isset( $data['id']) ) {
				$rec = $db->selectRecord($data['id']);
			}

			if( isset($rec) ) {
				$body = json_decode($db->getData( $rec, 'part_body'), true);
				if( !$body ) { return ""; }

				$gm->clearVariable();
				foreach( $body as $key => $val) {
					$gm->setVariable($key, $val);
				}
				$brCol = array('body', 'body_comment', 'body_description', 'body_quote');
				foreach( $brCol as $val ) {
					$txt = "";
					if( isset( $body[$val]) ) { $txt = brChange($body[$val]); }
					$gm->setVariable('view_'.$val, $txt);
				}
				if( isset( $body['body_html']) ) { $gm->setVariable( "encode_body_html", rawurlencode( $body['body_html'] ) ); }

				$type = $db->getData( $rec, 'part_type').'_part';
				$html = $gm->getString( $template, $rec, 'part_start');
				if( $edit ) { 
					$html .= $gm->getString( $template, $rec, "view_head");
				}
				$html .= $gm->getString( $template, $rec, $type);
				if( $edit ) { 
					$html .= $gm->getString( $template, $rec, "view_foot");
					$html .= $gm->getString( $template, $rec, $type."_edit");
				}
				$html .= $gm->getString( $template, $rec, 'part_end');
				return $html;
			}
		}

		/**
		 * IDを元にレコード群を削除
		 *
		 * @param $original_id 親ID。
		 */
		function delete( $iOriginalId )
		{
			$db = GMList::getDB(articleLogic::$sub_type);

			$table = $db->getTable();
			$table = $db->searchTable( $table, 'original_id', '=', $iOriginalId );

			$db->deleteTable($table);
		}

		/**
		 * ユーザが削除されたときのPartsを削除
		 * 
		 * @param userDelete
		 */
		function userDelete(&$gm, &$loginUserType,$LOGIN_ID)
		{
			$subDb = $gm[articleLogic::$type]->getDB();

			if( $loginUserType == 'admin' ){
				$table = $subDb->searchTable(  $subDb->getTable(), 'owner', '=', $_GET['id']  );
			}else{
				$table = $subDb->searchTable(  $subDb->getTable(), 'owner', '=', $LOGIN_ID  );
			}

			$row = $subDb->getRow($table);
			$dataList = array();
			for($i=0; $i<$row; $i++){
				$subRec = $subDb->getRecord( $table, $i );
				$dataList[] = $subDb->getData( $subRec, 'id');
			}

			if(count($dataList) > 0)
			{
				// データの削除
				$db = $gm[articleLogic::$sub_type]->getDB();
				$table = $db->searchTable(  $db->getTable(), 'original_id', 'in', $dataList );
				if( $db->existsRow($table) ){
					$db->deleteTable($table);
				}
				
				// アップロードファイルの削除
				foreach($dataList as $val)
				{
					$directory = articleLogic::$upload_dir.$val;
				}
			}
		}

		/*
		 *  partの表示する内容とページャのソースを取得
		 */
		function getDrawParts( &$gm, $loginUserType, $loginUserRank, $original_id, $partsList, $resultNum, $pagejumpNum, $mode="")
		{
			global $NOT_LOGIN_USER_TYPE;

			if(!is_array($partsList) ) {
				$partsList = explode('/', $partsList);
			}
			$row = count($partsList);

			$design = Template::getTemplate( $loginUserType, $loginUserRank, articleLogic::$type, 'PARTS_DESIGN' );
			$jump_page = explode('?',end(explode("/",$_SERVER['SCRIPT_NAME'])));

			$_db    = GMList::getDB(articleLogic::$sub_type);

			if( ! isset( $_GET['page'] ) ){ $page = 0; }
			else                          { $page = $_GET['page']; }
			if(  $page == 0 ){ $page = 0; }
			else if( 0 < $page )
			{
				$beginRow = $page * $resultNum;
				$tableRow = $row;
				if( $tableRow <= $beginRow )
				{
					$maxPage = ( int )( ( $tableRow - 1 ) / $resultNum );
					$page = $maxPage;
				}
			}else if(  $page < 0 ) { $page = 0; }
			

			$parts['RES_ROW'] = $row;
			$parts['VIEW_BEGIN'] = $page * $resultNum + 1;
			if( $row >= $page * $resultNum + $resultNum )
			{
				$parts['VIEW_END'] = $page * $resultNum + $resultNum;
				$parts['VIEW_ROW'] = $resultNum;
				$view_cnt = $page * $resultNum + $resultNum;
			}
			else
			{
				$parts['VIEW_END'] = $row;
				$parts['VIEW_ROW'] = $row % $resultNum;
				$view_cnt = $row;
			}
			$parts['VIEW_CNT'] = $view_cnt;

			foreach( $parts as $key => $val) {
				$gm->setVariable($key, $val);
			}

			$start = $page * $resultNum;
			for( $i=$start; $i<$view_cnt;$i++) {
				$db = GMList::getDB(articleLogic::$sub_type);
				$_tmp['rec'] = $db->selectRecord($partsList[$i]);
				$parts['list'] .= self::draw( $_tmp , $design, $mode);
				unset($_tmp);
			}

			// partのページャ
			$parts['pager'] = articleLogic::getSearchPageChange( $gm, $NOT_LOGIN_USER_TYPE, $loginUserRank, $row, $pagejumpNum, $resultNum, $jump_page[0], 'page' ) ;

			return $parts;
		}

		/**
		 * ファイルアップロードの完了処理。
		 */
		function uploadComp( $id, $name )
		{
			global $FileBase;

			$directory	 = articleLogic::$upload_dir.$id.'/';
			if(!is_dir($directory)) { mkdir( $directory, 0777, true ); chmod( $directory, 0777 );} //ディレクトリが存在しない場合は作成
			$upfile_path = "";

			if( isset($_FILES[$name]['name']) ) {
				$before = $_FILES[$name]['tmp_name'];
				$after = $_FILES[$name]['name'];

				if( file_exists($before) )
				{ // アップロードされたファイルが画像でない場合削除
					$size = getimagesize($before);
					if( $size === false || count($size) < 1)
					{ // 画像情報がない場合
						unlink($before);
						$before = '';
					}
				}

				if( $before != "" && $after != "" )
				{// ファイルのアップロードが行われていた場合データを差し替える。
					// 拡張子の取得
					preg_match( '/(\.\w*$)/', $after, $tmp );
					$tmp1 = explode(".", basename($before));
					$ext		= strtolower(str_replace( ".", "", $tmp[1] ));
					$file_name	= hash('md5',$tmp1[0]).".".$ext;
					// ファイルを保存
					$FileBase->upload($before, $directory.$file_name);
					$upfile_path = $directory.$file_name;
				}
			}
			return $upfile_path;
		}

		function getPartsImage($iArec)
		{
			$db = GMList::getDB(articleLogic::$type);
			$partsList = $db->getData($iArec, 'parts');

			$subDb = GMList::getDB(articleLogic::$sub_type);
			$subTable = $subDb->getTable();
			$subTable = $subDb->searchTable($subTable, 'part_type', 'like', 'image');
			$subTable = $subDb->searchTable($subTable, 'id', 'in', $partsList);
			$pRec = $subDb->getFirstRecord($subTable);
			$body = json_decode($subDb->getData($pRec, 'part_body'), true);

			if( isset($body['body_image']) && strlen($body['body_image']) ){
				return $body['body_image'];
			}
			return "";
		}


		/* 
		 *  リンクアイテムのキーワード検索が可能か確認する
		 */
		function checkLinkKeywordSearch()
		{
			$check = self::checkGoogleSearch();
			if(!$check) { return false; }
			else        { return true; }
		}
		
		/* 
		 *  リンクアイテムのキーワード検索が可能か確認する
		 */
		function checkImageKeywordSearch($type=null)
		{
			if($type != null)
			{
				// 指定のものが設定されているか確認
				$check = false;
				switch ($type)
				{
					case 'google';
						$check = self::checkGoogleSearch();
						break;
					case 'picpad';
						$check = self::checkPicPadSearch();
				}
				
				if(!$check)
				{
					return false;
				}else{
					return true;
				}
			}else{
				// ひとつでも設定されている場合、trueを返す。
				$check = self::checkGoogleSearch();
				if($check) { return true; }

				$check = self::checkPicPadSearch();
				if($check) { return true; }

				return false;
			}
		}


		/*
		 * Googelのキーワード検索の設定ができている確認する
		 * （リンクアイテムと画像アイテムのキーワード検索で利用）
		 */
		function checkGoogleSearch()
		{
			if( self::$searcgGoogle != NULL) { return self::$searcgGoogle; }

			$google['google_api_key'] = SystemUtil::getSystemData('google_api_key');
			$google['google_custom_id'] = SystemUtil::getSystemData('google_custom_id');

			if(strlen($google['google_api_key']) < 1 || strlen($google['google_custom_id']) < 1)
			{ self::$searcgGoogle = false ; }
			else 
			{ self::$searcgGoogle = true ; }
			return self::$searcgGoogle;
		}

		/*
		 * Twiiterのキーワード検索の設定ができている確認
		 * （Twitterアイテムで利用）
		 */
		function checkTwitterSearch()
		{
			if( self::$searcgTwitter != NULL) { return self::$searcgTwitter; }
			$twitter['tw_consumer_key'] = SystemUtil::getSystemData('tw_consumer_key');
			$twitter['tw_consumer_secret'] = SystemUtil::getSystemData('tw_consumer_secret');

			if(strlen($twitter['tw_consumer_key']) < 1 || strlen($twitter['tw_consumer_secret']) < 1)
			{ self::$searcgTwitter = false ; }
			else 
			{ self::$searcgTwitter = true ; }
			return self::$searcgTwitter;
		}

		/*
		 * PicPadの設定ができている確認
		 * （画像アイテムで利用）
		 */
		function checkPicPadSearch()
		{
			global $PicPadUrl;
			if( self::$searcgPicpad != NULL) { return self::$searcgPicpad; }

			self::$searcgPicpad = false;
			
			if( is_array($PicPadUrl) && count($PicPadUrl) > 0 )
			{
				foreach($PicPadUrl as $key => $urls)
				{ if( strlen(trim($urls['site_url']))>0 && strlen(trim($urls['site_name']))>0) { self::$searcgPicpad = true ; break; } }
			}
			return self::$searcgPicpad;
		}
		
		/*
		 * 画像アイテムでURLを指定されているものをダウンロードする
		 * 　ファイルアップロードの完了処理（function uploadComp）で使用することを想定。
		 * $items_id itemsのid
		 * $directory 保存するディレクトリ
		 */
		function getUrlImage( $iDirectory, $iImagSrc )
		{
			global $FileBase;

			set_time_limit(30);
			$data = [];
			$datascheme = articleLogic::saveDataScheme($iImagSrc,$iDirectory);
			if( $datascheme )
			{ // dataストリーム形式の画像が保存できた場合
					$FileBase->upload($datascheme, $datascheme);
					$data['path'] = $datascheme;
					$data['url'] = $iImagSrc;
			}
			else {
				if( !preg_match('/(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)/', $iImagSrc) )
				{ // URLでない場合
					$data['url'] = '';
//					break ;
				}
				elseif( preg_match( '/file\/parts\/tmp\//', $iImgSrc) )
				{ // 一時フォルダにあるものを移動
					$oidfile = $iImgSrc;
					$newfile = str_replace('file/parts/tmp/', $iDirectory, $iImgSrc);
					$FileBase->upload($oidfile,$newfile);
					$data['path'] = $newfile;
				}
				else {
					$imgSrc = articleLogic::changeWebp($iImagSrc);

					$size = @getimagesize($imgSrc);
					if( $size === false || count($size) < 1)
					{ // 画像情報がない場合
						$data['url'] = '';
					}

					$filePath = pathinfo($iImagSrc);
					$imgData = file_get_contents($iImagSrc,false, articleLogic::sslConTextSetting($iImagSrc));
					if(strlen($imgData) < 1 )
					{
						$data['path'] = '';
					}
					$ext = "";
					if(isset($size[2]) && isset($imgExt[$size[2]])) $ext = ".".self::$imgExt[$size[2]];
					$saveFile = $iDirectory.'/'.hash('md5',$filePath['filename'] . microtime()).$ext;
					if( file_put_contents( $saveFile,$imgData) )
					{
						$FileBase->upload($saveFile, $saveFile);
						$data['path'] = $saveFile;
					}
				}
			}
			return $data;
		}

		/*
		 * 記事に登録されている最初の画像パスを取得
		 */
		function getImage($item_id)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $IMAGE_NOT_FOUND_SRC;
			// **************************************************************************************

			$items_img  ="";

			// 画像の登録がない場合、partsから取得
			$subDb = GMList::getDB('parts');
			$subTable = $subDb->getTable();
			$subTable = $subDb->searchTable($subTable, '', '', '' );
			$subTable = $subDb->searchTable($subTable, 'item_id', 'like', $item_id );

			// 1件取得
			if( $subDb->existsRow($subTable) ){
				$subRec = $subDb->getRecord($subTable,0);
				$items_img = $subDb->getData( $subRec, 'part_image');
			}
			return $items_img;
		}

		/*
		 * データ肥大化対応用にparts_deleteのレコードを削除
		 */
		function deleteAll()
		{
			global $SQL_MASTER;
			$db = GMList::getDB( 'parts' );
			if($SQL_MASTER=='SQLiteDatabase') { $sql = 'DELETE FROM parts_delete;'; }
			else                              { $sql = 'TRUNCATE parts_delete;'; }
			$result = $db->sql_query( $sql );
		}
		
		function formatInfodata(&$rec)
		{
			global $FileBase;
			global $IMAGE_NOT_FOUND_SRC;

			if(isset($rec['shadow_id']))         { unset($rec['shadow_id']); }
			if(isset($rec['delete_key']))        { unset($rec['delete_key']); }
			if(isset($rec['view_part_title']))   { unset($rec['view_part_title']); }
			if(isset($rec['view_part_comment'])) { unset($rec['view_part_comment']); }

			$noimage = SystemUtil::getImageURL($IMAGE_NOT_FOUND_SRC);
			if(strlen($rec['part_image']) > 0) { $rec['part_image'] = SystemUtil::getImageURL($FileBase->geturl($rec['part_image'])); }
			else                               { $rec['part_image'] = $noimage; }
		}
	}

?>