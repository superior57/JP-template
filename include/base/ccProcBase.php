<?php

	class ccProcBase //
	{
		// 関数の割り振り
		static function controller(&$gm, $rec, $cc)
		{
			return ccProc::controller($gm, $rec, $cc);
		}

		// 設定されたレコードをカラム名で検索し、マッチした項目を複数の文字列から検索、対応する文字列を表示する。
		function valueReplace(&$gm, $rec, $cc)
		{
			$ret = "";
			if( !isset($gm) ) { throw new InternalErrorException( 'CommandComment Null Pointer Error -> $gm ( via valueReplace )' ); }
			$db   = $gm->getDB();
			$data = $db->getData( $rec, $cc[1] );
			if( is_bool($data) )
			{
				if( $data ) { $data	 = 'TRUE'; }
				else		{ $data	 = 'FALSE'; }
				$cc[2] = strtoupper($cc[2]);
			}
			$befor = explode( '/', $cc[2] );
			$after = explode( '/', $cc[3] );
			for($i=0; $i<count($befor); $i++)
			{
				if( $data == $befor[$i] ) { $ret .= $after[$i]; break; }
			}
			
			//戻り値が空の場合のデフォルトをセット
			if( !strlen($ret) && isset($cc[4]) ){  $ret = $cc[4]; }
			return $ret;
		}

		// 第一引数で入力した文字列を複数の文字列から検索、対応する文字列を表示する。
		function valueValueReplace(&$gm, $rec, $cc)
		{
			$ret = "";
			if( !isset($gm) ) { throw new InternalErrorException( 'CommandComment Null Pointer Error -> $gm ( via valueValueReplace )' ); }
			$data = $cc[1];
			if( is_bool($data) )
			{
				if( $data ) { $data	 = 'TRUE'; }
				else		{ $data	 = 'FALSE'; }
				$cc[2] = strtoupper($cc[2]);
			}
			$befor	 = explode( '/', $cc[2] );
			$after	 = explode( '/', $cc[3] );
			for($i=0; $i<count($befor); $i++)
			{
				if( $data == $befor[$i] ) { $ret .= $after[$i]; break; }
			}
			
			//戻り値が空の場合のデフォルトをセット
			if( !strlen($ret) && isset($cc[4]) ){  $ret = $cc[4]; }
			return $ret;
		}
        
        function arrayReplace(&$gm, $rec, $cc)
        {
			$ret = "";
			if( !isset($gm) ) { throw new InternalErrorException( 'CommandComment Null Pointer Error -> $gm ( via arrayReplace )' ); }
			$db = $gm->getDB();
            
			$array	 = explode( '/', $db->getData( $rec, $cc[1] ) );
			$befor	 = array_flip(explode( '/', $cc[3] ));
			$after	 = explode( '/', $cc[4] );
                
			foreach( $array as $data ){
                if( is_bool($data) )
                {
                    if( $data ) { $data	 = 'TRUE'; }
                    else		{ $data	 = 'FALSE'; }
                    $cc[3]	 = strtoupper($cc[3]);
                }
                
                if( strlen($ret) ) { $ret .= $cc[2]; }
                
                if( isset( $befor[$data] ) && isset($after[ $befor[$data] ]) ){
               		$ret .= $after[ $befor[$data] ];
				}
			}
			
			//戻り値が空の場合のデフォルトをセット
			if( !strlen($ret) && isset($cc[5]) ){  $ret = $cc[5]; }
			return $ret;
        }

        static $alias_cash = null;
		// テンプレートに関連づけられたレコードの指定されたカラムを別テーブルの指定カラムをキーに検索を行ない置換を行なう。 
		function alias(&$_gm, $rec, $cc)
		{
			$ret = "";
			if( !isset($_gm) ) { throw new InternalErrorException( 'CommandComment Null Pointer Error -> $gm ( via alias )' ); }
			
			$tables		= explode( '/', $cc[1] );
			$key		= $cc[2];
			$vals		= explode( '/', $cc[3] );
			$draws		= explode( '/', $cc[4] );
			$brflags	= explode( '/', $cc[5] );
			
			$value		 = $_gm->db->getData( $rec, $key );
			
			$cnt = count($tables);
			for( $i=0 ; $i<$cnt && $value != '' ; $i++ )
			{
				if( !isset($_gm->aliasDB[$tables[$i]]) ) { $_gm->addAlias($tables[$i]); }
				$gm = GMList::getGM( $tables[$i] );
				$db = $gm->getDB();
				if( isset(self::$alias_cash[$tables[$i]]) && isset(self::$alias_cash[$tables[$i]][$vals[$i]]) && isset(self::$alias_cash[$tables[$i]][$vals[$i]][$value]) ){
					$rec = self::$alias_cash[$tables[$i]][$vals[$i]][$value];
				}else{
					$table = $db->getTable($_gm->table_type);
					$table		 = $db->searchTable(  $table, $vals[$i], '=', $value );

					if( !$db->existsRow( $table ) )
					{
						$value = '';
						break;
					}

					$rec		= $db->getRecord( $table, 0 );
					self::$alias_cash[$tables[$i]][$vals[$i]][$value] = $rec;
				}
				
				//timestampやboolに対応する為
				//$value		= $db->getData( $rec, $draws[$i], true );
				$oldFormat = $gm->timeFormatOnce;
				$gm->setTimeFormatOnce( $_gm->getTimeFormat() );
				if( isset( $brflags[$i] ) && $brflags[$i] )
					{ $value = self::controller( $gm, $rec, array( "value", $draws[$i], $brflags[$i] ) ); }
				else
					{ $value = self::controller( $gm, $rec, array( "value", $draws[$i] ) ); }
				$gm->timeFormatOnce = $oldFormat;
			}	
			
			$ret .= $value;
		
			//戻り値が空の場合のデフォルトをセット
			if( !strlen($ret) && isset($cc[5]) ){  $ret = $cc[5]; }
			return $ret;
		}

		// 指定した文字列を別テーブルの指定カラムをキーに検索を行ない置換を行なう。 
		function valueAlias(&$_gm, $_rec, $cc)
		{
			$ret = "";
			if( !isset($_gm) ) { throw new InternalErrorException( 'CommandComment Null Pointer Error -> $_gm ( via valueAlias )' ); }
			if( !isset($_gm->aliasDB[$cc[1]]) ) { $_gm->addAlias($cc[1]); }
			
			$gm = GMList::getGM( $cc[1] );
			$db = $_gm->aliasDB[$cc[1]];
			
			if( isset(self::$alias_cash[$cc[1]]) && isset(self::$alias_cash[$cc[1]][ $cc[3]]) && isset(self::$alias_cash[$cc[1]][ $cc[3]][$cc[2]]) ){
				$rec = self::$alias_cash[$cc[1]][ $cc[3]][$cc[2]];
			}else{
				$table = $db->getTable($_gm->table_type);
				$table = $db->searchTable( $table, $cc[3], '=', $cc[2] );

				if( !$db->existsRow( $table ) )
				{
					if( !strlen($ret) && isset($cc[5]) ){  $ret = $cc[5]; }
					return $ret;
				}

				$rec   = $db->getRecord( $table, 0 );
				self::$alias_cash[$cc[1]][ $cc[3]][$cc[2]] = $rec;
			}
			
				//timestampやboolに対応する為
			//$ret .= $db->getData( $rec, $cc[4], true );
			$oldFormat = $gm->timeFormatOnce;
			$gm->setTimeFormatOnce( $_gm->getTimeFormat() );
			$ret .= self::controller( $gm, $rec, array( "value",  $cc[4] ) );
			$gm->timeFormatOnce = $oldFormat;
			
			//戻り値が空の場合のデフォルトをセット
			if( !strlen($ret) && isset($cc[5]) ){  $ret = $cc[5]; }
			
			return $ret;
		}

		// 指定したカラムに入った各文字列を別テーブルの指定カラムをキーに検索を行ない置換を行ない、その一覧を返す。
		function arrayAlias(&$_gm, $_rec, $cc)
		{
			$ret = array();
			if( !isset($_gm) ) { throw new InternalErrorException( 'CommandComment Null Pointer Error -> $_gm ( via arrayAlias )' ); }
			if( !isset( $_gm->aliasDB[$cc[1]] ) ) { $_gm->addAlias($cc[1]); }
			
			$sep = '/';
			if( isset( $cc[5])){ $sep = $cc[5]; }
			
			$gm = GMList::getGM( $cc[1] );
			$db    = $_gm->aliasDB[$cc[1]];
			$table = $db->getTable($_gm->table_type);
					
			$data = $_gm->db->getData( $_rec, $cc[2] );

			if( !empty( $data ) ){
				$array       = explode( '/' , $data );
			foreach( $array as $key ){
				if( strlen($key) == 0 ) { continue; }
				if( isset(self::$alias_cash[$cc[1]]) && isset(self::$alias_cash[$cc[1]][$cc[3]]) && isset(self::$alias_cash[$cc[1]][$cc[3]][$key]) ){
					$arec = self::$alias_cash[$cc[1]][$cc[3]][$key];
				}else{
					$stable	 = $db->searchTable( $table, $cc[3], '=', $key );
					if( $db->getRow( $stable ) == 0 ) { continue; }
					$arec	 = $db->getRecord( $stable, 0 );
					self::$alias_cash[$cc[1]][$cc[3]][$key] = $arec;
				}

				$oldFormat = $gm->timeFormatOnce;
				$gm->setTimeFormatOnce( $_gm->getTimeFormat() );
				$ret[] = self::controller( $gm, $arec, array( "value",  $cc[4] ) );
				$gm->timeFormatOnce = $oldFormat;
			}
			
				return join( $ret, $sep );
			}else{
				//戻り値が空の場合のデフォルトをセット
				return $cc[6];
			}
		}

		// /で結合した置換後の文字列配列を返す。 
		function arrayValueAlias(&$_gm, $_rec, $cc)
		{
			$ret = array();
			if( !isset($_gm) ) { throw new InternalErrorException( 'CommandComment Null Pointer Error -> $_gm ( via arrayValueAlias )' ); }
			if( !isset( $_gm->aliasDB[$cc[1]] ) ) { $_gm->addAlias($cc[1]); }
			
			$sep = '/';
			if( isset( $cc[5])){ $sep = $cc[5]; }
			
			$gm = GMList::getGM( $cc[1] );
			$db    = $_gm->aliasDB[$cc[1]];
			$table = $db->getTable();
			
			if( !empty( $cc[2] ) ){

				$array = explode( '/' , $cc[2] );
				foreach( $array as $key ){
					if( isset(self::$alias_cash[$cc[1]]) && isset(self::$alias_cash[$cc[1]][$cc[3]]) && isset(self::$alias_cash[$cc[1]][$cc[3]][$key]) ){
						$arec = self::$alias_cash[$cc[1]][$cc[3]][$key];
					}else{
						$stable	 = $db->searchTable( $table, $cc[3], '=', $key );
						$arec	 = $db->getRecord( $stable, 0 );
						self::$alias_cash[$cc[1]][$cc[3]][$key] = $arec;
					}

					$oldFormat = $gm->timeFormatOnce;
					$gm->setTimeFormatOnce( $_gm->getTimeFormat() );
					$ret[] = self::controller( $gm, $arec, array( "value",  $cc[4] ) );
					$gm->timeFormatOnce = $oldFormat;
				}
			
				return join( $ret, $sep );
			}else{
				//戻り値が空の場合のデフォルトをセット
				return $cc[6];
			}
		}
	}

	class CommandBase extends command_base //
	{
		/**
		 * テーブルの全行から選択するselectBoxの表示
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 *
         * 第一引数：name
         * 第二引数：table名
         * 第三引数：option名となるカラム名
         * 第四引数：valueとなるカラム名
         * 第五引数：初期値(省略可)
         * 第六引数：未選択項目値(省略可)
         * 第七引数：タグオプション要素(省略可)
         * 第八～引数：カラム名、演算子、値の3セットのループ。
		 */
        function tableSelectForm( &$gm , $rec , $args ){
			$nrec = $rec;
            if(isset($args[4]) && strlen($args[4]))
                $check = $args[4];
            else
                $check = "";

            if(isset($args[6]) && strlen($args[6]))
                $option = ' '.$args[6];
            else
                $option = "";

            $tgm = SystemUtil::getGMforType( $args[1] );
            $db = $tgm->getDB();

            $table      = $db->getTable();
			$columnName = '';
			$parentName = '';
			$CCID       = 0;

            if(isset($args[7])){
            	for($i=0;isset($args[$i+7]);$i+=3){

					switch( $args[8+$i] )
					{
						case 'sort' :
						{
							$table = $db->sortTable( $table, $args[7+$i], $args[9+$i], true );
							break;
						}

						case 'linkage' :
						{
							$columnName = $args[7+$i];
							$parentName = $args[9+$i];
							$CCID       = rand();
							break;
						}

						default :
						{
							$table = $db->searchTable( $table, $args[7+$i], $args[8+$i], $args[9+$i] );
							break;
						}
					}
            	}
            }

            $row = $db->getRow( $table );

            $index = Array();
            $value  = Array();

            if( isset($args[5]) && strlen($args[5]) ){
                $index[] = SystemUtil::systemArrayEscape( $args[5] );
                $value[] = "";
            }

            for($i=0;$i<$row;$i++){
                $rec = $db->getRecord( $table , $i );
                $index[] = SystemUtil::systemArrayEscape($db->getData( $rec , $args[2] ) );
                $value[] = SystemUtil::systemArrayEscape($db->getData( $rec , $args[3] ) );
            }

            $index = join('/',$index);
            $value = join('/',$value);

            $param = Weave::Get( 'tagParam' , 'tableSelectForm' );

            if( count( $param ) )
			{
				if( !$option )
					{ $option = ' ' . implode( '\ ' , $param ); }
				else
					{ $option .= '\ ' . implode( '\ ' , $param ); }
			}


			if( $columnName && $parentName )
			{
				$_SESSION[ 'CC' ][ $CCID ][ 'indexName' ] = $args[ 2 ];
				$_SESSION[ 'CC' ][ $CCID ][ 'valueName' ] = $args[ 3 ];

				$this->addBuffer( $gm->getCCResult( $nrec, '<!--# form option '.$args[0].' '.$check.' '.$value.' '.$index.$option.' #-->' ) );
	            $this->addBuffer( '<script>LinkageForm( "' . $parentName . '" , "' . $args[ 0 ] . '" , "' . $args[ 1 ] . '" , "' . $columnName . '" , "' . $CCID . '" );</script>' );
			}
			else
				{ $this->addBuffer( $gm->getCCResult( $nrec, '<!--# form option '.$args[0].' '.$check.' '.$value.' '.$index.$option.' #-->' ) ); }
        }

		/**
		 * テーブルの全行から選択するselectBoxの表示
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 *
         * 第一引数：name
         * 第二引数：table名
         * 第三引数：option名となるカラム名
         * 第四引数：valueとなるカラム名
         * 第五引数：行数
         * 第六引数：初期値(省略可)
         * 第七引数：未選択項目値(省略可)
         * 第八引数：タグオプション要素(省略可)
         * 第九～引数：カラム名、演算子、値の3セットのループ。
		 */
        function tableMultipleForm( &$gm , $rec , $args ){
			$nrec = $rec;
            if(isset($args[5]) && strlen($args[5]))
                $check = $args[5];
            else
                $check = "";

            if(isset($args[7]) && strlen($args[7]))
                $option = ' '.$args[7];
            else
                $option = "";

            $tgm = SystemUtil::getGMforType( $args[1] );
            $db = $tgm->getDB();

            $table = $db->getTable();

            if(isset($args[8])){
            	for($i=0;isset($args[$i+8]);$i+=3){

					switch( $args[9+$i] )
					{
						case 'sort' :
						{
							$table = $db->sortTable( $table, $args[8+$i], $args[10+$i], true );
							break;
						}

						default :
						{
							$table = $db->searchTable( $table, $args[8+$i], $args[9+$i], $args[10+$i] );
							break;
						}
					}

            	}
            }

            $row = $db->getRow( $table );

            $index = Array();
            $value  = Array();

            if( isset($args[6]) && strlen($args[6]) ){
                $index[] = SystemUtil::systemArrayEscape( $args[6] );
                $value[] = "";
            }

            for($i=0;$i<$row;$i++){
                $rec = $db->getRecord( $table , $i );
                $index[] = SystemUtil::systemArrayEscape($db->getData( $rec , $args[2] ) );
                $value[] = SystemUtil::systemArrayEscape($db->getData( $rec , $args[3] ) );
            }

            $index = join('/',$index);
            $value = join('/',$value);

            $this->addBuffer( $gm->getCCResult( $nrec, '<!--# form multiple '.$args[0].' '.$check.' '.$value.' '.$index.' '.$args[4].$option.' #-->' ) );
        }

		/**
		 * 親子関係のテーブルの全行から、親テーブルでグループ化した子テーブル選択のためのselectBoxの表示
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 *
         * 第一引数：name
         * 第二引数：親table名
         * 第三引数：グループ名
         * 第四引数：子table名
         * 第五引数：option名となるカラム名
         * 第六引数：valueとなるカラム名
         * 第七引数：親のIDを示すカラム名
         * 第八引数：初期値(省略可)
         * 第九引数：未選択項目値(省略可)
		 */
        function groupTableSelectForm( &$gm , $rec , $args ){
            if( isset( $_POST[ $args[0] ] ) )
                $check = $_POST[ $args[0] ];
            else if(isset($args[7]))
                $check = $args[7];
            else
                $check = "";

            $pgm = SystemUtil::getGMforType( $args[1] );
            $cgm = SystemUtil::getGMforType( $args[3] );

            $pdb = $pgm->getDB();
            $cdb = $cgm->getDB();

            $ptable = $pdb->getTable();
            $prow = $pdb->getRow( $ptable );

            $str = '<select name="'.$args[0].'" >'."\n";

            if( isset($args[8]) ){
                $str .= '  <optgroup label="'.$args[8].'" >'."\n";

                $str .= '    <option value="" >'.$args[8]."\n";
                $str .= '  </optgroup>'."\n";
            }

            for($i=0;$i<$prow;$i++){
                $prec = $pdb->getRecord( $ptable , $i );

                $str .= '  <optgroup label="'.$pdb->getData( $prec , $args[2] ).'" >'."\n";

                $ctable = $cdb->searchTable( $cdb->getTable() , $args[6] , '=' , $pdb->getData( $prec , 'id' ) );
                $crow = $cdb->getRow( $ctable );

                for($j=0;$j<$crow;$j++){
                    $crec = $cdb->getRecord( $ctable , $j );
                    $option = $cdb->getData( $crec , $args[4] );
                    $value = $cdb->getData( $crec , $args[5] );
                    if( $check == $value )
                        $str .= '    <option value="'.$value.'" selected="selected">'.$option."\n";
                    else
                        $str .= '    <option value="'.$value.'" >'.$option."\n";
                }
                $str .= '  </optgroup>'."\n";
            }

            $str .= '</select>'."\n";

            $this->addBuffer( $str );
        }

		/**
		 * 多段階の親子関係のテーブルの全行を使ったGroupサーチ用のフォームを出力
         * valueは全てIDとします。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 *
         *
         * 第一引数：name
         * 第二引数：初期値
         * 第三引数：未選択項目値
         * 第四引数：親table
         * 第五引数：親option
         * 第六引数：子table
         * 第七引数：子option
         * 第八引数：親のIDを示す子のカラム名
         *
         * 以下、六～八がループ
		 */
        function groupTableSelectFormMulti( &$gm , $rec , $args ){

            if( isset( $_POST[ $args[0] ] ) )
                $check = $_POST[ $args[0] ];
            else if(isset($args[1]))
                $check = $args[1];
            else
                $check = "";

            $tcount = ( count($args) - 5 ) / 3;

            $_gm = SystemUtil::getGM();

            $param = Array();

            $param[0]['db'] = $_gm[ $args[3] ]->getDB();    //最上位テーブルを取得
            $param[0]['name'] = $args[4];
            for($i=0;$i<$tcount;$i++){
                $param[$i+1]['db'] = $_gm[ $args[ 5 + $i*3 ] ]->getDB();
                $param[$i+1]['name'] = $args[ 6 + $i*3 ];
                $param[$i+1]['parent'] = $args[ 7 + $i*3 ];
            }

            $str = '<select name="'.$args[0].'" >'."\n";


            if( isset($args[2]) ){
                $str .= '    <option value="" >'.$args[2]."\n";
            }

            groupTableSelectFormMultiReflexive( $str, $param , $check );

            $str .= '</select>'."\n";

            $this->addBuffer( $str );
        }

		/**
		 * 親子関係のテーブルの全行を使ったGroupサーチ用のフォームを出力
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 *
         * 第一引数：name
         * 第二引数：親table名
         * 第三引数：グループ名
         * 第四引数：子table名
         * 第五引数：option名となるカラム名
         * 第六引数：valueとなるカラム名
         * 第七引数：親のIDを示すカラム名
         * 第八引数：初期値(省略可)
         * 第九引数：未選択項目値(省略可)
		 */
        function searchGroupTableForm( &$gm , $rec , $args ){
            if( isset( $_POST[ $args[0] ] ) )
                $check = $_POST[ $args[0] ];
            else if(isset($args[7]))
                $check = $args[7];
            else
                $check = "";

            $pgm = SystemUtil::getGMforType( $args[1] );
            $cgm = SystemUtil::getGMforType( $args[3] );

            $pdb = $pgm->getDB();
            $cdb = $cgm->getDB();

            $ptable = $pdb->getTable();
            $prow = $pdb->getRow( $ptable );

            $str = '<select name="'.$args[0].'" >'."\n";

            if( isset($args[8]) ){
                $str .= '    <option value="" >'.$args[8]."\n";
            }

            for($i=0;$i<$prow;$i++){
                $prec = $pdb->getRecord( $ptable , $i );

                $pid = $pdb->getData( $prec , 'id' );
                $str .= '  <option value="'.$pid.'" >'.$pdb->getData( $prec , $args[2] )."\n";

                $ctable = $cdb->searchTable( $cdb->getTable() , $args[6] , '=' , $pid );
                $crow = $cdb->getRow( $ctable );

                for($j=0;$j<$crow;$j++){
                    $crec = $cdb->getRecord( $ctable , $j );
                    $option = "　".$cdb->getData( $crec , $args[4] );
                    $value = $cdb->getData( $crec , $args[5] );
                    if( $check == $value )
                        $str .= '    <option value="'.$value.'" selected="selected">'.$option."\n";
                    else
                        $str .= '    <option value="'.$value.'" >'.$option."\n";
                }
            }

            $str .= '</select>'."\n";

            $this->addBuffer( $str );
        }

		/**
		 * 多段階の親子関係のテーブルの全行を使ったGroupサーチ用のフォームを出力
         * valueは全てIDとします。
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 *
         *
         * 第一引数：name
         * 第二引数：初期値
         * 第三引数：未選択項目値
         * 第四引数：親table
         * 第五引数：親option
         * 第六引数：子table
         * 第七引数：子option
         * 第八引数：親のIDを示す子のカラム名
         *
         * 以下、六～八がループ
		 */
        function searchGroupTableFormMulti( &$gm , $rec , $args ){
            if( isset( $_POST[ $args[0] ] ) )
                $check = $_POST[ $args[0] ];
            else if(isset($args[1]))
                $check = $args[1];
            else
                $check = "";

            $tcount = ( count($args) - 5 ) / 3;

            $_gm = SystemUtil::getGM();

            $param = Array();

            $param[0]['db'] = $_gm[ $args[3] ]->getDB();    //最上位テーブルを取得
            $param[0]['name'] = $args[4];
            for($i=0;$i<$tcount;$i++){
                $param[$i+1]['db'] = $_gm[ $args[ 5 + $i*3 ] ]->getDB();
                $param[$i+1]['name'] = $args[ 6 + $i*3 ];
                $param[$i+1]['parent'] = $args[ 7 + $i*3 ];
            }

            $str = '<select name="'.$args[0].'" >'."\n";


            if( isset($args[2]) ){
                $str .= '    <option value="" >'.$args[2]."\n";
            }

            searchGroupTableFormMultiReflexive( $str, $param , $check );

            $str .= '</select>'."\n";

            $this->addBuffer( $str );
        }


		/**
		 * テーブルの全行から選択するcheckBoxの表示
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 *
         * 第一引数：name
         * 第二引数：table名
         * 第三引数：表示名となるカラム名
         * 第四引数：valueとなるカラム名
         * 第五引数：区切り文字
         * 第六引数：初期値(省略可)
         * 第七引数：未選択項目値(省略可)
         * 第八引数：一列に表示する数(省略可)
		 */
        function tableCheckForm( &$gm , $rec , $args ){

			$nrec = $rec;
            if(isset($args[5]) && strlen($args[5]))
                $check = $args[5];
            else
                $check = "";
            if(isset($args[7]) && strlen($args[7]))
                $option = ' '.$args[7];
            else
                $option = "";

            $tgm = SystemUtil::getGMforType( $args[1]);
            $db = $tgm->getDB();
            $table = $db->getTable();

			if(isset($args[8])){
				for($i=0;isset($args[$i+8]);$i+=3){

					switch( $args[9+$i] )
					{
						case 'sort' :
						{
							$table = $db->sortTable( $table, $args[8+$i], $args[10+$i], true );
							break;
						}

						default :
						{
							$table = $db->searchTable( $table, $args[8+$i], $args[9+$i], $args[10+$i] );
							break;
						}
					}

				}
			}

            $row = $db->getRow( $table );

            $index = Array();
            $value  = Array();

            if( isset($args[6]) && strlen($args[6]) ){
                $index[] = SystemUtil::systemArrayEscape($args[6]);
                $value[] = '';
            }

            for($i=0;$i<$row;$i++){
                $rec = $db->getRecord( $table , $i );
                $index[] = SystemUtil::systemArrayEscape($db->getData( $rec , $args[2] ) );
                $value[] = SystemUtil::systemArrayEscape($db->getData( $rec , $args[3] ) );
            }

            $index = join('/',$index);
            $value = join('/',$value);

            $this->addBuffer( $gm->getCCResult( $nrec, '<!--# form checkbox '.$args[0].' '.$check.' '.(isset($args[4])?$args[4]:'').' '.$value.' '.$index.$option.'  '.(isset($args[9])?$args[9]:'').' #-->' ) );
        }


		/**
		 * テーブルの全行から選択するradioButtonの表示
		 *
		 * @param gm GUIManagerオブジェクトです。
		 * @param rec 登録情報のレコードデータです。
		 * @param args コマンドコメント引数配列です。
		 *
         * 第一引数：name
         * 第二引数：table名
         * 第三引数：表示名となるカラム名
         * 第四引数：valueとなるカラム名
         * 第五引数：区切り文字
         * 第六引数：初期値(省略可)
         * 第七引数：未選択項目値(省略可)
         * 第八引数：一列に表示する数(省略可)
		 */
        function tableRadioForm( &$gm , $rec , $args ){
			$nrec = $rec;
            if(isset($args[5]) && strlen($args[5]))
                $check = $args[5];
            else
                $check = "";
            if(isset($args[7]) && strlen($args[7]))
                $option = ' '.$args[7];
            else
                $option = "";

            $tgm = SystemUtil::getGMforType( $args[1]);
            $db = $tgm->getDB();
            $table = $db->getTable();

			if(isset($args[8])){
				for($i=0;isset($args[$i+8]);$i+=3){

					switch( $args[9+$i] )
					{
						case 'sort' :
						{
							$table = $db->sortTable( $table, $args[8+$i], $args[10+$i], true );
							break;
						}

						default :
						{
							$table = $db->searchTable( $table, $args[8+$i], $args[9+$i], $args[10+$i] );
							break;
						}
					}

				}
			}

            $row = $db->getRow( $table );

            $index = Array();
            $value  = Array();

            if( isset($args[6]) && strlen($args[6]) ){
                $index[] = SystemUtil::systemArrayEscape( $args[6] );
                $value[] = '';
            }

            for($i=0;$i<$row;$i++){
                $rec = $db->getRecord( $table , $i );
                $index[] = SystemUtil::systemArrayEscape($db->getData( $rec , $args[2] ) );
                $value[] = SystemUtil::systemArrayEscape($db->getData( $rec , $args[3] ) );
            }

            $index = join('/',$index);
            $value = join('/',$value);

            $this->addBuffer( $gm->getCCResult( $nrec, '<!--# form radio '.$args[0].' '.$check.' '.$args[4].' '.$value.' '.$index.$option.'  '.$args[9].' #-->' ) );
        }

		/**
			@brief テーブルの内容を列挙するための単純な方法を提供する。
			@param $iGM   GUIManagerオブジェクトです。このメソッドでは利用しません。
			@param $iRec  登録情報のレコードデータです。このメソッドでは利用しません。
			@param $iArgs コマンドコメント引数配列です。検索ページに渡すクエリパラメータを指定します。
		*/
		function listing( &$iGM , $iRec , $iArgs ) //
		{
			$this->getEmbedParameter( $iArgs , $query , $search , $db , $system , $table );

			if( !$query[ 'listingID' ] ) //リストパーツの指定がない場合
				{ return; }

			$originRow = $db->getRow( $table );

			if( $query[ 'row' ] ) //最大行数の指定がある場合
				{ $table = $db->limitOffset( $table , 0 , $query[ 'row' ] ); }

			$row = $db->getRow( $table );

			if( !$query[ 'sort' ] ) //ソートが指定されていない場合
			{
				$query[ 'sort' ]     = 'shadow_id';
				$query[ 'sort_PAL' ] = 'asc';

				$table = $db->sortTable( $table , 'shadow_id' , 'asc' , true );
			}

			$gm = GMList::getGM( $query[ 'type' ] );

			array_push( $gm->templateStack , $iGM->getCurrentTemplate() );

			$getSwap   = $_GET;
			$queryHash = sha1( serialize( $query ) );

			if( !$_SESSION[ 'search_query_index' ] ) //クエリキャッシュのインデックスがない場合
				{ $_SESSION[ 'search_query_index' ] = 0; }

			if( !isset( $_SESSION[ 'search_query_hash' ][ $queryHash ] ) ) //クエリキャッシュがない場合
			{
				$_SESSION[ 'search_query_hash' ][ $queryHash ] = $_SESSION[ 'search_query_index' ];
				$_GET[ 'q' ]                                   = $_SESSION[ 'search_query_index' ];
				$query[ 'q' ]                                  = $_SESSION[ 'search_query_index' ];
				$_SESSION[ 'search_query' ][ $_GET[ 'q' ] ]    = $query;

				++$_SESSION[ 'search_query_index' ];
			}
			else //クエリキャッシュがある場合
			{
				$_GET[ 'q' ]  = $_SESSION[ 'search_query_hash' ][ $queryHash ];
				$query[ 'q' ] = $_SESSION[ 'search_query_hash' ][ $queryHash ];
			}

			if( !$row ) //検索結果が空の場合
			{
				$this->addBuffer( $gm->getCCResult( $rec , '<!--# adapt ' . $query[ 'listingID' ] . '_failed #-->' ) );
				return;
			}

			$repeat = ( $query[ 'row' ] ? $query[ 'row' ] : $row );
			$table->onCash();

			$this->addBuffer( $gm->getCCResult( $rec , '<!--# adapt ' . $query[ 'listingID' ] . '_head #-->' ) );

			if( $query[ 'row' ] && $originRow > $query[ 'row' ] ) //表示数より検索結果が多い場合
				{ $this->addBuffer( $gm->getCCResult( $rec , '<!--# adapt ' . $query[ 'listingID' ] . '_head_over #-->' ) ); }

			for( $i = 0 ; $repeat > $i ; ++$i ) //全ての行を処理
			{
				if( $row <= $i ) //テーブルの行数を超える場合
				{
					$output = $gm->getCCResult( null , '<!--# adapt ' . $query[ 'listingID' ] . '_empty #-->' );

					if( $output ) //出力内容がある場合
						{ $this->addBuffer( $output . "\n" ); }
				}
				else //テーブルからレコードが取れる場合
				{
					$rec = $db->getRecord( $table , $i );

					$gm->setVariable( 'num' , $i + 1 );

					if( $query[ 'source_nobr' ] ) //ソースの改行無効指定がある場合
						{ $this->addBuffer( $gm->getCCResult( $rec , '<!--# adapt ' . $query[ 'listingID' ] . ' #-->' ) ); }
					else
						{ $this->addBuffer( $gm->getCCResult( $rec , '<!--# adapt ' . $query[ 'listingID' ] . ' #-->' ) . "\n" ); }

					$db->cashReset();
				}
			}

			if( $query[ 'row' ] && $originRow > $query[ 'row' ] ) //表示数より検索結果が多い場合
				{ $this->addBuffer( $gm->getCCResult( $rec , '<!--# adapt ' . $query[ 'listingID' ] . '_foot_over #-->' ) ); }

			$this->addBuffer( $gm->getCCResult( $rec , '<!--# adapt ' . $query[ 'listingID' ] . '_foot #-->' ) );

			$table->offCash();
			$_GET = $getSwap;

			array_pop( $gm->templateStack );
		}

		/**
			@brief 値を列挙するための単純な方法を提供する。
			@param $iGM   GUIManagerオブジェクトです。このメソッドでは利用しません。
			@param $iRec  登録情報のレコードデータです。このメソッドでは利用しません。
			@param $iArgs コマンドコメント引数配列です。
		*/
		function each( &$iGM , $iRec , $iArgs ) //
		{
			$listingID  = array_shift( $iArgs );
			$values     = array_shift( $iArgs );
			$sourceNoBR = array_shift( $iArgs );
			$i          = 0;

			foreach( explode( '/' , $values ) as $value ) //全ての行を処理
			{
				$iGM->setVariable( 'num' , $i + 1 );
				$iGM->setVariable( 'val' , $value );

				if( $sourceNoBR ) //ソースの改行無効指定がある場合
					{ $this->addBuffer( $iGM->getCCResult( $iRec , '<!--# adapt ' . $listingID . ' #-->' ) ); }
				else
					{ $this->addBuffer( $iGM->getCCResult( $iRec , '<!--# adapt ' . $listingID . ' #-->' ) . "\n" ); }
			}
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
			$oDB     = $gm[ $_GET[ 'type' ] ]->getDB();
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
	}
