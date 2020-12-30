<?php

	include_once "./include/base/ExtensionBase.php";

	/**
	 * 拡張命令クラス
	 *
	 * @author 丹羽一智
	 * @version 1.0.0
	 *
	 */
	class Extension extends ExtensionBase
	{
		/**********************************************************************************************************
		 *　アプリケーション固有メソッド
		 **********************************************************************************************************/

		function drawSocial( &$gm, $rec , $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $HOME;
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************

			$file = Template::getTemplate( $loginUserType , $loginUserRank , '', 'SOCIAL_ICON_DESIGN' );

			$type = array_shift($args);
			$id = array_shift($args);
			$title = implode( " ", $args );
			if( strlen($title) == 0 ) { $title = SystemUtil::getSystemData('site_title'); }
			$mixi_check = SystemUtil::getSystemData('mixi_check');

			$url = $HOME;
			if( strlen($type) > 0 && strlen($id) > 0  )
			{ $url .= 'index.php?app_controller=info&type='.$type.'&id='.$id; }
			$url_encode = urlencode($url);

			$socialList = SystemUtil::getSystemData('social_icon');
			if( strlen($socialList) == 0 ) { return; }
			$socialList = explode( '/', $socialList );

			$gm->setVariable( 'URL', $url );
			$gm->setVariable( 'URL_ENCODE', $url );
			$gm->setVariable( 'TITLE', $title );
			$gm->setVariable( 'MIXI_CHECK', $mixi_check );

			$buffer .= $gm->getString($file, null, "head");
			foreach( $socialList as $social )
			{
				if( $social == 'mixi' && strlen($mixi_check) == 0 ) { continue; }
				$buffer .= $gm->getString($file, null, $social);
			}
			$buffer .= $gm->getString($file, null, "foot");

			$this->addBuffer($buffer);
		}
		
		function loginUserType( &$gm, $rec , $args ){

			global $loginUserType;
			$this->addBuffer($loginUserType);

		}
		
		function cookie( &$gm, $rec , $args ){

			$buffer=$_COOKIE[$args[0]];
			$this->addBuffer($buffer);

		}

		function getItemsEval( &$gm, $rec, $args )
		{
			global $ACTIVE_ACCEPT;
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			// **************************************************************************************

			$gm = GMList::getGM("review");
			$db = $gm->getDB();
			$table = $db->getTable();
			$table = $db->searchTable( $table, 'cid','=',$args[0]);
			$table = $db->searchTable($table,"activate" ,"=",$ACTIVE_ACCEPT);
			$row = $db->getRow($table);

			$eval1 = $db->getSum('eval1',$table)/$row;
			$eval2 = $db->getSum('eval2',$table)/$row;
			$eval3 = $db->getSum('eval3',$table)/$row;
			$eval4 = $db->getSum('eval4',$table)/$row;
			$eval5 = $db->getSum('eval5',$table)/$row;

			$this->addBuffer(number_format(($eval1+$eval2+$eval3+$eval4+$eval5)/5,1));
		}

		/**
		 * 時間に応じた挨拶を表示します。
		 *
		 * @param gm GUIManagerオブジェクトです。このメソッドでは利用しません。
		 * @param rec 登録情報のレコードデータです。このメソッドでは利用しません。
		 * @param args コマンドコメント引数配列です。このメソッドでは利用しません。
		 */
		function hello( &$gm, $rec, $args )
		{

			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			// **************************************************************************************

			$message	 = "";
			switch(  date( "G", time() )  )
			{
				case '0':
				case '1':
				case '2':
				case '3':
					$message = '遅くまでご苦労様です。';
					break;

				case '4':
				case '5':
				case '6':
				case '7':
				case '8':
				case '9':
				case '10':
					$message = 'おはようございます。';
					break;

				case '11':
				case '12':
				case '13':
				case '14':
				case '15':
				case '16':
					$message = 'こんにちは。';
					break;

				case '17':
				case '18':
				case '19':
					$message = 'こんばんは。';
					break;

				case '20':
				case '21':
				case '22':
				case '23':
					$message = '遅くまでご苦労様です。';
					break;
			}

			$this->addBuffer( $message );
		}

		function parseURLParam( &$gm, $rec, $args ){
			List($url,$key) = $args;
			parse_str($url,$array);

			$this->addBuffer($array[$key]);
		}

		function drawAccessTag( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $LOGIN_ID;
			// **************************************************************************************

			$tag=SystemUtil::getSystemData('access_tag');
			$target=explode("/",SystemUtil::getSystemData("access_target"));
			
			if(in_array($loginUserType,$target)){
				$this->addBuffer( $tag );
			}

		}

		function drawAccessCountDate( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			// **************************************************************************************

			if($args[0] && $args[1]){
				$year=$args[0];
				$month=$args[1];
			}else{
				$year=date('Y');
				$month=date('m');
			}

			$dayMax=date("t", mktime(0, 0, 0, $month, 1, $year));

			for($i=1;$i<=$dayMax;$i++){
				$buffer[$i]="'".$i."'";
			}


			$this->addBuffer( implode(",",$buffer) );

		}

		function drawAccessCountAll( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			// **************************************************************************************

			if($args[0] && $args[1]){
				$year=$args[0];
				$month=$args[1];
			}else{
				$year=date('Y');
				$month=date('m');
			}



			$gm = GMList::getGM("count");
			$db = $gm->getDB();
			$table = $db->getTable();
			$table = $db->searchTable($table, 'owner','=', $args[2]);
			$table = $db->searchTable( $table, 'year', '=', $year);
			$row = $db->getRow($table);

			$rec = $db->getRecord( $table, 0 );
			$countData=unserialize($db->getData($rec,"count_data"));

			$total=is_null(array_sum($countData[$year][sprintf("%02d",$month)])) ? 0 : array_sum($countData[$year][sprintf("%02d",$month)]);

			$this->addBuffer( $total );

		}

		function drawAccessCountNum( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			// **************************************************************************************

			if($args[0] && $args[1]){
				$year=$args[0];
				$month=$args[1];
			}else{
				$year=date('Y');
				$month=date('m');
			}

			$dayMax=date("t", mktime(0, 0, 0, $month, 1, $year));

			$gm = GMList::getGM("count");
			$db = $gm->getDB();
			$table = $db->getTable();
			$table = $db->searchTable($table, 'owner','=', $args[2]);
			$table = $db->searchTable( $table, 'year', '=', $year);
			$row = $db->getRow($table);


			$rec = $db->getRecord( $table, 0 );
			$countData=unserialize($db->getData($rec,"count_data"));

			for($i=1;$i<=$dayMax;$i++){
				if($countData[$year][sprintf("%02d",$month)][sprintf("%02d",$i)]){
					$clickCount=$countData[$year][sprintf("%02d",$month)][sprintf("%02d",$i)];
				}else{
					$clickCount=0;
				}
				$buffer[$i]=$clickCount;
			}


			$this->addBuffer( implode(",",$buffer) );

		}

		function getClickCounter4Ad( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			// **************************************************************************************


			$year=isset($args[2]) ? $args[2] : date('Y');
			$month=isset($args[1]) ? $args[1] : date('m');


			$gm = GMList::getGM("count");
			$db = $gm->getDB();
			$table = $db->getTable();
			$table = $db->searchTable($table, 'owner','=', $args[0]);
			$table = $db->searchTable( $table, 'year', '=', $year);
			$row = $db->getRow($table);

			$rec = $db->getRecord( $table, 0 );
			$countData=unserialize($db->getData($rec,"count_data"));

			$total=is_null(array_sum($countData[$year][sprintf("%02d",$month)])) ? 0 : array_sum($countData[$year][sprintf("%02d",$month)]);

			$this->addBuffer( $total );

		}

		function getUserType4ID(&$gm, $rec, $args){
			List($userID) = $args;
			$this->addBuffer(SystemUtil::getUserType($userID));
		}

		/**
		 * ログインユーザ固有情報の出力
		 *
		 */
		function getUserProfile( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $LOGIN_ID;
			global $loginUserType;
			// **************************************************************************************

			$colum	 = $args[0];
			switch($colum)
			{
			case 'pass':
				break;
			default:
				$this->addBuffer( SystemUtil::getTableData( $loginUserType, $LOGIN_ID, $colum ) );
				break;
			}
		}

		function getUserType(&$gm, $rec, $args){
			global $loginUserType;
			$this->addBuffer($loginUserType);
		}

		function getYearly( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			// **************************************************************************************

			$this->addBuffer(SystemUtil::getYearly($args[0]));
		}

		function getDayBack( &$gm, $rec, $args )
		{
			$dt1=time();
			$dt2=$args[0];
			if(!isset($dt2) || !strlen($dt2))
				{ $dt2 = mktime(0,0,0,$_POST["term_start_m"],$_POST["term_start_d"],$_POST["term_start_y"]); }
			$diff = $dt2 - $dt1;
			$diffDay = $diff / 86400;//1日は86400秒
			if(ceil($diffDay)<=0){
			$this->addBuffer("-1");
			}else{
				$this->addBuffer(ceil($diffDay));
			}
		}

		function drawCategoryName( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			// **************************************************************************************
			$gm = GMList::getGM($args[0]);
			$db = $gm->getDB();
			$table=$db->getTable();
			$table=$db->sortTable($table,"sort_rank","asc");
			$row = $db->getRow($table);

			for($i=0;$i<$row;$i++){
				$rec = $db->getRecord( $table , $i );
				$res[] = $db->getData( $rec , 'name' );
			}

			$this->addBuffer( implode("　",$res) );
		}

		/**
		 * サイト固有情報の出力
		 *
		 */
		function getSiteProfile( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			// **************************************************************************************

			$colum	 = $args[0];
			switch($colum)
			{
			case 'home':
				global $HOME;
				$this->addBuffer( $HOME );
				break;
			case 'charset':
				global $LONG_OUTPUT_CHARACODE;
				$this->addBuffer( $LONG_OUTPUT_CHARACODE );
				break;
			case 'site_title':
			case 'uuid':
			default:
				$this->addBuffer( SystemUtil::getSystemData($colum) );
				break;
			}
		}

		//css_list output
		function draw_css_list( &$gm , $rec , $args ){
		global $css_name;
			$tgm = SystemUtil::getGMforType('template');
			$db = $tgm->getDB();
			$table = $db->searchTable( $db->getTable() , 'label' , '=' , 'CSS_LINK_LIST' );

			$row = $db->getRow($table);
			$check = '';
			for($i=0;$i<$row;$i++){
				$rec = $db->getRecord( $table , $i );
				$check .= '/'.$db->getData( $rec , 'target_type' );
			}
			$check = substr( $check , 1);

			$this->addBuffer( $gm->getCCResult( $rec, '<!--# form option value '.$css_name.' '.$check.' '.$check.' #-->' ) );
		}

		function drawThreadID(&$gm , $rec , $args){
			List($cUser,$nUser) = $args;
			$thread_id = threadLogic::getThreadID($cUser,$nUser);
			$this->addBuffer($thread_id);
		}

		function drawThreadIDData(&$gm , $rec , $args){
			List($thread_id,$key) = $args;
			$data = threadLogic::getData($thread_id);
			$this->addBuffer($data[$key]);
		}

		function getName(&$gm , $rec , $args){
			global $TABLE_NAME;
			global $THIS_TABLE_IS_USERDATA;
			global $loginUserType;

			List($id) = $args;

			$name = '退会したユーザー';
			for( $i=0; $i<count($TABLE_NAME); $i++ ){
				if(  $THIS_TABLE_IS_USERDATA[ $TABLE_NAME[$i] ]  ){
					$db  = GMList::getDB($TABLE_NAME[$i]);
					$rec = $db->selectRecord($id);

					if( isset($rec) ){
						$id = $db->getData( $rec, 'id' );

						$name = $db->getData( $rec, 'name' );
						if( 'nUser' == $TABLE_NAME[ $i ] && $loginUserType != 'admin'
						//		&& Conf::getData( 'sys_mail', 'check_entry' ) != 'on'
						) { $name = $db->getData( $rec, 'nick_name' );  }

						if( $args[1] == 'true' || $args[1] == 'TRUE' )
							{ $name = '<a href="index.php?app_controller=info&type='. $TABLE_NAME[$i] .'&id='.$id.'">'. $name .'</a>'; }

						break;
					}
				}
			}
			$this->addBuffer( $name );
		}
	}
