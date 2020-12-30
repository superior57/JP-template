<?php

/**********          zipの定義          **********/

	$EDIT_TYPE                            = 'zip';
	$TABLE_NAME[]                         = $EDIT_TYPE;
	$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
	$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
	$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
	$LST[ $EDIT_TYPE ]                    = 'module/zip.csv';
	$TDB[ $EDIT_TYPE ]                    = 'module/zip.csv';
	$ID_HEADER[ $EDIT_TYPE ]              = 'Z';
	$ID_LENGTH[ $EDIT_TYPE ]              = 8;


$js_file_paths['all']['zip'] = './common/js/module/zip/zip.js';
$sp_js_file_paths['all']['zip'] = './common/js/module/zip/zip.js';

?>