<?PHP

class JobView extends command_base
{

	function drawJobType(&$gm,$rec,$args){
		List($jobID) = $args;
		$type = SystemUtil::getJobType($jobID);
		$this->addBuffer($type);
	}

	function getJobData(&$gm,$rec,$args){
		List($jobID,$column) = $args;
		$type = SystemUtil::getJobType($jobID);

		if(is_null($type)) return false;

		$db = GMList::getDB($type);
		$rec = $db->selectRecord($jobID);

		$this->addBuffer($db->getData($rec,$column));
	}

	/*
	応募可能な場合trueを返す
	*/
	function checkApply(&$gm,$rec,$args)
	{
		$db = $gm->getDB();

		$this->addBuffer( JobLogic::checkApply($_GET["type"],$db->getData( $rec, 'id' )) );
	}


	/**
	 * 管理者の承認が必要かどうかチェックする
	 *
	 * @param mode regist/edit
	 */
	function drawAdminActivateCheck( &$gm, $rec, $args )
	{
		$mode = $args[0];
		$this->addBuffer( Conf::checkData( 'job', 'ad_check', $mode ) );
	}


	/**
	 * 求人詳細でページビューを表示
	 *
	 * @param id ページビューを表示する求人ID
	 */
	function drawPV( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		// **************************************************************************************

		$id = $args[0];

		$pv = Conf::getData( 'job', 'pv' );

		$draw = false;
		switch($pv)
		{
		case 'admin': $draw = ($loginUserType == 'admin'); break;
		case 'owner': $draw = ($loginUserType == 'admin') || ($loginUserType == 'cUser') ; break;
		case 'all':   $draw = true;
		}

		if( $draw )
		{
			$access = AccessLogic::getAccess( 'job', $id, 'all' );
			$this->addBuffer( number_format($access).'PV' );
		}
	}



	/**
	 * 管理者に各条件の物件件数を表示
	 *
	 */
	function drawCount( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $LOGIN_ID;
		global $NOT_LOGIN_USER_TYPE;
		// **************************************************************************************

		/*
		if( !isset($_SESSION[$loginUserType."_count"]) && ( $loginUserType == 'nUser' || $loginUserType == 'nobody' ) )
		{// キャッシュデータがある場合そちらを参照
			$data = explode( "/", $_SESSION[$loginUserType."_count"] );
			if( $data[1] > time()-300 )
			{// キャッシュは5分位内なら有効
				$this->addBuffer( $data[0] );
				return;
			}
		}
		*/

		$userType	 = $loginUserType;
		$user		 = $args[0];
		$user_disp	 = $args[1];
		$admin_disp	 = $args[2];
		$mode		 = $args[3];
		$type		 = $args[4];
		$now		 = $args[5];

		$db = GMList::getDB($type);

		switch($now)
		{
		case 'on':
			$param['publish_now'] = 'on';
			break;
		}

		switch($user)
		{// 閲覧制限
		case 'nobody': // 制限なし
			$param['limitation'] = 'FALSE';
			$param['limitation_PAL'][] = 'match comp';
			break;
		case 'cUser': // 会員限定
			$param['limitation'] = 'TRUE';
			$param['limitation_PAL'][] = 'match comp';
			break;
		case 'all': // 全求人
			break;
		}


		switch($user_disp)
		{// 企業掲載状況
		case 'off': // 非掲載
			$param['publish'] = 'off';
			$param['publish_PAL'][] = 'match comp';
			break;
		case 'on': // 掲載
			$param['publish'] = 'on';
			$param['publish_PAL'][] = 'match comp';
			break;
		}
		switch($admin_disp)
		{// 管理者掲載状況
		case '1': // 非掲載
			$param['activate'] = '1';
			$param['activate_PAL'][] = 'match comp';
			break;
		case '4': // 掲載
			$param['activate'] = '4';
			$param['activate_PAL'][] = 'match comp';
			break;
		}

		switch($mode)
		{
		case 'attention': // おすすめ求人
			break;
		case 'noneApply': // 応募上限
			$param['apply_pos'] = 'TRUE';
			$param['apply_pos_PAL'][] = 'match comp';
			break;
		case 'limitOver': // 掲載期限
			break;
		}

		$table = JobLogic::getTable( $type, null, $param, $userType );

		switch($mode)
		{
		case 'attention': // おすすめ求人
			$table = $db->searchTable(  $table, 'attention_time', '>=', time() );
			break;
		case 'noneApply': // 応募上限
			break;
		case 'limitOver': // 掲載期限
			$table  = $db->searchTable( $table , 'use_limit_time_apply' , '=' , TRUE );
			//$table  = $db->searchTable( $table , 'limit_time_apply' , '<' , time() );
			$table  = $db->searchTable( $table , 'limits' , '<' , time() );
			break;
		}

		$buffer = $db->getRow($table);
		$_SESSION[$loginUserType."_count"] = $buffer."/".time();

		$this->addBuffer( $buffer );
	}


	/**
	 * 応募資格が設定されている場合onを返す
	 *
	 */
	function isReq( &$gm, $rec, $args )
	{
		$id = $args[0];

		$req = job::getReq($id);

		$result = '';
		if( count($req) > 0 ) { $result = "on"; }

		$this->addBuffer( $result );
	}


	/**
	 * 応募資格のチェックボックスを表示
	 *
	 */
	function drawReqCheckBox( &$gm, $rec, $args )
	{
		$id = $args[0];

		$req = job::getReq($id);

		if( count($req) > 0 )
		{
			foreach( $req as $tmp ) { $cc .= '<!--# form checkbox req  '.$tmp.' '.$tmp.'   true #--><br />'; }
			$buffer = $gm->getCCResult( null, $cc );
		}

		$this->addBuffer( $buffer );
	}


	/**
	 * おすすめ掲載のステータスを表示。
	 *
	 * @param mode registを渡した場合未入力時に申し込みフォームを表示
	 */
	function drawAttentionState(&$gm,$rec,$args)
	{
		$mode = $args[0];

		$db = $gm->getDB();

		$buffer = '未掲載';
		$button = '掲載申請';

		$att_time = $db->getData( $rec, 'attention_time' );
		if( $att_time > time() )
		{
			$buffer = date( 'Y', $att_time ).'年'.date( 'm', $att_time ).'月'.date( 'd', $att_time ).'日迄掲載';
			$button = '掲載延長';
		}

		if( $mode == 'regist' )
			{ $buffer = '　<input type="button" value="'.$button.'" onclick="location.href=\'index.php?app_controller=register&type=attention&job='.$db->getData($rec, 'id').'\'">'; }

		$this->addBuffer($buffer);
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Index関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 地域から探すを表示する
	 *
	 * @param type rental/buyの指定
	 */
	function drawAreaSearchList( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************

		$flg = Conf::getData( 'job', 'top_area');
		$noneDisp = true;
		switch($flg)
		{
		case 'all': break;
		case 'job': $noneDisp = false; break;
		case 'off': return; break;
		}


		$design	 = Template::getTemplate( $loginUserType, $loginUserRank, null, 'AREA_SEARCH_DESIGN' );

		$disp = Conf::getData( 'job', 'top_area_range');
		switch($disp)
		{
		case 'all':
		default:
			$buffer = self::getAddsAllList( $gm, $design, $noneDisp );
			break;
		case 'area':
			$area = Conf::getData( 'job', 'def_area');
			$buffer = self::getAddsList( $gm, $design, $noneDisp, $area );
			break;
		case 'adds':
			$adds = Conf::getData( 'job', 'def_adds');
			$buffer = self::getAdd_subList( $gm, $design, $noneDisp, $adds );
			break;
		}


		$this->addBuffer( $buffer );
	}

	function getAreaNameList()
	{
		$db = GMList::getDB('area');
		$table = $db->getTable();
		$row = $db->getRow($table);

		$areaList = array();
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );
			$id = $db->getData( $rec, 'id' );
			$name = $db->getData( $rec, 'name' );

			$areaList[$id] = $name;
		}

		return $areaList;
	}

	function getAddsNameList()
	{
		$db = GMList::getDB('adds');
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'disp', '=', true );
		//$table = $db->sortTable( $table, 'sort_rank', 'asc' );
		$row = $db->getRow($table);

		$addsList = array();
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );
			$id = $db->getData( $rec, 'id' );
			$area = $db->getData( $rec, 'area_id' );
			$name = $db->getData( $rec, 'name' );

			$addsList[$area][$id] = $name;
		}

		return $addsList;
	}

	function getAddsAllList( &$gm, $design, $noneDisp )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $LOGIN_ID;
		// **************************************************************************************

		// 求人の登録数を取得

		if( Conf::getData( 'job', 'top_count') == 'pre' )
		{
			$count = new Count( 'area', $loginUserType, viewMode::getViewMode() );
			$countList = $count->getDataList();
		}
		else
		{
			$countList = CountLogic::controller( viewMode::getViewMode(), 'work_place_adds' );
		}

		$col = 'all';
		$db = GMList::getDB('area');

		$areaList = self::getAreaNameList();
		$addsList = self::getAddsNameList();


		$buffer = $gm->getString( $design, null, 'head_'.$col );

		$kindList = array( 'prefectures','foreign' );



		foreach( $kindList as $kind )
		{
			switch($kind){
				case "prefectures":
					$gm->setVariable( 'KIND', $kind );
					$buffer .= $gm->getString( $design, null, 'kind_head_'.$col );

					foreach( $areaList as $area_id => $area_name )
					{
						$gm->setVariable( 'AREA_ID', $area_id );
						$gm->setVariable( 'AREA_NAME', $area_name );

						if (isset($addsList[$area_id]))
						{
							$buffer .= $gm->getString( $design, null, 'element_head_'.$col );

							foreach( $addsList[$area_id] as $id => $name )
							{
								if( !$noneDisp && (int)$countList[$id] == 0 ) { continue; }
								$gm->setVariable( 'ID', $id);
								$gm->setVariable( 'NAME', $name.' ('.(int)$countList[$id].')');
								$buffer .= $gm->getString( $design, null, 'element_'.$col );
							}
							$buffer .= $gm->getString( $design, null, 'element_foot_'.$col );
						}
					}
					$buffer .= $gm->getString( $design, null, 'kind_foot_'.$col );
					break;
				case "foreign":
					$foreignCount = $countList["foreign"];
					$gm->setVariable( 'KIND', $kind );
					$gm->setVariable( 'NAME', "海外".' ('.(int)$foreignCount.')');

					$buffer .= $gm->getString( $design, null, 'kind_head_foreign_'.$col );
					$buffer .= $gm->getString( $design, null, 'element_head_foreign_'.$col );

					if( $noneDisp || (int)$countList["foreign"] != 0 ) {
						$buffer .= $gm->getString( $design, null, 'element_foreign_'.$col );
					}

					$buffer .= $gm->getString( $design, null, 'element_foot_foreign_'.$col );
					$buffer .= $gm->getString( $design, null, 'kind_foot_foreign_'.$col );
					break;
			}

		}

		$buffer .= $gm->getString( $design, null, 'foot_'.$col );


		return $buffer;
	}

	function getAddsList( &$gm, $design, $noneDisp, $area )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		// **************************************************************************************

		// 求人の登録数を取得
		if( Conf::getData( 'job', 'top_count') == 'pre' )
		{
			$count = new Count( 'area', $loginUserType,viewMode::getViewMode());
			$countList = $count->getDataList();
		}
		else
		{ $countList = CountLogic::controller( viewMode::getViewMode(), 'work_place_adds' ); }

		$col = 'adds';
		$db = GMList::getDB('area');

		$db = GMList::getDB($col);
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'area_id', '=', $area );
		$table = $db->searchTable( $table, 'disp', '=', true );
		//$table = $db->sortTable( $table, 'sort_rank', 'asc' );
		$row = $db->getRow($table);

		$buffer = $gm->getString( $design, null, 'head_'.$col );
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );
			$id = $db->getData( $rec, 'id' );

			if( !$noneDisp && (int)$countList[$id] == 0 ) { continue; }

			$gm->setVariable( 'NAME', $db->getData( $rec , 'name' ).'（'.(int)$countList[$id].'）');
			$buffer .= $gm->getString( $design, $rec, 'element_'.$col );
		}
		$buffer .= $gm->getString( $design, null, 'foot_'.$col );

		return $buffer;
	}

	function getAdd_subList( &$gm, $design, $noneDisp, $adds )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		// **************************************************************************************

		$param['adds'] = $adds;
		$param['adds_PAL'][] = 'match comp';
		// 求人の登録数を取得
		if( Conf::getData( 'job', 'top_count') == 'pre' )
		{
			$count = new Count( 'area', $loginUserType ,viewMode::getViewMode());
			$countList = $count->getDataList();

		}
		else
		{ $countList = CountLogic::controller( viewMode::getViewMode(), 'work_place_add_sub', $param ); }


		$col = 'add_sub';

		$db = GMList::getDB($col);
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'adds_id', '=', $adds );
		$table = $db->searchTable( $table, 'disp', '=', true );
		//$table = $db->sortTable( $table, 'sort_rank', 'asc' );
		$row = $db->getRow($table);

		$buffer = $gm->getString( $design, null, 'head_'.$col );
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );
			$id = $db->getData( $rec, 'id' );

			if( !$noneDisp && (int)$countList[$id] == 0 ) { continue; }

			$gm->setVariable( 'NAME', $db->getData( $rec , 'name' ).'（'.(int)$countList[$id].'）');
			$buffer .= $gm->getString( $design, $rec, 'element_'.$col );
		}
		$buffer .= $gm->getString( $design, null, 'foot_'.$col );

		return $buffer;
	}


	/**
	 * 雇用形態から探すを表示
	 */
	function drawJobFormSearchList( &$gm, $rec, $args )
	{
		$type   = 'items_form';
		$label  = 'JOBFORM_SEARCH_DESIGN';
		$buffer = $this->getCategoryList( $type, $label );

		$this->addBuffer( $buffer );
	}


	/**
	 * 特徴から探すを表示
	 *
	 * @param gm GUIManagerオブジェクトです。このメソッドでは利用しません。
	 * @param rec 登録情報のレコードデータです。このメソッドでは利用しません。
	 * @param args コマンドコメント引数配列です。
	 */
	function drawFeaturesSearchList( &$gm, $rec, $args )
	{
		$type   = 'job_addition';
		$label  = 'FEATURES_SEARCH_DESIGN';
		$buffer = $this->getCategoryList( $type, $label );

		$this->addBuffer( $buffer );
	}


	/**
	 * 職種から探すを表示
	 */
	function drawJobtypeSearchList( &$gm, $rec, $args )
	{
		$type   = 'items_type';
		$label  = 'JOBTYPE_SEARCH_DESIGN';
		$buffer = $this->getCategoryList( $type, $label );

		$this->addBuffer( $buffer );
	}


	/**
	 * xxxから探すの表示情報を取得
	 */
	function getCategoryList( $type, $label )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************

		$flg = Conf::getData( 'job', 'top_'.$type);

		$noneDisp = true;
		switch($flg)
		{
		case 'all': break;
		case 'job': $noneDisp = false; break;
		case 'off': return; break;
		}

		$design	 = Template::getTemplate( $loginUserType , $loginUserRank , 'mid' , $label );

		$gm = GMList::getGM($type);
		$db = $gm->getDB();

		$table = CategoryLogic::getTableByType($type);
		$row   = $db->getRow($table);

		// 求人の登録数を取得
		if( Conf::getData( 'job', 'top_count') == 'pre' )
		{
			$count = new Count( $type, $loginUserType, viewMode::getViewMode() );
			$countList = $count->getDataList();
		}else{
			if($type=="items_type") $type="category";
			if($type=="items_form") $type="work_style";
			if($type=="job_addition") $type="addition";
			$countList = CountLogic::controller( viewMode::getViewMode(), $type, $_GET );
		}


		$buffer = $gm->getString( $design , null , 'head' );
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );

			$id = $db->getData( $rec , 'id' );

			if( !$noneDisp && (int)$countList[$id] == 0 ) { continue; }

			$gm->setVariable( 'id',		 $id );
			$gm->setVariable( 'name',	 $db->getData( $rec , 'name' ) );
			$gm->setVariable( 'row',	 (int)$countList[$id] );
			$buffer .= $gm->getString( $design , null , 'element' );
		}
		$buffer .= $gm->getString( $design , null , 'foot' );

		return $buffer;
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// Side_bar関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////



	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 詳細関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * 特徴をアイコン表示
	 */
	function drawJobAdditionIcon( &$gm, $rec, $args )
	{
		global $LOGIN_ID;
		global $STATIC_URL_FLG;

		if($_GET["type"]=="mid" || $_GET["type"]=="fresh"){
			$type=$_GET["type"];
		}else{
			$type=viewMode::getViewMode();
		}


		$db = $gm->getDB();

		$buffer = "";
		$nameList = SystemUtil::getNameList( "job_addition", $db->getData( $rec, 'addition' ) );

		if(count((array)$nameList) == 0) { return; }
		foreach( $nameList as $id => $name )
		{
			if($STATIC_URL_FLG) {
				$buffer .= '<li><a href="'.SystemUtil::getStaticURL($id, $type, 'search').'">'.$name.'</a></li>';
			} else {
				$buffer .= '<li><a href="index.php?app_controller=search&type='.$type.'&run=true&addition='.$id.'&addition_CHECKBOX=&addition_PAL[]=match+or">'.$name.'</a></li>';				
			}
		}

		$this->addBuffer($buffer);
	}


	/**
	 * 紹介写真の情報があるかチェック（mobile用）
	 */
	function drawPhotoCheck( &$gm, $rec, $args )
	{
		$db = $gm->getDB();

		$flg = false;
		$labelList = array( 'photo01', 'photo02', 'photo03' );
		foreach( $labelList as $label )
		{
			if( strlen($db->getData( $rec, $label.'_main' )) )
				$flg = true; break;
		}

		$this->addBuffer($flg);
	}


	/**
	 * 紹介写真を表示。
	 */
	function drawPhoto( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************

		$design	 = Template::getTemplate( $loginUserType , $loginUserRank , 'job' , 'INFO_PHOTO_DESIGN' );

		$db = $gm->getDB();

		$labelList = array( 'photo01', 'photo02', 'photo03' );
		foreach( $labelList as $label )
		{
			if( strlen($db->getData( $rec, $label.'_img' )) )
			{
				$gm->setVariable( 'col', $label );
				$element .= $gm->getString( $design , $rec , 'element' );
			}
		}

		if( strlen($element) )
		{
			$buffer  = $gm->getString( $design , null , 'head' );
			$buffer .= $element;
			$buffer .= $gm->getString( $design , null , 'foot' );
		}

		$this->addBuffer($buffer);
	}


	/**
	 * 会社からのメッセージの情報があるかチェック（mobile用）
	 */
	function drawMessageCheck( &$gm, $rec, $args )
	{
		$db = $gm->getDB();

		$flg = false;
		$labelList = array( 'com_mes01', 'com_mes02', 'com_mes03', 'com_mes04'  );
		foreach( $labelList as $label )
		{
			if( strlen($db->getData( $rec, $label.'_main' )) )
				$flg = true; break;
		}

		$this->addBuffer($flg);
	}


	/**
	 * 会社からのメッセージを表示。
	 */
	function drawMessage( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************

		$design	 = Template::getTemplate( $loginUserType , $loginUserRank , 'job' , 'INFO_MESSAGE_DESIGN' );

		$db = $gm->getDB();

		$labelList = array( 'com_mes01', 'com_mes02', 'com_mes03', 'com_mes04'  );
		foreach( $labelList as $label )
		{
			if( strlen($db->getData( $rec, $label.'_main' )) )
			{
				$gm->setVariable( 'col', $label );
				$element .= $gm->getString( $design , $rec , 'element' );
			}
		}

		if( strlen($element) )
		{
			$buffer  = $gm->getString( $design , null , 'head' );
			$buffer .= $element;
			$buffer .= $gm->getString( $design , null , 'foot' );
		}

		$this->addBuffer($buffer);
	}


	/**
	 * 求人情報の登録企業情報を表示。
	 */
	function drawCompany( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************

		$design	 = Template::getTemplate( $loginUserType , $loginUserRank , 'job' , 'INFO_COMPANY_DESIGN' );

		$db = GMList::getDB('job');
		$flg = $db->getData( $rec, 'logo_flg' );
		$img = $db->getData( $rec, 'logo_img' );

		$db = GMList::getDB('cUser');
		$rec = $db->selectRecord( $db->getData( $rec, 'owner' ) );

		switch($flg)
		{
		case 'job':
			break;
		case 'cUser':
		default:
			$img = $db->getData( $rec, 'image' );
			break;
		}

		if( strlen($img) ) { $gm->setVariable( 'img', $img ); }
		$buffer .= $gm->getString( $design , $rec );

		$this->addBuffer($buffer);
	}


	/////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 検索関係
	/////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Commandの同名関数にカウントを追加
	 *
	 */
	function tableSelectForm( &$gm , $rec , $args ){

		// 求人の登録数を取得
		$countList = CountLogic::controller( 'job', $args[0], $_GET );

		if(isset($args[4]) && strlen($args[4]))
			$check = $args[4];
		else
			$check = "";

		if(isset($args[6]) && strlen($args[6]))
			$option = ' '.$args[6];
		else
			$option = "";

		$tgm = SystemUtil::getGMforType( $args[1] );
		$db = $tgm->getDB();

		$table = $db->getTable();

		if(isset($args[7])){
			for($i=0;isset($args[$i+7]);$i+=3){
				$table = $db->searchTable( $table, $args[7+$i], $args[8+$i], $args[9+$i] );
			}
		}

		$row = $db->getRow( $table );

		$index = "";
		$value  = "";

		if( isset($args[5]) && strlen($args[5]) ){
			$index .= $args[5];

			if($row){
				$index  .= '/';
				$value  .= '/';
			}
		}

		for($i=0;$i<$row-1;$i++){
			$rec = $db->getRecord( $table , $i );
			$index .= $db->getData( $rec , $args[2] ).'（'.(int)$countList[$db->getData( $rec , $args[3] )].'）'."/";
			$value .= $db->getData( $rec , $args[3] )."/";
		}
		$rec = $db->getRecord( $table , $i );
		$index .= $db->getData( $rec , $args[2] ).'（'.(int)$countList[$db->getData( $rec , $args[3] )].'）';
		$value .= $db->getData( $rec , $args[3] );

		$this->addBuffer( $gm->getCCResult( $rec, '<!--# form option '.$args[0].' '.$check.' '.$value.' '.$index.$option.' #-->' ) );
	}

	/**
	 * Commandの同名関数にカウントを追加
	 *
	 */
	function tableCheckForm( &$gm , $rec , $args ){
		// 求人の登録数を取得
		$countList = CountLogic::controller( 'job', $args[0], $_GET );

		if(isset($args[5]) && strlen($args[5]))
			$check = $args[5];
		else
			$check = "";
		if(isset($args[7]) && strlen($args[7]))
			$option = ' '.$args[7];
		else
			$option = "";

		$tgm = SystemUtil::getGMforType( $args[1]);
		$db = $tgm->getDB();
		$table = $db->getTable();
		$row = $db->getRow( $table );

		$index = array();
		$value  = array();

		if( isset($args[6]) && strlen($args[6]) ){
			$index[] = $args[6];
			$value[] = '';
		}

		for($i=0;$i<$row;$i++){
			$rec = $db->getRecord( $table , $i );
			$index[] = $db->getData( $rec , $args[2] ).'（'.(int)$countList[$db->getData( $rec , $args[3] )].'）';
			$value[] = $db->getData( $rec , $args[3] );
		}

		$this->addBuffer( $gm->getCCResult( $rec, '<!--# form checkbox '.$args[0].' '.$check.' '.$args[4].' '.implode("/",$value).' '.implode("/",$index).$option.'  '.$args[9].' #-->' ) );
	}


	/*
	 * 求人に応募があるか
	 */
	function drawExistApply( &$gm, $rec, $args ){
		List($type ,$id) = $args;
		$this->addBuffer(entryLogic::existsApply($id) ? "TRUE":"FALSE");
	}


	/*
	 * 応募数を表示
	 */
	function drawEntryCount( &$gm, $rec, $args )
	{
		$type = $args[0];
		$id = $args[1];

		$progressList = Entry::getProgressList();
		switch($type)
		{
			case 'mid':
			case'fresh':
				$owner = "items_id";
				$countList = Entry::getCountByJob( $type, $id );
			break;
			case'cUser':
				$owner = "items_owner";
				$countList = Entry::getCountBycUser( $id );
			break;
		}

		$buffer = '<div class="entryCount__list">';
		foreach( $progressList as $status => $name )
		{
			$count = 0;
			if( isset($countList[$status]) ) { $count = $countList[$status]; }
			$link = 'index.php?app_controller=search&type=entry&run=true&'.$owner.'='.$id.'&'.$owner.'_PAL[]=match like&status='.$status.'&status_PAL[]=match in';
			$buffer .= '<div class="entryCount__item">
				<div class="entryCount__sub">
					'.$name.'
				</div>
				<div class="entryCount__count">
					<a href="'.$link.'">'.$count.'</a>
				</div>
			</div>
			';
		}
		$buffer .= '</div>';
		$this->addBuffer($buffer);
	}
}

?>