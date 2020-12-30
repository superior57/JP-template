<?php
class at_termSystem extends System{
	function searchProc( &$gm, &$table, $loginUserType, $loginUserRank )
	{
		$type = SearchTableStack::getType();
		$db = $gm[ $type ]->getDB();

		switch($_GET["pal"]){
			case "mid":
			case "fresh":
				$table = $db->searchTable($table,"category","=",$_GET["pal"]);
				break;
			default:
				$table = $db->getEmptyTable();
		}

		$table = $db->sortTable( $table, 'sort_rank', 'asc' );
	}

	function drawSearch( &$gm, &$sr, $table, $loginUserType, $loginUserRank ){
		SearchTableStack::pushStack($table);
		$design = $this->getDesign('SEARCH_RESULT_DESIGN');
		Template::drawTemplate( $gm[ $_GET['type'] ] , null , $loginUserType , $loginUserRank , $_GET['type'] , $design );
	}

	function drawSearchNotFound( &$gm, $loginUserType, $loginUserRank )
	{
		$design = $this->getDesign('SEARCH_NOT_FOUND_DESIGN');
		if( strlen($design) )	 {
			Template::drawTemplate( $gm[ $_GET['type'] ] , null , $loginUserType , $loginUserRank , $_GET['type'] , $design );
		}
		else					 { Template::drawErrorTemplate();
		}
	}

	function drawSearchForm( &$sr, $loginUserType, $loginUserRank )
	{
		$sr->addHiddenForm( 'type', $_GET['type'] );

		$file = Template::getTemplate( $loginUserType , $loginUserRank , $_GET['type'] , 'SEARCH_FORM_PAGE_DESIGN' );
		if( strlen($file) )
			print $sr->getFormString( $file , 'search.php' );
		else
			Template::drawErrorTemplate();
	}


	function getSearchResult( &$gm, $table, $loginUserType, $loginUserRank )
	{
		$type = SearchTableStack::getType();

		$html = '';
		$design = $this->getDesign('SEARCH_LIST_PAGE_DESIGN');
		if( strlen($design) ) {
			$html = Template::getListTemplateString( $gm , $table , $loginUserType , $loginUserRank , $_GET['type'] , $design );
		}

		$this->addBuffer( $html );
	}


	function getDesign( $design )
	{
		switch($_GET['pal'])
		{
			case 'mid':  $design = $design.'_MID'; break;
			case 'fresh': $design = $design.'_FRESH'; break;
			default:		$design .= '';  break;
		}
		return $design;
	}
}