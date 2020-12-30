<?php
class Bill{
	private $fiscal = null;

	function __construct(){
		$close = Conf::getData("charges","closing_date");
		$this->fiscal = new fiscalDateUtil($close);
	}

	function setCurrent($time = null){
		$this->fiscal->setCurrent($time);
	}

	function getFiscal(){
		return $this->fiscal;
	}

	//締め日の翌日なら真
	function canAggregate(){
		$yesterdayYear = date("Y",strtotime("-1day",$this->fiscal->getNow()));
		$yesterdayMonth = date("n",strtotime("-1day",$this->fiscal->getNow()));
		$yesterdayDay = date("j",strtotime("-1day",$this->fiscal->getNow()));

		if($this->fiscal->getCloseDay() == 0){
			$close = strtotime("last day of previous month",$this->fiscal->getClose());
		}else{
			$close = strtotime("previous month",$this->fiscal->getClose());
		}
		return $close == mktime(0,0,0,$yesterdayMonth,$yesterdayDay,$yesterdayYear);
	}

	function canNotice(){
		$billingDate = Conf::getData("charges","billing_date");
		return $billingDate == $this->fiscal->getNow("d");
	}

	function doAggregate(){
		$range = $this->fiscal->getRange($this->fiscal->getNow("y"),$this->fiscal->getNow("m"));

		$db = GMList::getDB("pay_job");
		$table = $db->gettable();
		$table = $db->searchTable($table,"money","!",0);
		$table = $db->searchTable($table,"is_billed","=",false);
		$table = $db->searchTable($table,"regist","b",$range["s"],$range["e"]);
		$table = $db->getSumTable("money","owner",$table);

		if(!$db->existsRow($table))
			return;

		$row = $db->getRow($table);

		for($i=0;$i<$row;$i++){
			$rec = $db->getRecord($table,$i);
			$owner = $db->getData($rec,"owner");
			$sum = $db->getData($rec,"sum");

			$this->addBills($owner,$range,$sum);

			$tableOwn = $db->searchTable($table,"owner","=",$owner);
			$db->setTableDataUpdate($tableOwn,"is_billed",true);
		}

	}

	function doNotice(){
		$db = GMList::getDB("bill");
		$table = $db->getTable();
		$table = $db->searchTable($table,"publish","=",false);
		if(!$db->existsRow($table)) return;

		$row = $db->getRow($table);
		for($i=0;$i<$row;$i++){
			$rec = $db->getRecord($table,$i);
			MailLogic::noticeBill($rec);
		}

		$db->setTableDataUpdate($table,"billdate",$this->getFiscal()->getNow());
		$db->setTableDataUpdate($table,"publish",true);
	}

	private function addBills($owner,$range,$money){
		$db = GMList::getDB("bill");

		$rec = $db->getNewRecord();
		$db->setData($rec,"owner",$owner);
		$db->setData($rec,"money",$money);
		$db->setData($rec,"notice",false);
		$db->setData($rec,"pay_time",0);
		$db->setData($rec,"pay_flg",false);
		$db->setData($rec,"publish",false);
		$db->setData($rec,"demand_s",$range["s"]);
		$db->setData($rec,"demand_e",$range["e"]);
		$db->setData($rec,"regist",$this->fiscal->getNow() );
		$db->addRecord($rec);
		return $rec;
	}
}