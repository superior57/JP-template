<?php

	//★関数 //

	/**
		@brief モジュールのバージョンを取得する。
	*/
	function GetVersion_mysql() //
	{
		global $SQL_SERVER;
		global $SQL_PORT;
		global $DB_NAME;
		global $SQL_ID;
		global $SQL_PASS;

		ob_start();
		$result = system( 'mysql --version' );
		ob_end_clean();

		if( $result )
			{ return $result; }

		if( !InstallStatus::Skipable() )
			{ return false; }

		try
		{
			if( !InstallConfig::VerifyDBConfig( 'MySQLDatabase' , $SQL_SERVER , $SQL_PORT , $DB_NAME , $SQL_ID , $SQL_PASS ) )
				{ throw new Exception(); }

			$db        = CreateDBConnect( 'MySQLDatabase' , $SQL_SERVER , $SQL_PORT , $DB_NAME , $SQL_ID , $SQL_PASS );
			$statement = $db->query( 'select version()' );
			$row       = $statement->fetch();
			$result    = array_shift( $row );

			unset( $db );

			return $result;
		}
		catch( Exception $e )
			{ $result = false; }

		return $result;
	}
