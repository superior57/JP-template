<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのファイル出力処理のモデル。
	*/
	class AppExportModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief     テーブルをCSVファイルに出力する。
			@param[in] $iTableName テーブル名。
			@retval    true  処理に成功した場合。
			@retval    false 処理に失敗した場合。
		*/
		function doExport( $iTableName ) //
		{
			global $SYSTEM_CHARACODE;
			global $PASSWORD_MODE;

			$main   = new TableName( $iTableName );
			$delete = new TableName( $iTableName , '_delete' );
			$csv    = new CSV( $iTableName );

			$mainFP   = fopen( $main->exportFile() . '.tmp' , 'wb' );
			$deleteFP = fopen( substr( $delete->exportFile() , 0 , -4 ) . '_delete.csv.tmp' , 'wb' );

			if( !$mainFP || !$deleteFP ) //ファイルが開けない場合
				{ return false; }

			$mainStatement   = Query::GetSelectStatement( $main->real() , $csv->getColumns() );
			$deleteStatement = Query::GetSelectStatement( $delete->real() , $csv->getColumns() );

			if( !$mainStatement || !$deleteStatement ) //メインテーブルからのレコード選択に失敗した場合
				{ return false; }

			$mainStatement->setFetchMode( PDO::FETCH_ASSOC );
			$deleteStatement->setFetchMode( PDO::FETCH_ASSOC );

			foreach( $mainStatement as $row ) //全てのレコードを処理
			{
				foreach( $csv->getcolumns() as $column => $option )
				{
					if( 'boolean' == $option[ 'type' ] )
						{ $row[ $column ] = $row[ $column ] ? 1 : 0; }
					else if( 'password' == $option[ 'type' ] )
						{ $row[ $column ] = EncodePassword( $row[ $column ] , $PASSWORD_MODE ); }
				}

				fputcsv( $mainFP , $row );
			}

			foreach( $deleteStatement as $row ) //全てのレコードを処理
			{
				foreach( $csv->getcolumns() as $column => $option )
				{
					if( 'boolean' == $option[ 'type' ] )
						{ $row[ $column ] = $row[ $column ] ? 1 : 0; }
					else if( 'password' == $option[ 'type' ] )
						{ $row[ $column ] = EncodePassword( $row[ $column ] , $PASSWORD_MODE ); }
				}

				fputcsv( $deleteFP , $row );
			}

			fclose( $mainFP );
			fclose( $deleteFP );

			$mainRead   = fopen( $main->exportFile() . '.tmp' , 'rb' );
			$deleteRead = fopen( substr( $delete->exportFile() , 0 , -4 ) . '_delete.csv.tmp' , 'rb' );

			$mainFP   = fopen( $main->exportFile() , 'wb' );
			$deleteFP = fopen( substr( $delete->exportFile() , 0 , -4 ) . '_delete.csv' , 'wb' );

			while( $mainRead && !feof( $mainRead ) )
				{ fputs( $mainFP , mb_convert_encoding( fgets( $mainRead ) , 'SJIS' , $SYSTEM_CHARACODE ) ); }

			while( $deleteRead && !feof( $deleteRead ) )
				{ fputs( $deleteFP , mb_convert_encoding( fgets( $deleteRead ) , 'SJIS' , $SYSTEM_CHARACODE ) ); }

			fclose( $mainRead );
			fclose( $deleteRead );

			unlink( $main->exportFile() . '.tmp' );
			unlink( substr( $delete->exportFile() , 0 , -4 ) . '_delete.csv.tmp' );

			return true;
		}
	}
