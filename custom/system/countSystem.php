<?php

	/**
	 * システムコールクラス
	 *
	 * @author ----
	 * @version 1.0.0
	 *
	 */
	class countSystem extends System
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
			$db		 = $gm[ $_GET['pal'] ]->getDB();
			$table = $db->getTable();

			$table = JobLogic::getTable($_GET["pal"],$table,$_GET,$loginUserType);

			$iDB = $gm[ $_GET['type'] ]->getDB();

			$table = $db->joinTable($table,$_GET['pal'],"count","id","owner");
			$table = $db->joinTableSearch($iDB,$table, 'c_type', '=', $_GET["pal"] );
			$table = $db->joinTableSearch($iDB,$table, 'total', '>', 0 );
			$table = $db->joinTableSearch($iDB,$table,'year','=',date("Y"));
			$table = $db->sortTable($table,"total","desc");


			if($_GET["mode"]=="ranking"){

				$table = $db->limitOffset( $table,0,10);

			}else{
				if(  !isset( $_GET['sort'] ) || $_GET['sort'] == '' ) { $table	 = $db->sortTable( $table, 'SHADOW_ID', 'desc' ); }
			}
		}

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