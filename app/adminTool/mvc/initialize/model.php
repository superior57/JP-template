<?php

	//★クラス //

	/**
		@brief 既定の管理ツールの初期化処理のモデル。
	*/
	class AppInitializeModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief     テーブルを初期化する。
			@param[in] $iTableName テーブル名。
			@retval    true  処理に成功した場合。
			@retval    false 処理に失敗した場合。
		*/
		function doInitialize( $iTableName ) //
		{
			$main      = new TableName( $iTableName );
			$delete    = new TableName( $iTableName , '_delete' );
			$csv       = new CSV( $iTableName );
			$deleteCSV = new CSV( $iTableName , 'delete' );
			$scheduler = new InsertScheduler();

			$deleteCSV->addColumn( 'delete_type' , Array( 'type' => 'string'    , 'length' => '' ) );
			$deleteCSV->addColumn( 'delete_id'   , Array( 'type' => 'string'    , 'length' => '' ) );
			$deleteCSV->addColumn( 'delete_time' , Array( 'type' => 'timestamp' , 'length' => '' ) );

			try
			{
				$existsTableList = Query::ShowTables();

				if( in_array( $main->temp() , $existsTableList ) ) //以前の検証テーブルが残っている場合
					{ Query::DropTable( $main->temp() ); }

				if( in_array( $delete->temp() , $existsTableList ) ) //以前の検証テーブルが残っている場合
					{ Query::DropTable( $delete->temp() ); }

				if( !Query::CreateTable( $main->temp() , $csv->getColumns() , $csv->getIndexes() ) || !Query::CreateTable( $delete->temp() , $deleteCSV->getColumns() , $csv->getIndexes() ) ) //検証テーブルの作成に失敗した場合
					{ throw new Exception(); }

				Query::Begin();

				while( $row = $csv->readRow() ) //全ての初期値を処理
				{
					if( $row[ 'delete_key' ] ) //削除されたレコードの場合
					{
						if( !$scheduler->push( $delete->temp() , $deleteCSV->getColumns() , $row ) ) //スケジュールの追加に失敗した場合
							{ throw new Exception(); }
					}
					else //通常レコードの場合
					{
						if( !$scheduler->push( $main->temp() , $csv->getColumns() , $row ) ) //スケジュールの追加に失敗した場合
							{ throw new Exception(); }
					}
				}

				while( $row = $deleteCSV->readRow( 'noException' ) ) //全ての初期値を処理
				{
					if( !$scheduler->push( $delete->temp() , $deleteCSV->getColumns() , $row ) ) //スケジュールの追加に失敗した場合
						{ throw new Exception(); }
				}

				if( !$scheduler->flush() ) //スケジュールの処理に失敗した場合
					{ throw new Exception(); }

				Query::End();

				try
				{
					if( in_array( $main->real() , Query::ShowTables() ) ) //メインテーブルがある場合
					{
						if( !Query::RenameTable( $main->real() , $main->newBackup() ) || !Query::RenameTable( $delete->real() , $delete->newBackup() ) ) //バックアップの作成に失敗した場合
							{ throw new Exception(); }
					}

					if( !Query::RenameTable( $main->temp() , $main->real() ) || !Query::RenameTable( $delete->temp() , $delete->real() ) ) //バックアップの作成に失敗した場合
						{ throw new Exception(); }
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

					throw $e;
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

				UpdateSystemTable( $iTableName );

				return true;
			}
			catch( Exception $e ) //例外処理
			{
				if( in_array( $main->temp() , Query::ShowTables() ) ) //検証テーブルがある場合
					{ Query::DropTable( $main->temp() ); }

				if( in_array( $delete->temp() , Query::ShowTables() ) ) //検証テーブルがある場合
					{ Query::DropTable( $delete->temp() ); }

				return false;
			}
		}
	}
