<?php

include_once './custom/global.php';

class SmartPhoneUtil
{
	static $COOKIE_NAME	= 'sp_mode';

	static function checkTablet()
	{
		$useragents = array(
			'Android' ,
			'iPad'
		);

		foreach( $useragents as $useragent )
		{
			if( preg_match( '/' . $useragent . '/i' , $_SERVER[ 'HTTP_USER_AGENT' ] ) )
				{ return true; }
		}

		return false;
	}

	/**
	 *	スマートフォンの場合trueを返す
	 *
	 *	@return true/false
	 */
	static function checkSP()
	{
		$useragents = array(
		  'iPhone',
		  'Android.*Mobile',
		  'BlackBerry',
		  'IEMobile'
		);

		foreach( $useragents as $useragent )
		{
			if( preg_match( '/' . $useragent . '/i' , $_SERVER[ 'HTTP_USER_AGENT' ] ) )
				{ return true; }
		}

		return false;
	}

	/**
	 *	sp/pcどちらのデザインで表示するかセット
	 *
	 *	@param mode pc/sp
	 */
	function setMode( $mode )
	{
		SystemUtil::setCookieUtil( self::$COOKIE_NAME, $mode );
	}

	/**
	 *	sp/pcどちらのデザインで表示するか返す
	 *
	 *	@return pc/sp
	 */
	function getMode()
	{
		$design = SystemUtil::getCookieUtil( self::$COOKIE_NAME );

		if( !strlen($design) )
		{
			if( SmartPhoneUtil::checkSP() )
				{ $design = 'sp'; }
			else if( SmartPhoneUtil::checkTablet() )
				{ $design = 'tablet'; }
			else
				{ $design = 'sp'; }
		}
		
		return $design;
	}
}
?>