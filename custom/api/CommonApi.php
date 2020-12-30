<?php

	class mod_CommonApi extends apiClass
	{
		/**
		 * 子カラムの要素を出力
		 *
		 * @param tableName テーブル名
		 * @param parentCol 親IDが格納されているカラム名
		 * @param parent 親ID
		 */
		function getChildJsonData( &$param )
		{
			global $SYSTEM_CHARACODE;
			header('Content-Type: application/json;charset='.$SYSTEM_CHARACODE);

			// 選択肢生成に利用
			$valueCol	 = 'id';
			$indexCol	 = 'name';
			$noneIndex	 = '未選択';

			if( $param[ 'CCID' ] )
			{
				$valueCol = $_SESSION[ 'CC' ][ $param[ 'CCID' ] ][ 'valueName' ];
				$indexCol = $_SESSION[ 'CC' ][ $param[ 'CCID' ] ][ 'indexName' ];
			}

			if( strlen($param['noneFlg']) ) { $noneIndex = ''; }

			if( !strlen($param['parent']) ) { print '{"":"'.$noneIndex.'"}';return; }

			$db	 = GMList::getDB($param['tableName']);
			$table = $db->getTable();
			$table = $db->searchTable( $table, $param['parentCol'], '=', '%'.$param['parent'].'%' );
			$countList = null;
			if( strlen($param['countCol']) )
			{
				switch($param['type'])
				{
				case 'buy':
				case 'rental':
					$_GET['adds'] = $param['parent'];
					$_GET['adds_PAL'][] = 'match like';
					break;
				}
				$countList = CountLogic::controller($param['type'], $param['countCol'], $_GET );
			}

			print Format::createJsonData( $db, $table, $valueCol, $indexCol, $noneIndex, $countList );
		}


		/**
		 * 子カラムの要素を出力
		 *
		 * @param tableName テーブル名
		 * @param parentCol 親IDが格納されているカラム名
		 * @param parent 親ID
		 */
		function getStationJsonData( &$param )
		{
			global $SYSTEM_CHARACODE;
			header('Content-Type: application/json;charset='.$SYSTEM_CHARACODE);
			// 選択肢生成に利用
			$valueCol	 = 'id';
			$indexCol	 = 'name';
			$noneIndex	 = '未選択';

			if( !strlen($param['parent']) ) { return; }

			$db	 = GMList::getDB($param['tableName']);
			$table = $db->getTable();
			$table = $db->searchTable( $table, $param['parentCol'], '=', '%'.$param['parent'].'%' );
			$table = $db->searchTable( $table, $param['prefCol'], '=', '%'.$param['pref'].'%' );

			$countList = null;
			if( strlen($param['countCol']) )
			{
				switch($param['type'])
				{
				case 'buy':
				case 'rental':
					$_GET['adds'] = $param['pref'];
					$_GET['adds_PAL'][] = 'match like';
					break;
				}
				$noneIndex = ''; $countList = CountLogic::controller($param['type'], $param['countCol'], $_GET );
			}

			print Format::createJsonData( $db, $table, $valueCol, $indexCol, $noneIndex, $countList );
		}


		/**
		 * 管理者がlockフォルダのデータにアクセス出来るよう簡易ダウンロード
		 *
		 * @param type テーブル名。
		 * @param id レコードID。
		 * @param col ファイルカラム。
		 */
		function download( $param )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			// **************************************************************************************

			if( $loginUserType == 'admin' )
			{
				$db	 = GMList::getDB($param['type']);
				$rec = $db->selectRecord( $param['id'] );

				if( isset($rec) )
				{
					$file = $db->getData( $rec, $param['col'] );
					preg_match( '/(\.\w*$)/', $file, $tmp );
					$name = $param['type'].'_'.$param['id'].$tmp[1];
					SystemUtil::download( $name, $file );
				}
			}
		}


		/**
		 * PC/SP切り替え
		 *
		 * @mode pc/sp
		 */
		function setSmartPhoneDispMode( &$param ) { SmartPhoneUtil::setMode($param['mode']); }

		/**
			@brief     非同期指定されたCCの実行結果を返す。
			@param[in] $param 引数配列。
			@attention ターゲットレコード等の設定の問題で、同期実行とは異なる結果が返る可能性があります。
		*/
		function callASyncCC( $param )
		{
			global $gm;

			$tokenName = 'async_cc_' . $param[ 'id' ];

			if( !isset( $_SESSION[ $tokenName ] ) ) //トークンが存在しない場合
			{
				print 'async token error';
				return;
			}

			$code = $_SESSION[ $tokenName ];

			unset( $_SESSION[ $tokenName ] );

			print $gm[ 'system' ]->getCCResult( null , $code );
		}

		/**
			@brief     検索ページの出力内容を返す。
			@param[in] $param 引数配列。
		*/
		function embedSearch( $param ) //
		{
			global $gm;

			print $gm[ 'system' ]->getCCResult( null , '<!--# code embedSearch ' . http_build_query( $param , null , '&' ) . ' #-->' );
		}

		/**
			@brief     検索結果の件数を返す。
			@param[in] $param 引数配列。
		*/
		function embedSearchRow( $param ) //
		{
			global $gm;

			print $gm[ 'system' ]->getCCResult( null , '<!--# code embedSearchRow ' . http_build_query( $param , null , '&' ) . ' #-->' );
		}


		function getMapData( $param ){

			global $SYSTEM_CHARACODE;
			header('Content-type: text/xml; charset='.$SYSTEM_CHARACODE);

			//範囲データ取得
			$ne_lat = $param["ne_lat"];
			$sw_lat = $param["sw_lat"];
			$ne_lng = $param["ne_lng"];
			$sw_lng = $param["sw_lng"];

			$mode = viewMode::getViewMode();
			$gm = GMList::getGM($mode);
			$db = $gm->getDB();
			$table = $db->getTable();
			
			$table = $this->getJobTable($mode, $table, $param); 
			
			$table = $db->searchTable( $table, "lat", '!', 0 );
			$table = $db->searchTable( $table, "lon", '!', 0 );
			$table = $db->searchTable( $table, "lat", '<', $ne_lat );
			$table = $db->searchTable( $table, "lat", '>', $sw_lat );
			$table = $db->searchTable( $table, "lon", '<', $ne_lng );
			$table = $db->searchTable( $table, "lon", '>', $sw_lng );
			$table = $db->sortTable( $table, 'regist','desc' );
			//$table = $db->limitOffset( $table , 0 , 99 );

			$row = $db->getRow($table);
			$design = Template::getLabelFile( 'MAP_SEARCH_SIDE_LIST' );

			for($i=0;$i<$row;$i++){
				$rec = $db->getRecord( $table, $i );
				$gm->setVariable("num", ($i+1));
				$html=$gm->getString( $design , $rec , "job" );
				$body.="<Locate>\n";
				$body.="<html><![CDATA[".$html."]]></html>\n";
				$body.="<lat>".$db->getData($rec,"lat")."</lat>\n";
				$body.="<lng>".$db->getData($rec,"lon")."</lng>\n";
				$body.="<id>".$db->getData($rec,"id")."</id>\n";
				$body.="<name>".preg_replace("/&/","&amp;",$db->getData($rec,"name"))."</name>\n";
				$body.="<type>".$mode."</type>\n";
				if($db->getData($rec,"foreign_flg")){
					$body.="<address>".$db->getData($rec,"foreign_address")."</address>\n";
				}else{
					$body.="<address>".preg_replace("/&/","&amp;",(
							systemUtil::getTableData("adds",$db->getData($rec,"work_place_adds"),"name").
							systemUtil::getTableData("add_sub",$db->getData($rec,"work_place_add_sub"),"name")
							.$db->getData($rec,"work_place_add_sub2").$db->getData($rec,"work_place_add_sub3")))."</address>\n";
				}
				$body.="</Locate>\n";
			}

			//sleep(1);

			//出力
			echo "<?xml version=\"1.0\" standalone=\"yes\"?>\n";
			echo "<Locations>\n";
			echo $body;
			echo "</Locations>\n";
		}

		/**
		 * @brief 検索結果の件数を返す。
		 * @param string $type 求人タイプ
		 * @param Database $db 求人タイプのDB
		 * @param TableBase $table 求人タイプのTable
		 * @return TableBase 条件を付加したTable
		*/
		private function getJobTable($type, $table, $param) {
			global $loginUserType;
			global $loginUserRank;
			global $gm;

			$tmp = $_GET;
			$_GET = $param;
			$_GET['limit'][0] = 'on';

			$sys = SystemUtil::getSystem($type);
			$sys->searchProc($gm, $table, $loginUserType, $loginUserType);
			
			$_GET = $tmp;

			return $table;
		}

		/**
		 * 仮登録完了時、本登録URLを登録者(非会員)にメールで通知
		 *
		 * @param array $param ユーザー種別とメールアドレス
		 */
		function sendValidity(&$param)
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $THIS_TABLE_IS_USERDATA;
			global $TABLE_NAME;
			global $gm;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;
			// **************************************************************************************

			$type = $param["type"];
			$mail = $param["mail"];

			$sGM = GMList::getGM("system");
			$design = Template::getTemplate("nobody", 15, "validity", "CHECK_VALIDITY_MAIL_" . $type);

			$result = "ok";

			if (! CheckDataBase::is_mail($mail)) {
				$result = "mailFormat";
			}

			$max = count($TABLE_NAME);
			for ($i = 0; $i < $max; $i ++) {
				if ($THIS_TABLE_IS_USERDATA[$TABLE_NAME[$i]]) {
					$db = $gm[$TABLE_NAME[$i]]->getDB();
					$table = $db->getTable();
					$table = $db->searchTable($table, 'mail', '=', $mail);
					if ($db->existsRow($table)) {
						$result = "dup";
						break;
					}
				}
			}
			$key = base64_encode($mail);

			// 一部メーラーにて=で終わるURLが対応していないケースがある。
			// =はbase64におけるpadding文字なので削っても問題なし。
			$key = str_replace("=", "", $key);

			$url = "index.php?app_controller=register&type=" . $type . "&key=" . $key;
			$sGM->setVariable("url", $url);

			if ($result == "ok") {
				Mail::send($design, $MAILSEND_ADDRES, $mail, $sGM);
			}
			if (SystemUtil::existsModule('nobody')) {
				nobodyLogic::clearNBID();
			}

			print $result;
		}

		/*
		 * アップロードファイルの保存先の確認
		 */
		function checkUploadFileSetting($param){
			global $S3;

			$result = 'not';
			if(isset($S3)){
				$result = $S3->getS3Setting();
			}
			switch ($result)
			{
				case 'ok':
					$message = "AWS S3の設定が完了しております。現在のアップロードファイルの保存先はAWS S3です。";
					break ;
				case 'error':
					$message = "AWS S3の接続ができません。設定を確認してください。";
					break ;
				case 'not';
					$message = "現在のアップロードファイルの保存先はシステムの設置しているサーバです。";
					break ;
			}
			echo $message;
		}
	}

?>