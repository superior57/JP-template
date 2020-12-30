<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class Items_typeImportLogic extends ImportLogic
{
	var $type = 'items_type';
	var $check_name = 'id';
	var $id_update = false;
	var $delete = true;

}
