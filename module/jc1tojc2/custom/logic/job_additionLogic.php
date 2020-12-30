<?php
include_once './module/'.$moduleName.'/custom/logic/ImportLogic.php';

class Job_additionImportLogic extends ImportLogic
{
	var $type = 'job_addition';
	var $check_name = 'id';
	var $id_update = false;
	var $delete = true;
}
