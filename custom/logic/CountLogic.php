<?php

class CountLogic
{
	/**
	 * GETパラメータを基にテーブルを取得し、指定カラムをカウントする。
	 *
	 * @param column 集計を行うカラム名
	 * @param param 検索パラメータ
	 * @return 集計データ配列
	 */
	static function controller( $dbName, $column, $param = null, $userType = null )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		// **************************************************************************************

		$countList = array();
		if( !is_array($userType) ) { $userType = $loginUserType; }

		switch($dbName)
		{
		case 'job':
		default:
			$db		 = GMList::getDB($dbName);
			if($_GET["label"]=="countUpdate"){
				$table = $db->getTable();
			}else{
				$table=null;
			}
			$table	 = JobLogic::getTable( $dbName ,$table, $param, $userType );


			switch($column)
			{
			case 'line':
				$table = $db->searchTable( $table, 'adds', '=', '%'.$param['parent'].'%' );
				$columnList	 = array( 'traffic1_line', 'traffic2_line', 'traffic3_line', 'traffic4_line', 'traffic5_line' );
				$countList	 = self::getCountListMalti( $db, $table, $columnList);
				break;
			case 'station':
				$table = $db->searchTable( $table, 'adds', '=', '%'.$param['pref'].'%' );
				$columnList	 = array( 'traffic1_station', 'traffic2_station', 'traffic3_station', 'traffic4_station', 'traffic5_station' );
				$countList	 = self::getCountListMalti( $db, $table, $columnList );
				break;
			case 'addition':

				$countList	 = self::getCountListTable( $db, $table, $column, 'job_addition' );
				break;
			case 'job_addition':

				$countList	 = self::getCountListTable( $db, $table, $column, 'job_addition' );
				break;
			//case 'adds':
			//case 'add_sub':
			default:
				if($column=="items_type") $column="category";
				if($column=="items_form") $column="work_style";
				if($column=="adds") $column="work_place_adds";
				if($column=="add_sub") $column="work_place_add_sub";
				//echo $column."<br />";
				$countList	 = self::getCountList( $db, $table, $column );
				break;
			}
			break;

		}


		return $countList;
	}


	/**
	 * 指定カラムをカウントする。
	 *
	 * @param db dbオブジェクト
	 * @param table 集計を行うテーブル
	 * @param column 集計を行うカラム名
	 * @return 集計データ配列
	 */
	static function getCountList( $db, $table, $column )
	{
		$table	 = $db->getCountTable( $column, $table );
		$row	 = $db->getRow($table);

		$countList = array();
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );

			$countList[$db->getData($rec, $column)] = $db->getData( $rec, 'cnt' );
		}

		return $countList;
	}


	/**
	 * 指定カラム郡を統合してカウントする。
	 *
	 * @param db dbオブジェクト
	 * @param table 集計を行うテーブル
	 * @param columnList 集計を行うカラム名配列
	 * @return 集計データ配列
	 */
	static function getCountListMalti( $db, $table, $columnList )
	{
		$table = $db->getColumn( 'id', $table );
		while( $columnList ) { $tableList[] = $db->addSelectColumn( $table, array_shift($columnList).' as cnt_col' ); }

		$table = array_shift($tableList);
		while( $tableList ) { $table = $db->unionTable( $table, array_shift($tableList) ); }

		$cntTable = $db->getCountTable( 'cnt_col', $db->getTable() );
		$cntTable->from = '('. $table->getString() .') cnt_table';

		$table = $cntTable;
		$row   = $db->getRow($table);

		$countList = array();
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );

			$countList[$db->getData($rec, 'cnt_col')] = $db->getData( $rec, 'cnt' );
		}

		return $countList;
	}


	/**
	 * like検索にてカウントする。
	 *
	 * @param db dbオブジェクト
	 * @param table 集計を行うテーブル
	 * @param column 集計を行うカラム名
	 * @param colList 集計対象リスト
	 * @return 集計データ配列
	 */
	static function getCountListLike( $db, $table, $column, $idList )
	{
		if($column=="job_addition") $column="addition";

		$countList = array();
		foreach( $idList as $id )
		{
			$countList[$id] = $db->getRow( $db->searchTable( $table, $column, '=', '%'.$id.'%' ) );
		}

		return $countList;
	}


	/**
	 * テーブル情報を元に指定絡むをカウントする。
	 *
	 * @param db dbオブジェクト
	 * @param table 集計を行うテーブル
	 * @param column 集計を行うカラム名
	 * @param tableName 集計対象IDを取得するテーブル名
	 * @return 集計データ配列
	 */
	static function getCountListTable( $db, $table, $column, $tableName )
	{
		$idList = self::getIdList( $tableName );

		return self::getCountListLike( $db, $table, $column, $idList );
	}


	/**
	 * IDリストを取得する。
	 *
	 * @param tableName IDを取得するテーブル名
	 * @return ID配列
	 */
	static function getIdList($tableName)
	{
		$db = GMList::getDB($tableName);
		$table = $db->getTable();
		$row = $db->getRow($table);

		$idList = array();
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );
			$id  = $db->getData( $rec, 'id' );
			$idList[$id] = $id;
		}

		return $idList;
	}

}

?>