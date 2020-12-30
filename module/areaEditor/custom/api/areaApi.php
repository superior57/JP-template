<?php

class mod_AreaApi extends apiClass
{
	/**
	 * 子カラムの要素を出力
	 *
	 * @param tableName テーブル名
	 * @param parentCol 親IDが格納されているカラム名
	 * @param parent 親ID
	 */
	function getChildJsonData( &$param )
	{
		global $SYSTEM_CHARACODE;
		header('Content-Type: application/json;charset='.$SYSTEM_CHARACODE);

		// 選択肢生成に利用
		$valueCol	 = 'id';
		$indexCol	 = 'name';
		$noneIndex	 = '未選択';
		if( strlen($param['noneFlg']) ) { $noneIndex = ''; }

		if( !strlen($param['parent']) ) { return; }

		$db	 = GMList::getDB($param['tableName']);
		$table = $db->getTable();
		$table = $db->searchTable( $table, $param['parentCol'], '=', '%'.$param['parent'].'%' );
		if( strlen($param['dispFlg']) ) { $table = $db->searchTable( $table, 'disp', '=', true ); }
//		$table = $db->sortTable( $table, 'sort_rank', 'asc' );
		$countList = null;
		if( strlen($param['countCol']) )
		{
			switch($param['type'])
			{
				case 'buy':
				case 'rental':
					$_GET['adds'] = $param['parent'];
					$_GET['adds_PAL'][] = 'match like';
					break;
			}
			$countList = CountLogic::controller($param['type'], $param['countCol'], $_GET );
		}

		print Format::createJsonData( $db, $table, $valueCol, $indexCol, $noneIndex, $countList );
	}
	/**
	 * 子カラムの要素を出力
	 *
	 * @param tableName テーブル名
	 * @param parentCol 親IDが格納されているカラム名
	 * @param parent 親ID
	 */
	function getLineJsonData( &$param )
	{
		global $SYSTEM_CHARACODE;
		header('Content-Type: application/json;charset='.$SYSTEM_CHARACODE);

		// 選択肢生成に利用
		$valueCol	 = 'id';
		$indexCol	 = 'name';
		$noneIndex	 = '未選択';
		if( strlen($param['noneFlg']) ) { $noneIndex = ''; }

		if( !strlen($param['parent']) ) { return; }

		$db	 = GMList::getDB($param['tableName']);
		$table = $db->getTable();
		$table = $db->searchTable( $table, $param['parentCol'], '=', '%'.$param['parent'].'%' );
		$countList = null;
		if( strlen($param['countCol']) )
		{
			if( $param['mode'] == 'search' && Conf::getData( 'job', 'search_line_count' ) != 'all' )
			{
				$countList = CountLogic::controller($param['type'], $param['countCol'], $param );
			}
		}

		print Format::createJsonData( $db, $table, $valueCol, $indexCol, $noneIndex, $countList );
	}

	/**
	 * 都道府県の表示状況を変更する
	 *
	 * @param idList 表示するID郡
	 */
	function editAddsDisp( &$param )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		// **************************************************************************************

		if( $loginUserType == 'admin' )
		{
			$db	 = GMList::getDB('adds');

			$idList = explode( '/', $param['idList'] );

			$table = $db->getTable();
			$table = $db->searchTable( $table, 'id', 'not in', $idList );
			$db->setTableDataUpdate( $table, 'disp', false );

			if( count($idList) > 0 )
			{
				$table = $db->getTable();
				$table = $db->searchTable( $table, 'id', 'in', $idList );
				$db->setTableDataUpdate( $table, 'disp', true );
			}
		}

	}

	/**
	 * 市区町村の登録。
	 *
	 * @param tableName 対象テーブル名。
	 * @param id 対象ID。
	 */
	function regist( $params )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************

		if( $loginUserType == 'admin' )
		{
			$gm = SystemUtil::getGM();
			$tbl = $gm[ $params['tableName'] ]->colRegist;
			if ( strpos( strtolower($tbl['name']) , 'null' ) !== FALSE ) {
				if ( !isset($params['name']) || !strlen($params['name']) ) {
					SystemUtil::innerLocation( "index.php?app_controller=search&run=true&type=".$params['tableName']."&e=1&adds_id_PAL[]=match like&adds_id=".$params['adds_id'] );
					return;
				}
			}
			Category::regist( $params['tableName'], $params );
			SystemUtil::innerLocation( "index.php?app_controller=search&run=true&type=".$params['tableName']."&adds_id_PAL[]=match like&adds_id=".$params['adds_id'] );
		}

	}


	/**
	 * 市区町村の編集。
	 *
	 * @param tableName 対象テーブル名。
	 * @param id 対象ID。
	 */
	function edit( $params )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************

		if( $loginUserType == 'admin' )
		{
			$db = GMlist::getDB($params['tableName']);
			$rec = $db->selectRecord($params['id']);
			$db->setData( $rec, 'area_id', $params['area_id'] );
			$db->setData( $rec, 'name', $params['name'] );
			$db->setData( $rec, 'disp', $params['disp'] );
			$db->updateRecord( $rec );
			SystemUtil::innerLocation( "index.php?app_controller=search&run=true&type=".$params['tableName']."&adds_id_PAL[]=match like&adds_id=".$params['adds_id'] );
		}

	}


	/**
	 * カテゴリの並び替え
	 *
	 * @param tableName 対象テーブル名。
	 * @param id 並び替え対象製品ID
	 * @param sort_pal up/down
	 */
	function rankSort( &$param )
	{
		$db = GMlist::getDB($param['tableName']);
		$rec = $db->selectRecord($param['id']);

		switch($param['sort_pal'])
		{// 条件に応じた交換レコードを検索用パラメータをセット
			case 'down':
				$search	 = '>';
				$sort	 = 'asc';
				break;
			case 'up':
			default:
				$search	 = '<';
				$sort	 = 'desc';
				break;
		}
		$rank	 = $db->getData( $rec, 'sort_rank' );

		$table	 = $db->getTable() ;
		$table	 = $db->searchTable( $table, 'id', '!', $param['id'] );
		$table	 = $db->searchTable( $table, 'adds_id', '=', $param['adds_id'] );
		$table	 = $db->searchTable( $table, 'sort_rank', $search, $rank );
		$table	 = $db->sortTable( $table, 'sort_rank', $sort );
		if( $db->getRow($table) > 0 )
		{// 対象レコードが存在する場合ソートランクを交換
			$trec = $db->getRecord( $table, 0 );
			$db->setData( $rec, 'sort_rank', $db->getData( $trec, 'sort_rank' ) );
			$db->setData( $trec, 'sort_rank', $rank );
			$db->updateRecord( $rec );
			$db->updateRecord( $trec );
		}
	}

}

?>