<?PHP

class article_tagLogic
{
	function getDataList($original_id) {
		if( !strlen($original_id) ) { return array(); }

		$subDb = GMList::getDB(articleLogic::$relationship_type);
		$subTable = $subDb->getTable();
		$subTable = $subDb->searchTable($subTable, 'original_id', 'like', $original_id);
		$subTable = $subDb->searchTable($subTable, 'relationship_type', 'like', 'tag');
		$subTable = $subDb->getColumn('relationship_id', $subTable);

		$db = GMList::getDB(articleLogic::$tag_type);
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

	function update( $original_id, $word, $ope='update' )
	{
		$result = [];

		$word = preg_replace('/[^ぁ-んァ-ンーa-zA-Z0-9一-龠０-９\-\r]+/u','' ,mb_convert_kana($word, "a"));// エスケープ
		if( strlen($word) == 0 ) {
			$result['status'] = 'error';
			$result['msg'] = 'タグを入力してください。';
			return $result;
		}

		$db = GMList::getDB(articleLogic::$tag_type);
		$table = $db->getTable();
		$table = $db->getColumn('id', $table);
		$table = $db->searchTable($table, 'name', 'like', $word);
		if( $db->existsRow($table)) {
			$rec = $db->getFirstRecord($table);
		}
		else {
			$rec = $db->getNewRecord();
			$db->setData($rec,'name',$word);
			$db->addRecord($rec);
		}
		$tagId = $db->getData($rec, 'id');
		unset($db);unset($table);unset($rec);

		if( !strlen($tagId) ) {
			$result['status'] = 'error';
			$result['msg'] = 'データベースエラー';
		}
		else {

			$db = GMList::getDB(articleLogic::$relationship_type);
			$table = $db->getTable();
			$table = $db->searchTable($table, 'original_id', 'like', $original_id);
			$table = $db->searchTable($table, 'relationship_type', 'like', 'tag');
			$table = $db->searchTable($table,'relationship_id', 'like', $tagId);
			$check = $db->existsRow($table);
			switch ( $ope ) {
				case 'update':
					if( !$check ) { // データがないので登録
						$rec = $db->getNewRecord();
						$db->setData($rec, 'original_id', $original_id);
						$db->setData($rec, 'relationship_type', 'tag');
						$db->setData($rec, 'relationship_id', $tagId );
						$db->addRecord($rec);
						$result['status'] = 'success';
						$result['word'] = ($word);
					}
					else {
						$result['status'] = 'error';
						$result['msg'] = '登録済みです';
					}
					break;
				case 'delete':
					if( $check ) { // データがあるので削除
						$rec = $db->getFirstRecord($table);
						$db->deleteRecord($rec);
						$result['status'] = 'success';
						$result['word'] = ($word);
					}
					else {
						$result['status'] = 'error';
						$result['msg'] = '指定されたタグは存在しません。';
					}
					break;
			}
		}
		return $result;
	}

}