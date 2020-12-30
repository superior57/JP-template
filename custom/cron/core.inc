<?php

include_once 'custom/extends/cronConf.php';

//cron main class.

class cron_master{
	private static $_DEBUG = DEBUG_FLAG_CRON;
	static function getDebug(){ return self::$_DEBUG; }
	static function onDebug(){ self::$_DEBUG = true; }
	static function offDebug(){ self::$_DEBUG = false; }

	private static $cron_list = Array(
		'declineScout' => Array(
			'class'  => 'messageCron' ,
			'method' => 'declineScout'
		),
		'attentionNoticeMid' => Array(
			'class'  => 'noticeCron' ,
			'method' => 'attentionMid'
		),
		'attentionNoticeFresh' => Array(
			'class'  => 'noticeCron' ,
			'method' => 'attentionFresh'
		),
		'userLimitNoticeMid' => Array(
			'class'  => 'noticeCron' ,
			'method' => 'userLimitMid'
		),
		'userLimitNoticeFresh' => Array(
			'class'  => 'noticeCron' ,
			'method' => 'userLimitresh'
		),
		'sendReserveMail' => Array(
			'class'  => 'mailSendCron' ,
			'method' => 'send'
		),
		'checkFreeScout' => Array(
			'class'  => 'payCron' ,
			'method' => 'scout'
		),
		'setPayFlg' => Array(
			'class'  => 'payCron' ,
			'method' => 'setFlg'
		)
	);


	static function setCron( $label, $class, $method ){
		if(self::$_DEBUG){ d('cron_master::'.__FUNCTION__.":class($class),method($method)",'cron'); }
		self::$cron_list[$label] = Array( 'class' => $class, 'method' => $method );
	}

	static function cron_exec($label){
		if(! isset(self::$cron_list[$label]) ){
			if(self::$_DEBUG){ d('cron_master::'.__FUNCTION__.' no setting.','cron'); }
			return;
		}
		$class = self::$cron_list[$label]['class'];
		$method = self::$cron_list[$label]['method'];

		SystemUtil::lockProccess( 'cron_' . $class . '_' . $method );

		$instance = new $class();
		if(self::$_DEBUG){ d('cron_master::'.__FUNCTION__.":class($class),method($method)",'cron'); }
		if( method_exists($instance, $method) ) { $instance->$method( $param ); }

		SystemUtil::unlockProccess( 'cron_' . $class . '_' . $method );
	}
}