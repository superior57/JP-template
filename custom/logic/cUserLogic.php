<?php
class cUserLogic{

	//課金クフラグの更新
	static function setFlg($userID , $label, $flg){
		$db = GMList::getDB(self::getType());
		$rec = $db->selectRecord($userID);

		switch($label){
			case "mid":
				$db->setData($rec,"charging_mid",$flg);
				break;
			case "fresh":
				$db->setData($rec,"charging_fresh",$flg);
				break;
			default:
				return;
		}

		$db->updateRecord($rec);
	}

	/*
	 * 中途、新卒の契約有効ユーザーを返す
	*
	* label mid/fresh
	*/
	static function getActiveUser($table,$label){
		$db = GMList::getDB(self::getType());
		if(is_null($table)){ $table = $db->gettable(); }

		$pDB = GMList::getDB("pay_job");
		$pTable = $pDB->gettable();
		$pTable = $pDB->searchTable($pTable,"label","=",$label);
		$pTable = $pDB->searchTable($pTable,"pay_flg","=",true);

		$aTable  = $db->searchTable( $pTable,"limits",">",time());
		$bTable = $db->searchTable( $pTable,"limits","=",0);
		$pTable    = $db->orTable( $aTable , $bTable );

		$table = $db->joinTableSubQuery($table, $pTable, "pay_job", "id", "owner");
		return $table;
	}

	static function getTable( $table = null, $param = null, $userType = null  ,$label = null)
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		global $loginUserType;
		global $magic_quotes_gpc;
		global $ACTIVE_ACCEPT;
		// **************************************************************************************

		$gm = GMList::getGM('cUser');
		$db = $gm->getDB();

		if( !isset($table) ) {
			$table = $db->getTable();
		}
		if( !isset($userType) ) {
			$userType = $loginUserType;
		}

		if(isset($param))
		{
			$sr		 = new Search( $gm, 'cUser' );
			if( $magic_quotes_gpc )	 {
				$sr->setParamertorSet($param);
			}
			else					 { $sr->setParamertorSet(addslashes_deep($param));
			}

			$table	 = $sr->getResult();
		}

		switch($userType)
		{
			case 'admin':
			case 'cUser':
			case 'nUser':
			default:
				$table = $db->searchTable( $table, 'activate', '=', $ACTIVE_ACCEPT );

				if( Conf::getData( 'charges', 'user_limit' ) == 'on' )
				{
					if( cUserLogic::checkPlanSelect() ){
						$table = self::getActiveUser($table ,$label);	//getActiveUserだけで月額、従量の判定に対応しているが保守しやすいよう分岐
					}else{
						$table = self::getActiveUser($table ,$label);
					}
				}

				break;
		}

		$table	 = $db->sortTable( $table, 'edit', 'desc' );

		return $table;
	}

	static $checkPlanSelect;
	static function checkPlanSelect()
	{
		if( isset(self::$checkPlanSelect) ) {
			return self::$checkPlanSelect;
		}

		$result = false;

		if( Conf::getData( 'charges', 'plan_select' ) == 'on' )
		{
			$apply = Conf::getData( 'charges', 'apply' );
			$employment = Conf::getData( 'charges', 'employment' );
			if( $apply == 'on' || $employment == 'on' )	 {
				$job = true;
			}

			$user_limit = (Conf::getData( 'charges', 'user_limit' ) == 'on');
			if( $job && $user_limit ) {
				$result = true;
			}
		}

		self::$checkPlanSelect = $result;
		return self::$checkPlanSelect;
	}

	function getActivateIdTable( $userType ,$label)
	{
		$db = GMList::getDB('cUser');

		$table = self::getTable( null, null, $userType ,$label);
		$table = $db->getColumn( 'id', $table );

		return $table;
	}

	static $jobCharges;
	/**
	 * 求人の課金方式を取得。応募・採用共にの場合maltiを返す
	 *
	 * @return none/apply/employment/malti/user_limit
	 */
	function getJobCharges( $owner ,$label)
	{
		if( isset(self::$jobCharges[$owner]) ) { return self::$jobCharges[$owner]; }

		$plan_select = (Conf::getData( 'charges', 'plan_select' ) == 'on');
		$apply		 = (Conf::getData( 'charges', 'apply' ) == 'on');
		$employment	 = (Conf::getData( 'charges', 'employment' ) == 'on');

		$ul_term = pay_jobLogic::getUserTerm($owner, $label) == "time"; 	//利用期間契約ならtrue

		$result = 'none';
		if( $plan_select )
		{
			if( $ul_term )					 { $result = 'user_limit'; }
			else if( $apply && $employment ) { $result = 'malti'; }
			else if( $apply )				 { $result = 'apply'; }
			else if( $employment )			 { $result = 'employment'; }

		}
		else
		{
			if( $apply && $employment )	 { $result = 'malti'; }
			else if( $apply )			 { $result = 'apply'; }
			else if( $employment )		 { $result = 'employment'; }
			else if( $ul_term )			 { $result = 'user_limit'; }
		}

		self::$jobCharges[$owner] = $result;

		return self::$jobCharges[$owner];
	}

	static function canResign($userID){
		$hasUnsettled = billLogic::existsUnsettled($userID);
		$haUnclaimed = pay_jobLogic::existsUnclaimed($userID);

		return !$hasUnsettled && !$haUnclaimed;
	}


	private function getType(){
		return "cUser";
	}
}