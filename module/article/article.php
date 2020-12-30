<?PHP

class mod_article extends command_base
{
	function drawCheckbox( &$gm, $rec, $args )
	{
		$col = $args[0] ? $args[0] : 'category';

		$db = GMList::getDB(articleLogic::$category_type);
		$table = $db->getTable();
		$table = $db->sortTable($table, 'parent', 'asc');
		$row = $db->getRow($table);
		if( $row > 0)
		{
			$data = [];
			for( $i=0; $i<$row; $i++ )
			{
				$rec = $db->getRecord( $table, $i );

				$id = $db->getData( $rec, 'id' );
				$name = articleLogic::ccEscape( $db->getData( $rec, 'name' ) );
				$parent = $db->getData( $rec, 'parent' );
				$data[$id]['name']   = $name;
				$data[$id]['parent'] = $parent;
				if( strlen($parent) ) {
					$data[ $parent ]['child'][] = $id;
				}
			}
			$ids = [];
			$names = [];
			foreach( $data as $caId => $ca)
			{
				if( !strlen( $ca['parent'] )) {
					$ids[] = $caId;
					$names[] = $ca['name'];
					article_categoryLogic::childData($ids, $names, $data, $caId, 1);
				}
			}
			$buffer = $gm->getCCResult( null, '<!--# form checkbox '.$col.' '.$def.'  '.implode('/',$ids).' '.implode('/',$names).' #-->' );
		}
		else {
			$buffer = 'カテゴリーが設定されていません。';
		}
		$this->addBuffer( $buffer );
	}

}