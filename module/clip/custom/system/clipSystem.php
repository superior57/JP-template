<?php

	/**
	 * システムコールクラス
	 * 
	 * @author ----
	 * @version 1.0.0
	 * 
	 */
	class clipSystem extends System
	{
		/**********************************************************************************************************
		 * 汎用システム用メソッド
		 **********************************************************************************************************/

		/////////////////////////////////////////////////////////////////////////////////////////////////////////
		// 検索関係
		/////////////////////////////////////////////////////////////////////////////////////////////////////////

		/**
		 * 検索処理。
		 * フォーム入力以外の方法で検索条件を設定したい場合に利用します。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param table フォームのからの入力内容に一致するレコードを格納したテーブルデータ。
		 */
		function searchProc( &$gm, &$table, $loginUserType, $loginUserRank )
		{
			
			$db		 = $gm[ $_GET['type'] ]->getDB();
			$clip	 = new mod_Clip();
			
			if( $loginUserType != 'admin' && $_GET["c_id"]=="")	
			{
				$table = $db->searchTable( $table, 'user_id', '=', $clip->getUserId() );
				$table = $db->searchTable( $table, 'c_type', '=', $_GET["pal"] );
				$row = $db->getRow($table);
				
				$idList = array();
				for( $i=0; $i<$row; $i++ ){
					$rec	  = $db->getRecord( $table, $i );
					$idList[] = $db->getData($rec, 'c_id');
				}
			}elseif( $loginUserType != 'admin' && in_array($_GET["pal"],array("mid","fresh")) && $_GET["c_id"]!=""){
			
				$owner_id=SystemUtil::getTableData($_GET["pal"],$_GET["c_id"],"owner");
				
				if($clip->getUserId() == $owner_id){
					
					$table = $db->searchTable( $table, 'c_id', '=', $_GET["c_id"] );
					$table = $db->searchTable( $table, 'c_type', '=', $_GET["pal"] );
					$row = $db->getRow($table);
					
				}else{
					$row=0;
				}
				
				$idList = array();
				for( $i=0; $i<$row; $i++ ){
					$rec	  = $db->getRecord( $table, $i );
					$idList[] = $db->getData($rec, 'user_id');
				}
				$_GET["mode"]=$_GET["pal"];
				$_GET["pal"]="resume";
				
			}
			
			
			switch($loginUserType)
			{
			case 'nUser':
			case 'nobody':
			case 'cUser':
				if( count($idList) > 0 ){
					$db  = GMList::getDB($_GET["pal"]);
					$table = $db->getTable();
					if($_GET["c_id"]!=""){
						$table = $db->searchTable( $table, 'owner', 'in', $idList );
						$table = $db->searchTable( $table, 'publish', '=', 'on' );
					}else{
						$table = $db->searchTable( $table, 'id', 'in', $idList );
					}
				}else{
					$table = $db->getEmptyTable();
				}
				break;
			}
			if(  !isset( $_GET['sort'] ) || $_GET['sort'] == '' ) { $table	 = $db->sortTable( $table, 'SHADOW_ID', 'desc' ); }
		}

		
		function drawSearch( &$gm, &$sr, $table, $loginUserType, $loginUserRank ){
			SearchTableStack::pushStack($table);
			if($_GET["c_id"]!=""){
				$design = 'SEARCH_RESULT_DESIGN_RESUME';
			}else{
				$design = 'SEARCH_RESULT_DESIGN';
			}
			Template::drawTemplate( $gm[ $_GET['type'] ] , null , $loginUserType , $loginUserRank , $_GET['type'] , $design );
		}
		
		/**********************************************************************************************************
		 * 汎用システム描画系用メソッド
		 **********************************************************************************************************/

		/**
		 * 検索結果をリスト描画する。
		 * ページ切り替えはこの領域で描画する必要はありません。
		 *
		 * @param gm GUIManagerオブジェクト配列　連想配列で gm[ TABLE名 ] でアクセスが可能です。
		 * @param table 検索結果のテーブルデータ
		 * @param loginUserType ログインしているユーザの種別
		 * @param loginUserRank ログインしているユーザの権限
		 */
		function getSearchResult( &$gm, $table, $loginUserType, $loginUserRank )
		{
			switch($loginUserType)
			{
			case 'cUser':
			case 'nUser':
			case 'nobody':
				$gm_job=GMList::getGM($_GET["pal"]);
				$db  = GMList::getDB($_GET["pal"]);
				$row = $db->getRow($table);
				for( $i=0; $i<$row; $i++ )
				{
					$rec = $db->getRecord( $table, $i );
					$gm_job->setVariable('TYPE', $_GET["pal"] );
					if($_GET["c_id"]!=""){
						$design = 'SEARCH_LIST_PAGE_DESIGN_RESUME';
					}else{
						$design = 'SEARCH_LIST_PAGE_DESIGN';
					}
					$html .= Template::getTemplateString( $gm_job, $rec , $loginUserType , $loginUserRank , $_GET['type'] , $design );
				}
				break;
			default:
			
				$db[$_GET['type']] = $gm->getDB();
				$row = $db[ $_GET['type'] ]->getRow($table);
				for( $i=0; $i<$row; $i++ )
				{
					$rec	 = $db[$_GET['type']]->getRecord( $table, $i );
					$type	 = $db[$_GET['type']]->getData($rec, 'c_type');
					if( !isset($db[$type]) ) { $db[$type] = SystemUtil::getGMforType($type)->getDB(); }
					$trec	 = $db[$type]->selectRecord($db[$_GET['type']]->getData($rec, 'c_id'));
					$gm->setVariable('TYPE', $type);
					$html .= Template::getTemplateString( $gm, $trec , $loginUserType , $loginUserRank , $_GET['type'] , 'SEARCH_LIST_PAGE_DESIGN' );
				}
			}

			return $html;
		}

	}

?>