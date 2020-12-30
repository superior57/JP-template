<?php
class resumeView extends command_base{

	//希望勤務地の一件目を表示
	function drawContinuationHopeWorkPlace( &$_gm, $_rec, $_args ){
		List($label) = $_args;
		$strs = explode("<br/>" ,$label);
		$this->addBuffer($strs[0]);
	}

	function drawExistsResume( &$_gm, $_rec, $_args ){
		List($userID) = $_args;
		$result = resumeLogic::existsResume($userID) ? "TRUE" : "FALSE";
		$this->addBuffer($result);
	}

	function drawEntryStatus( &$_gm, $_rec, $_args ){
		List($thread_id) = $_args;
		if(messageLogic::checkEntry($thread_id)){
			$this->addBuffer("Entry");
		}else{
			$this->addBuffer("NotEntry");
		}
	}

	function drawColmunStr( &$_gm, $_rec, $_args ){
		global $loginUserType;
		global $loginUserRank;

		List($id,$column,$label) = $_args;

		$design = Template::getTemplate($loginUserType, $loginUserRank, $this->getType(), "COLMUN_STR_PART");



		$gm = GMList::getGM($this->getType());
		$db = $gm->getDB();
		$table = $db->getTable();
		$table = $db->searchTable($table,"id","=",$id);
		$rec = $db->getFirstRecord($table);
		$data = $db->getData($rec,$column);



		if(empty($data)){ return; }

		switch($column){
			case "hope_work_place";
				$hope_work_place = explode("/", $data);
				foreach($hope_work_place as $val){
					$place = explode(",", $val);
					$adds = array_shift($place);
					$add_sub = array_shift($place);
					$gm->setVariable("adds",$adds);
					$gm->setVariable("add_sub",$add_sub);
					$str .= $gm->getString($design,$rec,$label);
				}
				break;
			default;
				$str = "";
				break;
		}
		$this->addBuffer($str);


	}

	function drawColmunStr4Regist( &$_gm, $_rec, $_args ){
		global $loginUserType;
		global $loginUserRank;

		List($data,$column,$label) = $_args;

		$design = Template::getTemplate($loginUserType, $loginUserRank, $this->getType(), "COLMUN_STR_PART");


		$gm = GMList::getGM($this->getType());

		if(empty($data)){ return; }

		switch($column){
			case "hope_work_place";
				$hope_work_place = explode("/", $data);
				foreach($hope_work_place as $val){
					$place = explode(",", $val);
					$adds = array_shift($place);
					$add_sub = array_shift($place);
					$gm->setVariable("adds",$adds);
					$gm->setVariable("add_sub",$add_sub);
					$str .= $gm->getString($design,$rec,$label);
				}
				break;
			default;
				$str = "";
				break;
		}
		$this->addBuffer($str);


	}

	function getPublishData( &$_gm, $_rec, $_args ){
		global $loginUserType;
		global $loginUserRank;

		List($nUserId,$column) = $_args;

		$gm = GMList::getGM($this->getType());
		$db = $gm->getDB();
		$table = $db->getTable();
		$table = $db->searchTable($table,"owner","=",$nUserId);
		$table = $db->searchTable($table,"publish","=","on");

		$rec = $db->getFirstRecord($table);
		$str = $db->getData($rec,$column);

		$this->addBuffer($str);
	}

	//企業の所在地と一致した求職希望者をカウントする
	function drawMatchCount( &$_gm, $_rec, $_args ){
		List($cUserID) = $_args;

		$foreignFlg = SystemUtil::getTableData("cUser", $cUserID, "foreign_flg");
		if($foreignFlg){
			$address = SystemUtil::getTableData("cUser", $cUserID, "foreign_address");
		}else{
			$adds = SystemUtil::getTableData("cUser", $cUserID, "adds");
			$address = SystemUtil::getTableData("adds", $adds, "name");
		}

		$db = GMList::getDB(self::getType());
		$table = resumeLogic::getTable();
		$table = $db->searchTable($table,"hope_work_place_label","=","%".$address."%");
		$this->addBuffer($db->getRow($table));
	}

	function getType(){
		return "resume";
	}
}