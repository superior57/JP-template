<?php
	
	$moduleName = 'category';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => 'カテゴリ用テーブル3種管理' , ///<このモジュールの説明。
	);
	
	include_once './module/' . $moduleName . '/custom/model/category.php';
	include_once './module/' . $moduleName . '/custom/logic/categoryLogic.php';
	include_once './module/' . $moduleName . '/custom/api/categoryApi.php';
	include_once './module/' . $moduleName . '/custom/view/categoryView.php';
	
	
	/**********          items_formの定義          **********/

		$EDIT_TYPE                            = 'items_form';
		$TABLE_NAME[]                         = $EDIT_TYPE;
		$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
		$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
		$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
		$LST[ $EDIT_TYPE ]                    = '[category]/form.csv';
		$TDB[ $EDIT_TYPE ]                    = '[category]/form.csv';
		$ID_HEADER[ $EDIT_TYPE ]              = 'IF';
		$ID_LENGTH[ $EDIT_TYPE ]              = 5;

	
	/**********          items_typeの定義          **********/
	
		$EDIT_TYPE                            = 'items_type';
		$TABLE_NAME[]                         = $EDIT_TYPE;
		$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
		$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
		$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
		$LST[ $EDIT_TYPE ]                    = '[category]/type.csv';
		$TDB[ $EDIT_TYPE ]                    = '[category]/type.csv';
		$ID_HEADER[ $EDIT_TYPE ]              = 'IT';
		$ID_LENGTH[ $EDIT_TYPE ]              = 5;
	
	
	/**********          items_elementsの定義          **********/
	
		$EDIT_TYPE                            = 'items_elements';
		$TABLE_NAME[]                         = $EDIT_TYPE;
		$THIS_TABLE_IS_USERDATA[ $EDIT_TYPE ] = false;
		$LOGIN_KEY_COLUM[ $EDIT_TYPE ]        = null;
		$LOGIN_PASSWD_COLUM[ $EDIT_TYPE ]     = null;
		$LST[ $EDIT_TYPE ]                    = '[category]/elements.csv';
		$TDB[ $EDIT_TYPE ]                    = '[category]/elements.csv';
		$ID_HEADER[ $EDIT_TYPE ]              = 'IE';
		$ID_LENGTH[ $EDIT_TYPE ]              = 5;
	
	
	
	
    $js_file_paths['admin']['mod_category'] = './module/category/common/js/category.js';



?>
