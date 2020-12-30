<?php

class GiftView extends command_base
{
	/**
	 * 申請可能な求人一覧を表示
	 */
	function getItemsList4Gift(&$_gm, $_rec, $_args){
		global $loginUserType;
		global $loginUserRank;
		global $LOGIN_ID;

		$template = Template::getTemplate($loginUserType, $loginUserRank, "", "BOTH_JOB_LIST");
		
		$gm = GMList::getGM("entry");
		$db = $gm->getDB();
		$table = $db->getTable();
		$table = $db->searchTable($table,"entry_user","=",$LOGIN_ID);
		$table = $db->searchTable(  $table, 'status', '=','SUCCESS' );
		$table=$db->sortTable($table,"items_type","asc");
		$row = $db->getRow($table);
		
		$buffer=$gm->getString($template,"","unknown");
		
		if($row>0){
			
			$buffer=$gm->getString($template,"","head");
			if($db->getData($db->getRecord($table, 0),"items_type")=="mid"){
				$buffer.=$gm->getString($template,"","group_items_head");
			}else{
				$buffer.=$gm->getString($template,"","group_fresh_head");
			}
			
			for($i=0;$i<$row;$i++){
				$rec = $db->getRecord($table, $i);
				$next_rec = $db->getRecord($table, $i+1);
				
				$buffer .= $gm->getString($template,$rec,"list_gift");
				
				if($db->getData($next_rec,"items_type")=="fresh" && $db->getData($rec,"items_type")=="mid"){
					$buffer.=$gm->getString($template,"","group_items_foot");
					$buffer.=$gm->getString($template,"","group_fresh_head");
				}
				
				
			}
			$buffer.=$gm->getString($template,"","group_fresh_foot");
			$buffer.=$gm->getString($template,"","foot");
	
			
			
		}
		$this->addBuffer($buffer);

	}


	/**
	 * 未ログインユーザーのメールアドレスを表示
	 */
	function drawMail( &$gm , $rec , $args )
	{
		$buffer = GiftLogic::getMailAddressByMd5( $_GET['cd'] );
		
		$this->addBuffer( $buffer );
	}


	/**
	 * 申請者名をを表示
	 */
	function drawName( &$gm , $rec , $args )
	{
		$db = GMList::getDB('gift');
		
		$nuser = $db->getData( $rec, 'nuser' );
		$entry = $db->getData( $rec, 'entry' );
		
		$id = $entry;
		$tableName = 'entry';
		if(strlen($nUser))
		{
			$id = $nuser;
			$tableName = 'nUser';
		}
		$name = SystemUtil::getTableData( $tableName, $id, 'name' );
		
		$this->addBuffer( $name );
	}


	/**
	 * お祝い金額をを表示
	 */
	function drawGift( &$gm , $rec , $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $ACTIVE_NONE;
		global $ACTIVE_ACCEPT;
		// **************************************************************************************

		$activate = $args[0];

		$db = GMList::getDB('gift');
		
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'activate', '=', $activate );
		
		$gift = $db->getSum( 'money', $table );
		
		$this->addBuffer( number_format($gift) );
	}


	/**
	 * 求人情報にてお祝い金額をを表示
	 */
	function drawGiftFromJob( &$gm , $rec , $args )
	{
		static $gift;

		$type = $args[0];
		$id = $args[1];
		
		if( !is_array($gift) ) { $gift = explode( '/', Conf::getData( 'charges', 'gift' ) ); }

		$param = JobLogic::getParamForEntry($type,$id); // 応募用データ取得から課金種別取得を流用

		$result = 'off';
		if( in_array( $param['term_type'], $gift ) ) { $result = 'on'; }
		$this->addBuffer( $result );
	}

}
?>
