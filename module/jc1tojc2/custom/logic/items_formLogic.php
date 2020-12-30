<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class Items_formImportLogic extends ImportLogic
{
	var $type = 'items_form';
	var $check_name = 'id';
	var $id_update = false;
	var $delete = true;
}
