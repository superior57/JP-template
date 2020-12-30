<?php


	include_once "./custom/extends/debugConf.php";
	include_once "./include/base/Util.php";
	include_once "./custom/conf.php";
	include_once "./include/base/ccProcBase.php";
	include_once "./include/ccProc.php";
	include_once "./include/IncludeObject.php";
	include_once "./include/GUIManager.php";
	include_once "./include/Search.php";
	include_once "./include/Mail.php";
	include_once "./include/Template.php";
	include_once "./include/Command.php";
	include_once "./include/GMList.php";



	interface Initialize{
		public function initTable();

		/*
		public function initValue($args){

		}
		*/

	}