<?php

    $moduleName = 'indeed_feed';

    $MODULES[$moduleName] = array(
        'version' => '1.0',                             // このモジュールのバージョン情報。
        'summary' => 'Indeed XMLフィード出力モジュール' // このモジュールの説明。
    );

    require_once './module/' . $moduleName . '/custom/api/indeed_feedApi.php';
    require_once './module/' . $moduleName . '/custom/logic/indeed_feedLogic.php';
    require_once './module/' . $moduleName . '/custom/view/indeed_feedView.php';
    require_once './module/' . $moduleName . '/indeed_feed.php';

    require_once './module/' . $moduleName . '/custom/cron/indeed_feedCron.php';
    CodeScheduler::SetCron('updateIndeedFeed', 'indeed_feedCron', 'updateIndeedFeed');


    /**********          indeed_feedの定義          **********/

    $EDIT_TYPE                          = 'indeed_feed';
    $TABLE_NAME[]                       = $EDIT_TYPE;
    $THIS_TABLE_IS_USERDATA[$EDIT_TYPE] = false;
    $LOGIN_KEY_COLUM[$EDIT_TYPE]        = null;
    $LOGIN_PASSWD_COLUM[$EDIT_TYPE]     = null;
    $LST[$EDIT_TYPE]                    = '[indeed_feed]/indeed_feed.csv';
    $TDB[$EDIT_TYPE]                    = '[indeed_feed]/indeed_feed.csv';
    $ID_HEADER[$EDIT_TYPE]              = '';
    $ID_LENGTH[$EDIT_TYPE]              = 0;

    $API_ALLOW_LIST['mod_indeed_feedApi']   = array('all');
    $js_file_paths['admin']['indeed_feed'] = './module/' . $moduleName . '/common/js/indeed_feed.js';
