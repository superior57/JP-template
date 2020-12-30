<?php

class ConvartTable
{
	static $typeList =  array("job"=> "mid", "nUser"=> "resume");
	static $sexList = array("男性"=> "m", "女性"=> "f");
	static $resumeList;
	static $latLonList;
	static $addsNameList;
	static $add_subNameList;

	static $cUserList;
	static $addsIdList;
	static $add_subIdList;
	static $lineIdList;
	static $stationIdList;

	// フォーマットを変更して返す
	static function getTypeList( $name ) { return self::$typeList[$name]; }
	static function getSex( $name )		 { return self::$sexList[$name]; }


	/**
	 * 該当ユーザーの履歴書IDを返す
	 *
	 * @param owner ユーザーID
	 * @return 履歴書ID
	 */
	static function getResumeId( $owner )
	{
		if( isset( self::$resumeList[$owner] ) ) { return self::$resumeList[$owner]; }

		$db = GMList::getDB("resume");

		$table = $db->gettable();
		$table =$db->searchTable($table,"owner","=",$owner);

		$rec = $db->getFirstRecord($table);
		self::$resumeList[$owner] = "";
		if( isset($rec) )
		{
			self::$resumeList[$owner] = $db->getData( $rec, 'id' );
		}

		return self::$resumeList[$owner];
	}


	/**
	 * 緯度経度を返す
	 *
	 * @param add_sub add_subID
	 * @param col lat/lon
	 * @return 緯度経度
	 */
	static function getLatLon( $add_sub, $col )
	{
		if( isset( self::$latLonList[$add_sub][$col] ) ) { return self::$latLonList[$add_sub][$col]; }
		
		// まだレコードを取得していない場合は取得してから返す。
		$db	 = GMList::getDB('add_sub');
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'id', '=', $add_sub );
		if( $db->getRow($table) > 0 )
		{
			$rec = $db->getRecord( $table, 0 );
			self::$latLonList[$add_sub]['lat'] = $db->getData($rec, 'lat');
			self::$latLonList[$add_sub]['lon'] = $db->getData($rec, 'lon');
			return self::$latLonList[$add_sub][$col];
		}
		else{ return $add_sub; }
	}


	/**
	 * 市区町村IDを返す
	 *
	 * @param name 名称
	 * @return レコード
	 */
	static function getAddsName( $id )
	{
		if( isset( self::$addsNameList[$id] ) ) { return self::$addsNameList[$id]; }
		
		// まだレコードを取得していない場合は取得してから返す。
		$db	 = GMList::getDB('adds');
		$rec = $db->selectRecord($id);
		if( isset($rec) )
		{
			self::$addsNameList[$id] = $db->getData($rec, 'name');
			return self::$addsNameList[$id];
		}
		else{ return $id; }
	}

	/**
	 * 市区町村IDを返す
	 *
	 * @param name 名称
	 * @return レコード
	 */
	static function getAddSubName( $id )
	{
		if( isset( self::$add_subNameList[$id] ) ) { return self::$add_subNameList[$id]; }
		
		// まだレコードを取得していない場合は取得してから返す。
		$db	 = GMList::getDB('add_sub');
		$rec = $db->selectRecord($id);
		if( isset($rec) )
		{
			self::$add_subNameList[$id] = $db->getData($rec, 'name');
			return self::$add_subNameList[$id];
		}
		else{ return $id; }
	}


	/**
	 * cUserの情報を返す
	 *
	 * @param id cUserId
	 * @param col カラム
	 * @return cUserデータ
	 */
	static function getcUserData( $id, $col )
	{
		if( isset( self::$cUserList[$id] ) ) { return $db->getData( self::$cUserList[$id], $col ); }
		
		// まだレコードを取得していない場合は取得してから返す。
		$db	 = GMList::getDB('adds');
		$rec = $db->selectRecord($id);
		if( isset($rec) )
		{
			self::$cUserList[$id] = $rec;
			return $db->getData( $rec, $col );
		}
		else{ return ""; }
	}

	/**
	 * 都道府県IDを返す
	 *
	 * @param name 名称
	 * @return レコード
	 */
	static function getAddsId( $name )
	{
		if( isset( self::$addsIdList[$name] ) ) { return self::$addsIdList[$name]; }
		
		// まだレコードを取得していない場合は取得してから返す。
		$db	 = GMList::getDB('adds');
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'name', '=', '%'.$name.'%' );
		if( $db->getRow($table) > 0 )
		{
			$rec = $db->getRecord( $table, 0 );
			self::$addsIdList[$name] = $db->getData($rec, 'id');
			return self::$addsIdList[$name];
		}
		else{ return $name; }
	}

	/**
	 * 市区町村IDを返す
	 *
	 * @param name 名称
	 * @return レコード
	 */
	static function getAddSubId( $adds, $name )
	{
		if( isset( self::$add_subIdList[$adds][$name] ) ) { return self::$add_subIdList[$adds][$name]; }
		
		// まだレコードを取得していない場合は取得してから返す。
		$db	 = GMList::getDB('add_sub');
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'adds_id', '=', $adds );
		$table = $db->searchTable( $table, 'name', '=', '%'.$name.'%' );
		if( $db->getRow($table) > 0 )
		{
			$rec = $db->getRecord( $table, 0 );
			self::$add_subIdList[$name] = $db->getData($rec, 'id');
			return self::$add_subIdList[$name];
		}
		else{ return $name; }
	}

	/**
	 * 沿線IDを返す
	 *
	 * @param name 名称
	 * @return レコード
	 */
	static function getLineId( $name )
	{
		if( isset( self::$lineIdList[$name] ) ) { return self::$lineIdList[$name]; }
		
		// まだレコードを取得していない場合は取得してから返す。
		$db	 = GMList::getDB('line');
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'name', '=', '%'.$name.'%' );
		if( $db->getRow($table) > 0 )
		{
			$rec = $db->getRecord( $table, 0 );
			self::$lineIdList[$name] = $db->getData($rec, 'id');
			return self::$lineIdList[$name];
		}
		else{ return $name; }
	}

	/**
	 * 駅IDを返す
	 *
	 * @param name 名称
	 * @return レコード
	 */
	static function getStationId( $line, $name )
	{
		if( isset( self::$stationIdList[$line][$name] ) ) { return self::$stationIdList[$line][$name]; }
		
		// まだレコードを取得していない場合は取得してから返す。
		$db	 = GMList::getDB('station');
		$table = $db->getTable();
		$table = $db->searchTable( $table, 'line_ids', '=', '%'.$line.'%' );
		$table = $db->searchTable( $table, 'name', '=', '%'.$name.'%' );
		if( $db->getRow($table) > 0 )
		{
			$rec = $db->getRecord( $table, 0 );
			self::$stationIdList[$name] = $db->getData($rec, 'id');
			return self::$stationIdList[$name];
		}
		else{ return $name; }
	}

}
?>