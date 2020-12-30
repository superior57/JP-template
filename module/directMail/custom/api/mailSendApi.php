<?php

	class mod_mailSendApi extends apiClass
	{
		/**
		 * メールの送信
		 *
		 * @param id レコードID
		 * @param receive_id 送信先ID
		 */
		function send( &$param )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$data = array();
			if( $loginUserType == "admin" && count($param) > 0){
				$id 	= $param['id'];
				$receive_id = $param['receive_id'];

				$mdb = GMList::getDB($this->getType());
				$mrec = $mdb->selectRecord($id);
				$sub = $mdb->getData($mrec,"sub");
				$main = $mdb->getData($mrec,"main");
				$userType = $mdb->getData($mrec,"user_type");
				$mailType = $mdb->getData($mrec,"mail_type");

				$gm = SystemUtil::getGMforType($userType);
				$db = $gm->getDB();
				$table = $db->getTable();
				$table = $db->searchTable( $table, 'id', '=', $receive_id );

				if($db->existsRow($table)){
					$rec = $db->getFirstRecord( $table );
					if(!$db->getData($rec,"forse_reject")){
						if($db->getData($rec,$mailType)){
							if(	class_exists('cmsSPCodeLogic') ){
								$sub = cmsSPCodeLogic::convertSpecialChars($sub,$userType);
								$main = cmsSPCodeLogic::convertSpecialChars($main,$userType);
							}

							$s_mail = $mdb->getData($mrec,'sender_mail');
							$s_name = $mdb->getData($mrec,'sender_name');
							$s_name = is_null($s_name) ? $MAILSEND_NAMES : $s_name;
		
							$_sub = $gm->getCCResult($rec,$sub);
							$_main = $gm->getCCResult($rec,$main);
		
							$mail = $db->getData( $rec, 'mail' );
		
		                    if(!Mail::sendString( $_sub , $_main , $s_mail, $mail, $s_name ))
		                        { $data["faled"] = true; }
	
						}else{
							$data["through"] = true;
						}
					}else{
						$data["through"] = true;
					}
				}else{
					$data["through"] = true;
				}
				$data["success"] = true;
			}else{
				throw new Exception();
			}

			print json_encode($data);
		}

		/*
		 *		メール送信の完了処理
		 * 	@param success_id 成功ID
		 * 	@param through_id 除外ID
		 * 	@param faled_id 失敗ID
		 */
		function complete(&$param){
			global $loginUserType;
			if($loginUserType == "admin"){
				$db = GMList::getDB("mailSend");
				$rec = $db->selectRecord($param["id"]);

				$successCnt = array_filter(explode("/",$param["success_id"]));
				$successCnt = count($successCnt);

                $faledCnt	= array_filter(explode("/",$param["faled_id"]));
                $faledCnt	= count($faledCnt);

				$throughCnt	= array_filter(explode("/",$param["through_id"]));
				$throughCnt	=  count($throughCnt);

				$db->setData($rec,"success_cnt",$successCnt);
                $db->setData($rec,"faled_cnt",$faledCnt);
				$db->setData($rec,"through_cnt",$throughCnt);

				$db->setData($rec,"success_id",$param["success_id"]);
				$db->setData($rec,"through_id",$param["through_id"]);
				$db->setData($rec,"faled_id",$param["faled_id"]);

				if($faledCnt == 0){
					$db->setData($rec,"send_f",true);
					$db->setData($rec,"send_time",time());
				}
				$db->updateRecord($rec);
			}
		}

		function reSendComp(&$param){
			global $loginUserType;
			if($loginUserType == "admin"){
				$db = GMList::getDB("mailSend");
				$rec = $db->selectRecord($param["id"]);

				//実行完了したステータスを取得
				$currentSuccess = explode("/",$param["success_id"]);
                $currentFaled = explode("/",$param["faled_id"]);
				$currentThrough	= explode("/",$param["through_id"]);

                $currentSuccessCnt = count(array_filter($currentSuccess));
                $currentFaledCnt = count(array_filter($currentFaled));
                $currentThroughCnt = count(array_filter($currentThrough));

                //処理件数が0なら何もしない
                if(($currentSuccessCnt + $currentFaledCnt + $currentThroughCnt) == 0)
                    return;

				//現在のステータスを取得
				$poolSuccess = $db->getData($rec, "success_id");
				$poolSuccess = explode("/",$poolSuccess);
				$poolThrough = $db->getData($rec, "through_id");
				$poolThrough = explode("/",$poolThrough);

				//結合し、空要素を排除
				$success = array_filter(array_merge($currentSuccess,$poolSuccess));
                $faled = array_filter($currentFaled);
				$through = array_filter(array_merge($currentThrough,$poolThrough));

				//重複削除
				$success = array_unique((array)$success);
				$through = array_unique((array)$through);

				//成功､スルーの件数をカウント
				$successCnt = count($success);
                $faledCnt = count($faled);
				$throughCnt	=  count($through);

				//施行件数を追加セット
				$db->setData($rec,"success_cnt",$successCnt);
				$db->setData($rec,"through_cnt",$throughCnt);

				$db->setData($rec,"success_id",implode("/",$success));
				$db->setData($rec,"through_id",implode("/",$through));
				$db->setData($rec,"faled_id",$param["faled_id"]);

				$db->setData($rec,"edit",time());

				if($faledCnt == 0){
					$db->setData($rec,"send_f",true);
					$db->setData($rec,"send_time",time());
				}
				$db->updateRecord($rec);
			}
		}

		/*
		 *  	メール送信結果表示
		 * 	@param recID レコードID
		 */

		function drawSendList(&$param){
			$id = $param["id"];

			mod_mailSend::setSendListDesign();
			$gm = GMList::getGM($this->getType());

			$buffer  = mod_mailSend::getSendListHead($gm);
			$buffer .= mod_mailSend::getSendListRows($id);
			$buffer .= mod_mailSend::getSendListFoot($gm);

			$json["html"] = $buffer;
			print json_encode($json);
		}

		function nextList(&$param){
			$id = $param["id"];
			$current = $param["current"];

			$buffer = mod_mailSend::getSendListRows($id,$current);
			if(mod_mailSend::$isEnd)$json["end"] = true;

			$json["html"] = $buffer;
			print json_encode($json);
		}

		function getType(){
				return "mailSend";
		}
	}

?>