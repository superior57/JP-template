<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのその他の設定処理のモデル。
	*/
	class AppInstallMiscModel extends AppBaseModel //
	{
		//■処理 //

		/**
			@brief 設定適用処理を実行する。
		*/
		function doInstall() //
		{
			global $mobile_flag;
			global $sp_flag;
			global $CONFIG_SSL_ENABLE;
			global $CONFIG_SSL_MOBILE;
			global $HOME;
			global $USE_TEMPLATE_CACHE;

			InstallConfig::SetMobileConfig( $mobile_flag , $sp_flag );
			InstallConfig::SetSSLConfig( $CONFIG_SSL_ENABLE , $CONFIG_SSL_MOBILE );
			InstallConfig::SetTemplateCache( $USE_TEMPLATE_CACHE );
			InstallConfig::SetHomeURL( $HOME );
			InstallStatus::Set( 'misc' , true );
			InstallStatus::DoComplete();
		}

		/**
			@brief クエリを更新する。
		*/
		function updateQuery() //
		{
			global $mobile_flag;
			global $sp_flag;
			global $CONFIG_SSL_ENABLE;
			global $CONFIG_SSL_MOBILE;
			global $USE_TEMPLATE_CACHE;
			global $HOME;

			InstallConfig::ReadMiscConfig();

			if( isset( $_POST[ 'enableMobile' ] ) ) //クエリがある場合
				{ $mobile_flag = $_POST[ 'enableMobile' ]; }

			if( isset( $_POST[ 'enableSmartPhone' ] ) ) //クエリがある場合
				{ $sp_flag = $_POST[ 'enableSmartPhone' ]; }

			if( isset( $_POST[ 'enableSSL' ] ) ) //クエリがある場合
				{ $CONFIG_SSL_ENABLE = $_POST[ 'enableSSL' ]; }

			if( isset( $_POST[ 'enableMobileSSL' ] ) ) //クエリがある場合
				{ $CONFIG_SSL_MOBILE = $_POST[ 'enableMobileSSL' ]; }

			if( isset( $_POST[ 'enableTemplateCache' ] ) ) //クエリがある場合
				{ $USE_TEMPLATE_CACHE = $_POST[ 'enableTemplateCache' ]; }

			if( isset( $_POST[ 'home_url' ] ) ) //クエリがある場合
				{ $HOME = $_POST[ 'home_url' ]; }
			else
			{
				$tableName = new TableName( 'system' );
				$csv       = new CSV( 'system' );
				$statement = Query::GetSelectStatement( $tableName->real() , $csv->getColumns() );

				$statement->setFetchMode( PDO::FETCH_ASSOC );

				foreach( $statement as $row ) //全てのレコードを処理
				{
					$HOME = $row[ 'home' ];
					break;
				}
			}
		}

		//■データ取得 //

		/**
			@brief  設定を適用できる状態か確認する。
			@retval true  適用できる状態の場合。
			@retval false 適用できる状態ではない場合。
		*/
		function canInstall() //
			{ return ( 0 >= count( $this->errors ) ? true : false ); }
	}
