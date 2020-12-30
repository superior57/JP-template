<?php

	//★関数 //

	/**
		@brief モジュールのバージョンを取得する。
	*/
	function GetVersion_sqlite() //
	{
		global $SQL_SERVER;
		global $SQL_PORT;
		global $DB_NAME;
		global $SQL_ID;
		global $SQL_PASS;

		if( function_exists( 'sqlite_libversion' ) )
			{ return sqlite_libversion(); }

		if( !InstallStatus::Skipable() )
			{ return false; }

		try
		{
			if( !InstallConfig::VerifyDBConfig( 'MySQLDatabase' , $SQL_SERVER , $SQL_PORT , $DB_NAME , $SQL_ID , $SQL_PASS ) )
				{ throw new Exception(); }

			$db        = CreateDBConnect( 'SQLite' , $SQL_SERVER , $SQL_PORT , $DB_NAME , $SQL_ID , $SQL_PASS );
			$statement = $db->query( 'select sqlite_version()' );
			$row       = $statement->fetch();
			$result    = array_shift( $row );

			unset( $db );

			return $result;
		}
		catch( Exception $e )
			{ $result = false; }

		return $result;
	}
