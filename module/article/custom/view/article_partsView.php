<?php
class article_partsView extends command_base
{

	/*
	 * itemsに登録されている内容(Parts)をすべて表示する
	 * itemsの編集画面で使用
	 */
	function drawEditParts( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************

		$db = GMList::getDB(articleLogic::$type);
		$design	  = Template::getTemplate( $loginUserType, $loginUserRank, articleLogic::$type, 'PARTS_DESIGN' );

		$buffer = "";
		$parts = explode('/', $db->getData($rec,'parts'));

		foreach( $parts as $i => $pID ) {
			if( strlen($pID) > 0) {
				$pdb = GMList::getDB( articleLogic::$sub_type );
				$prec = $pdb->selectRecord( $pID );
				if( is_array($prec) ) { $buffer .= article_partsLogic::draw(array('rec'=>$prec),$design, true); }
			}
		}
		$this->addBuffer($buffer);
	}

	/*
	 * 記事のパーツをバッファーに追加する
	 * 
	 */
	function drawListPartView(&$gm, $design, $original_id, $start=null, $cnt=null, $edit=false){

		if( strlen($original_id) == 0 ) { return ; }

		$db = GMList::getDB(articleLogic::$sub_type);

		$table = $db->getTable();
		$table = $db->searchTable( $table, 'original_id', '=', $original_id );

		if($start >0 ||$cnt >0 ){
			$table = $db->limitOffset(  $table, $start, $cnt  );
		}

		$row = $db->getRow($table);
		$buffer = "";

		if(strlen($row) > 0)
		{
			for( $i=0; $i<$row; $i++ )
			{
				$rec = $db->getRecord( $table, $i );
				$buffer .= article_partsLogic::draw(array('rec'=>$rec),$design, $edit);
			}
		}
		return $buffer;
		
	}
}