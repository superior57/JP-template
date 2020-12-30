<?php

	include_once "custom/head_main.php";

	switch( $_POST[ 'method' ] )
	{
		case 'feedUpdate' :
		{
			foreach( $CONF_FEED_TABLES as $tableName )
			{
				$_GET[ 'type' ] = $tableName;

				$sys = SystemUtil::getSystem( $_GET[ 'type' ] );
				$sys->feedProc();
			}

			break;
		}

		default :
			{ break; }
	}
