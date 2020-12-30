<?php

/*****************************************************************
 ** 汎用プログラム（regist.php / search.php / info.php）用 定義 **
 *****************************************************************/
/**********          テーブル定義          **********/

/****     sample

	$EDIT_TYPE								= 'sample';							// 設定名。
	$TABLE_NAME[]							= $EDIT_TYPE;						// テーブル名として登録。
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	= false;							// このテーブルがユーザデータかを登録。
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			= null;								// ユーザデータの場合はログインに用いるキー（メールアドレスなど）を保存しているカラム名を指定する。
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		= null;								// ユーザデータの場合はログインに用いるパスワードを保存しているカラム名を指定する。

	$LST[ $EDIT_TYPE ]						= 'sample.csv';				// DB情報定義ファイル
	$TDB[ $EDIT_TYPE ]						= 'sample.csv';				// DBファイル

	$ID_HEADER[ $EDIT_TYPE ] 				= 'S';								// IDの頭に付加する文字列
	$ID_LENGTH[ $EDIT_TYPE ]				= 8;								// ヘッダを含めたIDの長さ（例:'S1234567' = 8）

//以下任意項目
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ]	= Array( $NOT_LOGIN_USER_TYPE );	//登録を行なえるユーザー
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]		= Array('sample_user');				//編集を行なえるユーザー
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ]	= Array( 'sample_user' => 'id' );	//編集を行なえるユーザーに制限をかける

	$ADD_LST[ 'system' ][ 'add_system' ]  = 'add_sample.csv';				//lstに追加する定義
	$THIS_TABLE_IS_QUICK[ $EDIT_TYPE ]	  = true;								//クイックログインを使うユーザーかどうか
	$SUPER_USER_COLUMN[ $EDIT_TYPE ]	  = 'su';								//super userモードを使う場合、権限tableのidが設定されているカラム
 */


/**********          adminの定義          **********/

	$EDIT_TYPE                            = 'admin';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = true;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = 'mail';
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = 'pass';
	$LST[ $EDIT_TYPE ]                    = 'user/admin.csv';
	$TDB[ $EDIT_TYPE ]                    = 'user/admin.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'ADMIN';
	$ID_LENGTH[ $EDIT_TYPE ]              = 5;
	$ADD_LST[ "admin" ][ 'e-animal' ]    = 'add/admin.csv';


/**********          cUserの定義          **********/

	$EDIT_TYPE                            = 'cUser';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = true;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] = Array( $NOT_LOGIN_USER_TYPE );
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]   = Array('cUser');
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ] = Array( 'cUser' => 'id' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = 'mail';
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = 'pass';
	$LST[ $EDIT_TYPE ]                    = 'user/cuser.csv';
	$TDB[ $EDIT_TYPE ]                    = 'user/cuser.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'C';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;
	$ADD_LST[ "cUser" ][ 'e-animal' ]    = 'add/cUser.csv';

/**********          nUserの定義          **********/

	$EDIT_TYPE                            = 'nUser';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = true;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] = Array( $NOT_LOGIN_USER_TYPE );
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]   = Array('nUser');
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ] = Array( 'nUser' => 'id' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = 'mail';
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = 'pass';
	$LST[ $EDIT_TYPE ]                    = 'user/nuser.csv';
	$TDB[ $EDIT_TYPE ]                    = 'user/nuser.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'N';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;
	$ADD_LST[ "nUser" ][ 'e-animal' ]    = 'add/nUser.csv';



/**********          midの定義          **********/

	$EDIT_TYPE                            = 'mid';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] = Array('cUser');
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]   = Array('cUser');
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ] = Array( 'cUser' => 'owner' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'items/mid.csv';
	$TDB[ $EDIT_TYPE ]                    = 'items/mid.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'J';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;


/**********          at_termの定義          **********/

	$EDIT_TYPE                            = 'at_term';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'items/at_term.csv';
	$TDB[ $EDIT_TYPE ]                    = 'items/at_term.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'AT';
	$ID_LENGTH[ $EDIT_TYPE ]              = 5;

/**********          mid_termの定義          **********/

	$EDIT_TYPE                            = 'mid_term';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'items/mid_term.csv';
	$TDB[ $EDIT_TYPE ]                    = 'items/mid_term.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'MT';
	$ID_LENGTH[ $EDIT_TYPE ]              = 5;

/**********          fresh_termの定義          **********/

	$EDIT_TYPE                            = 'fresh_term';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'items/fresh_term.csv';
	$TDB[ $EDIT_TYPE ]                    = 'items/fresh_term.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'FT';
	$ID_LENGTH[ $EDIT_TYPE ]              = 5;

/**********          job_additionの定義          **********/

	$EDIT_TYPE                            = 'job_addition';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'items/job_addition.csv';
	$TDB[ $EDIT_TYPE ]                    = 'items/job_addition.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'JA';
	$ID_LENGTH[ $EDIT_TYPE ]              = 5;

/**********          scoutの定義          **********/

	$EDIT_TYPE                            = 'scout';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'items/scout.csv';
	$TDB[ $EDIT_TYPE ]                    = 'items/scout.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'SC';
	$ID_LENGTH[ $EDIT_TYPE ]              = 5;

/**********          entry_progressの定義          **********/

	$EDIT_TYPE								 = 'entry_progress';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = 'items/entry_progress.csv';
	$TDB[ $EDIT_TYPE ]						 = 'items/entry_progress.csv';
	$ID_HEADER[ $EDIT_TYPE ] 				 = 'EP';
	$ID_LENGTH[ $EDIT_TYPE ]				 = 5;

/**********          areaの定義          **********/

	$EDIT_TYPE                            = 'area';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'common/area.csv';
	$TDB[ $EDIT_TYPE ]                    = 'common/area.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'AREA';
	$ID_LENGTH[ $EDIT_TYPE ]              = 6;


/**********          addsの定義          **********/

	$EDIT_TYPE                            = 'adds';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'common/adds.csv';
	$TDB[ $EDIT_TYPE ]                    = 'common/adds.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'PF';
	$ID_LENGTH[ $EDIT_TYPE ]              = 4;


/**********          add_subの定義          **********/

	$EDIT_TYPE                            = 'add_sub';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'common/add_sub.csv';
	$TDB[ $EDIT_TYPE ]                    = 'common/add_sub.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'AD';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;

/**********          lineの定義          **********/

	$EDIT_TYPE                            = 'line';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'common/line.csv';
	$TDB[ $EDIT_TYPE ]                    = 'common/line.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = '';
	$ID_LENGTH[ $EDIT_TYPE ]              = 0;


/**********          stationの定義          **********/

	$EDIT_TYPE                            = 'station';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'common/station.csv';
	$TDB[ $EDIT_TYPE ]                    = 'common/station.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = '';
	$ID_LENGTH[ $EDIT_TYPE ]              = 0;

/**********          templateの定義          **********/

	$EDIT_TYPE                            = 'template';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'system/template.csv';
	$TDB[ $EDIT_TYPE ]                    = 'system/template.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'T';
	$ID_LENGTH[ $EDIT_TYPE ]              = 5;


/**********          systemの定義          **********/

	$EDIT_TYPE                            = 'system';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'system/system.csv';
	$TDB[ $EDIT_TYPE ]                    = 'system/system.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = '';
	$ID_LENGTH[ $EDIT_TYPE ]              = 0;


/**********          pageの定義          **********/

	$EDIT_TYPE                            = 'page';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'system/page.csv';
	$TDB[ $EDIT_TYPE ]                    = 'system/page.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'P';
	$ID_LENGTH[ $EDIT_TYPE ]              = 6;

	/**********          freshの定義          **********/

	$EDIT_TYPE                            = 'fresh';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] = Array( "cUser" );
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]   = Array( "cUser" );
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ] = Array( 'cUser' => 'owner' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'items/fresh.csv';
	$TDB[ $EDIT_TYPE ]                    = 'items/fresh.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'JN';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;

	/**********          contractの定義          **********/
/*
	$EDIT_TYPE                            = 'contract';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] = Array( "cUser" );
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]   = null;
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ] = Array( 'cUser' => 'owner' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'contract.csv';
	$TDB[ $EDIT_TYPE ]                    = 'contract.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'CN';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;
	$ADD_LST[ "contract" ][ 'e-animal' ]    = 'add/contract.csv';
*/
	/**********          mailの定義          **********/

	$EDIT_TYPE                            = 'message';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] = Array( "cUser","nUser" );
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]   = null;
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ] = Array( 'cUser' => 'owner','nUser' => 'owner' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'module/message.csv';
	$TDB[ $EDIT_TYPE ]                    = 'module/message.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'M';
	$ID_LENGTH[ $EDIT_TYPE ]              = 12;

	/**********          resumeの定義          **********/

	$EDIT_TYPE                            = 'resume';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] = Array( "nUser" );
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]   = Array( "nUser" );
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ] = Array( 'nUser' => 'owner' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'user/resume.csv';
	$TDB[ $EDIT_TYPE ]                    = 'user/resume.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'R';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;

	/**********          pay_jobの定義          **********/

	$EDIT_TYPE                            = 'pay_job';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] = Array( "cUser" );
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]   = null;
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ] = Array( 'cUser' => 'owner' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'payment/pay_job.csv';
	$TDB[ $EDIT_TYPE ]                    = 'payment/pay_job.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'PJ';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;

	/**********          billの定義          **********/

	$EDIT_TYPE                            = 'bill';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] = Array( "cUser" );
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]   = null;
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ] = Array( 'cUser' => 'owner' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'payment/bill.csv';
	$TDB[ $EDIT_TYPE ]                    = 'payment/bill.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'B';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;

	/**********          charges_confの定義          **********/

	$EDIT_TYPE								 = 'charges_conf';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = 'conf/charges_conf.csv';
	$TDB[ $EDIT_TYPE ]						 = 'conf/charges_conf.csv';
	$ID_HEADER[ $EDIT_TYPE ]                 = '';
	$ID_LENGTH[ $EDIT_TYPE ]                 = 0;


/**********          user_confの定義          **********/

	$EDIT_TYPE								 = 'user_conf';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = 'conf/user_conf.csv';
	$TDB[ $EDIT_TYPE ]						 = 'conf/user_conf.csv';
	$ID_HEADER[ $EDIT_TYPE ]                 = '';
	$ID_LENGTH[ $EDIT_TYPE ]                 = 0;


/**********          job_confの定義          **********/

	$EDIT_TYPE								 = 'job_conf';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = 'conf/job_conf.csv';
	$TDB[ $EDIT_TYPE ]						 = 'conf/job_conf.csv';
	$ID_HEADER[ $EDIT_TYPE ]                 = '';
	$ID_LENGTH[ $EDIT_TYPE ]                 = 0;

/**********          entryの定義          **********/

	$EDIT_TYPE								 = 'entry';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ]	 = Array( $NOT_LOGIN_USER_TYPE,'nUser');
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]  	 = Array('nUser');
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ]	 = Array( 'cUser' => 'items_owner','nUser' => 'entry_user' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = 'items/entry.csv';
	$TDB[ $EDIT_TYPE ]						 = 'items/entry.csv';
	$ID_HEADER[ $EDIT_TYPE ]                 = 'E';
	$ID_LENGTH[ $EDIT_TYPE ]                 = 8;

/**********          bank_accountの定義          **********/

	$EDIT_TYPE								 = 'bank_account';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ]	 = Array('nUser');
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]  	 = Array('nUser');
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ]	 = Array( 'nUser' => 'id' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = 'user/bank_account.csv';
	$TDB[ $EDIT_TYPE ]						 = 'user/bank_account.csv';
	$ID_HEADER[ $EDIT_TYPE ]                 = 'N';
	$ID_LENGTH[ $EDIT_TYPE ]                 = 8;

/**********          giftの定義          **********/

	$EDIT_TYPE								 = 'gift';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ]	 = Array('nUser');
	$THIS_TABLE_EDIT_USER[ $EDIT_TYPE ]  	 = Array('nUser','nobody');
	$THIS_TABLE_OWNER_COLUM[ $EDIT_TYPE ]	 = Array( 'nUser' => 'owner','nobody' => 'owner' );
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = 'items/gift.csv';
	$TDB[ $EDIT_TYPE ]						 = 'items/gift.csv';
	$ID_HEADER[ $EDIT_TYPE ]                 = 'G';
	$ID_LENGTH[ $EDIT_TYPE ]                 = 10;


/**********          countの定義          **********/

	$EDIT_TYPE                            = 'count';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'items/count.csv';
	$TDB[ $EDIT_TYPE ]                    = 'items/count.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'CT';
	$ID_LENGTH[ $EDIT_TYPE ]              = 9;

/**********          items_form_countの定義          **********/
	$EDIT_TYPE								 = 'items_form_count';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = 'count/items_form_count.csv';
	$TDB[ $EDIT_TYPE ]						 = 'count/items_form_count.csv';
	$ID_HEADER[ $EDIT_TYPE ] 				 = '';
	$ID_LENGTH[ $EDIT_TYPE ]				 = 10;

/**********          items_type_countの定義          **********/
	$EDIT_TYPE								 = 'items_type_count';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = 'count/items_type_count.csv';
	$TDB[ $EDIT_TYPE ]						 = 'count/items_type_count.csv';
	$ID_HEADER[ $EDIT_TYPE ] 				 = '';
	$ID_LENGTH[ $EDIT_TYPE ]				 = 10;

/**********          job_addition_countの定義          **********/
	$EDIT_TYPE								 = 'job_addition_count';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = 'count/job_addition_count.csv';
	$TDB[ $EDIT_TYPE ]						 = 'count/job_addition_count.csv';
	$ID_HEADER[ $EDIT_TYPE ] 				 = '';
	$ID_LENGTH[ $EDIT_TYPE ]				 = 10;

/**********          area_countの定義          **********/
	$EDIT_TYPE								 = 'area_count';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = 'count/area_count.csv';
	$TDB[ $EDIT_TYPE ]						 = 'count/area_count.csv';
	$ID_HEADER[ $EDIT_TYPE ] 				 = '';
	$ID_LENGTH[ $EDIT_TYPE ]				 = 10;


	/**********          old_add_subの定義          **********/
	$EDIT_TYPE								 = 'old_add_sub';
	$TABLE_NAME[]							 = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ]	 = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]			 = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]		 = null;
	$LST[ $EDIT_TYPE ]						 = 'common/old_add_sub.csv';
	$TDB[ $EDIT_TYPE ]						 = 'common/old_add_sub.csv';
	$ID_HEADER[ $EDIT_TYPE ] 				 = '';
	$ID_LENGTH[ $EDIT_TYPE ];
