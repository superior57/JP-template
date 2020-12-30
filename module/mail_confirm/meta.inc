<?php

	$moduleName = 'mail_confirm';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => 'メール認証' , ///<このモジュールの説明。
	);

	include_once './module/mail_confirm/custom/api/mail_confirmApi.php';

	$js_file_paths['nUser']['mail_confirm'] = './module/mail_confirm/common/js/mail_confirm.js';
	$js_file_paths['cUser']['mail_confirm'] = './module/mail_confirm/common/js/mail_confirm.js';

	//include_once './module/mail_confirm/session_start.php';

?>