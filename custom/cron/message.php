<?php
cron_master::setCron('deleteMessage','messageCron','delete');
cron_master::setCron('declineScout','messageCron','declineScout');

class messageCron{

	//スカウトの自動辞退処理
	function declineScout(){
		$conf = Conf::getData("charges","decline_scout");
		if($conf == 0) exit;

		$gm = GMList::getGM("message");
		$db = $gm->getDB();
		$table = $db->getTable();
		$table = $db->searchTable($table,"mailtype","=","scout");
		$table = $db->searchTable($table,"declination_scout","=",false);
		$table = $db->searchTable($table,"file","!","");
		$table = $db->searchTable($table,"read_flg","=",false);
		$table = $db->searchTable($table,"regist","<",time()-(60*60*24*$conf));
		$table = $db->searchTable($table,"regist",">",1377529200);		//2013/08/27以降
		$row = $db->getRow($table);

		for($i=0;$i<$row;$i++){
			$rec = $db->getRecord($table,$i);
			$destination = $db->getData($rec,"destination");
			$file = $db->getData($rec,"file");
			parse_str($file,$array);
			if(!entryLogic::isApply( $destination, $array["id"] )){
				$db->setData($rec,"declination_scout",true);
				$db->updateRecord($rec);
				MailLogic::noticeDeclinationScout($rec);
			}
		}
	}

	function delete(){
		$keepLimit = SystemUtil::getSystemData("keep_limit");
		$del_type = SystemUtil::getSystemData("del_type");
		if($keepLimit == 0) return;

		$db = GMList::getDB("message");
		$table = $db->getTable();
		$table = $db->searchTable($table,"read_flg" ,"!",true);
		$table = $db->searchTable($table,"receiver_del" ,"!",true);
		$table = $db->searchTable($table,"regist" ,"<",time()-60*60*24*$keepLimit);

		switch($del_type){
			case "logic":
				$db->setTableDataUpdate($table, "receiver_del", true);
				break;
			case "physical":
				$db->deleteTable($table);
				break;
		}
	}
}