<?php

class mod_CategoryApi extends apiClass
{
	/**
	 * カテゴリの登録。
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
					SystemUtil::innerLocation( "index.php?app_controller=search&run=true&type=".$params['tableName']."&e=1" );
					return;
				}
			}
			Category::regist( $params['tableName'], $params );
			SystemUtil::innerLocation( "index.php?app_controller=search&run=true&type=".$params['tableName']."&pal=".$params['category'] );
		}

	}


	/**
	 * カテゴリの編集。
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
			Category::edit( $params['tableName'], $params['id'], $params );
			SystemUtil::innerLocation( "index.php?app_controller=search&run=true&type=".$params['tableName']."&pal=".$params['category'] );
		}

	}


	/**
	 * カテゴリの削除。
	 *
	 * @param tableName 対象テーブル名。
	 * @param id 対象ID。
	 */
	function delete( $params )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		// **************************************************************************************

		if( $loginUserType == 'admin' )
		{
			Category::delete( $params['tableName'], $params['id'] );
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