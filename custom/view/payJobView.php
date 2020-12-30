<?php
class payJobView extends command_base{

	//有料サービスが1つでも有効かどうか
	function drawOnPaySetting(){
		$result = pay_jobLogic::onPaySetting()?"TRUE":"FALSE";
		$this->addBuffer($result);
	}

	//利用券の有効可否を出力
	function drawIsAvailable(&$_gm,$_rec,$_args){
		List($type,$userID) = $_args;
		if(!in_array($type,array("mid","fresh"))){
			return;
		}

		if(pay_jobLogic::isAvailable($userID, $type))
			$this->addBuffer("TRUE");
		else
			$this->addBuffer("FALSE");
	}

	function drawJobTermType(&$_gm,$_rec,$_args)
	{
		List($jobType,$jobID) = $_args;
		$result = SystemUtil::getTableData($jobType,$jobID,"term_type");
		$this->addBuffer($result);
	}

	function drawCost(&$_gm,$_rec,$_args){
		List($termType,$id,$draw) = $_args;

		$cost = SystemUtil::getTableData($termType,$id,"cost");
		$taxRate = SystemUtil::getSystemData("tax");

		switch($draw){
			case "cost":
				$result = $cost;
				break;
			case "tax":
				$result = ceil($cost * $taxRate/100);
				break;
			case "total":
				$result = $cost + ceil($cost * $taxRate/100);
				break;
		}

		$this->addBuffer(ceil($result));
	}

	function drawBillDate(&$_gm,$_rec,$_args){
		List($id) = $_args;
		$db = GMList::getDB($this->getType());
		$rec = $db->selectRecord($id);
		if(!$rec) return;

		$regist = $db->getData($rec,"regist");

		$close = Conf::getData("charges","closing_date");
		$billing = Conf::getData("charges","billing_date");

		$billFDU = new fiscalDateUtil($billing);
		$billFDU->setCurrent($regist);
		$closeTime = $billFDU->getClose();

		$closeFDU = new fiscalDateUtil($close);
		$closeFDU->setCurrent($regist);

		if($billFDU->getClose() < $closeFDU->getClose()){
			$closeTime = strtotime("+1Month",$closeTime);
		}

		$this->addBuffer($closeTime);
	}

	function getType(){
		return "pay_job";
	}
}