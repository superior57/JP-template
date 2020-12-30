<?php

class Count
{
	var $db;
	var $userType;
	var $itemsType;

	/**
	 * 指定テーブルのカウントDBの作成とユーザータイプの記録
	 *
	 * @param dbName カウントテーブル名
	 * @param userType nUser/nobody
	 */
	function __construct( $dbName, $userType, $itemsType ){
		$this->db = GMList::getDB($dbName.'_count');
		$this->userType = $userType;
		$this->itemsType = $itemsType;
	}

	/**
	 * カウントデータを登録
	 *
	 * @param id 対象ID
	 * @param cnt カウント数
	 */
	function regist( $id, $cnt )
	{
		$db = $this->db;
		
		$rec = $db->getNewRecord();
		$db->setData( $rec, 'target_id', $id );
		$db->setData( $rec, 'user_type', $this->userType );
		$db->setData( $rec, 'items_type', $this->itemsType );
		$db->setData( $rec, 'cnt', $cnt );

		$db->addRecord($rec);
	}

	/**
	 * 一括更新時用にデータの全削除
	 */
	function deleteAll()
	{
		$db = $this->db;
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'user_type', '=', $this->userType );
		if( $db->getRow($table) > 0 ) { $db->deleteTable($table); }
	}

	/**
	 * カウント数配列を返す
	 *
	 * @return カウント数配列
	 */
	function getDataList()
	{
		$db = $this->db;
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'user_type', '=', $this->userType );
		$table = $db->searchTable( $table, 'items_type', '=', $this->itemsType );
		$row = $db->getRow($table);
		

		$countList = array();
		for( $i=0; $i<$row; $i++ )
		{
			$rec = $db->getRecord( $table, $i );
			$countList[ $db->getData($rec, 'target_id') ] = $db->getData($rec, 'cnt');
		}

		return $countList;
	}

	/**
	 * カウント数を更新
	 *
	 * @param dataList カウントデータ配列
	 */
	function update( $dataList )
	{
		//$this->deleteAll();

		foreach( $dataList as $id => $cnt ) {
			if(empty($id))
				{ $id = "foreign"; }
			$this->regist( $id, $cnt );
		}
	}

}

?>