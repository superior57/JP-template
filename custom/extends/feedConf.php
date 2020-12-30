<?php

	$CONF_FEED_ENABLE     = true;                                //フィードを更新する場合はtrue。
	$CONF_FEED_TABLES     = Array( 'nUser' , 'cUser' , 'mid' , 'fresh', 'article'); //フィードを生成するテーブル種別。
	$CONF_FEED_MAX_ROW    = 10;                                   //フィードの最大出力可能数。これより多い数はfeedProcで指定しても切り捨てられます。
	$CONF_FEED_OUTPUT_DIR = 'feed/';                              //フィードを出力するディレクトリ。
