<?php

	include_once 'app/mvc/mvc.php';

	$controllerName = 'API';

	if( array_key_exists( 'type' , $_GET ) )
		{ $type = $_GET[ 'type' ]; }
	else if( array_key_exists( 'type' , $_POST ) )
		{ $type = $_POST[ 'type' ]; }
	else
		{ $type = null; }

	ob_start();

	foreach( MVC::GetNeedIncludes( $controllerName ) as $path ) //全てのインクルードパスを処理
		{ include_once $path; }

	$controller = MVC::Call( $controllerName , $type );

	System::flush();
