<?php

	include_once 'app/mvc/mvc.php';

	$controllerName = ( array_key_exists( 'app_controller' , $_GET ) ? $_GET[ 'app_controller' ] : 'Index' );
	$type           = ( array_key_exists( 'type' , $_GET ) ? $_GET[ 'type' ] : null );

	ob_start();

	foreach( MVC::GetNeedIncludes( $controllerName ) as $path ) //全てのインクルードパスを処理
		{ include_once $path; }

	$controller = MVC::Call( $controllerName , $type );

	System::flush();
