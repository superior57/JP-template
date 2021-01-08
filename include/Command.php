<?php

	/**
	 * 基本命令クラス
	 *
	 * @author 丹羽一智
	 * @version 1.0.0
	 *
	 */
	class Command extends CommandBase
	{

		/**********************************************************************************************************
		 * システム用メソッド
		 **********************************************************************************************************/

		/**
		 * ベースタグを描画します。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 */
		function base_tag( &$gm, $rec, $args ){
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $HOME;
			global $BASE_TAG;

			// $HOME = 'http://244268ddfa09.ngrok.io/';
			// **************************************************************************************

			if( $BASE_TAG && strlen($HOME) > 0 )
			{
				$url = $HOME;
				if( $_SERVER['HTTPS'] == 'on' ) { $url = str_replace ( 'http://', 'https://', $HOME ); }
				$buffer = '<base href="'.$url.'" />';
			}
			$this->addBuffer( $buffer );
		}

		/**
		 * ログインIDを描画します。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 */
		function loginid( &$gm, $rec, $args ){
			global $LOGIN_ID;
			$this->addBuffer( $LOGIN_ID );
		}

		/**
		 * タイムスタンプを変換します。
		 * 指定が無い場合はシステムデフォルトの物が使用されます。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 * 		第一引数にUNIXタイムを渡します。
		 * 		第二引数にdateに渡すtimeformatを指定します(任意)
		 */
		function timestamp( &$gm, $rec, $args ){
			if(isset($args[1])){ $this->addBuffer(SystemUtil::mb_date( str_replace( '!CODE001;', ' ' , $args[1] ), $args[0] )); }
			else{ $this->addBuffer(SystemUtil::mb_date( $gm->getTimeFormat(), $args[0] )); }
		}

		/**
		 * 現在の時間を取得します。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 */
		function now( &$gm, $rec, $args ){
			$kind	 = isset($args[0])?$args[0]:'';
			$add	 = isset($args[1])?$args[1]:0;

			switch( $kind ){
				case 'y': // 4桁の年
				case 'year':
					$this->addBuffer( date('Y') + $add );
					break;
				case 'm': // 2桁の月
				case 'month':
					$this->addBuffer( sprintf('%02d', date('m') + $add ));
					break;
				case 'd': // 2桁の日
				case 'day':
					$this->addBuffer( sprintf('%02d', date('d') + $add ));
					break;
				case 'h': // 2桁の時
				case 'hour':
					$this->addBuffer( sprintf('%02d', date('H') + $add ));
					break;
				case 'i': //2桁の分
				case 'minute':
					$this->addBuffer( sprintf('%02d', date('i') + $add ));
					break;
				case 's': //2桁の秒
				case 'second':
					$this->addBuffer( sprintf('%02d', date('s') + $add ));
					break;
				case 'u': // unixtime
				case 'unix':
					$this->addBuffer(time()+$add);
					break;
				case 'n': // 月 1～12
                case 'g': // 時 1～12
                case 'G': // 時 0～23
                case 'j': // 日 1～31
					$this->addBuffer( date($kind) + $add );
					break;
				case 'this_month':
					$this->addBuffer( mktime (0, 0, 0, date("m"), 1, date("y")) );
					break;
				case 'prev_month':
					$this->addBuffer( mktime (0, 0, 0, date("m")-1, 1, date("y")) );
					break;
				default:
					$this->addBuffer( SystemUtil::mb_date( $gm->getTimeFormat() ) );
			}
		}


        //タイムスタンプカラム値の名前を受けて、そのタイムスタンプ値の経過年数を返す
        function getPassage( &$gm, $rec, $args ){

			$db		 = $gm->getDB();
            $passage = localtime( $db->getData( $rec, $args[0] ) );
            $now = localtime( );

            $y = $now[5] - $passage[5];
            $m = $now[4] - $passage[4];

            if($m < 0 ){$y--;}

			$this->addBuffer( $y );
        }

        // 年　月　日を受け取って、年齢を描画
        function drawAgeByBirth( &$gm, $rec , $args ){
			if( 1850<$args[0] )
			{
				if(!isset($args[1])){$args[1]=1;}
				if(!isset($args[2])){$args[2]=1;}
				$birth = sprintf("%4d%02d%02d",$args[0],$args[1],$args[2]);
				$now = date('Ymd');
				$this->addBuffer( (int)(($now - $birth)/10000) );
			} else {
				$this->addBuffer("?");
			}
        }
        // dateを受け取って、年齢を描画
        function drawAgeByDate( &$gm, $rec , $args ){

			$db		= $gm->getDB();
			if( isset($db->colType[ $args[0] ] ) ){
    		    $date	= $db->getData( $rec, $args[0] );
			}else{
				$date = $args[0];
			}

        	$date = str_replace( '-','',$date);


        	if( $date ){
				$this->addBuffer( (int)((date('Ymd') - $date)/10000) );
        	}
        }
        // 経過年数をうけとって、現在から遡ったdateを返す
        function getDateByElapsedYears(&$gm, $rec , $args ){
        	$date = sprintf("%4d-%02d-%02d",date("Y")-$args[0],date("n"),(empty($args[1]))?date("j"):date("j")+$args[1]);
			$this->addBuffer( $date );
        }

		/**
			任意の日付を指定してタイムスタンプを出力する。
		*/
		function dateToTime( &$gm , $rec , $args ) //
		{
			$year   = ( is_numeric( $args[ 0 ] ) ? $args[ 0 ] : date( 'Y' ) );
			$month  = ( is_numeric( $args[ 1 ] ) ? $args[ 1 ] : date( 'n' ) );
			$day    = ( is_numeric( $args[ 2 ] ) ? $args[ 2 ] : date( 'j' ) );
			$hour   = ( is_numeric( $args[ 3 ] ) ? $args[ 3 ] : date( 'G' ) );
			$minute = ( is_numeric( $args[ 4 ] ) ? $args[ 4 ] : date( 'i' ) );
			$second = ( is_numeric( $args[ 5 ] ) ? $args[ 5 ] : date( 's' ) );

			$this->addBuffer( mktime( $hour , $minute , $second , $month , $day , $year ) );
		}

		/**
		 * アクティベートコードを発行します。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。このメソッドでは利用しません。
		 */
		function activate( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $HOME;
			// **************************************************************************************

			$db		 = $gm->getDB();
			$this->addBuffer(   $HOME. 'activate.php?type='. $_GET['type'] .'&id='. $db->getData( $rec, 'id' ) .'&md5='. md5(  $db->getData( $rec, 'id' ). $db->getData( $rec, 'mail' )  )   );
		}

		function drawImage( &$gm, $rec, $args ){
		 	if(  is_file( $args[0] )  ){
				// ファイルが存在する場合
				if(  isset( $args[1] ) && isset( $args[2] )  ){
					$this->addBuffer( '<img src="'. $args[0] .'" width="'. $args[1] .'" height="'. $args[2] .'" border="0"/>' );
				}else{
					$this->addBuffer( '<img src="'. $args[0] .'" border="0"/>' );
				}

			}else{
				// ファイルが存在しない場合
				$this->addBuffer( '<span>イメージは登録されていません</span>' );
			}
		 }

		/**
		 * データの件数を取得。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。　第一引数にテーブル名　第二引数にカラム名　第三引数に演算子　第四引数に値　をしています。
		 */
		function getRow( &$gm, $rec, $args ){
			$tgm	 = SystemUtil::getGMforType($args[0]);
			$db		 = $tgm->getDB();
            $table = $db->getTable();
            for($i=0;isset($args[1+$i]);$i+=3){
            	if($args[2+$i] == 'b'){
	    			$table = $db->searchTable( $table, $args[1+$i], $args[2+$i], $args[3+$i], $args[4+$i] );
	    			$i++;
            	}else{
	    			$table = $db->searchTable( $table, $args[1+$i], $args[2+$i], $args[3+$i] );
            	}
            }
            $this->addBuffer( $db->getRow( $table ) );
		}

		/**
			@brief   現在のURLを基準にクエリの追加・削除を行い、その検索結果の行数を取得する。
			@details setまたはunsetに続いて任意のクエリを記述することでそのクエリを現在のクエリとマージします。
			         setとunsetは記述した順に先頭から処理されます。
			         処理法則：
			             set foo=bar   ... fooにbarをセットします。配列での指定も可能です。既存のクエリがスカラであれば上書き、配列であればマージされます。
			             unset foo=bar ... fooの値がbarであったなら削除し、それ以外の場合は残します。配列で削除する値を複数指定することもできます。
			             unset foo     ... 既存の値に関わらず、fooを完全に削除します。
			         記述例：
			             <!--# code rebuildRow unset tag&category set tag[]=foo&tag[]=bar&category[]=fizz&category[]=buzz #--> ... tagとcategoryを初期化した上で任意の値をセット
		*/
		function rebuildRow( &$gm , $rec , $args ) //
		{
			global $gm;
			global $loginUserType;
			global $loginUserRank;
			global $magic_quotes_gpc;

			$baseQuery = $_GET;

			while( count( $args ) ) //全てのCC引数を処理
			{
				$argsType = array_shift( $args );

				if( 'set' == $argsType ) //クエリを追加する場合
				{
					parse_str( array_shift( $args ) , $setQuery );

					foreach( $setQuery as $name => $value ) //全ての追加クエリを処理
					{
						if( is_array( $baseQuery[ $name ] ) ) //現在のクエリが配列の値を持つ場合
						{
							if( is_array( $value ) ) //追加クエリが配列の値を持つ場合
								{ $baseQuery[ $name ] = array_merge( $baseQuery[ $name ] , $value ); }
							else //追加クエリがスカラの値を持つ場合
								{ $baseQuery[ $name ][] = $value; }

							$baseQuery[ $name ] = array_unique( $baseQuery[ $name ] );
						}
						else //基準クエリがスカラの値を持つ場合
							{ $baseQuery[ $name ] = $value; }
					}

					continue;
				}
				else if( 'unset' == $argsType ) //クエリを削除する場合
				{
					parse_str( array_shift( $args ) , $unsetQuery );

					foreach( $unsetQuery as $name => $value ) //全ての削除クエリを処理
					{
						if( !$value ) //削除する値の指定がない場合
							{ unset( $baseQuery[ $name ] ); }
						else //削除する値の指定がある場合
						{
							if( !is_array( $value ) ) //削除する値が配列指定ではない場合
								{ $value = array( $value ); }

							foreach( $value as $elem ) //全ての要素を処理
							{
								if( is_array( $baseQuery[ $name ] ) ) //基準クエリが配列の値を持つ場合
								{
									$index = array_search( $elem , $baseQuery[ $name ] );

									if( FALSE === $index ) //基準クエリの値が削除する値と一致する場合
										{ continue; }

									unset( $baseQuery[ $name ][ $index ] );
								}
								else if( $elem == $baseQuery[ $name ] ) //基準クエリの値が削除する値と一致する場合
									{ unset( $baseQuery[ $name ] ); }
							}
						}
					}

					continue;
				}
			}

			$getSwap = $_GET;
			$_GET    = $baseQuery;

			$search = new Search( $gm[ $_GET[ 'type' ] ] , $_GET[ 'type' ] );
			$db     = $gm[ $_GET[ 'type' ] ]->getDB();
			$system = SystemUtil::getSystem( $_GET[ 'type' ] );

			if( $magic_quotes_gpc || $db->char_code != 'sjis' ) //エスケープが不要な場合
				{ $search->setParamertorSet( $_GET ); }
			else //エスケープが必要な場合
				{ $search->setParamertorSet( addslashes_deep( $_GET ) ); }

			$system->searchResultProc( $gm , $search , $loginUserType , $loginUserRank );

			$table = $search->getResult();

			$system->searchProc( $gm , $table , $loginUserType , $loginUserRank );

			$_GET = $getSwap;

			$this->addBuffer( $db->getRow( $table ) );
		}

		/**
		 * 削除済みデータの件数を取得。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。　第一引数にテーブル名　第二引数にカラム名　第三引数に演算子　第四引数に値　をしています。
		 */
		function getDeleteRow( &$gm, $rec, $args ){
			$tgm	 = SystemUtil::getGMforType($args[0]);
			$db		 = $tgm->getDB();
            $table = $db->getTable('delete');
            for($i=0;isset($args[1+$i]);$i+=3){
            	if($args[2+$i] == 'b'){
	    			$table = $db->searchTable( $table, $args[1+$i], $args[2+$i], $args[3+$i], $args[4+$i] );
	    			$i++;
            	}else{
	    			$table = $db->searchTable( $table, $args[1+$i], $args[2+$i], $args[3+$i] );
            	}
            }
            $this->addBuffer( $db->getRow( $table ) );
		}

		/**
		 * データの合計を取得。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。　第一引数にテーブル名　第二引数に集計カラム名　第三～五引数に検索カラム名、演算子、値　をしています。
		 */
		function getSum( &$gm, $rec, $args ){
			$tgm	 = SystemUtil::getGMforType($args[0]);
			$db		 = $tgm->getDB();
            $table = $db->getTable();
            for($i=0;isset($args[2+$i]);$i+=3){
            	if($args[3+$i] == 'b'){
	    			$table = $db->searchTable( $table, $args[2+$i], $args[3+$i], $args[4+$i], $args[5+$i] );
	    			$i++;
            	}else{
	    			$table = $db->searchTable( $table, $args[2+$i], $args[3+$i], $args[4+$i] );
            	}
            }

            $this->addBuffer( $db->getSum( $args[1], $table ) );
		}

		/**
		 * 任意のテーブルの任意のidの任意のカラムのデータを取り出す
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。　第一引数にテーブル名　第二引数に集計カラム名　第三～五引数に検索カラム名、演算子、値　をしています。
		 */
		function getData( &$gm, $rec, $args ){
			$data = SystemUtil::getTableData( $args[0], $args[1], $args[2] );
			if( is_null($data)){
				$this->addBuffer("");
				return;
			}
			$this->addBuffer($data);
		}

		/**
		 * 任意のテーブルの検索結果から任意のカラムのデータを連結して取り出す
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。　第一引数にテーブル名　第二引数に集計カラム名　第三引数に区切り文字　第四引数移行に検索カラム名、演算子、値　を指定します。
		 */
		function findData( &$gm , $rec , $args )
		{
			$targetType   = array_shift( $args );
			$targetColumn = array_shift( $args );
			$separator    = array_shift( $args );

			$targetDB    = GMList::getDB( $targetType );
			$targetTable = $targetDB->getTable();
			$results = array();

			while( count( $args ) ) //全ての引数を処理
			{
				$column = array_shift( $args );
				$op     = array_shift( $args );
				$value  = array_shift( $args );

				if( 'b' == $op ) //範囲検索の場合
				{
					$subValue    = array_shift( $args );
					$targetTable = $targetDB->searchTable( $targetTable , $column , $op , $value , $subValue );
				}
				else //通常の検索の場合
					{ $targetTable = $targetDB->searchTable( $targetTable , $column , $op , $value ); }
			}

			$row = $targetDB->getRow( $targetTable );

			for( $i = 0 ; $row > $i ; ++$i ) //全ての行を処理
			{
				$rec       = $targetDB->getRecord( $targetTable , $i );
				$results[] = $targetDB->getData( $rec , $targetColumn );
			}

			$this->addBuffer( implode( $separator , $results ) );
		}

		/**
		 * コードが記述されたページをログイン後にリダイレクトするページとして記録
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。　第一引数にテーブル名　第二引数に集計カラム名　第三～五引数に検索カラム名、演算子、値　をしています。
		 */
		function saveRedirectPage( &$gm, $rec, $args ){
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			// **************************************************************************************

			$notAdmin = $args[0];
            $script = SystemInfo::GetScriptName();

			$nameList = array('previous_page');
			if( !strlen($notAdmin) ) { $nameList[] = 'previous_page_admin'; }

			foreach($nameList as $name)
			{
				$_SESSION[$name] = $script;
				if( strlen( $_SERVER[ 'QUERY_STRING' ] ) ) { $_SESSION[$name] .= '?' . $_SERVER[ 'QUERY_STRING' ]; }
			}
		}

		/**********************************************************************************************************
		 * 拡張システム用メソッド
		 **********************************************************************************************************/

		/**
		 * ユーザ名取得。
		 * IDからユーザ名を検索し、該当する ユーザ名( ユーザID ) の形式で出力します。
		 * どのユーザ情報テーブルにユーザデータがあるのかわからないときなどに有効です。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。　第一引数にIDを渡します。 第二引数にリンクするかを真偽値で渡します。
		 */
		function getName( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $TABLE_NAME;
			global $THIS_TABLE_IS_USERDATA;
			// **************************************************************************************

			$link_f = $args[1];
			$null_msg = $args[2];

			for( $i=0; $i<count($TABLE_NAME); $i++ )
			{
				if(  $THIS_TABLE_IS_USERDATA[ $TABLE_NAME[$i] ]  )
				{
					$tgm	 = SystemUtil::getGMforType( $TABLE_NAME[$i] );
					$db		 = $tgm->getDB();
					$rec	 = $db->selectRecord( $args[0] );
					if( $rec )
					{
						if( strtolower( $link_f ) == 'true' )
						{
							$this->addBuffer(
								'<a href="index.php?app_controller=info&type='. $TABLE_NAME[$i] .'&id='. $db->getData( $rec, 'id' ) .'">'.
								$db->getData( $rec, 'name' ). '( '. $db->getData( $rec, 'id' ). ' )'.
								'</a>'  );
						}else
						{
							$this->addBuffer(  $db->getData( $rec, 'name' ). '( '. $db->getData( $rec, 'id' ). ' )'  );
						}
						return;
					}
				}
			}
			$this->addBuffer( $null_msg );
		}



		/**
		 * データ名を取得。
		 * IDからデータを検索し、該当する データ名( データID ) の形式で出力します。
		 * どのテーブルにデータがあるのかわからないときなどに有効です。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。　第一引数にIDを渡します。　第二引数に名前の格納されているカラム名を渡します。 第三引数にリンクするかを真偽値で渡します。
		 */
		function getDataName( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $TABLE_NAME;
            global $ID_LENGTH;
			// **************************************************************************************

			// 全てのテーブルのGUIManagerインスタンスを取得します。
			$tgm	 = SystemUtil::getGM();
			$flg	 = false;
			for( $i=0; $i<count($tgm); $i++ ){

                if( $ID_LENGTH[ $TABLE_NAME[$i] ] == 0)
                    continue;

				$db		 = $tgm[ $TABLE_NAME[$i] ]->getDB();
				$table	 = $db->searchTable( $db->getTable(), 'id', '=', $args[0] );
				if( $rec = $db->getFirstRecord( $table ) )
				{
					if( $args[2] == 'true' || $args[2] == 'TRUE' )
					{
						$this->addBuffer(
							'<a href="index.php?app_controller=info&type='. $TABLE_NAME[$i] .'&id='. $db->getData( $rec, 'id' ) .'">'.
							$db->getData( $rec, $args[1] ). '( '. $db->getData( $rec, 'id' ). ' )'.
							'</a>'  );
					}
					else
					{
						$this->addBuffer(  $db->getData( $rec, $args[1] ). '( '. $db->getData( $rec, 'id' ). ' )'  );
					}
					$flg	 = true;
					break;
				}
			}

			if( !$flg )	{ $this->addBuffer( '該当データ無し' ); }
		}




		/*******************************************************************************************************/
		/*******************************************************************************************************/
		/*******************************************************************************************************/
		/*******************************************************************************************************/
		/*******************************************************************************************************/



		/**********************************************************************************************************
		 * サイトシステム用メソッド
		 **********************************************************************************************************/




		/*******************************************************************************************************/
		/*******************************************************************************************************/
		/*******************************************************************************************************/
		/*******************************************************************************************************/
		/*******************************************************************************************************/



		/**********************************************************************************************************
		 *　拡張汎用メソッド
		 **********************************************************************************************************/


		/**
		 * 引数で渡した数字までを選択できるselectコントロールを表示。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 *
		 * 第一引数でnameを指定
		 * 第二引数で最後の数字を指定値(省略可)
		 * 第三引数で初期値(選択中の項目の数字を指定値)(省略可)
		 * 第四引数で開始値(省略可)
         * 第五引数で接頭項目の追加値(例：未選択) (省略可)
         * 第六引数でタグオプションを設定（省略可能）
		 */
        function num_option( &$gm , $rec , $args ){

            $name = $args[0];

            $max = 1;
            if(strlen($args[1])){ $max = $args[1]; }

            $check = 0;
            if( isset( $_POST[$args[0]] ) && strlen( $_POST[$args[0]] ) ){ $check = $_POST[$args[0]]; }
            else if( isset($args[2]) && strlen($args[2])){ $check = $args[2]; }

            $start = 1;
            if( isset( $args[3] ) && strlen($args[3])){ $start = $args[3]; }

            $option = "";
            if( isset( $args[5] ) && strlen($args[5]) ){ $option = $args[5]; }


            if( strlen($name) ){
                $index = "";
                $value  = "";
                if(  isset( $args[4] ) && strlen($args[4]) ){
                    $index .= $args[4].'/';
                    $value  .= '/';
                }
                for($i=$start;$i<$max;$i++){
                    $index .= $i.'/';
                    $value  .= $i.'/';
                }
                $index .= $i;
                $value  .= $i;

                $this->addBuffer( $gm->getCCResult( $rec, '<!--# form option '.$name.' '.$check.' '.$value.' '.$index.' '.$option.' #-->' ) );
            }

        }

        /**
         * 引数で指定した文字と同数の*を出力する。
         *
         */
        function drawPassChar( &$gm , $rec , $args ){
            $PASS_CHAR = '*';
            $str = "";
            for($i=0;strlen($args[0]) > $i ;$i++){
                $str .= $PASS_CHAR;
            }
            $this->addBuffer( $str );
        }

		//現在のページのURLを表示する
		function currentPage( &$gm , $rec , $args )
		{
			$uri = $_SERVER[ 'REQUEST_URI' ];
			$uri = h( $uri , ENT_QUOTES | ENT_HTML401);

			$this->addBuffer( $uri );
		}

		//現在のページのURLを表示する
		function currentURL( &$gm , $rec , $args )
		{
			$uri = ( 'on' == $_SERVER[ 'HTTPS' ] ? 'https://' : 'http://' ) . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];
			$uri = h( $uri , ENT_QUOTES | ENT_HTML401 );

			$this->addBuffer( $uri );
		}

        /*
          注目の○○リストを表示
          つまりは、任意のテーブルの任意のフラグがtrueの項目を一覧として表示する。

        args
         0:テーブル名
         1:フラグカラム名
         2:表示数
        */
        function attentionListDraw( &$gm, $rec , $args ){
        global $loginUserType;
        global $loginUserRank;
            $HTML = Template::getTemplate( $loginUserType , $loginUserRank , $args[0] , 'ATTENTION_TEMPLATE' );

            if( !strlen( $HTML ) ){
                throw new InternalErrorException('dos not template');
            }

            $tgm = SystemUtil::getGMforType( $args[0] );
            $db = $tgm->getDB();
            $list = $db->searchTable( $db->getTable() , $args[1] , '=' , true );

            $row = $db->getRow( $list );

            $this->addBuffer( $tgm->getString( $HTML , null , 'head' ) );
            for( $i = 0 ; $i < $args[2] ; $i++ ){
                if( $i < $row ){
                    $lrec = $db->getRecord( $list , $i );
                    $this->addBuffer( $tgm->getString( $HTML , $lrec , 'element' ) );
                }else{
                    $this->addBuffer( $tgm->getString( $HTML , null , 'dummy' ) );
                }
            }
            $this->addBuffer( $tgm->getString( $HTML , null , 'foot' ) );
        }

        /*
          新着の○○リストを表示
          つまりは、任意のテーブルのregistが指定した期間以内の項目を一覧表示。

        args
         0:テーブル名
         1:新着とする期間(時間で)
         2:表示数
        */
        function newListDraw( &$gm, $rec , $args ){
        global $loginUserType;
        global $loginUserRank;
            $HTML = Template::getTemplate( $loginUserType , $loginUserRank , $args[0] , 'NEW_TEMPLATE' );

            if( !strlen( $HTML ) ){
                throw new InternalErrorException('dos not template');
            }

            $tgm = SystemUtil::getGMforType( $args[0] );
            $db = $tgm->getDB();
            $list = $db->searchTable( $db->getTable() , 'regist' , '>' , time() - ($args[1]*60*60) );
            $row = $db->getRow( $list );

            $this->addBuffer( $tgm->getString( $HTML , null , 'head' ) );
            for( $i = 0 ; $i < $args[2] ; $i++ ){
                if( $i < $row ){
                    $lrec = $db->getRecord( $list , $i );
                    $this->addBuffer( $tgm->getString( $HTML , $lrec , 'element' ) );
                }else{
                    $this->addBuffer( $tgm->getString( $HTML , null , 'dummy' ) );
                }
            }
            $this->addBuffer( $tgm->getString( $HTML , null , 'foot' ) );
        }

        /*
         * レコードに値が存在する場合リンクを表示する
         *
         * 0:レコード名
         * 1:URL
         * 2:リンクの表示文言
         * 3:リンクが無い場合の表示文言
         */
         function drawLinkByRec( &$gm, $rec, $args ){
             $db = $gm->getDB();
             $data = $db->getData( $rec , $args[0]);
             if( ! strlen($data) ){
                 $this->addBuffer( $args[3] );
             }else{
                 //Linkが空の時はrecのデータ
                 if( !strlen($args[1]) )
                     $url = $data;
                 else
                     $url = $args[1];

                 $this->addBuffer( '<a href="'.$url.'">'.$args[2].'</a>' );
             }
         }

        /*
         * 引数が存在する場合リンクを表示する
         *
         * 0:URL
         * 1:リンクの頭に付ける文字（mailto:とか
         */
         function drawLink( &$gm, $rec, $args ){
             if( strlen($args[0]) )
                 $this->addBuffer( '<a href="'.$args[1].$args[0].'" target="_blank">'.$args[0].'</a>' );
         }


        function getReferer(&$gm , $rec , $args ){
            $this->addBuffer( $_SERVER['HTTP_REFERER'] );
        }

        /*
         * 複数ID指定に対応したリンク出力
         * レコードに値が存在する場合リンクを表示する
         *
         * 0:レコード名
         * 1:URL(末尾にIDを付与する形)
         * 2:リンクの表示文言
         * 3:リンクが無い場合の表示文言
         */
         function drawLinkMultiID( &$gm, $rec, $args ){
             $sep = '/';

             $db = $gm->getDB();
             $data = $db->getData( $rec , $args[0]);
             if( ! strlen($data) ){
                 $this->addBuffer( $args[3] );
             }else{
                 $array = explode( $sep , $data );

                 $row = count( $array );
                 for($i=0; $i < $row-1 ; $i++){
                     $this->addBuffer( '<a href="'.$args[1].$array[$i].'">'.$args[2].'</a><br/>' );
                 }

                 $this->addBuffer( '<a href="'.$args[1].$array[$i].'">'.$args[2].'</a>' );
             }
         }


         //1:全角かな 2:半角カナ 3:英字 4:数字。
         function getInputMode( &$gm , $rec , $args ){
         global $terminal_type; // 1:docomo 2:au 3:softbank
             $e = Array(
                     1 => Array( '1' => 'istyle="1" style="-wap-input-format:&quot;*&lt;ja:h&gt;&quot;"' ,
                                  '2' => 'istyle="2" style="-wap-input-format:&quot;*&lt;ja:hk&gt;&quot;"' ,
                                  '3' => 'istyle="3" style="-wap-input-format:&quot;*&lt;ja:en&gt;&quot;"' ,
                                  '4' => 'istyle="4" mode="numeric" style="-wap-input-format:&quot;*&lt;ja:n&gt;&quot;"' ) ,
                     2 => Array( '1' => 'format="*M"' , '2' => 'istyle="2"' , '3' => 'format="*x"' , '4' => 'format="*N"' ) ,
                     3 => Array( '1' => 'MODE="hiragana"' , '2' => 'MODE="hankakukana"' , '3' => 'MODE="alphabet"' , '4' => 'MODE="numeric"' ) );
             $this->addBuffer( $e[$terminal_type][$args[0]] );
         }

         //args[0]:「0」～「9」、「*」、「#」
         //args[1]: true 'NONUMBER' ,false ''
         function getAccesskey( &$gm , $rec , $args ){
         global $terminal_type;
//             $nonumber = '';
             // 1:docomo 2:au 3:softbank
             $elements = Array( 0 => 'accesskey' , 1 => 'accesskey', 2 => 'accesskey', 3 => 'DIRECTKEY' );

             $element = $elements[$terminal_type];

/*             if( $terminal_type == 3 ){
                 $nonumber = 'NONUMBER';
             }*/
//             $this->addBuffer( $element.'="'.$args[0].'"'.$nonumber );
             $this->addBuffer( $element.'="'.$args[0].'"' );
         }


         /*
          *  続きを見る様に文字列を切り出すするメソッド
          *　（引数に文字列を持たす形にすると、実装時に半角スペースでのセパレートに泣く事になる可能性が高いので要考慮
          *
          * 0:切り出し対象の文字列
          * 1:切り出し文字列の長さ(省略可能、システムのデフォルトの文字長
          */
         function Continuation( &$gm , $rec , $args ){
		 	global $SYSTEM_CHARACODE;
             if( !isset($args[1]) || $args[1] <= 0 ){
                $num = 32;
             }else{
             	$num = $args[1];
             }

             if( !isset($args[2]) || !strlen($args[2]) ){
                $sufix = "…";
             }else{
             	$sufix = $args[2];
             }


             $str = $args[0];

			$sufLength = mb_strlen( $sufix , $SYSTEM_CHARACODE );

			if( mb_strlen( str_replace( array('!CODE001;','!CODE101;'), ' ' , $str ) , $SYSTEM_CHARACODE ) > $num ){
				$this->addBuffer( str_replace( ' ' , '!CODE101;', mb_substr( str_replace( array('!CODE001;','!CODE101;'), ' ' , $str ), 0 , $num, $SYSTEM_CHARACODE ) . $sufix ) );
			}else{
				$this->addBuffer( $args[ 0 ] );
			}
         }

         /*
          * 基本システムの各種コードの引数に使うために、文字列内の半角スペースをEscapeして返す。
          *
          * 0:エスケープを行う文字列
          */
         function spaceEscape( &$gm , $rec , $args ){
             $this->addBuffer( join( '\ ' , $args) );
         }

         function urlencode( &$gm , $rec , $args ){
             $this->addBuffer( urlencode( $args[0] ) );
         }


		function base64encode( &$gm , $rec , $args ){
			$this->addBuffer( base64_encode( $args[0] ) );
		}

         /*
          * テキスト内のURLをリンクに変換して表示する。
          *
          * 0:変換を行う文字列
          * 1:Aタグに付加する属性
          */
		function linkText( &$gm , $rec , $args )
		{
			$re = "\b(?:https?|shttp):\/\/(?:(?:[-_.!~*'()a-zA-Z0-9;:&=+$,]|%[0-9A-Fa-f" .
			      "][0-9A-Fa-f])*@)?(?:(?:[a-zA-Z0-9](?:[-a-zA-Z0-9]*[a-zA-Z0-9])?\.)" .
			      "*[a-zA-Z](?:[-a-zA-Z0-9]*[a-zA-Z0-9])?\.?|[0-9]+\.[0-9]+\.[0-9]+\." .
			      "[0-9]+)(?::[0-9]*)?(?:\/(?:[-_.!~*'()a-zA-Z0-9:@&=+$,]|%[0-9A-Fa-f]" .
			      "[0-9A-Fa-f])*(?:;(?:[-_.!~*'()a-zA-Z0-9:@&=+$,]|%[0-9A-Fa-f][0-9A-" .
			      "Fa-f])*)*(?:\/(?:[-_.!~*'()a-zA-Z0-9:@&=+$,]|%[0-9A-Fa-f][0-9A-Fa-f" .
			      "])*(?:;(?:[-_.!~*'()a-zA-Z0-9:@&=+$,]|%[0-9A-Fa-f][0-9A-Fa-f])*)*)" .
			      "*)?(?:\?(?:[-_.!~*'()a-zA-Z0-9;\/?:@&=+$,]|%[0-9A-Fa-f][0-9A-Fa-f])" .
			      "*)?(?:#(?:[-_.!~*'()a-zA-Z0-9;\/?:@&=+$,]|%[0-9A-Fa-f][0-9A-Fa-f])*)?";

			$str = preg_replace( '/(' . $re . ')/' , '<a href="$1" ' . $args[ 1 ] . '>$1</a>' , $args[ 0 ] );

			$this->addBuffer( $str );
		}

		/**
		 * 条件分岐。 第一引数と第二引数が一致するかどうか。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。第一引数と第二引数の内容が一致した場合は　第三引数を、一致しなかった場合は第四引数を表示します。
		 */
		function ifelse( &$gm, $rec, $args ){
			if( $args[0] == $args[1] ){
				$this->addBuffer( $args[2] );
			}else if( isset($args[3]) ){
				$this->addBuffer( $args[3] );
			}
		}

		/**
		 * 条件分岐。 値がセットされているかどうか
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。第一引数と第二引数の内容が一致した場合は　第三引数を、一致しなかった場合は第四引数を表示します。
		 */
		function is_set( &$gm, $rec, $args ){
			if( $args[0] != "" ){
				$this->addBuffer( $args[1] );
			}else if(isset($args[2])){
				$this->addBuffer( $args[2] );
			}
		}

		/**
		 * 条件分岐。 正規表現マッチ
		 *
		 * @param args 0 値
		 * @param args 1 正規表現
		 * @param args 2 true draw
		 * @param args 3 false draw
		 */
		function ifmatch( &$gm, $rec, $args ){

			if( mb_ereg( $args[1], $args[0] ) !== FALSE ){
				$this->addBuffer( $args[2] );
			}else{
				$this->addBuffer( $args[3] );
			}
		}

		/**
		 * 第一引数に値が存在する場合は値を、存在しない場合は第二引数を出力
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 */
		function substitute( &$gm, $rec, $args ){
			if( $args[0] != "" ){
				$this->addBuffer( $args[0] );
			}else if(isset($args[1])){
				$this->addBuffer( $args[1] );
			}
		}


		/**
		 * ソートのためのURLを描画します。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。このメソッドでは利用しません。
		 */
		function sortLink( &$gm, $rec, $args ){
			$sort = '';
			if( isset( $_GET['sort'] ) ){
				$sort	 = $_GET['sort'];
			}
			if( $args[0] != '' ) { $sort	 =  $args[0]; }

			$url	 = basename($_SERVER['SCRIPT_NAME']).'?'.SystemUtil::getUrlParm($_GET);
			$url	 = preg_replace("/&sort=\w+/", "",$url);
			$url	 = preg_replace("/&sort_PAL=\w+/", "",$url);
			$url	.= '&sort='.$sort.'&sort_PAL=';
            if( isset($args[1]) && strlen($args[1]) ){
                 $url	 .= $args[1];
            }else if( isset($_GET['sort']) && $sort == $_GET['sort'] )
			{// ソート条件が現在と同一の場合
				if( $_GET['sort_PAL'] == 'asc' ){ $url	 .= 'desc'; }
				else							{ $url	 .= 'asc'; }
			}else{ $url	 .= 'desc'; }

			$this->addBuffer( $url );
		}


		/**
		 * GETパラメータ文字列を再現します。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。このメソッドでは利用しません。
		 */
		function getParam( &$gm, $rec, $args ){

				$param = $_GET;
			//除外するパラメータ
			if( isset($args[0]) ){
				unset($param[$args[0]]);
			}

			$this->addBuffer( SystemUtil::getUrlParm($param) );
		}

        //周期的に指定項目を出力する
        //1:cycle_id   1ページ内で複数の周期を仕様する際に、それぞれを区別するため
        //2:周期間隔 2～
        //3～:パターンの中身。  周期間隔の数だけ続く
        function drawPatternCycle( &$gm, $rec, $args ){
            global $CYCLE_PATTERN_STRUCT;
            $id = $args[0];
            if(!isset($CYCLE_PATTERN_STRUCT[$id]) ){
                $CYCLE_PATTERN_STRUCT[$id]['cnt'] = 0;
                $CYCLE_PATTERN_STRUCT[$id]['interval'] = $args[1];
                $CYCLE_PATTERN_STRUCT[$id]['pattern'] = array_slice( $args , 2 );
            }

            $this->addBuffer( $CYCLE_PATTERN_STRUCT[$id]['pattern'][ $CYCLE_PATTERN_STRUCT[$id]['cnt'] ] );

            $CYCLE_PATTERN_STRUCT[$id]['cnt']++;
            if( $CYCLE_PATTERN_STRUCT[$id]['cnt'] >= $CYCLE_PATTERN_STRUCT[$id]['interval'] )
                $CYCLE_PATTERN_STRUCT[$id]['cnt'] = 0;
        }
        //drawPatternCycleの現在のデータをインクリメントを行なわず表示する
        function drawPatternNow( &$gm, $rec, $args ){
            global $CYCLE_PATTERN_STRUCT;
            $id = $args[0];
            if(!isset($CYCLE_PATTERN_STRUCT[$id]) ){
                //drawPatternCycleが先に呼ばれていない場合はスルー
                return;
            }

            $this->addBuffer( $CYCLE_PATTERN_STRUCT[$id]['pattern'][ $CYCLE_PATTERN_STRUCT[$id]['cnt'] ] );
        }
        //drawPatternCycleの現在のデータをインクリメントを行なわず対応するデザインを表示する
        function drawPatternSet( &$gm, $rec, $args ){
            global $CYCLE_PATTERN_STRUCT;
            $id = $args[0];
            if(!isset($CYCLE_PATTERN_STRUCT[$id]) ){
                //drawPatternCycleが先に呼ばれていない場合はスルー
                return;
            }

            $this->addBuffer( $args[ $CYCLE_PATTERN_STRUCT[$id]['cnt']+1 ] );
        }

		/**
		 * 数字にコンマをつけて出力します。
		 *
		 * @param gm GUIManagerオブジェクトです。このメソッドでは利用しません。
		 * @param rec 登録情報のレコードデータです。このメソッドでは利用しません。
		 * @param args コマンドコメント引数配列です。 このメソッドでは利用しません。
		 */
		function comma( &$gm, $rec, $args ){
            $this->addBuffer(number_format(floor($args[0])). strstr($args[0], '.'));
		}

		/*
		 * モジュールが存在するかどうかを確認します
		 *
		 * addBuffer:TRUE/FALSE
		 *
		 * @param gm GUIManagerオブジェクトです。このメソッドでは利用しません。
		 * @param rec 登録情報のレコードデータです。このメソッドでは利用しません。
		 * @param args コマンドコメント引数配列です。 第一引数にモジュール名を指定します。
		 */
		function mod_on( &$gm, $rec, $args ){
			if( class_exists( 'mod_'.$args[0] ) ){
				$this->addBuffer( 'TRUE' );
			}else{
				$this->addBuffer( 'FALSE' );
			}
		}


		/**
		 * 指定されたカラムのデータと引数の論理演算の選択になるようなvalueパラメータを出力
		 *
		 * @param gm GUIManagerオブジェクトです。このメソッドでは利用しません。
		 * @param rec 登録情報のレコードデータです。このメソッドでは利用しません。
		 * @param args コマンドコメント引数配列です。 第一引数にモジュール名を指定します。
		 */
		function chengeLogicalOperation( &$gm, $rec, $args ){
			$db = $gm->getDB();
			$data = $db->getData( $rec, $args[0] );

			if(is_null($data)){
				$data = $args[2];
			}

			$data = (int)$data;
			$key_num = (int)$args[1];

			$ret = ( ($data & $key_num) ? $data-$key_num : $data )."/".( $data | $key_num );
			$this->addBuffer( $ret );
		}


		/**
		 * authenticity_tokenを埋め込む
		 * @param gm GUIManagerオブジェクトです。このメソッドでは利用しません。
		 * @param rec 登録情報のレコードデータです。このメソッドでは利用しません。
		 * @param args コマンドコメント引数配列です。 第一引数にモジュール名を指定します。
		 */
		function drawAuthenticityToken( &$gm, $rec, $args ){
			$this->addBuffer( '<input name="authenticity_token" type="hidden" value="'. h( SystemUtil::getAuthenticityToken() ) .'" />' );
		}


		/*
		 * 渡された複数の数値を比較し、一番小さいものを返す
		 * @param gm GUIManagerオブジェクトです。このメソッドでは利用しません。
		 * @param rec 登録情報のレコードデータです。このメソッドでは利用しません。
		 * @param args コマンドコメント引数配列です。 第一引数にモジュール名を指定します。
		 */
		function selectLower( &$gm, $rec, $args ){
			if( count($args) <= 0 ){ return 0; }
			else if ( count($args) <= 1 ){ return $args[0]; }

			$min = $args[0];

			foreach( $args as $v ){
				if( $min > $v ){
					$min = $v;
				}
			}
			$this->addBuffer($min);
		}

		/*
		 * 渡された複数の数値を比較し、一番大きいものを返す
		 * @param gm GUIManagerオブジェクトです。このメソッドでは利用しません。
		 * @param rec 登録情報のレコードデータです。このメソッドでは利用しません。
		 * @param args コマンドコメント引数配列です。 第一引数にモジュール名を指定します。
		 */
		function selectUpper( &$gm, $rec, $args ){
			if( count($args) <= 0 ){ return 0; }
			else if ( count($args) <= 1 ){ return $args[0]; }

			$max = $args[0];

			foreach( $args as $v ){
				if( $max < $v ){
					$max = $v;
				}
			}

			$this->addBuffer($max);
		}

		/**
			@brief 検索ページを埋め込んで表示する。
			@param $iGM   GUIManagerオブジェクトです。このメソッドでは利用しません。
			@param $iRec  登録情報のレコードデータです。このメソッドでは利用しません。
			@param $iArgs コマンドコメント引数配列です。検索ページに渡すクエリパラメータを指定します。
		*/
		function embedSearch( &$iGM , $iRec , $iArgs ) //
		{
			global $gm;
			global $loginUserType;
			global $loginUserRank;

			$this->getEmbedParameter( $iArgs , $query , $search , $db , $system , $table );

			$exists = $db->existsRow( $table );

			$getSwap   = $_GET;
			$_GET      = $query;
			$queryHash = sha1( serialize( $_GET ) );

			if( !$_SESSION[ 'search_query_index' ] ) //クエリキャッシュのインデックスがない場合
				{ $_SESSION[ 'search_query_index' ] = 0; }

			if( !isset( $_SESSION[ 'search_query_hash' ][ $queryHash ] ) ) //クエリキャッシュがない場合
			{
				$_SESSION[ 'search_query_hash' ][ $queryHash ] = $_SESSION[ 'search_query_index' ];
				$_GET[ 'q' ]                                   = $_SESSION[ 'search_query_index' ];
				$_SESSION[ 'search_query' ][ $_GET[ 'q' ] ]    = $_GET;

				++$_SESSION[ 'search_query_index' ];
			}
			else //クエリキャッシュがある場合
				{ $_GET[ 'q' ] = $_SESSION[ 'search_query_hash' ][ $queryHash ]; }

			$target = $_GET[ 'embedID' ];

			ob_start();

			$templateFile = Template::getTemplate( $loginUserType , $loginUserRank , $target , 'SEARCH_EMBED_DESIGN' );

			if( !isset( $query[ 'run' ] ) || strtolower( $_GET[ 'run' ] ) != 'true' ) //検索の実行が指示されていない場合
			{
				if( strlen( $templateFile ) ) //テンプレートがある場合
					{ print $search->getFormString( $file , 'search.php' , 'form' ); }
				else //テンプレートがない場合
					{ $system->drawSearchForm( $search , $loginUserType , $loginUserRank ); }
			}
			else //検索を実行する場合
			{
				if( strlen( $templateFile ) ) //テンプレートがある場合
				{
					if( $exists ) //検索結果がある場合
					{
						SearchTableStack::pushStack( $table );
						$search->addHiddenForm( 'type' , $_GET[ 'type' ] );

						System::$CallMode = 'embed';
						print $search->getFormString( $templateFile , 'search.php' , 'success' , 'v' );
						System::$CallMode = 'normal';
						SearchTableStack::popStack();
					}
					else //検索結果がない場合
						{ print $search->getFormString( $templateFile , 'search.php' , 'failed' , 'v' ); }
				}
				else //テンプレートがない場合
				{
					if( $exists ) //検索結果がある場合
						{ $system->drawSearch( $gm , $search , $table , $loginUserType , $loginUserRank ); }
					else //検索結果がない場合
						{ $system->drawSearchNotFound( $gm , $loginUserType , $loginUserRank ); }
				}
			}

			$contents = ob_get_clean();

			$_GET = $getSwap;

			$this->addBuffer( $contents );
		}

		/**
			@brief   検索結果の件数を表示する。
			@param   $iGM   GUIManagerオブジェクトです。このメソッドでは利用しません。
			@param   $iRec  登録情報のレコードデータです。このメソッドでは利用しません。
			@param   $iArgs コマンドコメント引数配列です。検索ページに渡すクエリパラメータを指定します。
			@remarks run=trueの指定がない場合は何も出力しません。
		*/
		function embedSearchRow( &$iGM , $iRec , $iArgs ) //
		{
			$this->getEmbedParameter( $iArgs , $query , $search , $db , $system , $table );

			if( !isset( $query[ 'run' ] ) || strtolower( $query[ 'run' ] ) != 'true' ) //検索の実行が指示されていない場合
				{ return; }

			$row = $db->getRow( $table );

			$this->addBuffer( $row );
		}

		/**
			@brief   登録件数上限をチェックして超過していれば対処する。
			@param   $iGM   GUIManagerオブジェクトです。このメソッドでは利用しません。
			@param   $iRec  登録情報のレコードデータです。このメソッドでは利用しません。
			@param   $iArgs コマンドコメント引数配列です。上限数を個別に指定したい場合に渡します。
		*/
		function maxRegistCheck( &$iGM , $iRec , $iArgs ) //
		{
			global $THIS_TABLE_MAX_REGIST;
			global $loginUserType;

			if( $iArgs[ 0 ] ) //上限数の指定がある場合
				{ $THIS_TABLE_MAX_REGIST[ $_GET[ 'type' ] ][ $loginUserType ] = $iArgs[ 0 ]; }

			$isOver = SystemUtil::CheckTableRegistCount( $_GET[ 'type' ] );

			if( is_string( $isOver ) )
			{
				if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
					{ header( 'Location: index.php?app_controller=Edit&type=' . $_GET[ 'type' ] . '&id=' . $isOver ); }
				else
					{ header( 'Location: edit.php?type=' . $_GET[ 'type' ] . '&id=' . $isOver ); }
			}
			else if( $isOver )
			{
				if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
					{ header( 'Location: index.php?app_controller=Regist&type=' . $_GET[ 'type' ] . '&mode=registMaxCountOver' ); }
				else
					{ header( 'Location: regist.php?type=' . $_GET[ 'type' ] . '&mode=registMaxCountOver' ); }
			}
		}

		function link( &$iGM , $iRec , $iArgs ) //
		{
			global $NOT_LOGIN_USER_TYPE;
			global $loginUserType;
			global $loginUserRank;
			global $ACTIVE_DENY;

			$target = array_shift( $iArgs );
			$args   = Array();

			$set = Array(
				'index'        => Array( ''                         , Array( 'index.php'   , 'Index'                    ) ) ,
				'regist'       => Array( 'REGIST_FORM_PAGE_DESIGN'  , Array( 'regist.php'  , 'Register' , 'type'        ) ) ,
				'edit'         => Array( 'EDIT_FORM_PAGE_DESIGN'    , Array( 'edit.php'    , 'Edit'     , 'type' , 'id' ) ) ,
				'delete'       => Array( 'DELETE_CHECK_PAGE_DESIGN' , Array( 'delete.php'  , 'Delete'   , 'type' , 'id' ) ) ,
				'search'       => Array( 'SEARCH_FORM_PAGE_DESIGN'  , Array( 'search.php'  , 'Search'   , 'type'        ) ) ,
				'searchResult' => Array( 'SEARCH_RESULT_DESIGN'     , Array( 'search.php'  , 'Search'   , 'type'        ) ) ,
				'info'         => Array( 'INFO_PAGE_DESIGN'         , Array( 'info.php'    , 'Info'     , 'type' , 'id' ) ) ,
				'other'        => Array( ''                         , Array( 'other.php'   , 'Other'    , 'key'         ) ) ,
				'page'         => Array( ''                         , Array( 'page.php'    , 'Page'     , 'p'           ) ) ,
				'preview'      => Array( ''                         , Array( 'preview.php' , 'Preview'  , 'type' , 'id' ) ) ,
				'login'        => Array( ''                         , Array( 'login.php'   , 'Login'                    ) ) ,
				'logout'       => Array( ''                         , Array( 'login.php'   , 'Login'                    ) ) ,
				'switchUser'   => Array( ''                         , Array( 'login.php'   , 'Login'    , 'type' , 'id' ) ) ,
			);

			$label = $set[ $target ][ 0 ];
			$set   = $set[ $target ][ 1 ];
			$url   = array_shift( $set );
			$app   = array_shift( $set );
			$type  = '';
			$id    = '';

			if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
			{
				$url    = 'index.php';
				$args[] = 'app_controller=' . $app;
			}

			foreach( $set as $param ) //全ての必須パラメータを処理
			{
				$data = array_shift( $iArgs );

				if( 'type' == $param ) //テーブル名の場合
					{ $type = $data; }
				else if( 'id' == $param && $iRec ) //IDの場合
					{ $id = $data; }

				if( !$data ) //引数が空の場合
				{
					if( 'type' == $param && $iGM ) //テーブル名の場合
					{
						$db   = $iGM->getDB();
						$data = $db->tablePlaneName;
						$type = $db->tablePlaneName;
					}

					if( 'id' == $param && $iRec ) //IDの場合
					{
						$data = $iRec[ 'id' ];
						$id   = $iRec[ 'id' ];
					}

					if( !$data ) //値が空の場合
						{ $data = $_GET[ $param ]; }
				}

				if( !$data ) //値が取得できなかった場合
					{ throw new Exception( '引数がありません : ' . $param ); }
				else //値が取得できた場合
					{ $args[] = $param . '=' . $data; }
			}

			if( 'searchResult' == $target ) //検索結果画面の場合
				{ $args[] = 'run=true'; }

			if( 'logout' == $target ) //ログアウト画面の場合
				{ $args[] = 'logout=true'; }

			if( 'preview' == $target ) //プレビュー画面の場合
			{
                global $controllerName;
                $controller = strtolower( $controllerName );

				if( 'register' == $controller )
					{ $args[] = 'mode=regist'; }

				if( 'edit' == $controller )
					{ $args[] = 'mode=edit'; }
			}

			if( 'switchUser' == $target ) //ユーザー切り替えの場合
				{ $args[] = 'run=true'; }

			if( count( $iArgs ) ) //追加の引数がある場合
				{ $args[] = implode( ' ' , $iArgs ); }

			if( count( $args ) ) //引数がある場合
				{ $url = $url . '?' . implode( '&' , $args ); }

			if( $NOT_LOGIN_USER_TYPE == $loginUserType && $label ) //表示権限チェック条件に一致する場合
			{
				$template = Template::getTemplate( $loginUserType , $loginUserRank , $type , $label );

				if( !$template ) //テンプレートが設定されていない場合
				{
					$id                                 = rand();
					$_SESSION[ 'redirect_path' ][ $id ] = $url;
					$url                                = 'login.php?redirect_id=' . $id;
				}
			}
			else if( $ACTIVE_DENY == $loginUserRank ) //利用停止中のユーザーの場合
			{
				if( !in_array( $target , Array( 'logout' , 'switchUser' ) ) ) //使用可能なリンクではない場合
				{
					$oldOwner = Template::getOwner();

					if( 'info' == $target ) //詳細ページの場合
					{
						$db  = GMList::getDB( $type );
						$rec = $db->selectRecord( $id );
						SystemUtil::checkTableOwner( $type , $db , $rec );
					}

					$template = Template::getTemplate( $loginUserType , $loginUserRank , $type , $label , Template::getOwner() );

					if( !$template ) //テンプレートが設定されていない場合
						{ $url = 'index.php'; }

					Template::setOwner( $oldOwner );
				}
			}

			$this->addBuffer( $url );
		}

		function linkTag( &$iGM , $iRec , $iArgs ) //
		{
			global $NOT_LOGIN_USER_TYPE;
			global $loginUserType;
			global $loginUserRank;
			global $ACTIVE_DENY;

			$target = array_shift( $iArgs );
			$text   = array_shift( $iArgs );
			$args   = Array();

			$set = Array(
				'index'        => Array( ''                         , Array( 'index.php'   , 'Index'                    ) ) ,
				'regist'       => Array( 'REGIST_FORM_PAGE_DESIGN'  , Array( 'regist.php'  , 'Register' , 'type'        ) ) ,
				'edit'         => Array( 'EDIT_FORM_PAGE_DESIGN'    , Array( 'edit.php'    , 'Edit'     , 'type' , 'id' ) ) ,
				'delete'       => Array( 'DELETE_CHECK_PAGE_DESIGN' , Array( 'delete.php'  , 'Delete'   , 'type' , 'id' ) ) ,
				'search'       => Array( 'SEARCH_FORM_PAGE_DESIGN'  , Array( 'search.php'  , 'Search'   , 'type'        ) ) ,
				'searchResult' => Array( 'SEARCH_RESULT_DESIGN'     , Array( 'search.php'  , 'Search'   , 'type'        ) ) ,
				'info'         => Array( 'INFO_PAGE_DESIGN'         , Array( 'info.php'    , 'Info'     , 'type' , 'id' ) ) ,
				'other'        => Array( ''                         , Array( 'other.php'   , 'Other'    , 'key'         ) ) ,
				'page'         => Array( ''                         , Array( 'page.php'    , 'Page'     , 'p'           ) ) ,
				'preview'      => Array( ''                         , Array( 'preview.php' , 'Preview'  , 'type' , 'id' ) ) ,
				'login'        => Array( ''                         , Array( 'login.php'   , 'Login'                    ) ) ,
				'logout'       => Array( ''                         , Array( 'login.php'   , 'Login'                    ) ) ,
				'switchUser'   => Array( ''                         , Array( 'login.php'   , 'Login'    , 'type' , 'id' ) ) ,
			);

			$label = $set[ $target ][ 0 ];
			$set   = $set[ $target ][ 1 ];
			$url = array_shift( $set );
			$app = array_shift( $set );
			$type  = '';
			$id    = '';

			if( 'index' == WS_SYSTEM_SYSTEM_FORM_ACTON )
			{
				$url    = 'index.php';
				$args[] = 'app_controller=' . $app;
			}

			foreach( $set as $param ) //全ての必須パラメータを処理
			{
				$data = array_shift( $iArgs );

				if( 'type' == $param ) //テーブル名の場合
					{ $type = $data; }
				else if( 'id' == $param && $iRec ) //IDの場合
					{ $id = $data; }

				if( !$data ) //引数が空の場合
				{
					if( 'type' == $param && $iGM ) //テーブル名の場合
					{
						$db   = $iGM->getDB();
						$data = $db->tablePlaneName;
						$type = $db->tablePlaneName;
					}

					if( 'id' == $param && $iRec ) //IDの場合
					{
						$data = $iRec[ 'id' ];
						$id   = $iRec[ 'id' ];
					}

					if( !$data ) //値が空の場合
						{ $data = $_GET[ $param ]; }
				}

				if( !$data ) //値が取得できなかった場合
					{ throw new Exception( '引数がありません : ' . $param ); }
				else //値が取得できた場合
					{ $args[] = $param . '=' . $data; }
			}

			if( 'searchResult' == $target ) //検索結果画面の場合
				{ $args[] = 'run=true'; }

			if( 'logout' == $target ) //ログアウト画面の場合
				{ $args[] = 'logout=true'; }

			if( 'preview' == $target ) //プレビュー画面の場合
			{
                global $controllerName;
                $controller = strtolower( $controllerName );

				if( 'register' == $controller )
					{ $args[] = 'mode=regist'; }

				if( 'Edit' == $controller )
					{ $args[] = 'mode=edit'; }
			}

			if( 'switchUser' == $target ) //ユーザー切り替えの場合
				{ $args[] = 'run=true'; }

			if( count( $iArgs ) ) //追加の引数がある場合
				{ $args[] = implode( ' ' , $iArgs ); }

			if( count( $args ) ) //引数がある場合
				{ $url = $url . '?' . implode( '&' , $args ); }

			if( $NOT_LOGIN_USER_TYPE == $loginUserType && $label ) //表示権限チェック条件に一致する場合
			{
				$template = Template::getTemplate( $loginUserType , $loginUserRank , $type , $label );

				if( !$template ) //テンプレートが設定されていない場合
				{
					$id                                 = rand();
					$_SESSION[ 'redirect_path' ][ $id ] = $url;
					$url                                = 'login.php?redirect_id=' . $id;
				}
			}
			else if( $ACTIVE_DENY == $loginUserRank ) //利用停止中のユーザーの場合
			{
				if( !in_array( $target , Array( 'logout' , 'switchUser' ) ) ) //使用可能なリンクではない場合
				{
					$oldOwner = Template::getOwner();

					if( 'info' == $target ) //詳細ページの場合
					{
						$db  = GMList::getDB( $type );
						$rec = $db->selectRecord( $id );
						SystemUtil::checkTableOwner( $type , $db , $rec );
					}

					$template = Template::getTemplate( $loginUserType , $loginUserRank , $type , $label , Template::getOwner() );

					if( !$template ) //テンプレートが設定されていない場合
						{ $url = 'index.php'; }

					Template::setOwner( $oldOwner );
				}
			}

			$this->addBuffer( '<a href="' . $url . '">' . $text . '</a>' );
		}


		function getEmbedSearchType($type){
			switch($type){
				case "clip":
					$type = $_GET[ 'pal' ];
					break;
				default:
					break;
			}
			return $type;
		}

		/**
			@brief      検索結果埋め込み処理のパラメータを準備する。
			@param[in]  $iArgs   クエリパラメータを格納したCC引数配列。
			@param[out] $oQuery  連想配列化されたクエリ。
			@param[out] $oSearch Searchオブジェクト。
			@param[out] $oDB     DBオブジェクト。
			@param[out] $oSystem systemオブジェクト。
			@param[out] $oTable  検索結果のテーブル。
		*/
		private function getEmbedParameter( $iArgs , &$oQuery , &$oSearch , &$oDB , &$oSystem , &$oTable ) //
		{
			global $gm;
			global $loginUserType;
			global $loginUserRank;
			global $magic_quotes_gpc;

			$queryString = implode( ' ' , $iArgs );
			$oQuery      = Array();

			parse_str( $queryString , $oQuery );

			$getSwap = $_GET;
			$_GET    = $oQuery;

			$oSearch = new Search( $gm[ $_GET[ 'type' ] ] , $_GET[ 'type' ] );
			$oDB     = $gm[ $this->getEmbedSearchType($_GET["type"]) ]->getDB();
			$oSystem = SystemUtil::getSystem( $_GET[ 'type' ] );

			if( $magic_quotes_gpc || $oDB->char_code != 'sjis' ) //エスケープが不要な場合
				{ $oSearch->setParamertorSet( $_GET ); }
			else //エスケープが必要な場合
				{ $oSearch->setParamertorSet( addslashes_deep( $_GET ) ); }

			$oSystem->searchResultProc( $gm , $oSearch , $loginUserType , $loginUserRank );

			$oTable = $oSearch->getResult();

			$oSystem->searchProc( $gm , $oTable , $loginUserType , $loginUserRank );

			$_GET = $getSwap;
		}

		function IP( &$iGM , $iRec , $iArgs ) //
			{ $this->addBuffer( $_SERVER[ 'REMOTE_ADDR' ] ); }

        function js_load(&$gm, $rec, $cc){
            list($file) = $cc;
            if( strpos($file,'http') === 0 || strpos($file,'//') === 0 ){
                $this->addBuffer( '<script type="text/javascript" src="'.$file.'"></script>'."\n" );
            }else{
                $ts = filemtime($file);
                $this->addBuffer( '<script type="text/javascript" src="'.$file.'?'.$ts.'"></script>'."\n" );
            }
        }

        function css_load(&$gm, $rec, $cc){
            list($file) = $cc;
            if( strpos($file,'http') === 0 || strpos($file,'//') === 0 ){
                $this->addBuffer('<link rel="stylesheet" type="text/css" href="' . $file . '" media="all" />' . "\n");
            }else{
                $ts = filemtime($file);
                $this->addBuffer('<link rel="stylesheet" type="text/css" href="' . $file . '?' . $ts. '" media="all" />' . "\n");
            }
        }
	}


//$db_a databaseの配列
//$d 現在の深さ
function groupTableSelectFormMultiReflexive( &$str , $param , $check , $d = 0 , $id = null ){

    $db = $param[$d]['db'];
    if( $id == null ){
        $table = $db->getTable();
    }else{
        $table = $db->searchTable( $db->getTable() , $param[$d]['parent'] , '=' , $id );
    }
    $row = $db->getRow( $table );

    $pad = putCnt($d,'　');

    for($i=0;$i<$row;$i++){
        $rec = $db->getRecord( $table , $i );
        if( isset( $param[ $d+1 ] ) ){
            $cid = $db->getData( $rec , 'id' );
            $str .= '<option value="" >'.$pad.$db->getData( $rec , $param[$d]['name'] )."\n";
            groupTableSelectFormMultiReflexive($str,$param,$check,$d+1,$cid);
        }else{
            $cid = $db->getData( $rec , 'id' );
            if( $cid == $check )
                $str .= '<option value="'.$cid.'" selected="selected">'.$pad.$db->getData( $rec , $param[$d]['name'] )."\n";
            else
                $str .= '<option value="'.$cid.'" >'.$pad.$db->getData( $rec , $param[$d]['name'] )."\n";
        }
    }
}
function searchGroupTableFormMultiReflexive( &$str , $param , $check , $d = 0 , $id = null ){
    $db = $param[$d]['db'];
    if( $id == null ){
        $table = $db->getTable();
    }else{
        $table = $db->searchTable( $db->getTable() , $param[$d]['parent'] , '=' , $id );
    }
    $row = $db->getRow( $table );

    $pad = putCnt($d,'　');

    for($i=0;$i<$row;$i++){
        $rec = $db->getRecord( $table , $i );
        if( isset( $param[ $d+1 ] ) ){
            $cid = $db->getData( $rec , 'id' );
            if( $cid == $check )
                $str .= '<option value="'.$cid.'" selected="selected">'.$pad.$db->getData( $rec , $param[$d]['name'] )."\n";
            else
                $str .= '<option value="'.$cid.'" >'.$pad.$db->getData( $rec , $param[$d]['name'] )."\n";
            searchGroupTableFormMultiReflexive($str,$param,$check,$d+1,$cid);
        }else{
            $cid = $db->getData( $rec , 'id' );
            if( $cid == $check )
                $str .= '<option value="'.$cid.'" selected="selected">'.$pad.$db->getData( $rec , $param[$d]['name'] )."\n";
            else
                $str .= '<option value="'.$cid.'" >'.$pad.$db->getData( $rec , $param[$d]['name'] )."\n";
        }
    }
}

//指定した数だけ、指定した文字を返す
function putCnt( $num , $char ){
    $str = "";
    for($i=0;$i<$num;$i++){
        $str .= $char;
    }
    return $str;
}

?>