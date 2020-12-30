<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのバックアップ処理のモデル。
	*/
	class AppBackupModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief     テーブルのバックアップを作成する。
			@param[in] $iTableName テーブル名。
			@retval    true  処理に成功した場合。
			@retval    false 処理に失敗した場合。
		*/
		function doBackup( $iTableName ) //
		{
			$main   = new TableName( $iTableName );
			$delete = new TableName( $iTableName , '_delete' );
			$csv       = new CSV( $iTableName );
			$deleteCSV = new CSV( $iTableName );

			if( !in_array( $main->real() , Query::ShowTables() ) ) //メインテーブルがない場合
				{ return false; }

			try
			{
				if( !Query::CloneTable( $main->real() , $main->newBackup() , $csv->getColumns() , $csv->getIndexes() ) || !Query::CloneTable( $delete->real() , $delete->newBackup() , $deleteCSV->getColumns() , $deleteCSV->getIndexes() ) ) //バックアップの作成に失敗した場合
					{ throw new Exception(); }
			}
			catch( Exception $e ) //例外処理
			{
				if( in_array( $main->newBackup() , Query::ShowTables() ) ) //新規バックアップがある場合
					{ Query::DropTable( $main->newBackup() ); }

				if( in_array( $delete->newBackup() , Query::ShowTables() ) ) //新規バックアップがある場合
					{ Query::DropTable( $delete->newBackup() ); }
			}

			if( in_array( $main->currentBackup() , Query::ShowTables() ) ) //古いバックアップがある場合
			{
				Query::DropTable( $main->currentBackup() );
				Query::DropTable( $delete->currentBackup() );
			}

			if( in_array( $main->currentBackup( 'oldSys' ) , Query::ShowTables() ) ) //古いバックアップ(旧システム名)がある場合
			{
				Query::DropTable( $main->currentBackup( 'oldSys' ) );
				Query::DropTable( $delete->currentBackup( 'oldSys' ) );
			}

			if( in_array( $main->currentBackup() , Query::ShowTables() ) ) //古いバックアップがある場合
			{
				Query::DropTable( $main->currentBackup() );
				Query::DropTable( $delete->currentBackup() );
			}

			return true;
		}
	}
