<?php

	include_once "include/base/interface/iFileBase.php";
	include_once 'include/extends/FileBaseControl.php';

	//アップロードファイルの保存先変更用の設定
    //$CONF_FILEDIR_ENGINEの設定ファイルを読み込み、アップロードファイルの保存先を変更する
    $CONF_FILEBASE_FLAG = false;
    $CONF_FILEBASE_ENGINE = 'Null';

    $FileBase = \Websquare\FileBase\FileBaseControl::getControl();

