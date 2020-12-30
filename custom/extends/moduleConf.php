<?php

	include_once 'include/extends/PathUtil.php';
	include_once 'include/extends/CheckDataAppend.php';

	$entries = glob( './module/*/meta.inc' );

	foreach( $entries as $entry )
		{ include_once $entry; }
