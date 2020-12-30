<?php

class billView extends command_base{

	function drawBillsList(&$_gm,$_rec,$_args){
		global $loginUserType;
		global $loginUserRank;
		List($id,$disp) = $_args;

		$gm = GMList::getGM($this->getType());
		$db = $gm->getDB();
		$rec = $db->selectRecord($id);
		if(!$rec) return;

		$owner = $db->getData($rec,"owner");
		$demand_s = (int)$db->getData($rec,"demand_s");
		$demand_e = (int)$db->getData($rec,"demand_e");
		$sum = (int)$db->getData($rec,"money");

		$pGM = GMList::getGM("pay_job");
		$pDB = $pGM->getDB();;

		$pTable = $pDB->getTable();
		$pTable = $pDB->searchTable($pTable,"owner","=",$owner);
		$pTable = $pDB->searchTable($pTable,"is_billed","=",true);
		$pTable = $pDB->searchTable($pTable,"regist","b",$demand_s,$demand_e);

		$row = $pDB->getRow($pTable);

		$buffer = "";
		$design = Template::getTemplate($loginUserType,$loginUserRank,"pay_job","BILLS_LIST");
		switch($disp){
			case "text":
				$buffer .= $gm->getString($design,null,"border");
				for($i=0;$i<$row;$i++){
					$pRec = $pDB->getRecord($pTable,$i);
					$label = $pDB->getData($pRec,"label");
					$buffer .= $pGM->getString($design,$pRec,"list_id");
					$buffer .= $pGM->getString($design,$pRec,"list_{$label}");
					$buffer .= $pGM->getString($design,$pRec,"list_money");
					$buffer .= $pGM->getString($design,$pRec,"list_regist");
					$buffer .= $pGM->getString($design,null,"border");
				}
			break;
			case "html":
				$gm->setVariable("sum",$sum);
				$buffer .= $gm->getString($design,null,"head");
				for($i=0;$i<$row;$i++){
					$pRec = $pDB->getRecord($pTable,$i);
					$buffer .= $pGM->getString($design,$pRec,"list");
				}
				$buffer .= $gm->getString($design,null,"sum");
				$buffer .= $gm->getString($design,null,"foot");
		}

		$this->addBuffer($buffer);
	}

	function drawcloseDay(&$gm, $rec, $args){
		$close = billLogic::getCloseDate();
		if($close == 0){
			$str = "毎月末日";
		}else{
			$str = "毎月{$close}日";
		}
		$this->addBuffer($str);
	}

	function drawBillingDay(&$gm, $rec, $args){
		$billing = billLogic::getBillDate();
		if($billing == 0){
			$str = "毎月末日";
		}else{
			$str = "毎月{$billing}日";
		}
		$this->addBuffer($str);
	}

	function getType(){
		return "bill";
	}
}