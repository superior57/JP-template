<?php

	$moduleName = 'jc1tojc2';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0', ///<このモジュールのバージョン情報。
		'summary' => 'jc1からjc2へデータのインポートモジュール。'  ///<このモジュールの説明。
	);

	// JC2のテーブルに対応するJC1のテーブル名のリスト
	// 先にID管理項目を移行
	$JC1TOJC2['items_type']		 = 'job_type';
	$JC1TOJC2['items_form']		 = 'job_form';
	$JC1TOJC2['job_addition']	 = 'job_addition';
	$JC1TOJC2['entry_progress']	 = 'entry_progress';
	$JC1TOJC2['mid_term']		 = 'ul_term';
	$JC1TOJC2['at_term']		 = 'at_term';

	$JC1TOJC2['admin']			 = 'admin';
	$JC1TOJC2['cUser']			 = 'cuser';
	$JC1TOJC2['pay_job']		 = 'cuser';
	$JC1TOJC2['nUser']			 = 'nuser';
	$JC1TOJC2['resume']			 = 'nuser';
	$JC1TOJC2['mid']			 = 'job';
	$JC1TOJC2['entry']			 = 'entry';
	$JC1TOJC2['message']		 = 'sys_mail';
	$JC1TOJC2['clip']			 = 'clip';
	$JC1TOJC2['inquiry']		 = 'inquiry';
	$JC1TOJC2['news']			 = 'news';
	$JC1TOJC2['page']			 = 'page';
	$JC1TOJC2['attention']		 = 'attention';
	$JC1TOJC2['user_limit']		 = 'user_limit';
	$JC1TOJC2['mail_send']		 = 'mail_send';
	$JC1TOJC2['mail_template']	 = 'mail_template';

	include_once './module/'.$moduleName.'/custom/model/ConvertTable.php';
	include_once './module/'.$moduleName.'/custom/logic/CsvLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

	include_once './module/'.$moduleName.'/custom/logic/items_typeLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/items_formLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/job_additionLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/entry_progressLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/mid_termLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/at_termLogic.php';

	include_once './module/'.$moduleName.'/custom/logic/adminLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/cUserLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/pay_jobLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/nUserLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/resumeLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/midLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/entryLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/messageLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/clipLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/inquiryLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/newsLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/pageLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/attentionLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/user_limitLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/mail_sendLogic.php';
	include_once './module/'.$moduleName.'/custom/logic/mail_templateLogic.php';


	
