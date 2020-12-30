<?php

class Category
{
	/**
	 * items_typeカラムのselectboxeを出力
	 *
	 * @param colName カラム名
	 * @param sortFlg 求人の件数によってsortするかどうかのフラグ
	 * @return CC
	 */
	public static function getSortCategorySelectCC( $colName, $sortFlg = false, $countFlg = false )
	{
		$tableName	 = 'items_type';
		$initial	 = '';

		// 選択肢生成に利用
		$valueCol	 = 'id';
		$indexCol	 = 'name';
		$noneIndex	 = '未選択';
		$db		 = GMList::getDB($tableName);
		$table	 = $db->getTable();

		// 求人数が多い順に表示
		if($sortFlg) {
			$table = commonLogic::getSortTable($colName, $db, $table);
		}

		$countList = null;

		if(empty($_GET["type"]))
			$_GET["type"] = viewMode::getViewMode();

		if( $countFlg ) { $countList = CountLogic::controller( $_GET['type'], $colName ); }
		$ccParam	 = Format::createCCParam( $db, $table, $valueCol, $indexCol, $noneIndex, $countList );

		return '<!--# form option '.$colName.' '.$initial.' '.$ccParam['value'].' '.$ccParam['index'].' #-->';
	}

	/**
	 * 指定テーブルのレコードを引数のデータを元に作成する
	 *
	 * @param tableName テーブル名
	 * @param data レコード内容
	 * @return レコード
	 */
	function regist( $tableName, $data )
	{
		$db	 = GMList::getDB($tableName);

		$rec = $db->getNewRecord( $data );

		$colList = array('apply', 'employment', 'gift', 'term', 'cost' );
		foreach( $colList as $col )
		{
			$val = $db->getData($rec, $col);

			if( strlen($val) )
			{
				$val = abs((int)mb_convert_kana($val, 'n'));
				$db->setData( $rec, $col, $val );
			}
		}
		$db->setData($rec, 'id',		 SystemUtil::getNewId( $db, $tableName ) );
		if (!isset($data['disp'])) {
			$db->setData($rec, 'disp', TRUE );
		}
		$db->setData($rec, 'delete_flg', TRUE );
		$db->setData($rec, 'sort_rank',	 time() );
		$db->setData($rec, 'regist',	 time() );

		$db->addRecord( $rec );

		return $rec;
	}


	/**
	 * 指定テーブル、指定レコードの内容を変更する
	 *
	 * @param tableName テーブル名
	 * @param id レコードID
	 * @param data レコード内容
	 * @return レコード
	 */
	function edit( $tableName, $id, $data )
	{
		$db	 = GMList::getDB($tableName);

		$rec = $db->selectRecord($id);

		$colList = array( 'name','url','inquiry','faqurl','base_charge', 'apply', 'employment', 'gift', 'term', 'cost' );
		foreach( $colList as $col )
		{
			if( strlen($data[$col]) )
			{
				$val = $data[$col];
				if( $col != 'name' && $col != 'url' && $col != 'faqurl' && $col != 'inquiry' && $col != 'base_charge' ) { $val = abs((int)mb_convert_kana($data[$col], 'n')); }
				$db->setData( $rec, $col, $val );
			}
		}

		$db->updateRecord($rec);

		return $rec;
	}


	/**
	 * 指定テーブル、指定レコードを削除する
	 *
	 * @param tableName テーブル名
	 * @param id レコードID
	 */
	function delete( $tableName, $id )
	{
		$db	 = GMList::getDB($tableName);
		$rec = $db->selectRecord($id);
		// 削除可能レコードのみ削除を実行
		if( $db->getData( $rec, 'delete_flg' ) ) { $db->deleteRecord($rec); }

	}

	/**
	 * 指定テーブル、指定レコードを削除する
	 *
	 * @param tableName テーブル名
	 * @param id レコードID
	 */
	function deleteProc( $tableName, $id )
	{
		$gm = GMList::getGM("mid");
		$db = $gm->getDB();
		$table = $db->getTable();
		$table = $db->searchTable( $table , $tableName , '=' , $id );
		$row = $db->getRow($table);
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );
			$db->setData( $rec, "publish", false);
			$db->updateRecord($rec);
		}
	}
}

?>