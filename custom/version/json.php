<?php

	//★関数 //

	/**
		@brief モジュールのバージョンを取得する。
	*/
	function GetVersion_json() //
		{ return ( function_exists( 'json_encode' ) ? '利用可能' : false ); }
