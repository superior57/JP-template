<?php

	//★関数 //

	/**
		@brief モジュールのバージョンを取得する。
	*/
	function GetVersion_curl() //
	{
		if( !function_exists( 'curl_version' ) )
			{ return false; }

		$info = curl_version();

		return $info[ 'version' ];
	}
