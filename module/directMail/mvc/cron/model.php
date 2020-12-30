<?php

	//★クラス //

	/**
		@brief   既定のCRONのモデル。
	*/
	class AppdirectMailCRONModel extends AppCRONModel //
	{
		//■処理 //

		/**
			@brief CRON実行のための設定を初期化する。
		*/
		function initializeCronSetting() //
		{
			global $CRON_SESSION_FLAG;

			set_time_limit( 0 );
			chdir( dirname( $_SERVER[ 'SCRIPT_FILENAME' ] ) );

			$CRON_SESSION_FLAG = true;
		}

		/**
			@brief ラベルで指定されたCRON処理を実行する。
		*/
		function doLabelCron() //
		{
			if( is_array( $_GET[ 'label' ] ) ) //配列の場合
			{
				foreach( $_GET[ 'label' ] as $label ) //全てのラベルを処理
					{ cron_master::cron_exec( $label ); }
			}
			else //スカラの場合
				{ cron_master::cron_exec( $_GET[ 'label' ] ); }
		}

		/**
			@brief 引数で指定されたCRON処理を実行する。
		*/
		function doArgcCron() //
		{
			global $argv;
			global $argc;

			for( $i = 1 ; $argc > $i ; ++$i ) //全ての引数を処理
				{ cron_master::cron_exec( $argv[ $i ] ); }
		}
	}
