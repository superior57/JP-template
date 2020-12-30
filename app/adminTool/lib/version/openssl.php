<?php

	//★関数 //

	/**
		@brief モジュールのバージョンを取得する。
	*/
	function GetVersion_openssl() //
		{ return function_exists( 'openssl_open' ); }
