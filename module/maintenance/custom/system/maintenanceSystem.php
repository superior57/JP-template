<?php

	//★クラス //

	class maintenanceSystem extends System //
	{
		//■登録関連 //

		function registProc( &$gm , &$rec , $loginUserType , $loginUserRank , $check = false ) //
		{
			$db                = $gm[ self::$Type ]->getDB();
			$beginYear         = $db->getData( $rec , 'begin_year' );
			$beginMonth        = $db->getData( $rec , 'begin_month' );
			$beginDay          = $db->getData( $rec , 'begin_day' );
			$beginHour         = $db->getData( $rec , 'begin_hour' );
			$beginMinute       = $db->getData( $rec , 'begin_minute' );
			$endYear           = $db->getData( $rec , 'end_year' );
			$endMonth          = $db->getData( $rec , 'end_month' );
			$endDay            = $db->getData( $rec , 'end_day' );
			$endHour           = $db->getData( $rec , 'end_hour' );
			$endMinute         = $db->getData( $rec , 'end_minute' );
			$noticeCountMinute = $db->getData( $rec , 'notice_count_minute' );

			$db->setData( $rec , 'begin_time' , mktime( $beginHour , $beginMinute , 0 , $beginMonth , $beginDay , $beginYear ) );
			$db->setData( $rec , 'end_time' , mktime( $endHour , $endMinute , 0 , $endMonth , $endDay , $endYear ) );
			$db->setData( $rec , 'notice_begin_time' , mktime( $beginHour , $beginMinute - $noticeCountMinute , 0 , $beginMonth , $beginDay , $beginYear ) );

			return parent::registProc( $gm , $rec , $loginUserType , $loginUserRank , $check );
		}

		//■編集関連 //

		function editProc( &$gm , &$rec , $loginUserType , $loginUserRank , $check = false ) //
		{
			$db                = $gm[ self::$Type ]->getDB();
			$beginYear         = $db->getData( $rec , 'begin_year' );
			$beginMonth        = $db->getData( $rec , 'begin_month' );
			$beginDay          = $db->getData( $rec , 'begin_day' );
			$beginHour         = $db->getData( $rec , 'begin_hour' );
			$beginMinute       = $db->getData( $rec , 'begin_minute' );
			$endYear           = $db->getData( $rec , 'end_year' );
			$endMonth          = $db->getData( $rec , 'end_month' );
			$endDay            = $db->getData( $rec , 'end_day' );
			$endHour           = $db->getData( $rec , 'end_hour' );
			$endMinute         = $db->getData( $rec , 'end_minute' );
			$noticeCountMinute = $db->getData( $rec , 'notice_count_minute' );

			$db->setData( $rec , 'begin_time' , mktime( $beginHour , $beginMinute , 0 , $beginMonth , $beginDay , $beginYear ) );
			$db->setData( $rec , 'end_time' , mktime( $endHour , $endMinute , 0 , $endMonth , $endDay , $endYear ) );
			$db->setData( $rec , 'notice_begin_time' , mktime( $beginHour , $beginMinute - $noticeCountMinute , 0 , $beginMonth , $beginDay , $beginYear ) );

			return parent::editProc( $gm , $rec , $loginUserType , $loginUserRank , $check );
		}

		private static $Type = 'maintenance';
	}
