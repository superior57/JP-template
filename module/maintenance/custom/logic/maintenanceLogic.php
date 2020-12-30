<?php

	class MaintenanceLogic //
	{
		static function Run()
		{
			global $gm;
			global $loginUserType;
			global $loginUserRank;
            global $controllerName;

			if( 'admin' == $loginUserType ) //管理者でログイン済みの場合
				{ return; }

            $controller = strtolower( $controllerName );

			if( 'login' == $controller ) //ログイン画面の場合
				{ return; }

			$db     = GMList::getDB( self::$Type );
			$table  = $db->getTable();
			$tableA = $db->searchTable( $table , 'use_end_time' , '=' , false );
			$tableB = $db->searchTable( $table , 'use_end_time' , '=' , true );
			$tableB = $db->searchTable( $tableB , 'end_time' , '>' , time() );
			$table  = $db->orTable( $tableA , $tableB );
			$table  = $db->searchTable( $table , 'begin_time' , '<' , time() );
			$table  = $db->searchTable( $table , 'arrow_ip' , '!=' , $_SERVER[ 'REMOTE_ADDR' ] );

			$table = $db->limitOffset( $table , 0 , 1 );
			$row   = $db->getRow( $table );

			if( $row ) //メンテナンス中の場合
			{
				header( 'HTTP/1.0 503 Service Temporarily Unavailable' );

				if( 'api' == $controller ) //API通信の場合
					{ exit; }

				$rec = $db->getRecord( $table , 0 );

				print System::getHead( $gm , $loginUserType , $loginUserRank );
				Template::DrawTemplate( $gm[ self::$Type ] , $rec , $loginUserType , $loginUserRank , '' , 'MAINTENANCE_DESIGN' );
				print System::getFoot( $gm , $loginUserType , $loginUserRank );

				exit;
			}
		}

		static private $Type = 'maintenance';
	}
