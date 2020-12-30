<?PHP


class messageView extends command_base
{
	function drawMessageList(&$_gm, $_rec, $_args){
		global $loginUserType;
		global $loginUserRank;
		global $LOGIN_ID;
		List($owner,$readhead,$thread_id) = $_args;

		if($loginUserType != "admin" && !$_SESSION['ADMIN_MODE'])
			messageLogic::setReadFlg($thread_id,$LOGIN_ID);

		$template = Template::getTemplate($loginUserType, $loginUserRank, $this->getType(), "MESSAGE_LIST");

		$gm = GMList::getGM($this->getType());
		$db = $gm->getDB();
		$table = $db->getTable();
		//$table = $db->searchTable($table,"items_id","=",$items_id);
		$table = $db->searchTable($table,"thread_id","=",$thread_id);
		$tableA = $db->searchTable($table,"owner","=",$owner);
		$tableB = $db->searchTable($table,"destination","=",$owner);
		$table = $db->orTable($tableA, $tableB);
		$table=$db->sortTable($table,"regist","asc");
		$row = $db->getRow($table);

		for($i=0;$i<$row;$i++){
			$rec = $db->getRecord($table, $i);
			$buffer .= $gm->getString($template,$rec,$readhead);
		}

		$this->addBuffer($buffer);
	}

	//ユーザーが送受信しているメッセージのitemsIDを描画
	function drawDialogueItemsID( &$_gm,$_rec,$_args){
		List($user_id) = $_args;
		$db = GMList::getDB($this->getType());
		$table = $db->getTable();
		switch($_GET["pal"]){
			case "sendbox";
				$table = $db->searchTable($table,"owner","=",$user_id);
				break;
			case "recbox";
				$table = $db->searchTable($table,"destination","=",$user_id);
				break;
			default;
				$table = $db->getEmptyTable();
				break;
		}
		$table = $db->getDistinctColumn("items_id", $table);
		$items_id = $db->getDataList($table, "items_id");
		$items_id = implode("/",$items_id);
		$this->addBuffer($items_id);
	}

	function getMessage4index(&$_gm, $_rec, $_args){
		global $loginUserType;
		global $loginUserRank;
		global $LOGIN_ID;

		$template = Template::getTemplate($loginUserType, $loginUserRank, $this->getType(), "MESSAGE_LIST");

		$gm = GMList::getGM($this->getType());
		$db = $gm->getDB();
		$table = $db->getTable();
		$table = $db->searchTable($table,"destination","=",$LOGIN_ID);
		$table=$db->sortTable($table,"regist","desc");
		$table = $db->limitOffset( $table,0,5);
		$row = $db->getRow($table);

		for($i=0;$i<$row;$i++){
			$rec = $db->getRecord($table, $i);
			$buffer .= $gm->getString($template,$rec,"new");
		}

		$this->addBuffer($buffer);


	}

	function getItemsList(&$_gm, $_rec, $_args){
		global $loginUserType;
		global $loginUserRank;
		global $LOGIN_ID;

		list($name,$type,$draw) = $_args;

		$template = Template::getTemplate($loginUserType, $loginUserRank, $this->getType(), "BOTH_JOB_LIST");

		if( isset($_POST[$name])){
			$initial = h($_POST[$name]);
		}else if( isset($_GET[$name]) ){
			$initial = h($_GET[$name]);
		}

		$gm = GMList::getGM("mid");
			$db = $gm->getDB();
			$table = $db->getTable();

		switch($loginUserType){
			case "admin":
				break;
			case "cUser":
				$table = $db->searchTable($table,"owner","=",$LOGIN_ID);

				if($draw != "all"){
					$table = JobLogic::getTable("mid",$table,$_GET);
				}
				break;
			default:
		}


		$table=$db->sortTable($table,"regist","asc");
			$row = $db->getRow($table);

		$name = empty($name)?"file_name":$name;
		$gm->setVariable("name",$name);

		$buffer=$gm->getString($template,"","head");

		if($type == "mid" || $type == "both"){
			$buffer.=$gm->getString($template,"","group_mid_head");
			for($i=0;$i<$row;$i++){
				$rec = $db->getRecord($table, $i);
				if($db->getData($rec,"id") == $initial)
					$buffer .= $gm->getString($template,$rec,"list_selected");
				else
				$buffer .= $gm->getString($template,$rec,"list");
			}
			$buffer.=$gm->getString($template,"","group_mid_foot");
		}

		$gm = GMList::getGM("fresh");
			$db = $gm->getDB();
			$table = $db->getTable();

		switch($loginUserType){
			case "admin":
				break;
			case "cUser":
				$table = $db->searchTable($table,"owner","=",$LOGIN_ID);

				if($draw != "all"){
					$table = JobLogic::getTable("fresh",$table,$_GET);
				}
				break;
			default:
		}


		$table=$db->sortTable($table,"regist","asc");
			$row = $db->getRow($table);

		if($type == "fresh" || $type == "both"){
			$buffer.=$gm->getString($template,"","group_fresh_head");
			for($i=0;$i<$row;$i++){
				$rec = $db->getRecord($table, $i);
				if($db->getData($rec,"id") == $initial)
					$buffer .= $gm->getString($template,$rec,"list_selected");
				else
				$buffer .= $gm->getString($template,$rec,"list");
			}
			$buffer.=$gm->getString($template,"","group_fresh_foot");
		}
		$buffer.=$gm->getString($template,"","foot");

		$this->addBuffer($buffer);
	}

	function drawUserList4MOS( &$_gm, $_rec, $_args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		global $LOGIN_ID;
		// **************************************************************************************

		$gm = GMList::getGM($this->getType());
		$db = $gm->getDB();
		$table=$db->getTable();
		$table=$db->searchTable($table,"items_id","=",$_args[0]);

		//SQL最適化のためコメント
		//$max = $db->getMaxTable( "regist", "thread_id", $table);
	    //$table = $db->joinTableSubQuerySQL($table,$max,"x","x.max=message.regist and x.thread_id=message.thread_id");

		//ここから最適化部分
		$column = array("shadow_id","id","owner","owner_type","file","destination","sub","message","read_flg","sender_del","mailtype","declination_scout","regist","edit");
		$table = $db->getMaxTable( "regist", "thread_id", $table,"string","max(regist)");
		$table = $db->addSelectColumn($table,implode(",",$column),false);
		//ここまで最適化部分

		$table = $db->addGroupColumn($table,"destination");
		$table=$db->sortTable($table,"regist","desc");
		$row=$db->getRow($table);


		$design = Template::getLabelFile( 'MESSAGE_DATA_LIST' );
		for($i=0;$i<$row;$i++){
			$rec=$db->getRecord($table,$i);
			$html .= $gm->getString( $design , $rec , "mos_list" );
		}

		$this->addBuffer($html);

	}

	function getMessageCount( &$_gm, $_rec, $_args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		global $LOGIN_ID;
		// **************************************************************************************

		$gm = GMList::getGM($this->getType());
		$db = $gm->getDB();
		$table=$db->getTable();
		$table=$db->searchTable($table,"items_id","=",$_args[0]);

		//SQL最適化のためコメント
		//$max = $db->getMaxTable( "regist", "thread_id", $table);
		//$table = $db->joinTableSubQuerySQL($table, $max, "x", "x.max=" . $db->tableName . ".regist and x.thread_id=" . $db->tableName . ".thread_id");

		//ここから最適化部分
		$column = array("shadow_id","id","owner","owner_type","file","destination","sub","message","read_flg","sender_del","mailtype","declination_scout","regist","edit");
		$table = $db->getMaxTable( "regist", "thread_id", $table,"string","max(regist)");
		$table = $db->addSelectColumn($table,implode(",",$column),false);
		//ここまで最適化部分

		$table = $db->addGroupColumn($table,"destination");
		$table=$db->sortTable($table,"regist","desc");
		$row=$db->getRow($table);


		$this->addBuffer($row);

	}

	function getType(){
		return "message";
	}


	function drawMessageListNewOne( &$_gm, $_rec, $_args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		global $LOGIN_ID;
		// **************************************************************************************


		$gm = GMList::getGM("message");
		$db = $gm->getDB();
		$table=$db->getTable();
		$table=$db->searchTable($table,"thread_id","=",$_args[0]);
		$table=$db->searchTable($table,"offer_id","!","");
		$table=$db->sortTable($table,"regist","desc");
		$table=$db->getDistinct($table);
		$table = $db->limitOffset( $table,0,1);
		$row=$db->getRow($table);

		for($i=0;$i<$row;$i++){
			$rec=$db->getRecord($table,$i);
			$html = $db->getData($rec,"offer_id");
		}

		$this->addBuffer($html);

	}

	function drawUnReadMessageCnt(&$_gm, $_rec, $_args){
		List($userID) = $_args;

		$gm = GMList::getGM("message");
		$db = $gm->getDB();
		$table	= $db->getTable();
		$table	= $db->searchTable(  $table, 'destination', '=', $userID	);
		$table	= $db->searchTable(  $table, 'read_flg', '=', false	);
		$table = $db->getCountTable("thread_id",$table);

		$row = $db->getRow($table);
		$this->addBuffer($row);
	}

	function drawOfferList4Offer( &$_gm, $_rec, $_args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		global $loginUserRank;
		global $LOGIN_ID;
		// **************************************************************************************

		List($thread_id) = $_args;

		$gm = GMList::getGM("message");
		$db = $gm->getDB();
		$table=$db->getTable();
		$table=$db->searchTable($table,"thread_id","=",$thread_id);
		$table=$db->searchTable($table,"offer_id","!","");
		$table=$db->sortTable($table,"regist","desc");
		$table=$db->getDistinct($table);

		$row=$db->getRow($table);

		$design = Template::getLabelFile( 'OFFER_DATA_LIST' );
		for($i=0;$i<$row;$i++){
			$rec=$db->getRecord($table,$i);
			$db->setData($rec,"count",$i);
			$html .= $gm->getString( $design , $rec , "default_list" );
		}

		$this->addBuffer($html);

	}

}

?>