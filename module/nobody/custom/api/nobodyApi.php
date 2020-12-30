<?php
class mod_nobodyApi extends apiClass{

	function checkEntry(&$param){
		$mail = $param["mail"];
		$jobID = $param["jobID"];
		$nobodyID = nobodyLogic::getID($mail,$jobID);
		if(!$nobodyID){
			print "notfound";
			return ;
		}

		$entryID = entryLogic::getID($nobodyID,$jobID);
		if(!$entryID){
			print "notfound";
			return ;
		}

		$giftID = GiftLogic::getID($entryID,$nobodyID);
		if(!$giftID){
			print "notfound";
			return ;
		}

		if(SystemUtil::getTableData("gift", $giftID, "activate") != 0){
			print "applied";
		}else{
			nobodyLogic::giftActivate($giftID);
			print $giftID;
		}


	}


}