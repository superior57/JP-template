<?php

class mailSendCron{

	/*
	 * 未送信の予約送信を送信する
	 */
	function send(){
		global $MAILSEND_NAMES;

		$MSdb = GMList::getDB(self::getType());
		$MStable = $MSdb->getTable();
		$MStable = $MSdb->searchTable($MStable,'reserve_flag','=',true);
		$MStable = $MSdb->searchTable($MStable,'reserve_time','<',time());
		$MStable = $MSdb->searchTable($MStable,'send_f','=',false);

		$MSrow = $MSdb->getRow($MStable);

		for($i = 0 ; $i < $MSrow ; $i++){
			$MSrec = $MSdb->getRecord($MStable,$i);

			$user_type = $MSdb->getData($MSrec,'user_type');
			$list_id = $MSdb->getData($MSrec,"list_id");
			$s_mail = $MSdb->getData($MSrec,'sender_mail');
			$s_name = $MSdb->getData($MSrec,'sender_name');
			$s_name = is_null($s_name) ? $MAILSEND_NAMES : $s_name;

            $mailType = $MSdb->getData($MSrec,"mail_type");

			$main = $MSdb->getData($MSrec,'main');
			$sub  = $MSdb->getData($MSrec,'sub');
			$sub = cmsSPCodeLogic::convertSpecialChars($sub,$user_type);
			$main = cmsSPCodeLogic::convertSpecialChars($main,$user_type);

			$gm = SystemUtil::getGMforType($user_type);
			$db = $gm->getDB();
			$table = DMList::getUserTable($list_id);
			$row = $db->getRow($table);

			$status = array();

			for( $j=0; $j<$row; $j++ )
			{
				$rec = $db->getRecord( $table, $j );
				if(!$db->getData($rec,"forse_reject")){
                    if($db->getData($rec,$mailType)) {
						$_sub = $gm->getCCResult($rec,$sub);
						$_main = $gm->getCCResult($rec,$main);
						$mail = $db->getData( $rec, 'mail' );
		
		                if (Mail::sendString($_sub, $_main, $s_mail, $mail, $s_name)) {
							$status["success"][] = $db->getData($rec,"id");
						}else{
                            $status["faled"][] = $db->getData($rec, "id");
                        }
	                }else{
						$status["through"][] = $db->getData($rec,"id");
					}
				}else{
					$status["through"][] = $db->getData($rec,"id");
				}
			}

            $faledCnt = count($status["faled"]);

			if($faledCnt == 0){
				$MSdb->setData($MSrec,"send_f",true);
				$MSdb->setData($MSrec,"send_time",time());
			}
			$MSdb->setData($MSrec,"success_id",implode("/", $status["success"]));
            $MSdb->setData($MSrec,"faled_id",implode("/", $status["faled"]));
			$MSdb->setData($MSrec,"through_id",implode("/", $status["through"]));
			$MSdb->setData($MSrec,"success_cnt", count($status["success"]));
            $MSdb->setData($MSrec,"faled_cnt", count($status["faled"]));
			$MSdb->setData($MSrec,"through_cnt", count($status["through"]));
			$MSdb->updateRecord($MSrec);
		}
	}

	function getType(){
		return "mailSend";
	}
}