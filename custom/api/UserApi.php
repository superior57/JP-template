<?php

	class mod_UserApi extends apiClass
	{
		/**
		 * 仮登録ユーザーの削除。
		 *
		 * @param tableName 対象テーブル名。
		 * @param id 対象ID。
		 */
		function deleteActivateNone( $params )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			// **************************************************************************************

			if( $loginUserType == 'admin' )
			{
				UserLogic::deleteActivateNone( $params['type'] );
			}

		}

		/**
		 * 選択されたユーザーの削除。
		 *
		 * @param tableName 対象テーブル名。
		 * @param id 対象ID。
		 */
		function deleteSelectUser( $params )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			// **************************************************************************************

			if( $loginUserType == 'admin' )
			{
				UserLogic::deleteSelectUser( $params['type'], $params['idList'] );
			}

		}


		/**
		 * 課金設定を利用期限に設定し､更に利用期間を一括設定する。
		 *
		 * @param year 年
		 * @param month 月
		 * @param day 日
		 * @param id 対象ID。
		 */
		function setLimitAll( $params )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			// **************************************************************************************

			if( $loginUserType == 'admin' )
			{
				$type = $params["type"];
				$onUserLimit = Conf::getData("charges", "user_limit") == "on";

				if($onUserLimit){
					$cnt = pay_jobLogic::setChargesUserLimit($type,$params['year'], $params['month'], $params['day']);	//支払済みの中途、新卒に対して新たな利用期限をセットする
					print $cnt;
				}else{
					print "unsetUserLimitConf";
				}
			}
		}


		/**
		 * 課金方法を求人ごとに設定する
		 *
		 */
		function setCharges( &$param )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			// **************************************************************************************

			if( $loginUserType == 'admin' ){
				$onApply = Conf::getData("charges","apply") == "on";
				$onEmployment = Conf::getData("charges","employment") == "on";

				if(!$onApply && !$onEmployment ){
					$dcnt = "unsetAppEmpConf";
				}else{
					$dcnt = 0;
					//利用期間無効化
					Conf::update("charges", "user_limit", "off");

					//プラン選択無効化
					Conf::update("charges", "plan_select", "off");

					//中途、新卒契約の調整
					$terms = array("mid","fresh");
					foreach($terms as $termType){
						$pDB = GMList::getDB("pay_job");
						$table = pay_jobLogic::getLsatTerm(array($termType));
						$table = $pDB->searchtable($table,"target_id","!","");
						$cnt = $pDB->getRow($table);

						$dcnt += $cnt;
						for($i=0;$i<$cnt;$i++){
							$rec = $pDB->getRecord($table,$i);
							$owner = $pDB->getData($rec,"owner");
							$this->setLimitChargeOff($termType,$owner);
							PayJob::add($owner,"{$termType}_term","",$termType,0,0);
						}
					}
				}

				print $dcnt;
			}

		}

		private function setLimitChargeOff($type,$owner){
			$db = GMList::getDB("pay_job");
			$table = $db->getTable();
			$table = $db->searchTable($table,"owner","=",$owner);
			$table = $db->searchTable($table,"target_type","=","{$type}_term");
			$table = $db->searchTable($table,"is_billed","=",false);
			$db->setTableDataUpdate($table,"pay_flg",false);
		}

		function tempChangeViewMode( &$param )
		{
			viewMode::setViewMode($param["view_mode"]);
			echo "Ok";
		}
	}

