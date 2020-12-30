<?PHP
	// api.phpにて利用するクラスを列挙
	include_once './custom/api/CommonApi.php';
	include_once './custom/api/FeedApi.php';
	include_once './custom/api/statusChangeApi.php';
	include_once './custom/api/UserApi.php';
	include_once './custom/api/ResumeApi.php';
	include_once './custom/api/MessageApi.php';
	include_once './custom/api/EntryApi.php';
	include_once './custom/api/adApi.php';
	include_once './custom/api/JobApi.php';

	$USE_API_ALLOW_LIST_CHECK = true;
	$USE_API_INHERIT_CHECK    = true;
	// $api_allow_list_flg = true 時
	// $API_ALLOW_LIST['クラス名'] = array('メソッド名','メソッド名');
	//  上記の形式でapi.phpで利用できるクラスとメソッドを指定
	//  $API_ALLOW_LIST['クラス名'] = array('all'); で定義されているメソッド全てを利用可能
	$API_ALLOW_LIST['Api_core']                = array('all');
	$API_ALLOW_LIST['mod_CommonApi']           = array('all');
	$API_ALLOW_LIST['mod_FeedApi']             = array('all');
	$API_ALLOW_LIST['mod_statusChangeApi']     = array('all');
	$API_ALLOW_LIST['mod_calendarApi']         = array('all');
	$API_ALLOW_LIST['mod_Clip']                = array('getCount','regist','delete');
	$API_ALLOW_LIST['cmsSPCodeApi']            = array('all');
	$API_ALLOW_LIST['mod_listApi']             = array('all');
	$API_ALLOW_LIST['mod_mailSendApi']         = array('all');
	$API_ALLOW_LIST['mod_mailTemplateApi']     = array('all');
	$API_ALLOW_LIST['mod_inquiryApi']          = array('all');
	$API_ALLOW_LIST['mod_socialLoginApi']      = array('all');
	$API_ALLOW_LIST['mod_webpayApi']           = array('all');
	$API_ALLOW_LIST['mod_UserApi']             = array('all');
	$API_ALLOW_LIST['mod_adApi']               = array('all');
	$API_ALLOW_LIST['mod_entryApi']            = array('all');
	$API_ALLOW_LIST['mod_JobApi']              = array('all');
	$API_ALLOW_LIST['mod_messageApi']          = array('all');
	$API_ALLOW_LIST['mod_ResumeApi']           = array('all');
	$API_ALLOW_LIST['mod_UserApi']             = array('all');
	$API_ALLOW_LIST['mod_AreaApi']             = array('all');
	$API_ALLOW_LIST['mod_autoMailApi']         = array('all');
	$API_ALLOW_LIST['mod_CategoryApi']         = array('all');
	$API_ALLOW_LIST['mod_mail_confirmApi']     = array('all');
	$API_ALLOW_LIST['mod_resetterApi']         = array('all');
	$API_ALLOW_LIST['mod_specialSettingApi']   = array('all');
	$API_ALLOW_LIST['mod_viewModeApi']         = array('all');
