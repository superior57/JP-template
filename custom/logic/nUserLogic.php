<?php
class nUserLogic{

	function registInit($nuser_id){
		$data = array("id"=>$nuser_id);
		bankAccountLogic::userRegistInit($data);
	}

	function deleteInit($nuser_id){
		bankAccountLogic::userDeleteInit($nuser_id);
	}

	static function getTable( $table = null, $param = null, $userType = null  )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		global $loginUserType;
		global $magic_quotes_gpc;
		global $ACTIVE_ACCEPT;
		// **************************************************************************************


		$gm = GMList::getGM('nUser');
		$db = $gm->getDB();

		if( !isset($table) ) { $table = $db->getTable(); }
		if( !isset($userType) ) { $userType = $loginUserType; }

		if(isset($param))
		{
			$sr		 = new Search( $gm, 'nUser' );
			if( $magic_quotes_gpc )	 { $sr->setParamertorSet($param); }
			else					 { $sr->setParamertorSet(addslashes_deep($param)); }

			$table	 = $sr->getResult();
		}

		switch($userType)
		{
		case 'admin':

		case 'cUser':
		case 'nUser':
		default:
			$table = $db->searchTable( $table, 'activate', '=', $ACTIVE_ACCEPT );
			break;
		}

		$table	 = $db->sortTable( $table, 'edit', 'desc' );

		return $table;
	}

	function getActivateIdTable( $userType ,$label)
	{
		$db = GMList::getDB('nUser');

		$table = self::getTable( null, null, $userType ,$label);
		$table = $db->getColumn( 'id', $table );

		return $table;
	}

	function getType(){
		return "nUser";
	}
}