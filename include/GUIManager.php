<?php
	if( $SQL && $SQL_MASTER != "SQLiteDatabase"){
		include_once "./include/extends/".$SQL_MASTER.".php";
	}else{
        if( PHP_VERSION_ID >= 50400  ){
            //PHP5.3以降の場合はSQLite3を使う
            include_once "./include/extends/SQLite3Database.php";
        }else{
			include_once "./include/extends/SQLiteDatabase.php";
		}
	}

	/*******************************************************************************************************
	 * <PRE>
	 *
	 * GUIマネージャクラス。
	 *
	 * @author 丹羽一智
	 * @version 3.0.0
	 *
	 * </PRE>
	 *******************************************************************************************************/

	class GUIManager
	{
		var $colName;								// カラム名
		var $colType;								// カラムの型
		var $colRegist;								// Regist時に適用するバリデータ関数の列挙
		var $colEdit;								// Edit時に適用するバリデータ関数の列挙
		var $colRegex;								// 正規表現によるチェック
		var $colStep;								// 多段階登録のステップ
		var $maxStep;								// 最大ステップ
		var $colExtend;								// 拡張情報
		var $colSummary;							// サマリー
		var $db;									// Database オブジェクト
		var $design_tmp;							// デザインファイルテンポラリ
		var $table_type;							// テーブルタイプa(all)/n(nomal)/d(delete)
		var $timeFormat = "Y/m/d";					// timestamp出力フォーマット
		var $timeFormatOnce = null;					// timestamp出力フォーマット
		var $templateStack = Array();
			//"Y/m/d(D) G:i:s"
		var $dateFormat = Array('y'=>'年 ','m'=>'月','d'=>'日');					// date出力フォーマット

		static $CC_HEAD	 = '!--# ';
		static $CC_FOOT	 = ' #--';

		static $CC_OR = '|OR|';
		static $CC_AND = '&AND&';

		/**
		* フォーム出力方法を指定
		* ・自動的に出力(標準)
		*   buffer
		*   b
		* ・出力箇所をCCで指定 <!--# variable form_begin #-->,<!--# variable form_end #-->
		*   variable
		*   v
		*/
		var $form_flg = 'v';
		var $form_method = 'post';
		var $authenticity_token = true;

		/**
		 * コンストラクタ(SQLDatabase)。
		 * @param $db_name string DB名
		 * @param $table_name string テーブルの名前
		 */
		function __construct($db_name, $table_name )
		{
			global $FORM_TAG_DRAW_FLAG;
			global $LST;
			global $ADD_LST;
			global $lst_path;
			global $SYSTEM_CHARACODE;

			$LST_CLM_NAME		= 0;
			$LST_CLM_TYPE		= 1;
			$LST_CLM_SIZE		= 2;
			$LST_CLM_REGIST		= 3;
			$LST_CLM_EDIT		= 4;
			$LST_CLM_REGEX		= 5;
			$LST_CLM_STEP		= 6;
			$LST_CLM_EXTEND		= 7;
			$LST_CLM_SUMMARY	= 8;

			if( !isset( $LST[ $table_name ] ) )	{ throw new InternalErrorException(  'DBが定義されていません。->'. $table_name  ); }

			$lst_file = PathUtil::ModifyLSTFilePath( $LST[ $table_name ] );

			if( !is_file( $lst_file ) )	{ throw new InternalErrorException(  'DB定義ファイルが開けません。->'. $lst_file  ); }

			$fps[0] = fopen ($lst_file, 'r');
            if($fps[0] ==  FALSE ){ throw new InternalErrorException('DB定義ファイルのオープンに失敗しました。->'. $db_name); }

            if( isset($ADD_LST[$table_name]) && is_array($ADD_LST[$table_name]) && count($ADD_LST[$table_name]) ){
            	foreach( $ADD_LST[$table_name] as $add ){
					$fp = fopen ( PathUtil::ModifyLSTFilePath( $add ) , 'r');
            		if($fp !=  FALSE ){ $fps[] = $fp; }
            	}
            }
            foreach( $fps as $fp ){
				while(!feof($fp))
				{
					if( function_exists( 'fgetcsv' ) )
						{ $tmp = fgetcsv( $fp , 20480 , ',' , '"' ); }
					else
					{
						$buffer	 = fgets( $fp , 20480 );
						$tmp	 = explode( "," , $buffer );
					}

					if(count((array)$tmp) == 1)	{ continue; }
					else
					{
						if( !is_array( $this->colName ) )
							{ $this->colName = Array(); }

						if( !in_array( trim( $tmp[$LST_CLM_NAME] ) , $this->colName ) )
							{ $this->colName[]									= trim( $tmp[$LST_CLM_NAME] ); }

						$this->colType[  trim( $tmp[$LST_CLM_NAME] )  ]		= trim( $tmp[$LST_CLM_TYPE] );

						$this->colSummary[trim( $tmp[$LST_CLM_NAME] )]		= trim( mb_convert_encoding($tmp[$LST_CLM_SUMMARY], $SYSTEM_CHARACODE,"shift-jis") );

						if(isset($tmp[$LST_CLM_SIZE])){
							$this->colSize[ trim( $tmp[$LST_CLM_NAME] ) ]		= trim( $tmp[$LST_CLM_SIZE] );
						}else{
							$this->colSize[ trim( $tmp[$LST_CLM_NAME] ) ] = "";
						}

						if(  isset( $tmp[$LST_CLM_REGIST] )  )	{ $this->colRegist[  trim( $tmp[$LST_CLM_NAME] )  ]	 = trim( $tmp[$LST_CLM_REGIST] ); }
						else						{ $this->colRegist[  trim( $tmp[$LST_CLM_NAME] )  ]	 = ""; }

						if(  isset( $tmp[$LST_CLM_EDIT] )  )	{ $this->colEdit[  trim( $tmp[$LST_CLM_NAME] )  ]	 = trim( $tmp[$LST_CLM_EDIT] ); }
						else						{ $this->colEdit[  trim( $tmp[$LST_CLM_NAME] )  ]	 = ""; }

						if(  isset( $tmp[$LST_CLM_REGEX] )  )
						{
							$tmp[$LST_CLM_REGEX] = str_replace( '<>' , ',' , $tmp[$LST_CLM_REGEX] );
							$tmp[$LST_CLM_REGEX] = str_replace( '\\<' , '<' , $tmp[$LST_CLM_REGEX] );
							$tmp[$LST_CLM_REGEX] = str_replace( '\\>' , '>' , $tmp[$LST_CLM_REGEX] );
							$this->colRegex[  trim( $tmp[$LST_CLM_NAME] )  ]	 = trim( $tmp[$LST_CLM_REGEX] );
						}
						else{ $this->colRegex[  trim( $tmp[$LST_CLM_NAME] )  ]	 = ""; }

						if( isset( $tmp[$LST_CLM_STEP] ) && strlen($tmp[$LST_CLM_STEP]) )	{ $this->colStep[  trim( $tmp[$LST_CLM_NAME] )  ]	 = trim( $tmp[$LST_CLM_STEP] ); }
						else					{ $this->colStep[  trim( $tmp[$LST_CLM_NAME] )  ]	 = 0; }

						if($this->maxStep < $this->colStep[  trim( $tmp[$LST_CLM_NAME] )  ])
							$this->maxStep = $this->colStep[  trim( $tmp[$LST_CLM_NAME] )  ];


						if(  isset( $tmp[$LST_CLM_EXTEND] )  )
						{
							$this->colExtend[  trim( $tmp[$LST_CLM_NAME] )  ]	 = trim( $tmp[$LST_CLM_EXTEND] );
						}
						else{ $this->colExtend[  trim( $tmp[$LST_CLM_NAME] )  ]	 = ""; }
					}
				}
				fclose($fp);
            }

			$this->db = new SQLDatabase($db_name, $table_name, $this->colName, $this->colType, $this->colSize, $this->colExtend);

			$this->form_flg = $FORM_TAG_DRAW_FLAG;
		}

		/**
		 * データベースを取得。
		 * @return SQLDatabaseBase データベース
		 */
		function getDB()	{	$this->db->cashReset();	return $this->db; }

		/**
		 * レコードの内容をPOSTで投げられてきたことにします。
		 * @param $rec array レコードデータ
		 */
		function setForm($rec)
		{
			for($i=0; $i<count($this->colName); $i++)
			{
				$data	 = $this->db->getData( $rec, $this->colName[$i] );

				if(  isset( $data )  ){
					if( $this->colType[ $this->colName[$i] ] == 'boolean' )
					{
						if($data)	{ $_POST[ $this->colName[$i] ] = 'TRUE'; }
						else		{ $_POST[ $this->colName[$i] ] = 'FALSE'; }
					}
					else
					{
						$_POST[ $this->colName[$i] ] = ($data);
					}
				}
			}
		}

		/**
		 * GETの内容をPOSTで投げられてきたことにします。
		 * @param $rec array レコードデータ
		 */
		function setFormGET($rec)
		{
			for($i=0; $i<count($this->colName); $i++)
			{
				$data	 = $this->db->getData( $rec, $this->colName[$i] );
				if( is_bool($data) )
				{
					if($data)	{ $_POST[ $this->colName[$i] ] = 'TRUE'; }
					else		{ $_POST[ $this->colName[$i] ] = 'FALSE'; }
				}
				else
				{
					if(  isset( $data )  ){ $_POST[ $this->colName[$i] ] = $data; }
				}
			}
		}

		var $aliasDB;

		/**
		 * エイリアスで用いるGUIManagerを追加。
		 * $name という名前のtableをalias表示用テーブルとして生成、記憶する。
		 * コマンドコメントのエイリアスコマンドにより描画が要求された際には
		 * このGUIManagerを用いて描画処理を行う。
		 * @param $name string 名前
		 * @internal param GUIManager $gm オブジェクト
		 */
		function addAlias($name)	{	$this->aliasDB[$name]	 = SystemUtil::getGMforType($name)->db; }

		/**
		 * timestamp型カラムの出力書式設定。
		 * コマンドコメント 値の出力で
		 * timestamp型の値を出力しようとした際に描画される時間の表示フォーマットを指定します。
		 * 指定方法はPHPのdate() メソッドに準じます。
		 * @param $str string 時間の表示フォーマット
		 */
		function setTimeFormat($str)	{ $this->timeFormat = $str; }

		function setTimeFormatOnce($str)	{ $this->timeFormatOnce = $str; }

		function getTimeFormat(){
			if( $this->timeFormatOnce )
			{
				$result               = $this->timeFormatOnce;
				$this->timeFormatOnce = null;

				return $result;
			}
			else
				{ return $this->timeFormat; }
		}

		var $variable;

		/**
		 * variable 命令で呼び出す変数をセットします。
		 * @param $name string
		 * @param $value int|string
		 */
		 function setVariable($name, $value){ $this->variable[$name] = $value; }
		/**
		 * variable 命令で呼び出す変数をゲットします。
		 * @param $name string 変数名
		 */
		 function getVariable($name){ return $this->variable[$name]; }

		/**
		 * variable をリセットする
		 */
		 function clearVariable(){ $this->variable = Array(); }

		/**
		 * レコードデータから不可視フォームを生成します。
		 * @param $rec array 変数名
		 */
		 function setHiddenFormRecord( $rec )
		 {
			for($i=0; $i<count($this->colName); $i++){
                $this->addHiddenForm(  $this->colName[$i], $this->db->getData( $rec, $this->colName[$i] )  );
                if( isset($_POST[$this->colName[$i].'_DELETE']) ){
                    $this->addHiddenForm(  $this->colName[$i].'_DELETE', $_POST[$this->colName[$i].'_DELETE']);
                }
            }
		 }
		/**
		 * レコードデータから不可視フォームを生成します。
		 * @param name string 変数名
		 * @param value string|int 値
		 */
		 function setHiddenFormRecordEdit( $rec )
		 {
			for($i=0; $i<count($this->colName); $i++){
                $this->addHiddenForm(  $this->colName[$i], $this->db->getData( $rec, $this->colName[$i] )  );
                if( isset($_POST[$this->colName[$i].'_DELETE']) ){
                    $this->addHiddenForm(  $this->colName[$i].'_DELETE', $_POST[$this->colName[$i].'_DELETE']);
                }
            }
		 }

		/**
		 * フォームつきHTMLを描画します。
		 * @param $html string デザインHTMLファイル
		 * @param $rec array レコードデータ
		 * @param $jump string submitで飛ぶ先
		 * @param $partkey string 分割キー
		 */
		function drawForm( $html, $rec = null, $jump = null, $partkey = null, $form_flg = null )
		{
			print $this->getFormString( $html, $rec, $jump, $partkey, $form_flg);
		}

		/**
		 * フォームつきHTMLデータを取得します。
		 * @param $html string デザインHTMLファイル
		 * @param $rec string レコードデータ
		 * @param $jump string submitで飛ぶ先
		 * @param $partkey string 分割キー
		 * @return string
		 */
		function getFormString( $html, $rec = null, $jump = null, $partkey = null, $form_flg = null )
		{
			if( !isset($form_flg) ) { $form_flg = $this->form_flg; }
			switch($form_flg)
			{
			case 'variable':
			case 'v':
				return $this->getFormStringSetVariable( $html, $rec, $jump, $partkey, $form_flg );
				break;
			case 'buffer':
			case 'b':
			default:
				return $this->getFormStringSetBuffer( $html, $rec, $jump, $partkey, $form_flg );
				break;
			}
		}

		/**
		 * フォームつきHTMLデータを取得します。
		 * @param $html string デザインHTMLファイル
		 * @param $rec array レコードデータ
		 * @param $jump string submitで飛ぶ先
		 * @param $partkey string 分割キー
		 * @return string
		 */
		function getFormStringSetBuffer( $html, $rec = null, $jump = null, $partkey = null )
		{
			global $terminal_type;

			$enctype	 = "";
			if(!$terminal_type) { $enctype = 'enctype="multipart/form-data"'; }
			if($this->authenticity_token ){
				$this->addHiddenForm( 'authenticity_token', SystemUtil::getAuthenticityToken() );
			}

			$buffer	 = "";
			if(  isset( $jump )  )	{ $buffer	 .= '<form name="sys_form" method="'.$this->form_method.'" action="'. $jump .'" '. $enctype .' style="margin: 0px 0px;">'. "\n"; }

			$buffer	 .= $this->addForm;
			$this->addForm = "";
			$buffer	 .= $this->getString($html, $rec, $partkey);
			if(  isset( $jump )  )	{ $buffer	 .= '</form>'. "\n"; }
			return $buffer;
		}

		/**
		 * フォームを描画します。
		 *
		 *  formタグ等をvariableにセットしてテンプレートに渡します。
		 *  header部等でformが使われていて、getFormStringだとformがネストしてしまう時にお使いください。
		 *
		 * @param $html string デザインHTMLファイル
		 * @param $rec array レコードデータ
		 * @param $jump string submitで飛ぶ先
		 * @param $partkey string 分割キー
		 * @return string
		 */
		function getFormStringSetVariable( $html,  $rec = null, $jump = null, $partkey = null )
		{
			global $terminal_type;

			$enctype	 = "";
			if(!$terminal_type) { $enctype = 'enctype="multipart/form-data"'; }
			if($this->authenticity_token ){
				$this->addHiddenForm( 'authenticity_token', SystemUtil::getAuthenticityToken() );
			}

			if(  isset( $jump )  )	{ $this->setVariable('form_begin','<form name="sys_form" method="'.$this->form_method.'" action="'. $jump .'" '. $enctype .' style="margin: 0px 0px;">'.$this->addForm); }
            else{ $this->setVariable('form_begin',$this->addForm); }
            $this->addForm = "";

			if(  isset( $jump )  )	{ $this->setVariable('form_end','</form>'); }
            else{ $this->setVariable(''); }

			$buffer	 = $this->getString($html, $rec, $partkey);
			return $buffer;
		}

		var $addForm;

		/**
		 * 不可視フォームの追加。
		 * @param $name string INPUT名
		 * @param $val string INPUTの値
		 */
		function addHiddenForm( $name, $val )
		{
			if( is_bool($val) )
			{
				if( $val )	{ $this->addForm .= '<input name="'. $name .'" type="hidden" value="TRUE" />'. "\n"; }
				else		{ $this->addForm .= '<input name="'. $name .'" type="hidden" value="FALSE" />'. "\n"; }
			}
			else	{ $this->addForm .= '<input name="'. $name .'" type="hidden" value="'. h( $val ) .'" />'. "\n"; }
		}

		/**
		 * HTMLを描画します。
		 * HTMLに含まれるコマンドコメントには $rec で渡したレコードデータの内容を反映します。
		 * @param $html string デザインHTMLファイル
		 * @param $rec array レコードデータ
		 * @param $partkey string 分割キー
		 */
		function draw($html, $rec = null, $partkey = null) { print $this->getString($html, $rec, $partkey); }

		/**
		 * 部分描画を実行します。
		 * @param $html string デザインHTMLファイル
		 * @param $partkey string 分割キー
		 */
		function partRead($html, $partkey)	{ print GUIManager::partGetString( $html, $partkey ); }

		/**
		 * 部分データ取得を実行します。
		 * @param $path string デザインファイル
		 * @param $partkey string 分割キー
		 * @return string
		 */
		function partGetString($html, $partkey)
		{
			if(  !is_file( $html )  )	{ print 'HTMLファイルが開けません。->'. $html; die(); }

			array_push( $this->templateStack , $html );

			if(  isset( $partkey )  )	{ $state = self::getDefState( false ); }
			else						{ throw new InternalErrorException( "GUIManager Error -> partRead() or partGetString() -> Not Set PartKey" ); }

			$str	 = "";
            $c_part = null;

			if( !isset($this->design_tmp[$html]) ) { $this->getFile($html); }

            $row = count($this->design_tmp[$html]);
            for($i=0;$row>$i;$i++){
				$str .= GUIManager::commandComment( $this->design_tmp[$html][$i], $this, null, $state , $c_part , $partkey );
                if( $state['template_end']){ break; }
            }

			$str	 = str_replace( array("!CODE001;","!CODE101;","!CODE000;","!CODE002;","!CODE005;","!CODE006;"), array(" ", " ", "/", "\\",'!--# ',' #--') , $str );

			array_pop( $this->templateStack );

			return DebugUtil::addFilePathComment( $str, $html, $partkey );
		}


		/**
		 * テーブルの内容をリストを描画します。
		 * @param $html string デザインHTMLファイル
		 * @param $table TableBase テーブルデータ
		 * @param $partkey string 分割キー
		 */
		function drawList($html, $table, $partkey = null)	{ print $this->getListString( $html, $table, $partkey ); }

		/**
		 * テーブルの内容のリスト描画結果のHTMLを取得します。
		 * @param $html string デザインHTMLファイル
		 * @param $table TableBase テーブルデータ
		 * @param $partkey string 分割キー
		 * @return string
		 */
		function getListString($html, $table, $partkey = null)
		{
			$buffer	 = "";
			$this->db->cashReset();
			$row	 = $this->db->getRow( $table );
			$table->onCash();
			for($i=0; $i<$row; $i++)
			{
				$rec = $this->db->getRecord( $table, $i );
				$buffer	 .= $this->getString( $html, $rec, $partkey );
			}
			$table->offCash();

			return $buffer;
		}

		/**
		 * テーブルの内容のリスト描画結果のHTMLを取得します。
		 * @param $html string デザインHTMLファイル
		 * @param $table TableBase テーブルデータ
		 * @param $partkey string 分割キー
		 * @return string
		 */
		function getListNumString($html, $table, $partkey = null,$start)
		{
			$buffer	 = "";
			$row	 = $this->db->getRow( $table );
			$table->onCash();
			for($i=0; $i<$row; $i++)
			{
				$rec = $this->db->getRecord( $table, $i );
				$this->setVariable('num',$start+$i);
				$buffer	 .= $this->getString( $html, $rec, $partkey );
			}
			$table->offCash();

			return $buffer;
		}

		/**
		 * テーブルの内容をフォーム形式でリスト描画します。
		 * @param $html string デザインHTMLファイル
		 * @param $table TableBase テーブルデータ
		 * @param $jump string submitで飛ぶ先
		 * @param $partkey string 分割キー
		 */
		function drawFormList($html, $table, $jump, $partkey = null)
		{
			$row	 = $this->db->getRow( $table );
			$table->onCash();
			for($i=0; $i<$row; $i++)
			{
				$rec = $this->db->getRecord( $table, $i );
				$this->drawForm( $html, $rec, $jump, $partkey );
			}
			$table->offCash();

		}

		/**
		 * HTMLをキャッシュします。
		 * @param $html string デザインHTMLファイル
		 */
		private function getFile( $html )
		{
			$fp = fopen ( $html, 'r' );

			$str = array();
			while(  !feof( $fp )  )
			{
				$buffer	 = fgets( $fp, 20480 );

				//$buffer = mb_convert_encoding( $buffer,'UTF-8','SJIS');

				$buffer		 = str_replace( "\\\\", "!CODE002;", $buffer );
				$buffer		 = str_replace( "\/", "!CODE000;", $buffer );
				$buffer		 = str_replace( "\ ", "!CODE001;", $buffer );

				//$buffer = mb_convert_encoding( $buffer,'SJIS','UTF-8');

				$str[] = $buffer;
			}
			fclose( $fp );
			$this->design_tmp[$html] = $str;
		}

		/**
		 * HTMLを取得します。
		 * HTMLに含まれるコマンドコメントには $rec で渡したレコードデータの内容を反映します。
		 * @param $path string デザインHTMLファイル
		 * @param $rec array レコードデータ
		 * @param $partkey string 分割キー
		 * @return string
		 */
		function getString($html, $rec = null, $partkey = null)
		{
			if( !is_file( $html ) )	{ print 'HTMLファイルが開けません。->'. $html; die(); }

			array_push( $this->templateStack , $html );


		    $state = self::getDefState( is_null($partkey) );
			$c_part = null;

			if( !isset($this->design_tmp[$html]) ) { $this->getFile($html); }

            $row = count($this->design_tmp[$html]);
            $str = "";
            for($i=0;$row>$i;$i++){
            	if( $this->design_tmp[$html][$i] != '' ){
					$str .= GUIManager::commandComment( $this->design_tmp[$html][$i], $this, $rec, $state , $c_part , $partkey );
                    if( $state['template_end']){ break; }
            	}
            }

			$str	 = str_replace( array("!CODE001;","!CODE101;","!CODE000;","!CODE002;","!CODE005;","!CODE006;"), array(" ", " ", "/", "\\",'!--# ',' #--') , $str );

			array_pop( $this->templateStack );

			return DebugUtil::addFilePathComment( $str, $html, $partkey );
		}

		function getCCResult($rec, $command)
		{
			$command = str_replace( "\\\\", "!CODE002;", $command );//\\にマッチ
			$command = str_replace( "\/", "!CODE000;", $command );
			$command = str_replace( "\ ", "!CODE001;", $command );

		    $state = self::getDefState( true );
			$str	 = trim(  GUIManager::commandComment( $command. " ", $this, $rec, $state , $c_part = null )  );

			$str	 = str_replace( array("!CODE001;","!CODE101;","!CODE000;","!CODE002;","!CODE005;","!CODE006;"), array(" ", " ", "/", "\\",'!--# ',' #--') , $str );

			return $str;
		}

        //$gmが不用意に書き換えられる事によりシステム全体に支障をきたさぬ為、$gmは参照で渡さない。
		function commandComment($buffer, $gm, $rec, &$state , &$current_part , $partkey = null)
		{
			if( $state['draw'] <= 0){
                //非表示でも処理をすべきコマンドコマメントを含んでいるかどうかチェックする。
				$syntax_list = array( 'read', 'endif', 'else', 'ifbegin', 'case', 'switch', 'endswitch', 'default' ); //※'break'は含まない。
				$syntax_f = false;
				foreach( $syntax_list as $syntax ){
					if( strpos( $buffer, '!--# '.$syntax) !== false ){ $syntax_f = true; }
				}
                if( !$syntax_f ){ return ""; }

                //パーツを指定していて、現在カレンドではなく、かつ当該パーツに辿り着いていない。
                if( !is_null($partkey) && $current_part != $partkey && strpos( $buffer, "readhead $partkey ") === false ){ return ""; }
            }
			$buffer		 = str_replace(  Array(  "#--)","#-->","(!--#","<!--#"), Array( "#--", "#--", "!--#", "!--#" ), $buffer );

			$ret			 = "";

			// まず、コマンドコメントが見つからない場合はそのまま返す。
			if(  strpos( $buffer, self::$CC_HEAD ) === false  )	{ return $buffer; }

			// コマンドコメントのヘッダがあるのに、フッタが見つからない場合は構文エラー
			if(  strpos( $buffer, self::$CC_FOOT ) === false  )	{ throw new InternalErrorException(   "CommandComment Syntax Error [". htmlspecialchars(  trim( $buffer )  ) ."]"   ); }

			// 構文解析開始
			$stack		 = array();
			$zStack		 = array();
			$counter	 = 0;
			$z			 = 0;
			$zMax		 = 0;
            $head_length = strlen( self::$CC_HEAD );
            $foot_length = strlen( self::$CC_FOOT );

            $stack[ $counter ]="";
			// コマンドコメントをヘッダ･フッタで分割し、階層構造にする。
			for( $pointer=0; $pointer<strlen( $buffer )+1; $pointer++ )
			{
				if( $foot_length <= $pointer &&  substr(  $buffer, $pointer - $foot_length, $foot_length  ) == self::$CC_FOOT   )
				{
					$zStack[ $counter ]	 = $z;
					$counter++;
					$z--;
                    $stack[ $counter ]="";
				}

				if(   substr(  $buffer, $pointer, $head_length  ) == self::$CC_HEAD   )
				{
					$zStack[ $counter ]	 = $z;
					$counter++;
					$z++;
					if( $zMax < $z )	{ $zMax	 = $z; }
                    $stack[ $counter ]="";
				}
				$stack[ $counter ]	  .= substr( $buffer, $pointer, 1 );
			}
			$zStack[ $counter ]	 = $z;

			//drawが一度でも切り替わったどうか
			$draw_change_all = false;
			$current = array();

            //$part_off = false;
			// 最も階層の深いところからコマンドコメントを実行していく。(0はコマンドが存在しないので無処理)
			for( $i=$zMax; $i>0; $i-- )
			{
				//drawが切り替わっているフラグ
				//drawが切り替わったタイミングの$jが入る
				$draw_change = array();
				

				for( $j=0; $j<count($stack); $j++ )
				{
					// もし このスタックが begin-end系のコメントコマンドに挟まれた下階層であり、挟まれている事により非表示である場合。
					if( $state['no'] && $zStack[ $j ] < $i && count($current) ){
						//この行の表示状態が非表示なら 空文字に置換する。
						$stack[ $j ] = "\0";
					}

					if(  $zStack[ $j ] == $i  )
					{
						if( $stack[ $j ] !== "\0" && strlen( $stack[ $j ] ) > 0  )
						{
							if(   strpos(  $stack[ $j ], self::$CC_HEAD  ) !== false && strpos(  $stack[ $j ], self::$CC_FOOT  ) !== false   )
							{ $command	 = substr(  $stack[ $j ], strlen( self::$CC_HEAD ), strlen( $stack[$j] ) - strlen( self::$CC_HEAD ) - strlen( self::$CC_FOOT )  ); }
							else if(   strpos(  $stack[ $j ], self::$CC_HEAD  ) !== false   )	{ $command	 = substr(  $stack[ $j ], strlen( self::$CC_HEAD )  ); }
							else if(   strpos(  $stack[ $j ], self::$CC_FOOT  ) !== false   )	{ $command	 = substr(  $stack[ $j ], 0, strlen( $stack[$j] ) - strlen( self::$CC_FOOT )  ); }
							else														{ $command	 = $stack[ $j ]; }
							$cc		 = explode( " ", $command );

							if( !$state['break'] ){
								switch( $cc[0] ){
	                                case 'ifbegin':
	                                    if($state['no']){$state['draw']--;}//非表示な為階層を下げる
	                                    else if(!self::ifbegin( $gm, $rec, $cc )){//結果が偽な為、非表示に。
	                                    	$state['draw']--;
	                                    	$state['no']=true;
	                                    	$draw_change[] = $j;
	                                    }
	                                    array_push( $current, $cc[0]);
	                                    break;
	                                case 'elseif':
	                                	if($state['in']){break;}//既にそのifグループは有効な経路を表示済み
	                                    if( $state['no'] && $state['draw'] === 0 ){//非表示状態であり、現在の階層のelseifである
	                                    	if(self::ifbegin( $gm, $rec, $cc )){
	                                    		$state['draw']++;
	                                    		$state['no']=false;
	                                    		$draw_change[] = $j;
	                                    	}
	                                    }else if( ! $state['no'] ){ //表示ブロックから抜けた
	                                    	$state['draw']--;
	                                    	$state['no']=true;
	                                    	$state['in']=true;
	                                    	$draw_change[] = $j;
	                                    } //階層が異なる
	                                	array_pop( $current);
	                                    array_push( $current, $cc[0]);
	                                	break;
	                                case 'else':
	                                	if($state['in']){break;}//既にそのifグループは有効な経路を表示済み
	                                    if( $state['no'] && $state['draw'] === 0 ){//非表示状態であり、現在の階層のelseifである
	                                    	$state['draw']++;
                                    		$state['no']=false;
                                    		$draw_change[] = $j;
	                                    }else if( ! $state['no'] ){ //表示ブロックから抜けた
	                                    	$state['draw']--;
	                                    	$state['no']=true;
	                                    	$state['in']=true;
	                                    	$draw_change[] = $j;
	                                    } //階層が異なる
	                                	array_pop( $current);
	                                    array_push( $current, $cc[0]);
	                                	break;
	                                case 'switch':
										if($state['no']){$state['draw']--;}//非表示な為階層を下げる
	                                    else{//分岐に入る、一端非表示
											array_push( $state['switch'], $cc[1] );
	                                    	$state['draw']--;
	                                    	$state['no']=true;
	                                    	$draw_change[] = $j;
	                                    }
	                                    array_push( $current, $cc[0]);
	                                	break;
	                                case 'default':
	                                    if( $state['no'] && $state['draw'] === 0 ){
	                                    	//現在非表示でありswitchの階層であり、かつbreak中でない
	                                    	$state['draw']++;
                                    		$state['no']=false;
                                    		$draw_change[] = $j;
	                                    }
	                                case 'case':
	                                    if( $state['no'] && $state['draw'] === 0 ){
	                                    	//現在非表示でありswitchの階層であり、かつbreak中でない
	                                    	if( $cc[1] == $state['switch'][0] ){
	                                    		//値が一致する為表示
		                                    	$state['draw']++;
	                                    		$state['no']=false;
	                                    		$draw_change[] = $j;
	                                    	}
	                                    }
	                                	break;
	                                case 'readhead':
	                                    if( $partkey != null && $partkey == $cc[1] ){//partkeyが未設定でなく、一致している為表示
	                                    	if( ++$state['draw'] > 0 ){
	                                    		$state['no']=false;
	                                    		if( $state['draw'] == 1 ){
	                                    			$draw_change[] = $j;
	                                    		}
	                                    	}
	                                    }
	                                    else{
	                                    	//非表示
	                                    	$state['draw']--;
                                    		if( $state['draw'] == 0 ){
                                    			$draw_change[] = $j;
                                    		}
	                                    }
	                                    $current_part = $cc[1];
	                                    array_push( $current, $cc[0]);
	                                    break;
	                            }
							}else if( $cc[0] == 'switch'){
								// break 中であっても、endswitch がネスト内のものかを判定する為、switch のネスト処理(階層下げ)だけ行う
								$state['draw']--;
								array_unshift( $state['switch'], '' );
								array_push( $current, $cc[0]);
							}
							
                            if( $state['draw'] > 0 ){//表示
								if(  strpos( $stack[ $j ], self::$CC_HEAD ) !== false  )	{
									//コマンドコメントを含む場合、処理をしてエスケープする。
	                                $stack[ $j ] = ccProc::controller( $gm, $rec, $cc );
									$stack[ $j ] = str_replace( array("!CODE001;","!CODE000;",self::$CC_HEAD,self::$CC_FOOT), array(" ", "/", "!CODE005;","!CODE006;") , $stack[ $j ] );
	                            }
							}else if($state['draw'] == 0 && strpos( $buffer, self::$CC_HEAD.'elseif' ) !== false  ){
								//非表示だが深度が1で行内にelseifを含む (elseif内の条件にコマンドコメントが使われる場合を想定した例外実装
								if(  strpos( $buffer, 'elseif' ) !== false  && strpos( $stack[ $j ], self::$CC_HEAD ) !== false )	{
									//コマンドコメントを含む場合、処理をしてエスケープする。
	                                $stack[ $j ] = ccProc::controller( $gm, $rec, $cc );
									$stack[ $j ] = str_replace( array("!CODE001;","!CODE000;",self::$CC_HEAD,self::$CC_FOOT), array(" ", "/", "!CODE005;","!CODE006;") , $stack[ $j ] );
	                            }
							}
                            else{ $stack[ $j ] = "";}	//非表示
						
                            //コマンド解釈済みな為、最上位でなければ当該箇所の深度を上げる。
							if( $zStack[ $j ] != 0 )							{ $zStack[ $j ]--; }
						
                            switch( $cc[0] ){
                                case 'endif':
                                    if( $state['no'] && !$state['break'] ){	//非表示であり、break中でない
                                    	//階層を上げる
                                    	if(++$state['draw']>0){	//全ての階層を抜けた
                                    		$state['no']=false;
                                    		$state['in']=false;
                                    		if( $state['draw'] == 1 ){
                                    			$draw_change[] = $j;
                                    		}
                                    	}
                                    }
	                                array_pop( $current);
                                    break;
                                case 'endswitch':
                                    if($state['no']){	//非表示である
                                    	//階層を上げる
                                    	if(++$state['draw']>0){	//全ての階層を抜けた
                                    		$state['no']=false;
                                    		if( $state['draw'] == 1 ){
                                    			$draw_change[] = $j;
                                    		}
											$state['break']=false;
                                    	}
                                    }
									array_shift( $state['switch'] );
	                                array_pop( $current);
                                    break;
                                case 'break':
                                    if( !$state['no'] && $state['draw'] > 0 && !$state['break'] ){
                                    	//表示中でありswitchの階層であり、かつbreak中でない
                                    	$state['draw']--;
                                    	$state['no']=true;
                                    	$state['break']=true;
                                    	if( $state['draw']==0){
                                    		$draw_change[] = $j;
                                    	}
                                    }
	                                array_pop( $current);
                                	break;
                                case 'readend':
                                    if( $partkey != null && $partkey == $current_part ){
	                                    $state['no']=true;
                                    	$state['draw']--; /*$part_off = true;*/
                                    	if( $state['draw']==0){
                                    		$draw_change[] = $j;
                                    	}
                                        $state['template_end'] = true;
                                    }
                                    else{
                                    	if( ++$state['draw'] > 0 ){
                                    		$state['no']=false;
                                    		if( $state['draw'] == 1 ){
                                    			$draw_change[] = $j;
                                    		}
                                    	}
                                    }
                                    $current_part = null;
	                                array_pop( $current);
                                    break;
                            }
						}//if( $stack[ $j ] !== "\0" && strlen( $stack[ $j ] ) > 0  )
					}//if(  $zStack[ $j ] == $i  )
				}//for( $j=0; $j<count($stack); $j++ )

				//表示の切り替えが行なわれた場合、その切り替えによって非表示であるべき範囲のみ空文字に置き変える
				if( count($draw_change) ){
					// この行中でdrawの切り替えが行なわれている。
	                $z	 = -1;
	                //出力切り替えの判定用の一次的な表示フラグ
	                $draw_b = (count($draw_change)%2) ? (!($state['draw']>0)):($state['draw']>0);
	                $change_point = 0;

	                for( $k=0; $k<count($stack); $k++ )
	                {
	                    if(   trim(  $stack[ $k ], "\n\r"  ) == "\0" )	{ continue; }

	                    if( !$draw_b )
	                    {
	                    	$stack[ $k ] = "";
	                    }

	                	if( !is_null($draw_change[$change_point]) && $draw_change[$change_point] <= $k )
	                	{
	                		$change_point++;
	                		$draw_b = !$draw_b;
	                	}
	                }
				}
                //1スタックにまとめる
                $z	 = -1;
                for( $k=0; $k<count($stack); $k++ )
                {
                    if(   trim(  $stack[ $k ], "\n\r"  ) == "\0" )	{ continue; }

                    if( $z == $zStack[ $k ] )
                    {
                        $stack[ $k - 1 ]		 .= $stack[ $k ];
                        for( $l=$k; $l<count($stack); $l++ )
                        {
                            $stack[ $l ]		 = isset($stack[ $l + 1 ]) ? $stack[ $l + 1 ] : '';
                            $zStack[ $l ]		 = isset($zStack[ $l + 1 ]) ? $zStack[ $l + 1 ]: '';
                        }
                        $stack[ count($stack) - 1 ]		 = "\0";
                        $zStack[ count($stack) - 1 ]	 = 0;
                        $k--;
                    }
                    $z	 = $zStack[ $k ];
                }

				//変更があったかどうかを記憶する。
                if( count($draw_change) > 0 )
                {
                	$draw_change_all = true;
                }
			}//for( $i=$zMax; $i>=0; $i-- )

			if( $draw_change_all ){
				//変更があった場合は表示する。
				$ret	 = $stack[ 0 ];
			}
			else if( $state['draw'] > 0 ){ $ret	 = $stack[ 0 ];}

			return $ret;
		}

		function ccProc(&$gm, $rec, $cc)
		{
			return ccProc::controller($gm, $rec, $cc);
		}

        //alias等で使うgetTableで参照するtableのタイプを指定する。(n/d/a)
        function setTableType($type){
            $this->table_type = $type;
        }

        /**
         * 指定カラムのstepを返す
         *
         * @param $column カラム名
         * @return step数
         *
         */
        function getStep( $column ){
        	return $this->colStep[ $column ];
        }

        /**
         * データを受け取りパラメータで指定された置換を行なう
         * @param $str 変換を行なう文字列
         * @param $param 変換パラメータ
         * @return 変換後の文字列
         */
        static function replaceString( $str, $param, $type )
        {
			$str_types = Array( 'string','varchar','char' );
	        $before = Array('<','>','"',"'");
	        $after = Array('＜','＞','”',"’");
	        $tmp = $str;

			if( in_array( $type, $str_types ) )
			{

				if(!empty($param) )
				{
					$params = explode('/', $param );

					foreach( $params as $p ){
						if( strpos($p,':') === FALSE )
						{
							$m = $p;
							$val = Array();
						}
						else
						{
							$val = explode(':', $p );
							$m = $val[0];
							$val = array_slice($val,1);
						}

			        	//デフォルトではnohtml
			        	$replace_type = Extension::GUIManager_replaceStringParam($m,$val,$before,$after,"nohtml");

					}
				}else{
					$replace_type = "nohtml";
				}
	        	$tmp = Extension::GUIManager_replaceStringExecute( $replace_type, $before, $after, $tmp );
			}

        	return $tmp;
        }


        /**
         *
         */
        static function getDefState( $draw ){
        	return Array(
        		'draw'=>$draw?1:0	//1以上で表示、0以下は非表示、マイナスの場合多階層に潜っている
        		,'no'=>!$draw		//分岐で非表示とされている時にtrue
        		,'in'=>false		//ifbeginの分岐で既にその階層のifで有効な経路を通過している時にtrue、endifでfalse
        		,'break'=>false		//switchの分岐で既にその階層のbreakが実行された時にtrue、endswitchでfalse
        		,'switch'=>array()		//switchの条件分岐に使用する値。 ネストの為にstack
                ,'template_end'=>false //part処理後など、これ以上の走査不要になった時
        	);
        }

        static function ifbegin( $gm, $rec, $cc )
        {
			while( true ){
				if( ($and_key = array_search(self::$CC_AND,$cc) )  !== FALSE ){
					$and_cc = array_splice( $cc,0,$and_key );
					$cc[0] = 'ifbegin';
				}else{
					$and_cc = $cc;
				}

				$result = false;

				while( true ){
					if( ( $or_key = $key = array_search( self::$CC_OR,$and_cc ) ) !== FALSE ){
						$or_cc = array_splice( $and_cc,0,$or_key );
						$and_cc[0] = 'ifbegin';
					}else{
						$or_cc = $and_cc;
					}
					$result = ccProc::ifbegin( $gm, $rec, $or_cc );
					if( $result || $or_key === FALSE ){ break; }
				}

				if( !$result ){
					//現在のandブロックに1つもtrueがなかった
					return false;
				}

				//最後のandブロック、もしくは唯一のブロックだった。
        		if( $and_key === FALSE ){
        			return true;
        		}
			}
        }

		function getCurrentTemplate()
			{ return end( $this->templateStack ); }
	}

	/*******************************************************************************************************/

	function uni2utf8($uniescape)
	{
		$c = "";

		$n = intval(substr($uniescape, -4), 16);
		if ($n < 0x7F)  { $c .= chr($n); }
		elseif ($n < 0x800)
		{
			$c .= chr(0xC0 | ($n / 64));
			$c .= chr(0x80 | ($n % 64));
		}
		else
		{
			$c .= chr(0xE0 | (($n / 64) / 64));
			$c .= chr(0x80 | (($n / 64) % 64));
			$c .= chr(0x80 | ($n % 64));
		}
		return $c;
	}

	function escuni2sjis($escunistr)
	{
		$eucstr = "";

		while(preg_match("/(.*)(%u[0-9A-F][0-9A-F][0-9A-F][0-9A-F])(.*)$/i", $escunistr, $fragment))
		{
			$eucstr = mb_convert_encoding(uni2utf8($fragment[2]).$fragment[3], 'SHIFT-JIS', 'UTF-8').$eucstr;
			$escunistr = $fragment[1];
		}
		return $fragment[1]. $eucstr;
	}

?>