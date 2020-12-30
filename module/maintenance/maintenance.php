<?php

	class mod_maintenance extends command_base //
	{
		function drawNoticeMessage( $gm , $rec , $args ) //
		{
			global $loginUserType;
			global $loginUserRank;

			$mtGM  = GMList::getGM( self::$Type );
			$db    = GMList::getDB( self::$Type );
			$table = $db->getTable();

			if( 'admin' != $loginUserType ) //管理者以外のユーザーの場合
				{ $table = $db->searchTable( $table , 'do_notice' , '=' , TRUE ); }

			$table = $db->searchTable( $table , 'begin_time' , '>' , time() );
			$table = $db->searchTable( $table , 'notice_begin_time' , '<' , time() );
			$table = $db->limitOffset( $table , 0 , 1 );
			$row   = $db->getRow( $table );

			if( $row ) //通知設定のメンテナンス予定がある場合
			{
				$mtRec = $db->getRecord( $table , 0 );

				$this->addBuffer( Template::GetTemplateString( $mtGM , $mtRec , $loginUserType , $loginUserRank , '' , 'MAINTENANCE_NOTICE_DESIGN' , false , null , 'notice' ) );

				return;
			}

			$table  = $db->getTable();
			$tableA = $db->searchTable( $table , 'use_end_time' , '=' , false );
			$tableB = $db->searchTable( $table , 'use_end_time' , '=' , true );
			$tableB = $db->searchTable( $tableB , 'end_time' , '>' , time() );
			$table  = $db->orTable( $tableA , $tableB );
			$table  = $db->searchTable( $table , 'begin_time' , '<' , time() );
			$table  = $db->limitOffset( $table , 0 , 1 );
			$row    = $db->getRow( $table );

			if( $row ) //メンテナンス中の場合
			{
				$mtRec = $db->getRecord( $table , 0 );

				$this->addBuffer( Template::GetTemplateString( $mtGM , $mtRec , $loginUserType , $loginUserRank , '' , 'MAINTENANCE_NOTICE_DESIGN' , false , null , 'now' ) );

				return;
			}
		}

		static private $Type = 'maintenance';
	}
