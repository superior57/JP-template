<?php

	//★クラス //

	/**
		@brief 既定の管理ツールの更新処理のモデル。
	*/
	class AppUpdateModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief     テーブルの構成情報を更新する。
			@param[in] $iTableName テーブル名。
			@retval    true  処理に成功した場合。
			@retval    false 処理に失敗した場合。
		*/
		function doUpdate( $iTableName ) //
		{
			$main      = new TableName( $iTableName );
			$delete    = new TableName( $iTableName , '_delete' );
			$csv       = new CSV( $iTableName );
			$deleteCSV = new CSV( $iTableName );
			$scheduler = new InsertScheduler();

			$deleteCSV->addColumn( 'delete_type' , Array( 'type' => 'string' , 'length' => '' ) );
			$deleteCSV->addColumn( 'delete_id'   , Array( 'type' => 'string' , 'length' => '' ) );
			$deleteCSV->addColumn( 'delete_time' , Array( 'type' => 'timestamp' , 'length' => '' ) );

			if( !in_array( $main->real() , Query::ShowTables() ) ) //メインテーブルがない場合
				{ return false; }

			try
			{
				$existsTableList = Query::ShowTables();

				if( in_array( $main->temp() , $existsTableList ) ) //以前の検証テーブルが残っている場合
					{ Query::DropTable( $main->temp() ); }

				if( in_array( $delete->temp() , $existsTableList ) ) //以前の検証テーブルが残っている場合
					{ Query::DropTable( $delete->temp() ); }

				if( !Query::CreateTable( $main->temp() , $csv->getColumns() , $csv->getIndexes() ) || !Query::CreateTable( $delete->temp() , $deleteCSV->getColumns() , $deleteCSV->getIndexes() ) ) //検証テーブルの作成に失敗した場合
					{ throw new Exception(); }

				$mainStatement   = Query::GetSelectStatement( $main->real() , $csv->getColumns() );
				$deleteStatement = Query::GetSelectStatement( $delete->real() , $deleteCSV->getColumns() );

				if( !$mainStatement || !$deleteStatement ) //メインテーブルからのレコード選択に失敗した場合
					{ throw new Exception(); }

				Query::Begin();

				foreach( $mainStatement as $row ) //全ての行を処理
				{
					if( !$scheduler->push( $main->temp() , $csv->getColumns() , $row ) ) //スケジュールの追加に失敗した場合
						{ throw new Exception(); }
				}

				foreach( $deleteStatement as $row ) //全ての行を処理
				{
					if( !$scheduler->push( $delete->temp() , $deleteCSV->getColumns() , $row ) ) //スケジュールの追加に失敗した場合
						{ throw new Exception(); }
				}

				if( !$scheduler->flush() ) //スケジュールの処理に失敗した場合
					{ throw new Exception(); }

				Query::End();

				try
				{
					if( !Query::RenameTable( $main->real() , $main->newBackup() ) || !Query::RenameTable( $delete->real() , $delete->newBackup() ) ) //バックアップの作成に失敗した場合
						{ throw new Exception(); }

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

		/**
			@brief     テーブルのインデックスを更新する。
			@param[in] $iTableName テーブル名。
			@retval    true  処理に成功した場合。
			@retval    false 処理に失敗した場合。
		*/
		function doUpdateIndex( $iTableName ) //
		{
			$main        = new TableName( $iTableName );
			$csv         = new CSV( $iTableName );
			$originIndex = Query::GetIndexData( $main->real() );

			foreach( $originIndex as $index ) //全てのインデックスを処理
			{
				$indexName = $index[ 'Key_name' ];

				if( 'system_shadow_id' == $indexName || 'system_id' == $indexName )
					{ continue; }

				Query::DropIndex( $iTableName , $indexName );
			}

			Query::SetIndex( $iTableName , $csv->getIndexes() );

			return true;
		}

		/**
			@brief     テーブルの構成情報に変更があるか確認する。
			@param[in] $iTableName テーブル名。
			@retval    true  変更がない場合。
			@retval    false 変更がある場合。
		*/
		function isNoChange( $iTableName ) //
		{
			$main         = new TableName( $iTableName );
			$csv          = new CSV( $iTableName );
			$changeStruct = true;
			$changeIndex  = true;

			if( !in_array( $main->real() , Query::ShowTables() ) ) //メインテーブルがない場合
				{ return Array( false , false ); }

			if( in_array( 'struct_check_table_temp' , Query::ShowTables() ) ) //構造チェック用のテーブルがある場合
				{ Query::DropTable( 'struct_check_table_temp' ); }

			if( in_array( 'struct_check_table' , Query::ShowTables() ) ) //構造チェック用のテーブルがある場合
				{ Query::DropTable( 'struct_check_table' ); }

			if( !Query::CreateTable( 'struct_check_table_temp' , $csv->getColumns() , $csv->getIndexes() ) ) //構造チェック用のテーブルが作れなかった場合
				{ throw new Exception(); }

			if( !Query::RenameTable( 'struct_check_table_temp' , 'struct_check_table' ) ) //リネームに失敗した場合
				{ throw new Exception(); }

			$originStruct = Query::GetStructData( $main->real() );
			$newStruct    = Query::GetStructData( 'struct_check_table' );

			if( $originStruct == $newStruct ) //構造に変化がない場合
				{ $changeStruct = false; }

			$originIndex = Query::GetIndexData( $main->real() );
			$newIndex    = Query::GetIndexData( 'struct_check_table' );

			if( $originIndex == $newIndex ) //構造に変化がない場合
				{ $changeIndex = false; }

			return Array( $changeStruct , $changeIndex );
		}
	}
