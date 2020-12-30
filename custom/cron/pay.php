<?php
cron_master::setCron('paycalc','payCron','aggregateCharges');
cron_master::setCron('billing','payCron','noticeBill');
cron_master::setCron('setPayFlg','payCron','setFlg');


class payCron
{
	function setFlg(){
		$db = GMList::getDB("cUser");
		$table = $db->getTable();
		$row = $db->getRow($table);

		for($i=0;$i<$row;$i++){
			$rec = $db->getRecord($table,$i);
			$id = $db->getData($rec,"id");
			if(pay_jobLogic::isAvailable($id,"mid"))
				$db->setData($rec,"charging_mid",true);
			else
				$db->setData($rec,"charging_mid",false);

			if(pay_jobLogic::isAvailable($id,"fresh"))
				$db->setData($rec,"charging_fresh",true);
			else
				$db->setData($rec,"charging_fresh",false);

			$db->updateRecord($rec);
		}
	}

	//利用料金を集計する
	function aggregateCharges(){
		$bill = new Bill();
		$bill->setCurrent();
		if($bill->canAggregate()){
			$bill->doAggregate();
		}
	}

	//請求情報を送信し、企業がサイト上で確認できるようにする
	function noticeBill(){
		$bill = new Bill();
		$bill->setCurrent();
		if($bill->canNotice()){
			$bill->doNotice();
		}
	}
}
