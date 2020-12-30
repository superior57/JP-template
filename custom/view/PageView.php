<?PHP

class PageView extends command_base
{
	/**
	 * リンクを表示
	 *
	 */
	function drawLink( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		global $terminal_type;
		global $sp_mode;
		// **************************************************************************************
		$design	  = Template::getTemplate( $loginUserType, $loginUserRank, 'page', 'LINK_DESIGN' );
		$terminal = ( $sp_mode ? 'smartphone' : ( $terminal_type ? 'mobile' : 'pc' ) );

		$db = GMList::getDB('page');
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'authority', '=', '%'.$loginUserType.'%' );
		$table = $db->searchTable( $table, 'link', '!', 'no' );
		$table = $db->searchTable( $table, 'link_terminal', '!', '%' . $terminal . '%' );
		$table = $db->searchTable( $table, 'open', '=', true );

		if(class_exists("mod_special")) $table = $db->searchTable( $table, 'mode', '!', 'special' );

		$table = $db->sortTable( $table, 'link_sort', 'asc' );
		
		$row = $db->getRow($table);
		if($row > 0)
		{
			$buffer	.= $gm->getString( $design, $rec, 'head' ); 
			for( $i=0; $i<$row; $i++ )
			{
				$rec = $db->getRecord( $table, $i );
				$buffer	.= $gm->getString( $design, $rec, $db->getData($rec,'link') ); 
			}
			$buffer	.= $gm->getString( $design, $rec, 'foot' ); 
			
		}
		
		$this->addBuffer( $buffer );
	}

	function drawPage(&$gm, $rec, $args){
		List($name) = $args;
		$buffer = "";
		$_GET["p"] = $name;

		$model = new AppPageModel();
		$view = new AppPageView();
		$model->searchPage();
		if($model->hasSearchResult()){
			$view->setBuffer($model);
			$buffer = $view->getBuffer();
		}

		$_GET["p"] = null;
		$this->addBuffer($buffer);
	}
	
}

?>