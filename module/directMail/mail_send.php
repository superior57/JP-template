<?php

class mod_mailSend extends command_base{

	static $isEnd = false;
	static $sendListDesign = null;
	static function setSendListDesign(){
		global $loginUserType;
		global $loginUserRank;
		self::$sendListDesign = Template::getTemplate($loginUserType, $loginUserRank, self::getType(), "MAIL_SEND_LIST_PARTS");
	}

	/*
	 *  配信先一覧を表示
	 *  $args[0] レコードid
	 */
	function drawSendUserList( &$_gm , $_rec , $_args ){
		List($id) = $_args;
		self::setSendListDesign();
		$gm = GMList::getGM($this->getType());

		$buffer  = $this->getSendListHead($gm);
		$buffer .= $this->getSendListRows($id);
		$buffer .= $this->getSendListFoot($gm);

		$this->addBuffer($buffer);
	}

	function isResendable( &$_gm , $_rec , $_args ){
		List($id) = $_args;
		$db = GMList::getDB($this->getType());
		$rec = $db->selectRecord($id);
		$flg = $db->getData($rec,"reserve_flag");
		$time = $db->getData($rec,"reserve_time");

		$sendFlag = $db->getData($rec,"send_f");

		if((!$flg || $flg && $time < time()) && !$sendFlag){
			$this->addBuffer("TRUE");
		}else{
			$this->addBuffer("FALSE");
		}
	}

	static function getSendListHead($gm)
		{ return $gm->getString(self::$sendListDesign,null,"head"); }

	static function getSendListFoot($gm){
		if(self::$isEnd)$sufix = "_nobutton";
			return $gm->getString(self::$sendListDesign,null,"foot".$sufix);
	}

	//送信リストの行を取得
	static function getSendListRows($id,$current = 0){
		global $loginUserType;
		global $loginUserRank;
		global $LIST_OFFSET;
		$design = Template::getTemplate($loginUserType, $loginUserRank, self::getType(), "MAIL_SEND_LIST_PARTS");

		$gm = GMList::getGM(self::getType());
		$db = $gm->getDB();
		$rec = $db->selectRecord($id);
		$user_id = mod_list::getMailReceiveList($db->getData($rec,"user_type"),$db->getData($rec,"list_id"));

		for($i = 0 ; $i < $LIST_OFFSET ;$i++){
			if( !empty($user_id[$current*$LIST_OFFSET+$i]) ){
				$gm->setVariable("userID", $user_id[$current*$LIST_OFFSET+$i]);
				$buffer .= $gm->getString($design,$rec,"list");
			}
			if(empty($user_id[$current*$LIST_OFFSET+$i+1]))
				{ self::$isEnd = true; }
		}
		return $buffer;
	}

	function drawUnSentUserList( &$gm , $_rec , $args ){
		List($mailSendID) = $args;
		$userList = mailSend::getUnSentUserList($mailSendID);
		$this->addBuffer(implode("/", $userList));
	}

	/*
	 *  最大送信数を表示
	 */
	function drawSendMax( &$_gm , $_rec , $_args ){
		$this->addBuffer(SystemUtil::getSystemData("dm_send_limit"));
	}

	function getType(){
		return "mailSend";
	}
}