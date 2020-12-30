<?php

	//★クラス //

	/**
		@brief 既定の管理ツールの復元処理のモデル。
	*/
	class AppRestoreModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief     テーブルをバックアップから復元する。
			@param[in] $iTableName テーブル名。
			@retval    true  処理に成功した場合。
			@retval    false 処理に失敗した場合。
		*/
		function doRestore( $iTableName ) //
		{
			$main   = new TableName( $iTableName );
			$delete = new TableName( $iTableName , '_delete' );
			$csv       = new CSV( $iTableName );
			$deleteCSV = new CSV( $iTableName );

			try
			{
				if( !Query::RenameTable( $main->real() , $main->newBackup() ) || !Query::RenameTable( $delete->real() , $delete->newBackup() ) ) //バックアップの作成に失敗した場合
					{ throw new Exception(); }

				if( !in_array( $main->currentBackup( 'oldSys' ) , Query::ShowTables() ) ) //旧システムのバックアップがある場合
				{
				if( !Query::CloneTable( $main->currentBackup() , $main->real() , $csv->getColumns() , $csv->getIndexes() ) || !Query::CloneTable( $delete->currentBackup() , $delete->real() , $deleteCSV->getColumns() , $deleteCSV->getIndexes() ) ) //バックアップからの復元に失敗した場合
					{ throw new Exception(); }
				}
				else //旧システムのバックアップがある場合
				{
					if( !Query::CloneTable( $main->currentBackup( 'oldSys' ) , $main->real() , $csv->getColumns() , $csv->getIndexes() ) || !Query::CloneTable( $delete->currentBackup( 'oldSys' ) , $delete->real() , $deleteCSV->getColumns() , $deleteCSV->getIndexes() ) ) //バックアップからの復元に失敗した場合
						{ throw new Exception(); }
				}
			}
			catch( Exception $e ) //例外処理
			{
				if( in_array( $main->newBackup() , Query::ShowTables() ) ) //新規バックアップがある場合
				{
					if( in_array( $main->real() , Query::ShowTables() ) ) //メインテーブルがある場合
						{ Query::DropTable( $main->real() ); }

					Query::RenameTable( $main->newBackup() , $main->real() );
				}

				if( in_array( $delete->newBackup() , Query::ShowTables() ) ) //新規バックアップがある場合
				{
					if( in_array( $delete->real() , Query::ShowTables() ) ) //メインテーブルがある場合
						{ Query::DropTable( $delete->real() ); }

					Query::RenameTable( $delete->newBackup() , $delete->real() );
				}

				return false;
			}

			Query::DropTable( $main->newBackup() );
			Query::DropTable( $delete->newBackup() );

			return true;
		}
	}
