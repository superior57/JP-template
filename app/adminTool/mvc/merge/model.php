<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのログイン・ログアウトのモデル。
	*/
	class AppMergeModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief バックアップを作成する。
		*/
		function doBackup( $iTableName ) //
		{
			if( 0 >= count( $_POST[ 'merge' ] ) ) //マージ要求がない場合
				{ return true; }

			$main   = new TableName( $iTableName );
			$delete = new TableName( $iTableName , '_delete' );

			if( !in_array( $main->real() , Query::ShowTables() ) ) //メインテーブルがない場合
				{ return false; }

			try
			{
				if( !Query::CloneTable( $main->real() , $main->newBackup() ) || !Query::CloneTable( $delete->real() , $delete->newBackup() ) ) //バックアップの作成に失敗した場合
					{ throw new Exception(); }
			}
			catch( Exception $e ) //例外処理
			{
				if( in_array( $main->newBackup() , Query::ShowTables() ) ) //新規バックアップがある場合
					{ Query::DropTable( $main->newBackup() ); }

				if( in_array( $delete->newBackup() , Query::ShowTables() ) ) //新規バックアップがある場合
					{ Query::DropTable( $delete->newBackup() ); }
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
