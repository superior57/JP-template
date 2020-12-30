<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのパスワード暗号化処理のモデル。
	*/
	class AppCryptModel extends AppBaseModel //
	{
		function isEnableCrypt() //
		{
			global $PASSWORD_MODE;

			return ( 'SHA' == $PASSWORD_MODE ? true : false );
		}

		function doCrypt() //
		{
			global $TABLE_NAME;
			global $THIS_TABLE_IS_USERDATA;

			foreach( $TABLE_NAME as $tableName ) //全てのテーブルを処理
			{
				if( !$THIS_TABLE_IS_USERDATA[ $tableName ] ) //ユーザーテーブルではない場合
					{ continue; }

				$main    = new CSV( $tableName );
				$columns = $main->getColumns();

				foreach( $columns as $name => $info ) //全てのカラムを処理
				{
					if( 'password' == $info[ 'type' ] ) //パスワードカラムの場合
					{
						$this->doCryptLogic( $tableName , $name );
						break;
					}
				}
			}
		}

		function doCryptLogic( $iTableName , $iColumn ) //
		{
			$csv       = new CSV( $iTableName );
			$tableName = new TableName( $iTableName );
			$statement = Query::GetSelectStatement( $tableName->real() , $csv->getColumns() );
			$updates   = Array();

			$statement->setFetchMode( PDO::FETCH_ASSOC );

			foreach( $statement as $row ) //全てのレコードを処理
				{ $updates[] = Array( 'id' => $row[ 'id' ] , $iColumn => sha1( $row[ $iColumn ] ) ); }

			$statement->closeCursor();

			foreach( $updates as $update ) //全てのレコードを処理
				{ Query::UpdateRecord( $tableName->real() , Array( $iColumn => Array( 'type' => 'password' ) ) , $update ); }
		}

		function updateCryptConfig() //
		{
			$fp     = fopen( 'custom/extends/initConf.php' , 'rb' );
			$result = '';

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			while( !feof( $fp ) ) //ファイルの末端まで繰り返し
			{
				$line = fgets( $fp );

				$PASSWORD_MODE = 'AES';

				$line = preg_replace( '/(\$PASSWORD_MODE[\t\s]*=[\t\s]*)(\'[^\']+\')/' , '$1\'SHA\''  , $line );

				$result .= $line;
			}

			$fp = fopen( 'custom/extends/initConf.php' , 'wb' );

			if( !$fp ) //ファイルが開けない場合
				{ throw new RuntimeException(); }

			fputs( $fp , $result );

			fclose( $fp );
		}
	}
