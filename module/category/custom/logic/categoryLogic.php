<?php

class CategoryLogic
{
	/**
	 * 指定タイプのテーブルを取得する
	 *
	 * @param type items_form/items_type/job_addtion
	 * @return table
	 */
	function getTableByType($type)
	{
		$db = GMList::getDB($type);
		
		$table = $db->getTable();
		$table = $db->sortTable( $table, 'sort_rank', 'asc' );
		
		return $table;
	}

	/**
	 * 課金パラメータを返す
	 *
	 * @param type items_form/items_type/job_addtion
	 * @return table
	 */
	function getChargesParam( $id, $charges )
	{
		$db = GMList::getDB('items_form');
		$rec = $db->selectRecord($id);
		
		$param['cost'] = 0;
		$param['gift'] = 0;
		if(isset($rec))
		{
			$param['cost'] = $db->getData( $rec, $charges );
			if( Conf::checkData('charges', 'gift', $charges ) )
			{
				$param['gift'] = $db->getData( $rec, 'gift' );
			}
		}

		return $param;
	}

}

?>