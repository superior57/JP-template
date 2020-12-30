<?php

class mod_FeedApi extends apiClass
{
	function update( $param )
	{
		global $CONF_FEED_TABLES;

		$targetList = $CONF_FEED_TABLES;
		if( isset($param['targetType']) ) { $targetList = array( $param['targetType'] );  }

		foreach( $targetList as $target )
		{
			$_GET[ 'type' ] = $target;
			$sys = SystemUtil::getSystem( $_GET[ 'type' ] );
			$sys->feedProc();
		}
	}
}