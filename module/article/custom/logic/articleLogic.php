<?PHP

class articleLogic
{
	public static $type          = 'article';
	public static $sub_type      = 'article_parts';
	public static $category_type = 'article_category';
	public static $relationship_type = 'article_relationship';
	public static $tag_type      = 'article_tag';
	public static $upload_dir    = 'file/article/';

	function getSearchPageChange( &$gm, $loginUserType, $loginUserRank, $row, $pagejumpNum, $resultNum, $phpName, $param )
	{
		$design = Template::getTemplate( $loginUserType , $loginUserRank , self::$type , 'SEARCH_PAGE_CHANGE_DESIGN' );
		if ( !strlen($design) || !file_exists($design) )
		{ $design = Template::getTemplate( $loginUserType , $loginUserRank , '' , 'SEARCH_PAGE_CHANGE_DESIGN' ); }

		$html = SystemUtil::getPager( $gm, $design, $_GET, $row, $pagejumpNum, $resultNum, $phpName, $param , SearchTableStack::getPartsName('change'), "/" );
		return $html;
	}

	/*
	 * 公開条件の取得
	 */
	function getOpenConditionTable($table=null)
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $ACTIVE_ACCEPT;
		// **************************************************************************************

		$db = GMList::getDB(self::$type);
		if( !isset($table) ) { $table = $db->getTable(); }
		$table = $db->searchTable( $table , 'open' , '<' , time() );
		$table = $db->searchTable( $table , 'activate' , '=' , $ACTIVE_ACCEPT );
		$table = $db->sortTable( $table , 'open' , 'desc' );
		return $table;
	}


	/*
	 * 公開アイテムか確認
	 */
	function checkOpen( $db, $rec )
	{
		global $ACTIVE_ACCEPT;

		$result = true;
		if( $db->getData( $rec , 'activate' ) != $ACTIVE_ACCEPT )
		{ $result = false; }
		if( $db->getData( $rec , 'open' ) > time() ){ $result = false; }

		return $result;
	}

	/*
	 * 作成者の退会フラグを変更する
	 */
	function changeOwnerWithdrawal($owner, $val=null)
	{
		$column = 'owner_withdrawal';
		if(is_null($val)) return ;
		if(strlen($owner) == 0) return ;

		$db = GMList::getDB(articleLogic::$type);
		$table = $db->getTable();
		$table = $db->searchTable($table,"owner","like",$owner);
		$db->setTableDataUpdate($table, $column, $val);
	}

	/*
	 * 詳細ページのメタタグの設定
	 */
	function setRobots($gm, $max=0, $row=0)
	{

		if(!isset($_GET['page'])) $page = 0;
		else                      $page = $_GET['page'];

		$uri = preg_replace(  '/\/$/', '', preg_replace( '/&page=(\d*)/', '', $_SERVER[ 'REQUEST_URI' ]) );
		$uri = ( 'on' == $_SERVER[ 'HTTPS' ] ? 'https://' : 'http://' ) . $_SERVER[ 'HTTP_HOST' ] . $uri;
		$uri = h( $uri , ENT_QUOTES | ENT_HTML401 );
		$gm->setVariable('index_page', $uri);

		$buffer = "";
		$cc = array('include', 'metaList');
		if($page == 0)
		{
			$cc = array('include', 'metaList', 'rel_canonical');
			$buffer .= $gm->ccProc( $gm, null, $cc );
			$cc = array('include', 'metaList', 'rel_next');
			$buffer .= $gm->ccProc( $gm, null, $cc );
		}
		else if($page*$max+$max >= $row)
		{
			$cc[] = 'rel_prev';
			$buffer .= $gm->ccProc( $gm, null, $cc );
		}
		else
		{
			$cc[] = 'rel_next_prev';
			$buffer .= $gm->ccProc( $gm, null, $cc );
		}

		if(strlen($buffer) > 0)
		{// 書き換え
			$output = ob_get_clean();
			$output = preg_replace( '/<meta name="robots" content="(.*)" \/>/', '<meta name="robots" content="ALL" />'.$buffer, $output);
			ob_start();
			print $output;
		}
	}

	/**
	 * 検索したデータを配列で取得
	 *
	 * @param table テーブルデータ
	 * @return 配列
	 */
	function getDataList( $table )
	{
		$db = GMList::getDB('items');

		$row = $db->getRow($table);
		$dataList = array();
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );
			self::formatInfoData($rec);
			$dataList[] = $rec;
		}
		return $dataList;
	}

	/*
	 *  APIで返す記事データで不要なものを削除
	 */
	function formatInfoData( &$rec )
	{
		global $FileBase;
		global $IMAGE_NOT_FOUND_SRC;

		if(isset($rec['shadow_id']))        { unset($rec['shadow_id']); }
		if(isset($rec['delete_key']))       { unset($rec['delete_key']); }
		if(isset($rec['url_key']))          { unset($rec['url_key']); }
		if(isset($rec['items_form']))       { unset($rec['items_form']); }
		if(isset($rec['activate']))         { unset($rec['activate']); }
		if(isset($rec['owner_withdrawal'])) { unset($rec['owner_withdrawal']); }

		$noimage = SystemUtil::getImageURL($IMAGE_NOT_FOUND_SRC);
		if(strlen($rec['image']) > 0) { $rec['image'] = SystemUtil::getImageURL($FileBase->geturl($rec['image'])); }
		else                          { $rec['image'] = $noimage; }

		if(isset($rec['owner']) &&  strlen($rec['owner']) > 0)
		{ // ユーザ名の取得
			if( $rec['owner'] == 'admin') {
				$rec['owner_name'] = 'システム管理者';
			}
			else
			{
				$_gm = SystemUtil::getGMforType( $rec['owner_type'] );
				$uDb = $_gm->getDB();
				$uRec = $uDb->selectRecord($rec['owner']);
				if( !is_null($uRec))
				{ $rec['owner_name'] = $uDb->getData($uRec,"name"); }
			}
		}

		if(isset($rec['category']) )
		{ // カテゴリー
			$rec['category_name'] = "";
			if( strlen($rec['category']) > 0)
			{
				$_gm = SystemUtil::getGMforType( 'category' );
				$uDb = $_gm->getDB();
				$uRec = $uDb->selectRecord($rec['category']);
				if( !is_null($uRec))
				{ $rec['category_name'] = $uDb->getData($uRec,"name"); }
			}
		}
	}

	function setSearchWord( $iId, $iPartsRec ){
		$db = GMList::getDB(self::$type);
		$rec = $db->selectRecord($iId);
		$search = $db->getData($rec, 'search');

		$pdb = GMList::getDB(self::$sub_type);
		$ptype = $pdb->getData($iPartsRec, 'part_type');
		$method = $ptype.'SearchWord';
		if( !function_exists($method) ) { $method = 'SearchWord'; }
		$new = articleLogic::{$method}($search, $iPartsRec);


		if( $new != $search ) {

			$db->setData($rec, 'search', $new);
			$db->updateRecord($rec);
		}
	}

	/**
	 * 検索用のカラムの設定
	 * @param $iSearchWord 検索文字。
	 * @param $iPrec 新しいレコード
	 */
	function SearchWord($iSearchWord, $iPrec ) {
		$db = GMList::getDB(articleLogic::$sub_type);
		$pid = $db->getData($iPrec,'id');
		$target = '/\{\['.$pid.':.*\]\}/';

		$new = "";
		$body = json_decode( $db->getData($iPrec,'part_body'), true);
		if( $body ) {
			$arr = array( 'body', 'body_title', 'body_comment', 'body_description', 'body_quote' );
			foreach( $arr as $key => $val ) {
				if( isset($body[$val]) )
				{ $new .= GUIManager::replaceString( $body[$val] , array() , 'string' ); }
			}
		}
		if( strlen($new)>0 ){ $new = '{['.$pid.':'.$new.']}'; }

		if( preg_match($target, $iSearchWord) )
		{
			$change = preg_replace($target, $new, $iSearchWord);
		}
		else {
			$change = $iSearchWord.$new;
		}
		return $change;
	}

	function saveDataScheme($url,$directory)
	{
		if( !preg_match('/^data:image.*;base64,/', $url) ) { return false ; }

		$i_data = @getimagesize($url);
		$b_data = base64_decode(preg_replace('/^data:image.*;base64,/', '',$url));
		if( preg_match('/^image\//', $i_data['mime'] ) )
		{
		    $ext = preg_replace('/^image\//', '', $i_data['mime'] );
		    $save_file = $directory.hash('md5',microtime()).".".$ext;
		    file_put_contents($save_file, $b_data);
			return $save_file;
		}
		return false;
	}

	function changeWebp($url_img)
	{
		// webpをサポートしていない環境があるため、URLの確認
		if( preg_match('/\.webp$/', $url_img ) )
		{ // 拡張子ありは拡張子を削除
			$tmp = preg_replace('/\.webp$/', '', $url_img);
		}else if(preg_match('/\-rw$/', $url_img ))
		{
			$tmp = preg_replace('/\-rw$/', '', $url_img);
		}

		return $tmp;
	}

	function changeJapaneseDomain($url)
	{
		global $IDNA2;
		if( !preg_match('/^http(s|):\/\/xn--/',$url)) {
			$urls = parse_url($url);
			$ch_domain = $IDNA2->encode($urls["host"]);
			return str_replace($urls["host"], $ch_domain, $url);
		}
		return $url;
	}

	function HttpCheck($url){
		global $USER_AGENT;
		global $OUTPUT_CHARACODE;

		if( strlen($url) < 1 ){
			$data['status'] = 'error';
			$data['Code'] = '';
			return $data;
		}

		// HttpUtilクラス(fsockopen)の場合、画像のURLが400になるため、独自
		$data = [];
		$url = self::changeJapaneseDomain($url);

		// curlの方が詳細を得られるので、書き直し水準
		ini_set('user_agent', $USER_AGENT);
		$response = @file_get_contents($url,false, self::sslConTextSetting($url));
		if(isset($http_response_header[0]))
		{
			$temp_status = explode(" ", $http_response_header[0]);
			$statusCode = $temp_status[1];
			if ($statusCode == 200 || ( 300 <= $statusCode && $statusCode <= 304)) {
				$data['status'] = 'success';
				$data['Code'] = $statusCode;
				$from = mb_detect_encoding($response, array('UTF-8', 'sjis-win', 'sjis', 'eucjp-win', 'eucjp', 'ASCII', 'JIS'));
				if($from !== FALSE){
					$data['src'] = mb_convert_encoding($response, $OUTPUT_CHARACODE, $from);
				}
			}else{
				$data['status'] = 'error';
				$data['Code'] = $statusCode;
			}
		}
		else {
			$data['status'] = 'error';
		}
		return $data;
	}

	function checkUrlImg($url_img)
	{
		$url = self::changeJapaneseDomain($url_img);

		global $USER_AGENT;
		ini_set('user_agent', $USER_AGENT);
		list($img_width, $img_height, $mime_type, $attr) = @getimagesize($url);
		//list関数の第3引数にはgetimagesize関数で取得した画像のMIMEタイプが格納されているので条件分岐で拡張子を決定する
		switch($mime_type){
			case IMAGETYPE_JPEG:
			case IMAGETYPE_PNG:
			case IMAGETYPE_GIF:
			case IMAGETYPE_BMP:
			case IMAGETYPE_WBMP:
				return true;
			default :
				return false;
		}
	}

	function sslConTextSetting($url,$ignore_erros = true)
	{
		global $caFile;
		global $caFileUse;

		$arrContext = stream_context_create();
		$arrContextOptions = array(
			'http' => array(
				'ignore_errors' =>  true
			)
		);
		stream_context_set_option($arrContext, $arrContextOptions );

		if(preg_match('/^https:/', $url))
		{ // SSL認証回避
			$arrContextSSLOptions=array(
				"ssl"=>array(
					"verify_peer"=>false,
					"verify_peer_name"=>false,
				),
			);

			stream_context_set_option( $arrContext, $arrContextSSLOptions);
		}
		return $arrContext;
	}

	/*
	 *  タグやカテゴリーで記事を検索する条件
	 *  @param $aRelationWord 検索する文字を配列で取得
	 *  @param $aSearchType word/id
	 */
	function setRelationSearchTable( $aTable, $aRelationWord,$aSearchType='id' )
	{
		$relation = array( 'tag', 'category' );

		$or = [];
		foreach( $relation as $relation_type ) {
			if( isset($aRelationWord[$relation_type]) ) {
				$_word = (array)$aRelationWord[$relation_type];
				foreach( $_word as $word) {
					if( strlen($word) ) {
						$table = self::relationSearch( $word, $relation_type, $aSearchType );
						if( $table ) {
							$or[] = $table;
						}
					}
				}
			}
		}

		$db = GMList::getDB(self::$type);
		if( count($or)>0) { // 各関係条件の検索
			$relation = $db->orTableM($or);
			$table = $db->andTable($aTable, $db->searchTableSubQuery( $db->getTable(), 'id', 'in', $relation) );
		}
		else {
			$table = $aTable;
		}
		return $table;
	}

	function relationSearch( $aWord, $aRelationType, $aSearchType )
	{
		if( $aSearchType == 'word' ) {
			$type = self::$type."_".$aRelationType;
			$db = GMList::getDB($type);
			$table = $db->getTable();
			$table = $db->searchTable($table, 'name', 'like', $aWord);
			$rec = $db->getFirstRecord($table);
			$id = $db->getData( $rec, 'id');
		}
		else {
			$id = $aWord;
		}

		if( strlen($id) ) {
			$RelationDb = GMList::getDB(self::$relationship_type);
			$RelationTable = $RelationDb->getTable();
			$RelationTable = $RelationDb->getColumn( 'original_id', $RelationTable);
			$RelationTable = $RelationDb->searchTable($RelationTable, 'relationship_type', 'like', $aRelationType);
			$RelationTable = $RelationDb->searchTable($RelationTable, 'relationship_id', 'like', $id);
			return $RelationTable;
		}
		return $RelationDb->getEmptyTable();
	}

	function ccEscape( $text ) { return str_replace( " ", "\ ", str_replace(  "/", "\/", $text ) ); }
}
?>