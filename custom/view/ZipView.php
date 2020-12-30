<?PHP

class ZipView extends command_base
{
	/**
	 * 郵便番号からの住所検索ボタンを表示
	 *
	 * @param kind normal/interview/job
	 */
	function drawButton( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************

		// IEMobile/IE9 はシングルタスクなので除外
		if( preg_match('/IEMobile/i', $_SERVER['HTTP_USER_AGENT']) ) { return; }

		$kind = $args[0];
		$design	 = Template::getTemplate( $loginUserType, $loginUserRank, 'zip', 'BUTTON_DESIGN' );
		$buffer	.= $gm->getString( $design, null, $kind );

		$this->addBuffer( $buffer );
	}


	/**
	 * 地域チェックボックスを表示
	 *
	 */
	function drawList( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		// **************************************************************************************

		if( isset($_GET['zip']))
		{
			$db	 = GMList::getDB('zip');
			$table = $db->getTable();
			$table = $db->searchTable(  $table, 'zip', '=' , $_GET['zip'] );

			$row = $db->getRow($table);
			if( $row != 0 )
			{
				$design	 = Template::getTemplate( $loginUserType, $loginUserRank, $_GET['type'], 'ZIP_DRAW_LIST' );
				$buffer	.= $gm->getString( $design, null, 'head' );
				for($i=0;$i<$row;$i++){
					$rec = $db->getRecord( $table , $i );
					$gm->setVariable( 'num' , $i );
					$buffer	.= $gm->getString( $design, $rec, 'row' );
				}

				$buffer	.= $gm->getString( $design, null, 'foot' );
			}
			else
			{// マッチ無し
				$design	 = Template::getTemplate( $loginUserType, $loginUserRank, 'notfound', 'ZIP_DRAW_FALED' );
				$buffer	.= $gm->getString( $design );
			}
		}
		else
		{ // 指定無し
			$design	 = Template::getTemplate( $loginUserType, $loginUserRank, 'notvalue', 'ZIP_DRAW_FALED' );
			$buffer	.= $gm->getString( $design );
		}

		$this->addBuffer( $buffer );
	}

}

?>