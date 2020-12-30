<?php

class DateUtil
{
	/**
	 * 指定された年月日のUnixtimeを返す
	 *
	 * @param year 年
	 * @param month 月
	 * @param day 日
	 * @param flg start:一日の始まり,end:一日の終わり
	 * @return Unixtime
	 */
	function getUnixtime( $year = null, $month = null, $day = null, $flg = 'start' )
	{
		$time	 = 0;
		if( strlen($year) && strlen($month) && strlen($day) ) 
		{
			$sec	 = 0;
			if( $flg == 'end' ) { $sec = -1; ++$day; } 
			$time = mktime( 0, 0, $sec, $month, $day, $year); 
		}
		
		return	$time;
	}

	/**
	 * unixtimeを年に書き換え。
	 * 
	 * @param time 年を抽出するunixtime。
	 * @param mode 年初(start)/年末(end)を指定。
	 * @return unixtime。
	 */
	function getYearTime( $time = null, $mode = 'start' )
	{
		if( !isset($time) ) { $time	 = time(); }

		$year	 = date('Y', $time);
		
		switch($mode)
		{
		case 'start':	 $time = mktime( 0, 0, 0, 1, 1, $year );	 break;
		case 'end':		 $time = mktime( 0, 0, -1, 1, 1, $year+1 ); break;
		}
		
		return	$time;
	}

	/**
	 * unixtimeを月に書き換え。
	 * 
	 * @param time 月を抽出するunixtime。
	 * @param mode 月初(start)/月末(end)を指定。
	 * @return unixtime。
	 */
	function getMonthTime( $time = null, $mode = 'start' )
	{
		if( !isset($time) ) { $time	 = time(); }

		$year	 = date('Y', $time);
		$month	 = date('m', $time);
		
		switch($mode)
		{
		case 'start':	 $time = mktime( 0, 0, 0, $month, 1, $year );	 break;
		case 'end':		 $time = mktime( 0, 0, -1, $month+1, 1, $year ); break;
		}
		
		return	$time;
	}


	/**
	 * unixtimeを週に書き換え。
	 * 
	 * @param time 週を抽出するunixtime。
	 * @param mode 週初(start)/週末(end)を指定。
	 * @return unixtime。
	 */
	function getWeekTime( $time = null, $mode = 'start' )
	{
		if( !isset($time) ) { $time	 = time(); }

		$year	 = date('Y', $time);
		$month	 = date('m', $time);
		$day	 = date('d', $time);
		$d_week	 = date('w', $time);
		
		$time = mktime( 0, 0, 0, $month, $day, $year );
		switch($mode)
		{
		case 'start':	 $time -= 86400*$d_week;		 break;
		case 'end':		 $time += 86400*( 7-$d_week )-1; break;
		}
		
		return	$time;
	}


	/**
	 * unixtimeを日に書き換え。
	 * 
	 * @param time 日を抽出するunixtime。
	 * @param mode 日初(start)/日末(end)を指定。
	 * @return unixtime。
	 */
	function getDayTime( $time = null, $mode = 'start' )
	{
		if( !isset($time) ) { $time	 = time(); }

		$year	 = date('Y', $time);
		$month	 = date('m', $time);
		$day	 = date('d', $time);
		
		switch($mode)
		{
		case 'start':	 $time = mktime( 0, 0, 0, $month, $day, $year );	 break;
		case 'end':		 $time = mktime( 0, 0, -1, $month, $day+1, $year );	 break;
		}
		
		return	$time;
	}


	/**
	 * 指定された年のデータに絞り込む
	 *
	 * @param db 対象DB
	 * @param table 対象テーブル
	 * @param y 年
	 * @param col 対象カラム
	 * @return table
	 */
	function setSearchYear($db, $table, $y, $col = 'regist')
	{ return $db->searchTable( $table, $col, 'b', mktime(0,0,0,1,1,$y), mktime(0,0,-1,1,1,$y+1 ) ); }


	/**
	 * 指定された月のデータに絞り込む
	 *
	 * @param db 対象DB
	 * @param table 対象テーブル
	 * @param y 年
	 * @param m 月
	 * @param col 対象カラム
	 * @return table
	 */
	function setSearchMonth($db, $table, $y, $m, $col = 'regist')
	{ return $db->searchTable( $table, $col, 'b', mktime(0,0,0,$m,1,$y), mktime(0,0,-1,$m+1,1,$y ) ); }


	/**
	 * 指定された日のデータに絞り込む
	 *
	 * @param db 対象DB
	 * @param table 対象テーブル
	 * @param y 年
	 * @param m 月
	 * @param d 日
	 * @param col 対象カラム
	 * @return table
	 */
	function setSearchDay($db, $table, $y, $m, $d, $col = 'regist')
	{ return $db->searchTable( $table, $col, 'b', mktime(0,0,0,$m,$d,$y), mktime(0,0,-1,$m,$d+1,$y ) ); }


	/**
	 * (col)_(start/end)_(year/month/day)の検索条件をセットする
	 *
	 * @param db 対象DB
	 * @param table 対象テーブル
	 * @param col 対象カラム
	 * @param palam 検索パラメータ
	 * @return table
	 */
	function setSearchTerm($db, $table, $col = 'regist', $palam)
	{
		$modeList = array( 'start', 'end' );
		$checkList = array( 'year', 'month', 'day' );
		
		foreach( $modeList as $mode )
		{
			$flg = true;
			foreach( $checkList as $check )
			{
				if( !strlen($palam[ $col.'_'.$mode.'_'.$check ]) ) { $flg = false; break; }
			}

			if($flg)
			{
				$time[$mode] = self::getUnixtime( $palam[$col.'_'.$mode.'_year'], $palam[$col.'_'.$mode.'_month'], $palam[$col.'_'.$mode.'_day'], $mode );
			}
		}

		$cnt = count($time);
		switch($cnt)
		{
		case 1:
			if( strlen($time['start']) )
			{ $table = $db->searchTable( $table, $col, '>=', $time['start']); }
			else if( strlen($time['end']) )
			{ $table = $db->searchTable( $table, $col, '<=', $time['end']); }
			break;
		case 2;
			$table = $db->searchTable( $table, $col, 'b', $time['start'], $time['end'] );
			break;
		}

		return $table;
	
	}

}

?>