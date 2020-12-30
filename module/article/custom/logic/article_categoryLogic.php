<?PHP

class article_categoryLogic
{
	function getDataList($original_id) {
		if( !strlen($original_id) ) { return array(); }

		$subDb = GMList::getDB(articleLogic::$relationship_type);
		$subTable = $subDb->getTable();
		$subTable = $subDb->searchTable($subTable, 'original_id', 'like', $original_id);
		$subTable = $subDb->searchTable($subTable, 'relationship_type', 'like', 'category');
		$subTable = $subDb->getColumn('relationship_id', $subTable);

		$db = GMList::getDB(articleLogic::$category_type);
		$table = $db->getTable();
		$table = $db->getColumn('id', $table);
		$table = $db->addSelectColumn($table, 'name');
		$table = $db->searchTableSubQuery($table, 'id', 'in', $subTable);

		$row = $db->getRow($table);
		$recs = [];
		for($i=0; $i<$row; $i++) {
			$_tmp = $db->getRecord($table,$i);
			if(is_array($_tmp) ) {
				$recs[] = $_tmp;
			}
			unset($_tmp);
		}
		return $recs;
	}

	function update( $original_id, $categoryIds )
	{
		$oldCa = [];

		$db = GMList::getDB(articleLogic::$relationship_type);
		$table = $db->getTable();
		$table = $db->searchTable($table, 'original_id', 'like', $original_id);
		$table = $db->searchTable($table, 'relationship_type', 'like', 'category');

		$row = $db->getRow($table);
		for( $i = 0; $i<$row; $i++) {
			$rec = $db->getRecord($table, $i);
			$oldCa[ $db->getData($rec,'relationship_id') ] = true;
		}

		// 更新対象がレコードにない場合は追加
		foreach( $categoryIds as $key => $cId) {
			if( !isset($oldCa[ $cId ]) ) {
				$rec = $db->getNewRecord();
				$db->setData($rec, 'original_id', $original_id);
				$db->setData($rec, 'relationship_type', 'category');
				$db->setData($rec, 'relationship_id', $cId );
				$db->addRecord($rec);
			}
			if( isset($oldCa[ $cId ]) ) { unset($oldCa[ $cId ]); } // 更新対象になっていた場合は削除対象から除外
		}

		if( count($oldCa) > 0) { // 以前のデータで更新対象にないIDをレコードから削除
			$del = [];
			foreach( $oldCa as $cid => $val) {
				$del[] = $cid;
			}
			$db = GMList::getDB(articleLogic::$relationship_type);
			$table = $db->getTable();
			$table = $db->searchTable($table, 'original_id', 'like', $original_id);
			$table = $db->searchTable($table, 'relationship_type', 'like', 'category');
			$table = $db->searchTable($table, 'relationship_id', 'in', $del );
			$db->deleteTable($table);
		}
	}

	function childData(&$ids, &$names, $data,$target_id, $cnt) {
		if( isset($data[$target_id]['child']) ) {
			foreach( $data[$target_id]['child'] as $child_id) {
				$ids[] = $child_id;
				$pref = "";
				for( $i=0; $i<$cnt; $i++) { $pref .= "ー"; }
				$names[] = $pref.$data[$child_id]['name'];
				self::childData($ids, $names, $data,$child_id, $cnt+1);
			}
		}
	}
}