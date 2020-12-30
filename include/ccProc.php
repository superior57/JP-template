<?php

	/*******************************************************************************************************
	 * <PRE>
	 *
	 * ccProcクラス。
	 *
	 * @author 丹羽一智
	 * @version 3.0.0
	 *
	 * </PRE>
	 *******************************************************************************************************/

	include_once "./include/base/ccProcBase.php";

	class ccProc extends ccProcBase
	{
		private static $_DEBUG	 = DEBUG_FLAG_CCPROC;
		private static $MemoCCValues = Array();

		//debugフラグ操作用
		static function onDebug(){ self::$_DEBUG = true; }
		static function offDebug(){ self::$_DEBUG = false; }

		// 関数の割り振り
		static function controller(&$gm, $rec, $cc)
		{
			if( self::$_DEBUG ){ d($cc,'ccProc'); }

			switch($cc[0])
			{
			case 'readhead':
			case 'readend':
			case 'ifbegin':
			case 'elseif':
			case 'else':
			case 'endif':
			case 'switch':
			case 'case':
			case 'break':
			case 'endswitch':
			case 'default':
				return;
			case 'include':
				return ccProc::drawDesign($gm, $rec, $cc);
			case 'adapt':
				return ccProc::drawAdapt($gm, $rec, $cc);
			case '//':
				return;
			default:
				return ccProc::{$cc[0]}($gm, $rec, $cc);
			}
		}

		// テンプレートに関連付けられたレコードの引数で指定されたカラムの内容を出力する。
		function value(&$gm, $rec, $cc)
		{
			$ret = "";
			if( !isset($gm) ) { throw new InternalErrorException( 'CommandComment Null Pointer Error -> $gm ( via value )' ); }
			if( isset($_GET[$cc[1]]) ) { $_POST[$cc[1]] = $_GET[$cc[1]]; }

			$db = $gm->getDB();
			$type = $gm->colType[$cc[1]];

			if( $cc[1] == 'delete_time')
			{
				$type = 'timestamp';
			}

			switch($type)
			{
			case 'timestamp':
				$time	  = $db->getData( $rec, $cc[1] );
				$format   = $gm->getTimeFormat();
				if( $time > 0 ){
					if( isset($cc[2]) && strtolower($cc[2]) == 'false' )	{ $ret	.= $time; }
					else								{ $ret	.= SystemUtil::mb_date(  $format, $time  ); }
				}
				break;
			case 'date':
				$date	  = $db->getData( $rec, $cc[1] );
				if( $date > 0 ){
					if( isset($cc[2]) && strtolower($cc[2]) == 'false' ){ $ret	.= $date; }
					else												{ $ret	.= SystemUtil::date( $gm->dateFormat, $date ); }
				}
				break;
			case 'boolean':
				if( $db->getData($rec, $cc[1]) ) { $ret .= 'TRUE'; }
				else							 { $ret .= 'FALSE'; }

				break;
			default:
				if( is_null($rec) && isset($_POST[$cc[1]]) )
				{
					$brFlg = false;
					if( isset($cc[2]) && strtoupper($cc[2]) == 'TRUE' ) { $brFlg = true; }

					if($brFlg)	 { $ret .= brChange($_POST[$cc[1]]); }
					else		 { $ret .= $_POST[$cc[1]]; }
				}
				else
				{
						$ret .= $db->getData( $rec, $cc[1], !(isset($cc[2]) && strtoupper($cc[2]) == 'FALSE') );
				}

				break;
			}

			//戻り値が空の場合のデフォルトをセット
			if( !strlen($ret) && isset($cc[3]) ){  $ret = $cc[3]; }

			return $ret;
		}

		function arrayValueReplace(&$gm, $rec, $cc)
		{
			$ret = "";
			if( !isset($gm) ) {
				throw new InternalErrorException( 'CommandComment Null Pointer Error -> $gm ( via arrayReplace )' );
			}
			$db = $gm->getDB();

			$data = $cc[1];

			$array	 = explode( '/', $data );
			$befor	 = array_flip(explode( '/', $cc[3] ));
			$after	 = explode( '/', $cc[4] );

			foreach( $array as $data ){
				if( is_bool($data) )
				{
					if( $data ) {
						$data	 = 'TRUE';
					}
					else		{ $data	 = 'FALSE';
					}
					$cc[3]	 = strtoupper($cc[3]);
				}

				if( strlen($ret) ) {
					$ret .= $cc[2];
				}


				if( isset( $befor[$data] ) && isset($after[ $befor[$data] ]) ){
					$ret .= $after[ $befor[$data] ];
				}
			}

			//戻り値が空の場合のデフォルトをセット
			if( !strlen($ret) && isset($cc[5]) ){
				$ret = $cc[5];
			}
			return $ret;
		}

		/**
			@brief テーブルの値を別の値に変換する。
			@details
				他のテーブルの値に変換する場合
					最短 : <!--# convert 元カラム テーブル名 #-->
					フル : <!--# convert 元カラム to テーブル名.検索カラム to 取得カラム split 分割文字 join 結合文字 single #-->
				変換リストを文字列で指定する場合
					最短 : <!--# convert 元カラム 元リスト 変換リスト #-->
					フル : <!--# convert 元カラム to 元リスト to 変換リスト split 分割文字 join 結合文字 single #-->
			@remarks
				フル構文のtoは省略可能
				singleを指定すると1つマッチした時点で変換を終了する
		*/
		function convert( &$iGM , $iRec , $iArgs ) //
		{
			$db = $iGM->getDB();

			if( 'boolean' == $db->colType[ $iArgs[ 1 ] ] ) //bool型の場合
				{ $iArgs[ 1 ] = ( $db->getData( $iRec , $iArgs[ 1 ] ) ? 'TRUE' : 'FALSE' ); }
			else //他の型の場合
				{ $iArgs[ 1 ] = $db->getData( $iRec , $iArgs[ 1 ] ); }

			return self::convertString( $iGM , $iRec , $iArgs );
		}

		/**
			@brief 任意の値を別の値に変換する。
			@details
				他のテーブルの値に変換する場合
					最短 : <!--# convertString 元文字列 テーブル名 #-->
					フル : <!--# convertString 元文字列 to テーブル名.検索カラム to 取得カラム split 分割文字 join 結合文字 single #-->
				変換リストを文字列で指定する場合
					最短 : <!--# convertString 元文字列 元リスト 変換リスト #-->
					フル : <!--# convertString 元文字列 to 元リスト to 変換リスト split 分割文字 join 結合文字 single #-->
			@remarks
				フル構文のtoは省略可能
				singleを指定すると1つマッチした時点で変換を終了する
		*/
		function convertString( &$iGM , $iRec , $iArgs ) //
		{
			global $TABLE_NAME;

			array_shift( $iArgs );

			$originValue = array_shift( $iArgs );
			$beforeParam = array_shift( $iArgs );

			if( '->' == $beforeParam || 'to' == $beforeParam ) //補助構文の場合
				{ $beforeParam = array_shift( $iArgs ); }

			$afterParam = array_shift( $iArgs );

			if( '->' == $afterParam || 'to' == $afterParam ) //補助構文の場合
				{ $afterParam = array_shift( $iArgs ); }

			$splitParam = '/';
			$joinParam  = '/';

			while( count( $iArgs ) ) //引数がある間繰り返し
			{
				$paramName = array_shift( $iArgs );

				switch( $paramName ) //パラメータ名で分岐
				{
					case 'single' : //単一変換
					{
						$singleMode = true;

						break;
					}

					case 'split' : //分割文字列
					{
						$splitParam = array_shift( $iArgs );

						break;
					}

					case 'join' : //結合文字列
					{
						$joinParam = array_shift( $iArgs );

						break;
					}

					default : //その他
						{ throw new LogicException( 'CC構文エラー:引数' . $paramName . 'は実装されていません。' ); }
				}
			}

			$originValue = ( $singleMode ? Array( $originValue ) : explode( $splitParam , $originValue ) );
			$result      = Array();

			if( FALSE !== strpos( $beforeParam , $splitParam ) ) //replaceモードの場合
			{
				$beforeParam = explode( $splitParam , $beforeParam );
				$afterParam  = explode( $splitParam , $afterParam );

				foreach( $originValue as $value ) //全ての値を処理
				{
					$index = array_search( $value , $beforeParam );

					if( FALSE !== $index ) //値が見つかった場合
						{ $result[] = $afterParam[ $index ]; }
				}
			}
			else //aliasモードの場合
			{
				List( $tableName , $searchColumn ) = explode( '.' , $beforeParam );

				if( !in_array( $tableName , $TABLE_NAME ) ) //テーブル名の指定が間違っている場合
					{ throw new LogicException( 'CC構文エラー:' . $tableName . 'テーブルは存在しません。' ); }

				$db = GMList::getDB( $tableName );

				if( !$searchColumn ) //検索カラムの指定がない場合
					{ $searchColumn = 'id'; }

				if( !$afterParam ) //取得カラムの指定がない場合
					{ $afterParam = 'name'; }

				if( !in_array( $searchColumn , $db->colName ) ) //検索カラムの指定が間違っている場合
					{ throw new LogicException( 'CC構文エラー:' . $tableName . 'に' . $searchColumn . 'カラムは存在しません。' ); }

				if( !in_array( $afterParam , $db->colName ) ) //取得カラムの指定が間違っている場合
					{ throw new LogicException( 'CC構文エラー:' . $tableName . 'に' . $afterParam . 'カラムは存在しません。' ); }

				foreach( $originValue as $value ) //全ての値を処理
				{
					$cacheName = implode( '.' , Array( $tableName , $searchColumn , $value ) );

					if( isset( self::$alias_cash[ $cacheName ] ) ) //キャッシュがある場合
					{
						$rec      = self::$alias_cash[ $cacheName ];
						$result[] = $db->getData( $rec , $afterParam );
					}
					else //キャッシュがない場合
					{
						$table = $db->getTable();
						$table = $db->searchTable( $table , $searchColumn , '=' , $value );
						$table = $db->limitOffset( $table , 0 , 1 );

						if( $db->getRow( $table ) ) //一致する行がある場合
						{
							$rec      = $db->getRecord( $table , 0 );
							$result[] = $db->getData( $rec , $afterParam );
						}
					}
				}
			}

			return implode( $joinParam , $result );
		}

		// テンプレートに関連づいたレコードから指定したカラムの値を抜きだし、存在する場合はそれを画像のパスとしてimgタグに受け渡し表示する。
		function object(&$gm, $rec, $cc)
		{
			global $IMAGE_NOT_FOUND;
			global $IMAGE_NOT_FOUND_SRC;
			global $THUMBNAIL_OPTIONS;
			global $FileBase;

			$ret = "";
			switch($cc[1])
			{
			case 'image':
					//size,alt,str,not,link等を柔軟に指定可能なimg出力
					$not = $IMAGE_NOT_FOUND;
					if( strlen($cc[2]) ){	$elements['src'] = $gm->db->getData( $rec, $cc[2] );	}
					$elements['alt'] = '';

					$link = false;
					$thumbnail = true;
					$ret = "";

					for($i=3;$i<count($cc);$i++){
						switch($cc[$i]){
							case 'size':
								$elements['width'] = $cc[++$i];
								$elements['height'] = $cc[++$i];
								break;
							case 'maxSize':
								$maxWidth  = $cc[++$i];
								$maxHeight = $cc[++$i];
								break;
							case 'minSize':
								$minWidth  = $cc[++$i];
								$minHeight = $cc[++$i];
								break;
							case 'not':		$not = $cc[++$i]; break;
							case 'subsrc':	$subsrc = $cc[++$i]; break;
							case 'defsrc':	$defsrc = $IMAGE_NOT_FOUND_SRC; break;
							case 'link':	$link = true; break;
							case 'nothumbnail': $thumbnail=false; break;
							case 'option': $option = $cc[++$i]; break;
							case 'img_suffix': $img_suffix = $cc[++$i]; break;
							case 'link_option': $link_option = $cc[++$i]; break;
							default://alt,srcなど
								$elements[$cc[$i]] = $cc[++$i];
								break;
						}
					}

					if( !isset($elements['src']) || !strlen($elements['src']) || !$FileBase->file_exists($elements['src']) )
					{
						if( isset($subsrc) && strlen($subsrc) && is_file($subsrc) )
							{ $elements['src'] = $subsrc; }
						else if( isset($defsrc) && strlen($defsrc) && is_file($defsrc) )
							{ $elements['src'] = $defsrc; }
						else
							{ return $not; }
					}

					$file_exists = $FileBase->file_exists($elements['src']);
					$file_src = $FileBase->geturl($elements['src']);
					if( !$file_exists ){ return $not; }

					$info = $FileBase->getimagesize( $elements['src'] );

					if( $maxWidth && ( $maxWidth <= $elements[ 'width' ] || ( !$elements[ 'width' ] && $maxWidth <= $info[ 0 ] ) ) )
						{ $elements[ 'width' ] = $maxWidth; }

					if( $maxHeight && ( $maxHeight <= $elements[ 'height' ] || ( !$elements[ 'height' ] && $maxHeight <= $info[ 1 ] ) ) )
						{ $elements[ 'height' ] = $maxHeight; }

					if( $minWidth && ( $minWidth >= $elements[ 'width' ] || ( !$elements[ 'width' ] && $minWidth >= $info[ 0 ] ) ) )
						{ $elements[ 'width' ] = $minWidth; }

					if( $minHeight && ( $minHeight >= $elements[ 'height' ] || ( !$elements[ 'height' ] && $minHeight >= $info[ 1 ] ) ) )
						{ $elements[ 'height' ] = $minHeight; }

					if($link){
						$url = $file_src;

						$ret	.= '<a href="'. $url .'" ';
						if(isset($link_option)){ $ret .= $link_option; }
						else{ $ret .= 'target="_blank" ';}
						$ret	.= '>';
					}

					if( $thumbnail && isset($elements['width']) && isset($elements['height']) ){


						if( WS_SYSTEM_GDIMAGE_PROGRESS_IMAGE )
						{
							$trimming = ( isset( $elements[ 'trimming' ] ) ? 'true' == strtolower( $elements[ 'trimming' ] ) : null );

							if( mod_Thumbnail::Useable( $elements[ 'src' ] , $elements[ 'width' ] , $elements[ 'height' ] , $trimming ) )
								{ $elements[ 'src' ] = mod_Thumbnail::Create( $elements[ 'src' ] , $elements[ 'width' ] , $elements[ 'height' ] , $trimming ); }
							else
							{
								$elements['src'] = 'thumb.php?src=' . $elements[ 'src' ] . '&width=' . $elements[ 'width' ] . '&height=' . $elements[ 'height' ];

								if( isset( $elements[ 'trimming' ] ) )
									{ $elements[ 'src' ] .= '&trimming=' . $elements[ 'trimming' ]; }
							}
						}
						else
						{
							$trimming = ( isset( $elements[ 'trimming' ] ) ? 'true' == strtolower( $elements[ 'trimming' ] ) : null );
							$elements['src'] = mod_Thumbnail::Create( $elements['src'],$elements['width'],$elements['height'],$trimming);
						}
					}else if($file_exists)
					{
						$elements['src'] = $file_src;
					}
					else if( !$elements[ 'width' ] && !$elements[ 'height' ] && isset($info[0]) && isset($info[1]) )
					{
						$elements[ 'width' ] = $info[ 0 ];
						$elements[ 'height' ] = $info[ 1 ];
					}

					$ret .= '<img ';
					foreach( $elements as $name => $val ){ $ret .= $name.'="'.$val.'" '; }
					if(isset($option)){ $ret .= $option.' '; }
					$ret .= '/>';

					if(isset($img_suffix)){ $ret .= $img_suffix; }

					if($link){	$ret    .= '</a>';	}
				break;

			case 'imageSize':
					// 画像が存在する場合はそれを画像のパスとしてimgタグに受け渡し表示する。widthとheightを設定可能。
					$param = Array( 'object','image',$cc[2],'size',$cc[3],$cc[4]);
					if(isset($cc[5])){ $param[] = 'option'; $param[] = $cc[5];}
					$ret .= self::object($gm, $rec, $param);
				break;
			case 'imageStr':
				// 表示する画像のパスを文字列指定で渡す。
					$param = Array( 'object','image','','src',$cc[2]);
					$ret .= self::object($gm, $rec, $param);
				break;
			case 'imageSizeStr':
				// 表示する画像のパスを文字列指定で渡す。widthとheightを設定可能。
					$param = Array( 'object','image','','src',$cc[2],'size',$cc[3],$cc[4]);
					if(isset($cc[5])){ $param[] = 'option'; $param[] = $cc[5];}
					$ret .= self::object($gm, $rec, $param);
				break;
			case 'linkImage':
					// 画像が存在する場合はそれを画像のパスとしてimgタグに受け渡し表示する。画像には画像へのリンクを付与する。
					$param = Array( 'object','image',$cc[2],'link');
					if(isset($cc[3])){ $param[] = 'option'; $param[] = $cc[3];}
					$ret .= self::object($gm, $rec, $param);
				break;
			case 'linkImageSize':
					$param = Array( 'object','image',$cc[2],'size',$cc[3],$cc[4],'link');
					if(isset($cc[5])){ $param[] = 'option'; $param[] = $cc[5];}
					$ret .= self::object($gm, $rec, $param);
				break;
			case 'imageSizeNotfound':
					$param = Array( 'object','image',$cc[2],'size',$cc[3],$cc[4],'not',$cc[5]);
					if(isset($cc[6])){ $param[] = 'option'; $param[] = $cc[6];}
					$ret .= self::object($gm, $rec, $param);
				break;
			}
			return $ret;
		}

		// formを出力する。
		function form(&$gm, $rec, $cc)
		{
			global $FileBase;

			$ret = "";
			$col = $cc[2];

			/*
				パラメータは POST,record,GET,デフォルト値の順で読み込まれる
			*/
			if( isset($_POST[$col]) ){
				$initial = $_POST[$col];
			}else if( !is_null($rec) && $gm->getDB()->isColumn($col) )
			{
				$db = $gm->getDB();
				$initial = $db->getData( $rec, $col );
				if( is_bool($initial) )
				{
					if( $initial )	{ $initial	 = 'TRUE'; }
					else			{ $initial	 = 'FALSE'; }
				}
			}else if( isset($_GET[$col]) )
			{
				$initial = $_GET[$col];
			}

			switch($cc[1])
			{
				case 'text':
					// textのinputタグを出力。
					$option = "";
					if( isset($cc[6]) ) { $option = $cc[6].' '; }

					if( isset($cc[3]) && strlen($cc[3]) ) { $option .= 'size="'. $cc[3] .'" '; }
					if( isset($cc[4]) && strlen($cc[4]) ) { $option .= 'maxlength="'. $cc[4] .'" '; }

					$value = "";
					if( isset($cc[5]) && strlen($cc[5]) ) {	$value = h($cc[5]); }
					if( isset($initial) ) { $value = h($initial); }

					$ret .= '<input type="text" name="'. $col .'" value="'.$value.'" '.$option .'/>'. "\n";
					break;

				case 'password':
						// passwordのinputタグを出力。
					$option = "";
					if( isset($cc[5]) ) { $option = $cc[5].' '; }

					if( isset($cc[3]) && strlen($cc[3]) ) { $option .= 'size="'. $cc[3] .'" '; }
					if( isset($cc[4]) && strlen($cc[4]) ) { $option .= 'maxlength="'. $cc[4] .'" '; }

					$ret .= '<input type="password" name="'. $col .'" '.$option .' />'. "\n";
					break;

				case 'textarea':
						// textareaタグを出力。
					$option	 = "";
					if( isset($cc[6]) ) { $option = $cc[6]; }

					$value = isset($cc[5])?$cc[5]:'';
					if( isset($initial) ) { $value = $initial; }
					if( isset($cc[ 7 ]) && 'nobr' != $cc[ 7 ] ){ $value = str_replace( '<br/>', "\n", $value ); }

					$ret .= '<textarea name="'. $col .'" cols="'. $cc[3] .'" rows="'. $cc[4] .'" '. $option .'>'. h ( $value, ENT_QUOTES | ENT_HTML401 ) .'</textarea>'. "\n";
					break;

				case 'radiobox':
				case 'radio':
					// radioのinputタグを配列の数だけ出力
					$value	 = explode( '/', $cc[5] );
					$index	 = explode( '/', $cc[6] );

					$option	 = "";
					if( isset($cc[7]) ) { $option = $cc[7]; }

					$init = isset($cc[3])?$cc[3]:'';
					if( isset($initial) ) { $init = $initial; }
					$init = self::initEscape($init);

					$count = count($value);
					for($i=0; $i<$count; $i++)
					{
						$checked = "";
						if( $value[$i] == $init ) { $checked = ' checked="checked" '; }

						$ret .= '<label><input type="radio" name="'. $col .'" value="'. $value[$i] .'" '. $option .''.$checked.'/>'. $index[$i]. $cc[4]. "</label>\n";
					}
					break;
				case 'checkbox':
				case 'check':
					// checkboxのinputタグを配列の数だけ出力
					$value	 = explode( '/', $cc[5] );
					$index	 = explode( '/', $cc[6] );

					$option	 = "";
					if( isset($cc[7]) ) { $option = $cc[7]; }

					$init = array();
					if( isset($initial) )
					{
						if( is_array($initial) ) { $init = $initial; }
						else						 { $init = explode( '/', $initial ); }
					}else{ $init	 = explode( '/', $cc[3] ); }
					$init = self::initEscape($init);

					$valueCount	 = count($value);
					for($i=0; $i<$valueCount; $i++)
					{
						$checked = "";
						if( array_search($value[$i],$init) !== FALSE ){ $checked = ' checked="checked" '; }

						$ret .= '<label><input type="checkbox" name="'. $col .'[]" value="'. $value[$i] .'" '. $option .$checked.'/>'. $index[$i]. $cc[4]. "</label>\n";
					}

					if(!strlen($cc[8]) || $cc[8] != 'true' ) { $ret .= '<input type="hidden" name="'. $col .'_CHECKBOX" value="" />'."\n"; }
					break;

				case 'option':
					// プルダウン(select-optionタグのセット)を出力
					$value	 = explode( '/', $cc[4] );
					$index	 = explode( '/', $cc[5] );

					$option = "";
					if( isset($cc[6]) ) { $option	 = $cc[6]; }

					$init = $cc[3];
					if( isset($initial) ) { $init = $initial; }
					$init = self::initEscape($init);


					$ret .= '<select name="'. $col .'" '. $option .'>'. "\n";
					$count = count($value);
					for($i=0; $i<$count; $i++)
					{
						$selected = "";
						if( $value[$i] == $init ) { $selected = ' selected="selected" '; }

						$ret .= '<option value="'. $value[$i] .'"'.$selected.'>'. $index[$i] .'</option>'. "\n";
					}
					$ret .= '</select>'. "\n";

					break;
				case 'multiple':
					// プルダウン(select-optionタグのセット)を出力
					$value	 = explode( '/', $cc[4] );
					$index	 = explode( '/', $cc[5] );

					$option		 = "";
					if(  isset(  $cc[7]  )  )	{ $option	 = $cc[7]; }

					$init = $cc[3];
					if( isset($initial) ) {
						$init = $initial;
					}
					// array_searchのために配列化
					$init = self::initEscape($init);
					if ( !is_array($init) ) { $init = explode('/', $init); }

					$ret .= '<select name="'. $col .'[]" multiple="multiple" size="'.$cc[6].'" '. $option .'>'. "\n";
					$count = count($value);

					for($i=0; $i<$count; $i++)
					{
						$selected = '';
						if( array_search( $value[$i], $init ) !== FALSE ){ $selected = 'selected="selected"'; }

						$ret	 .= '<option value="'. $value[$i] .'" '.$selected.'>'. $index[$i] .'</option>'. "\n";
					}
					$ret	 .= '</select>'. "\n";

					break;

				case 'multi_image':
				case 'multi_file':
					$option	        = '';
					$deleteText     = '削除';
					$max_num = $cc[3];
					$enableFileTemps = array();
					$enableDeletes = array();
					$ret="";

					$fileCount = 0;

					if( isset( $cc[ 4 ] ) && strlen( $cc[ 4 ] ) ) //オプションの指定がある場合
					{ $option = $cc[ 4 ]; }

					if( isset( $cc[ 5 ] ) && strlen( $cc[ 5 ] ) ) //削除チェックの文言指定がある場合
					{ $deleteText = $cc[ 5 ]; }


					//ファイルの有無を確認
					for ($i = 1; $i <= $max_num; $i++) {
						$col_name = $col.$i;

						if( !is_null($rec) && $gm->getDB()->isColumn($col_name) )
						{
							$db = $gm->getDB();
							$initial = $db->getData( $rec, $col_name );
						}else if( isset($_GET[$col_name]) )
						{
							$initial = $_GET[$col_name];
						}else if( isset($_POST[$col_name]) ) {
							$initial = $_POST[$col_name];
						}

						$enablePosts[$i]     = ( isset( $initial ) && strlen( $initial ) );
						$enableFileTemps[$i] = (isset($_POST[$col_name . '_filetmp']) && strlen($_POST[$col_name . '_filetmp']));
						$enableDeletes[$i] = (isset($_POST[$col_name . '_DELETE']) && 'true' == $_POST[$col_name . '_DELETE']);

						if( $enablePosts[$i] || $enableFileTemps[$i] ) //ファイルまたは引き継ぎ情報がある場合
						{
							// TODO: case の分岐と同じ事してて美しくない
							if( 'multi_file' == $cc[ 1 ] ) //ファイルフォームの場合
							{
								$param  = Array( 'value' , $col_name );
								$ret   .= '<a href="' . self::value( $gm , $rec , $param ) . '" target="_blank">' . self::value( $gm , $rec , $param ) . '</a>';
							}else{ // image フォームの場合
								if( $enablePosts[$i] ) //画像の情報がある場合
								{ $param = Array( 'object' , 'image' , $col_name , 'not' , '' , 'link' ); }
								else if( $enableFileTemps[$i] ) //引き継ぎ情報がある場合
								{ $param = Array( 'object' , 'image' , '' , 'not' , '' , 'link' , 'src' , $_POST[ $col_name . '_filetmp' ] ); }

								if( isset( $cc[ 6 ] ) && strlen( $cc[ 6 ] ) ) //幅の指定がある場合
								{
									$param[] = 'width';
									$param[] = $cc[ 6 ];
								}

								if( isset( $cc[ 7 ] ) && strlen( $cc[ 7 ] ) ) //高さの指定がある場合
								{
									$param[] = 'height';
									$param[] = $cc[ 7 ];
								}

								$ret .= self::object( $gm , $rec , $param );
							}
							$ret .= '<br />';

							if ($enablePosts[$i]) //ファイルの情報がある場合
							{
								$ret .= '<input name="' . $col_name . '_filetmp" type="hidden" value="' . $_POST[$col_name] . '" />' . "\n";
								$ret .= '<label><input type="checkbox" name="' . $col_name . '_DELETE" value="true" />' . $deleteText . '</label>';

							} else  //引き継ぎ情報がある場合
							{
								$ret .= '<input name="' . $col_name . '_filetmp" type="hidden" value="' . $_POST[$col_name . '_filetmp'] . '" />' . "\n";

								if ($enableDeletes[$i]) //削除チェックの引き継ぎがある場合
								{
									$ret .= '<label><input type="checkbox" name="' . $col_name . '_DELETE" value="true" checked="checked" />' . $deleteText . '</label>';
								} else //削除チェックの引き継ぎがない場合
								{
									$ret .= '<label><input type="checkbox" name="' . $col_name . '_DELETE" value="true" />' . $deleteText . '</label>';
								}
							}
							$ret   .= '<br />';

							$fileCount++;
						}
					}
					$ret = '<input name="' . $col . '[]" type="file" ' . $option . 'multiple="multiple">' . "\n<br />\n"
							. ($fileCount==0?'':"現在 $fileCount 個のファイルをアップロード済みで、残り"). ($max_num-$fileCount)."個のファイルをアップロード可能です。\n<br />\n"
							.$ret;

					break;
				case 'image' : //ファイル入力(type=fileのinputタグ)を出力(サムネイル表示付き)

					$enablePost     = ( isset( $initial ) && strlen( $initial ) );
					$enableFileTemp = ( isset( $_POST[ $col . '_filetmp' ] ) && strlen( $_POST[ $col . '_filetmp' ] ) );

					if( $enablePost || $enableFileTemp ) //画像または引き継ぎ情報がある場合
					{
						if( $enablePost ) //画像の情報がある場合
							{ $param = Array( 'object' , 'image' , $col , 'not' , '' , 'link' ); }
						else if( $enableFileTemp ) //引き継ぎ情報がある場合
							{ $param = Array( 'object' , 'image' , '' , 'not' , '' , 'link' , 'src' , $_POST[ $col . '_filetmp' ] ); }

						if( isset( $cc[ 5 ] ) && strlen( $cc[ 5 ] ) ) //幅の指定がある場合
						{
							$param[] = 'width';
							$param[] = $cc[ 5 ];
						}

						if( isset( $cc[ 6 ] ) && strlen( $cc[ 6 ] ) ) //高さの指定がある場合
						{
							$param[] = 'height';
							$param[] = $cc[ 6 ];
						}

						$ret .= self::object( $gm , $rec , $param );
						$ret .= '<br />';
					}

				case 'file' : //ファイル入力(type=fileのinputタグ)を出力

					$option	        = '';
					$deleteText     = '削除';
					$enablePost     = ( isset( $initial ) && strlen( $initial ) );
					$enableFileTemp = ( isset( $_POST[ $col . '_filetmp' ] ) && strlen( $_POST[ $col . '_filetmp' ] ) );
					$enableDelete   = ( isset( $_POST[ $col . '_DELETE' ] ) && 'true' == $_POST[ $col . '_DELETE' ] );

					if( $enablePost || $enableFileTemp ) //ファイルまたは引き継ぎ情報がある場合
					{
						if( 'file' == $cc[ 1 ] ) //ファイルフォームの場合
						{
							$param  = Array( 'value' , $col );
							$filepath = self::value( $gm , $rec , $param );
							$ret   .= '<a href="' . $FileBase->geturl($filepath) . '" target="_blank">' . $filepath . '</a>';
							$ret   .= '<br />';
						}
					}

					if( isset( $cc[ 3 ] ) && strlen( $cc[ 3 ] ) ) //オプションの指定がある場合
						{ $option = $cc[ 3 ]; }

					if( isset( $cc[ 4 ] ) && strlen( $cc[ 4 ] ) ) //削除チェックの文言指定がある場合
						{ $deleteText = $cc[ 4 ]; }

					$ret .= '<input name="' . $col . '" type="file" ' . $option . '>' . "\n";

					if( $enablePost ) //ファイルの情報がある場合
					{
						$ret .= '<input name="' . $col . '_filetmp" type="hidden" value="' . $_POST[ $col ] . '" />' . "\n";
						$ret .= '<label><input type="checkbox" name="' . $col . '_DELETE" value="true" />' . $deleteText . '</label>';

					}
					else if( $enableFileTemp ) //引き継ぎ情報がある場合
					{
						$ret .= '<input name="' . $col . '_filetmp" type="hidden" value="' . $_POST[ $col . '_filetmp' ] . '" />'. "\n";

						if( $enableDelete ) //削除チェックの引き継ぎがある場合
							{ $ret .= '<label><input type="checkbox" name="' . $col . '_DELETE" value="true" checked="checked" />' . $deleteText . '</label>'; }
						else //削除チェックの引き継ぎがない場合
							{ $ret .= '<label><input type="checkbox" name="' . $col . '_DELETE" value="true" />' . $deleteText . '</label>'; }
					}

				case 'hidden':
						// 不可視入力(type=hiddenのinputタグ)を出力
					$option	= "";
					if( isset($cc[4]) ) { $option = $cc[4]; }
					if( isset($cc[5]) ) { $num = $cc[5]; }else{$num="";}

					if( isset($initial ) ){
						if( is_array($initial) ){
							foreach( $initial as $val ){
								$ret .= '<input name="'. $col .'['.$num.']" type="hidden" value="'. h($val) .'" '. $option .'/>'. "\n";
							}
						}
						else{
							$ret .= '<input name="'. $col .'" type="hidden" value="'. h($initial) .'" '. $option .' />'. "\n";
						}
					}
					else {
						$value = '';
						if( isset($cc[3]) ){ $value = h($cc[3]); }
						$ret .= '<input name="'. $col .'" type="hidden" value="'. $value .'" '. $option .'/>'. "\n";
					}
					break;
				case 'date':
					$option = "";
					if( isset($cc[4]) ) { $option = $cc[4]; }else{ $option="";}
					$y_key = $col.'_year';
					$m_key = $col.'_month';
					$d_key = $col.'_day';

					if( isset($cc[3]) && strlen($cc[3]) ){
						list($init_y, $init_m, $init_d) = explode( '-',$cc[3]);
					}else if( isset($initial ) && strlen($initial) ){
						$init_y = (int)substr($initial,0,4);
						$init_m = (int)substr($initial,5,2);
						$init_d = (int)substr($initial,8);
					}else{
						$init_y = $init_m = $init_d = "";
					}

					$ret = ccProc::controller($gm, $rec, array('form','text',$y_key,'4','4',$init_y,$option) ).$gm->dateFormat['y'];
					$ret .= ccProc::controller($gm, $rec, array('code','num_option',$m_key,'12',$init_m,'1','未選択',$option ) ).$gm->dateFormat['m'];
					$ret .= ccProc::controller($gm, $rec, array('code','num_option',$d_key,'31',$init_d,'1','未選択',$option ) ).$gm->dateFormat['d'];
					break;

				case 'time':

					$formats = explode( '/' , $cc[ 3 ] );
					$suffixs = explode( '/' , $cc[ 4 ] );

					if( isset( $_POST[ $cc[ 2 ] ] ) && 0 != $_POST[ $cc[ 2 ] ] )
						{ $inits = explode( '/' , date( $cc[ 3 ] , $_POST[ $cc[ 2 ] ] ) ); }
					else
						{ $inits = explode( '/' , $cc[ 5 ] ); }

					if( isset( $cc[ 6 ] ) )
						{ $option = $cc[ 6 ]; }
					else
						{ $option = ''; }

					$ret = '';

					for( $i = 0 ; count( $formats ) > $i ; ++$i )
					{
						if( isset( $inits[ $i ] ) )
							{ $value = $inits[ $i ]; }
						else
							{ $value = null; }

						if( 'y' == strtolower( $formats[ $i ] ) )
						{
							$originValue = $_POST[ $cc[ 2 ] . '_year' ];

							if( 0 == $originValue ) //年の値が0の場合
								{ unset( $_POST[ $cc[ 2 ] . '_year' ] ); }

							$ret .= ccProc::controller( $gm , $rec , array( 'form' , 'text' , $cc[ 2 ] . '_year' , '4' , '4' , $value , $option ) ) . $suffixs[ $i ];

							$_POST[ $cc[ 2 ] . '_year' ] = $originValue;
						}
						else if( 'm' == strtolower( $formats[ $i ] ) )
							{ $ret .= ccProc::controller( $gm , $rec , array( 'code' , 'num_option' , $cc[ 2 ] . '_month' , '12' , $value , '1' , '--' , $option ) ) . $suffixs[ $i ]; }
						else if( 'd' == strtolower( $formats[ $i ] ) )
							{ $ret .= ccProc::controller( $gm , $rec , array( 'code' , 'num_option' , $cc[ 2 ] . '_day' , '31' , $value , '1' , '--' , $option ) ) . $suffixs[ $i ]; }
						else if( 'h' == strtolower( $formats[ $i ] ) )
							{ $ret .= ccProc::controller( $gm , $rec , array( 'code' , 'num_option' , $cc[ 2 ] . '_hour' , '23' , $value , '0' , '--' , $option ) ) . $suffixs[ $i ]; }
						else if( 'i' == strtolower( $formats[ $i ] ) )
							{ $ret .= ccProc::controller( $gm , $rec , array( 'code' , 'num_option' , $cc[ 2 ] . '_minute' , '59' , $value , '0' , '--' , $option ) ) . $suffixs[ $i ]; }
						else if( 's' == strtolower( $formats[ $i ] ) )
							{ $ret .= ccProc::controller( $gm , $rec , array( 'code' , 'num_option' , $cc[ 2 ] . '_sec' , '59' , $value , '0' , '--' , $option ) ) . $suffixs[ $i ]; }
					}
					break;
				//ここからhtml5でサポートされたコマンド
				case 'tel':
					// telのinputタグを出力。
					$option = "";
					if( isset($cc[6]) ) { $option = $cc[6]; }

					if( isset($cc[3]) && strlen($cc[3]) ) { $option .= 'size="'. $cc[3] .'" '; }
					if( isset($cc[4]) && strlen($cc[4]) ) { $option .= 'maxlength="'. $cc[4] .'" '; }

					$value = "";
					if( isset($cc[5]) && strlen($cc[5]) ) {	$value = h($cc[5]); }
					if( isset($initial) ) { $value = h($initial); }

					$ret .= '<input type="tel" name="'. $col .'" value="'.$value.'" '.$option .'/>'. "\n";
					//placeholder
					break;
				case 'url':
					// urlのinputタグを出力。
					$option = "";
					if( isset($cc[6]) ) { $option = $cc[6]; }

					if( isset($cc[3]) && strlen($cc[3]) ) { $option .= 'size="'. $cc[3] .'" '; }
					if( isset($cc[4]) && strlen($cc[4]) ) { $option .= 'maxlength="'. $cc[4] .'" '; }

					$value = "";
					if( isset($cc[5]) && strlen($cc[5]) ) {	$value = h($cc[5]); }
					if( isset($initial) ) { $value = h($initial); }

					$ret .= '<input type="url" name="'. $col .'" value="'.$value.'" '.$option .' autocapitalize="off"/>'. "\n";
					//placeholder
					break;
				case 'number':
					// numberのinputタグを出力。
					$option = "";
					if( isset($cc[6]) ) { $option = $cc[6]; }

					//max min
					if( isset($cc[3]) && strlen($cc[3]) ) { $option .= 'max="'. $cc[3] .'" '; }
					if( isset($cc[4]) && strlen($cc[4]) ) { $option .= 'min="'. $cc[4] .'" '; }

					$value = "";
					if( isset($cc[5]) && strlen($cc[5]) ) {	$value = h($cc[5]); }
					if( isset($initial) ) { $value = h($initial); }

					$ret .= '<input type="number" name="'. $col .'" value="'.$value.'" '.$option .'/>'. "\n";
					break;
				case 'email':
					// emailのinputタグを出力。
					$option = "";
					if( isset($cc[6]) ) { $option = $cc[6]; }

					if( isset($cc[3]) && strlen($cc[3]) ) { $option .= 'size="'. $cc[3] .'" '; }
					if( isset($cc[4]) && strlen($cc[4]) ) { $option .= 'maxlength="'. $cc[4] .'" '; }

					$value = "";
					if( isset($cc[5]) && strlen($cc[5]) ) {	$value = h($cc[5]); }
					if( isset($initial) ) { $value = h($initial); }

					$ret .= '<input type="email" name="'. $col .'" value="'.$value.'" '.$option .' autocapitalize="off" />'. "\n";

					//multiple,placeholder
					break;
			}

			return $ret;
		}

		/**
			@brief テーブルを元にフォームを生成する。
			@details
				最短 : <!--# build テーブル名 フォーム種別 #-->
				フル : <!--# build テーブル名.値カラム/表示カラム to フォーム種別.フォーム名 search 検索クエリ first 初期値 join 結合文字 null 未選択値 #-->
			@remarks
				フル構文のtoは省略可能
		*/
		function build( &$iGM , $iRec , $iArgs ) //
		{
			global $TABLE_NAME;

			array_shift( $iArgs );

			$tableName   = array_shift( $iArgs );
			$buildType   = array_shift( $iArgs );
			$nullName    = '';
			$firstValue  = '';
			$searchQuery = '';
			$joinParam   = '';

			if( '->' == $buildType || 'to' == $buildType ) //補助構文の場合
				{ $buildType = array_shift( $iArgs ); }

			List( $tableName , $useColumn )    = explode( '.' , $tableName );
			List( $valueColumn , $nameColumn ) = explode( '/' , $useColumn );
			List( $buildType , $formName )     = explode( '.' , $buildType );

			if( !$nameColumn ) //カラムの指定がない場合
				{ $nameColumn = 'name'; }

			if( !$valueColumn ) //カラムの指定がない場合
				{ $valueColumn = 'id'; }

			if( !$formName ) //フォーム名の指定がない場合
				{ $formName = $tableName; }

			while( count( $iArgs ) ) //引数がある間繰り返し
			{
				$paramName = array_shift( $iArgs );

				switch( $paramName ) //パラメータ名で分岐
				{
					case 'search' : //検索クエリ
					{
						$searchQuery = array_shift( $iArgs );

						break;
					}

					case 'first' : //初期選択値
					{
						$firstValue = array_shift( $iArgs );

						break;
					}

					case 'join' : //結合文字列
					{
						$joinParam = array_shift( $iArgs );

						break;
					}

					case 'null' : //未選択値
					{
						$nullName = array_shift( $iArgs );

						break;
					}

					default : //その他
						{ throw new LogicException( 'CC構文エラー:引数' . $paramName . 'は実装されていません。' ); }
				}
			}

			if( !in_array( $tableName , $TABLE_NAME ) ) //テーブル名の指定が間違っている場合
				{ throw new LogicException( 'CC構文エラー:' . $tableName . 'テーブルは存在しません。' ); }

			$db = GMList::getDB( $tableName );

			if( !in_array( $valueColumn , $db->colName ) ) //検索カラムの指定が間違っている場合
				{ throw new LogicException( 'CC構文エラー:' . $tableName . 'に' . $valueColumn . 'カラムは存在しません。' ); }

			if( !in_array( $nameColumn , $db->colName ) ) //検索カラムの指定が間違っている場合
				{ throw new LogicException( 'CC構文エラー:' . $tableName . 'に' . $nameColumn . 'カラムは存在しません。' ); }

			$table = $db->getTable();

			if( $searchQuery ) //検索クエリの指定がある場合
			{
				parse_str( $searchQuery , $query );

				$query[ 'type' ] = $tableName;
				$table           = SystemUtil::getSearchResult( $query );
			}

			$row    = $db->getRow( $table );
			$values = Array();
			$names  = Array();

			if( $nullName ) //未選択値の指定がある場合
			{
				$values[] = '';
				$names[]  = $nullName;
			}

			for( $i = 0 ; $row > $i ; ++$i ) //全ての行を処理
			{
				$rec      = $db->getRecord( $table , $i );
				$values[] = $db->getData( $rec , $valueColumn );
				$names[]  = $db->getData( $rec , $nameColumn );
			}

			$formName   = str_replace( Array( '!CODE001;' , ' ' ) , '!CODE101;' , $formName );
			$firstValue = str_replace( Array( '!CODE001;' , ' ' ) , '!CODE101;' , $firstValue );
			$joinParam  = str_replace( Array( '!CODE001;' , ' ' ) , '!CODE101;' , $joinParam );

			foreach( $names as &$ref ) //全ての値を処理
				{ $ref = str_replace( Array( '!CODE001;' , ' ' ) , '!CODE101;' , $ref ); }

			foreach( $values as &$ref ) //全ての値を処理
				{ $ref = str_replace( Array( '!CODE001;' , ' ' ) , '!CODE101;' , $ref ); }

			$result = '';

			switch( $buildType ) //出力方法で分岐
			{
				case 'select' : //単一選択プルダウン
				{
					$result .= $iGM->getCCResult( $iRec , implode( ' ' , Array( '<!--#' , 'form' , 'option' , $formName , $firstValue , implode( '/' , $values ) , implode( '/' , $names ) , ' #-->' ) ) );

					break;
				}

				case 'selects'  : //複数選択プルダウン
				case 'multiple' : //複数選択プルダウン
				{
					$result .= $iGM->getCCResult( $iRec , implode( ' ' , Array( '<!--#' , 'form' , 'multiple' , $formName , $firstValue , implode( '/' , $values ) , implode( '/' , $names ) , ' #-->' ) ) );

					break;
				}

				case 'radio' : //ラジオボタン
				{
					$result .= $iGM->getCCResult( $iRec , implode( ' ' , Array( '<!--#' , 'form' , 'radio' , $formName , $firstValue , $joinParam , implode( '/' , $values ) , implode( '/' , $names ) , ' #-->' ) ) );

					break;
				}

				case 'check'    : //チェックボックス
				case 'checkbox' : //チェックボックス
				{
					$result .= $iGM->getCCResult( $iRec , implode( ' ' , Array( '<!--#' , 'form' , 'checkbox' , $formName , $firstValue , $joinParam , implode( '/' , $values ) , implode( '/' , $names ) , ' #-->' ) ) );

					break;
				}
			}

			return $result;
		}

		/**
			@brief テーブルの値を繰り返し出力する
			@details
				最短 : <!--# repeat テーブル名 パーツ名 #-->
				フル : <!--# repeat テーブル名 to パーツ名 search 検索クエリ row 最大数 #-->
			@remarks
				フル構文のtoは省略可能
		*/
		function repeat( &$iGM , $iRec , $iArgs ) //
		{
			global $TABLE_NAME;

			array_shift( $iArgs );
			$result = '';

			$tableName = array_shift( $iArgs );
			$partsName = array_shift( $iArgs );

			if( '->' == $partsName || 'to' == $partsName ) //補助構文の場合
				{ $partsName = array_shift( $iArgs ); }

			if( preg_match( '/^(\d+)~(\d+)$/' , $tableName , $matches ) ) //数値指定の場合
			{
				$begin  = ( int )( $matches[ 1 ] );
				$end    = ( int )( $matches[ 2 ] );
				$values = Array();

				if( $begin < $end )
				{
					for( $i = $begin ; $end >= $i ; ++$i )
						{ $values[] = $i; }
				}
				else
				{
					for( $i = $begin ; $end <= $i ; --$i )
						{ $values[] = $i; }
				}

				return self::repeatString( $iGM , $iRec , Array( 'repeatString' , implode( '/' , $values ) , $partsName ) );
			}

			if( !in_array( $tableName , $TABLE_NAME ) ) //テーブル名の指定が間違っている場合
				{ throw new LogicException( 'CC構文エラー:' . $tableName . 'テーブルは存在しません。' ); }

			while( count( $iArgs ) ) //引数がある間繰り返し
			{
				$paramName = array_shift( $iArgs );

				switch( $paramName ) //パラメータ名で分岐
				{
					case 'search' : //検索クエリ
					{
						$searchQuery = array_shift( $iArgs );

						break;
					}

					case 'row' : //行数
					{
						$maxRow = array_shift( $iArgs );

						break;
					}

					default : //その他
						{ throw new LogicException( 'CC構文エラー:引数' . $paramName . 'は実装されていません。' ); }
				}
			}

			$db    = GMList::getDB( $tableName );
			$table = $db->getTable();

			if( $searchQuery ) //検索クエリの指定がある場合
			{
				parse_str( $searchQuery , $query );

				$query[ 'type' ] = $tableName;
				$table           = SystemUtil::getSearchResult( $query );
			}

			$originRow = $db->getRow( $table );

			if( $maxRow ) //最大行の指定がある場合
				{ $table = $db->limitOffset( $table , 0 , $maxRow ); }
			else //最大行の指定がない場合
				{ $maxRow = $originRow; }

			$row = $db->getRow( $table );

			if( !$row ) //レコードがない場合
			{
				$result .= $iGM->getCCResult( $iRec , '<!--# adapt ' . $partsName . '_failed #-->' );

				return $result;
			}

			$result = '';

			$result .= $iGM->getCCResult( $iRec , '<!--# adapt ' . $partsName . '_head #-->' );

			if( $originRow > $maxRow ) //表示数より検索結果が多い場合
				{ $result .= $iGM->getCCResult( $iRec , '<!--# adapt ' . $partsName . '_head_over #-->' ); }

			for( $i = 0 ; $maxRow > $i ; ++$i ) //出力数繰り返し
			{
				$iGM->setVariable( 'num' , $i + 1 );

				if( $row <= $i ) //レコードが取れない場合
					{ $result .= $iGM->getCCResult( null , '<!--# adapt ' . $partsName . '_empty #-->' ); }
				else //レコードが取れる場合
				{
					$rec = $db->getRecord( $table , $i );

					$result .= $iGM->getCCResult( $rec , '<!--# adapt ' . $partsName . ' #-->' );
				}
			}

			if( $originRow > $maxRow ) //表示数より検索結果が多い場合
				{ $result .= $iGM->getCCResult( $iRec , '<!--# adapt ' . $partsName . '_foot_over #-->' ); }

			$result .= $iGM->getCCResult( $iRec , '<!--# adapt ' . $partsName . '_foot #-->' );

			return $result;
		}

		/**
			@brief テーブルの値を繰り返し出力する
			@details
				最短 : <!--# repeatString 文字列 パーツ名 #-->
				フル : <!--# repeatString 文字列 to パーツ名 #-->
			@remarks
				フル構文のtoは省略可能
		*/
		function repeatString( &$iGM , $iRec , $iArgs ) //
		{
			array_shift( $iArgs );

			$elements  = explode( '/' , array_shift( $iArgs ) );
			$partsName = array_shift( $iArgs );
			$i         = 0;

			if( '->' == $partsName || 'to' == $partsName ) //補助構文の場合
				{ $partsName = array_shift( $iArgs ); }

			$result = '';

			$result .= $iGM->getCCResult( $iRec , '<!--# adapt ' . $partsName . '_head #-->' );

			foreach( $elements as $element ) //出力数繰り返し
			{
				$iGM->setVariable( 'num' , ++$i );
				$iGM->setVariable( 'value' , $element );

				$result .= $iGM->getCCResult( $iRec , '<!--# adapt ' . $partsName . ' #-->' );
			}

			$result .= $iGM->getCCResult( $iRec , '<!--# adapt ' . $partsName . '_foot #-->' );

			return $result;
		}

		// 初期値がエスケープされた要素と一致しないため初期値をエスケープデータにする
		function initEscape( $str )
		{
			if( !is_array($str) )
			{
				$str = str_replace( " ", "!CODE001;", $str );
			}
			else
			{
				$count = count($str);
				for( $i=0; $i<$count; $i++ )
				{
					$str[$i] = str_replace( " ", "!CODE001;", $str[$i] );
				}
			}

			return $str;
		}

		// テンプレートの表示に使用されるGUIManagerのインスタンスに設定した値を出力出来る。
		function variable(&$gm, $rec, $cc)
		{
			$ret = "";
			if(  is_null( $gm->variable[$cc[1]] )  ) { throw new InternalErrorException( 'CommandComment Null Pointer Error -> variable : '. $cc[1] ); }
			$ret .= $gm->variable[$cc[1]];

			return $ret;
		}
		// テンプレートの表示に使用されるGUIManagerのインスタンスに設定した値を出力出来る。
		// 未設定でもエラー出力がされない。
		function safeVariable(&$gm, $rec, $cc)
		{
			$ret = "";
			if( isset($gm->variable[ $cc[1] ]) && ! is_null( $gm->variable[ $cc[1] ] )  )	{ $ret	 .= $gm->variable[ $cc[1] ]; }

			return $ret;
		}

		/**
			@brief   テンプレート上で変数の読み書きを行う。
			@details 指定した名前の変数に値を読み書きします。値はccProcのstatic変数$MemoCCValuesに格納されます。
				書き込み : <!--# memo write 値 to 変数名 #-->
				読み込み : <!--# memo read 変数名 #-->
		*/
		function memo( &$gm , $rec , $cc ) //
		{
			array_shift( $cc );

			if( !count( $cc ) ) //引数がない場合
				{ throw new LogicException( 'CC構文エラー:引数がありません' ); }

			$procMode = array_shift( $cc );

			switch( $procMode ) //要求された処理モードで分岐
			{
				case 'write' : //変数への書き込みの場合
				{
					$varName     = array_pop( $cc );
					$conjunction = array_pop( $cc );
					$writeValue  = implode( ' ' , $cc );

					if( 'to' != $conjunction ) //文法が正しくない場合
						{ throw new LogicException( 'CC構文エラー:引数' . $conjunction . 'は実装されていません' ); }

					self::$MemoCCValues[ $varName ] = $writeValue;

					break;
				}

				case 'read' : //変数からの読み込みの場合
				{
					$varName = array_shift( $cc );

					if( isset( self::$MemoCCValues[ $varName ] ) ) //変数が存在する場合
						{ return self::$MemoCCValues[ $varName ]; }

					break;
				}

				default : //その他の場合
					{ throw new LogicException( 'CC構文エラー:引数' . $procMode . 'は実装されていません' ); }
			}
		}

		// テンプレートを表示しようとしているページへのリクエストで渡されたGETパラメータを表示出来る。
		function get(&$gm, $rec, $cc)
		{
			array_shift( $cc );
			List( $name , $index ) = $cc;

			$ret = "";

				if( is_array( $_GET[ $name ] ) ) //POSTが配列の場合
				{
					if( !isset( $index ) )
					{ //indexが指定されていない場合
						$ret .= implode( '/' , $_GET[ $name ] );
					}
					else
					{ //indexが指定されている場合
						$ret .= $_GET[ $name ][ $index ];
				}
				}
				else
				{ //POSTがスカラの場合
					$ret .= $_GET[ $name ];
				}

			return h($ret);
		}


		// テンプレートを表示しようとしているページへのリクエストで渡されたPOSTパラメータを表示出来る。
		function post( &$gm , $rec , $cc )
		{
			array_shift( $cc );
			List( $name , $index ) = $cc;

			$ret = "";

			if( is_array( $_POST[ $name ] ) ) //POSTが配列の場合
			{
				if( !isset( $index ) )
				{ //indexが指定されていない場合
					$ret .= implode( '/' , $_POST[ $name ] );
				}
				else
				{ //indexが指定されている場合
					$ret .= $_POST[ $name ][ $index ];
			}
			}
			else
			{ //POSTがスカラの場合
				$ret .= $_POST[ $name ];
			}

			return h($ret);
		}

		// $_SESSIONの値を出力
		function session(&$gm, $rec, $cc)
		{
			if( is_array( $_SESSION[$cc[1]] ) )
			{
				if( !isset($cc[2]) ) { throw new InternalErrorException( 'CommandComment Null Pointer Error -> session array index' ); }
				$ret .= $_SESSION[$cc[1]][$cc[2]];
			}
			else	{ $ret .= $_SESSION[$cc[1]]; }

			return $ret;
		}


		// $_REQUESTの値を出力
		function request(&$gm, $rec, $cc)
		{
			$ret = '';
			if( is_array( $_REQUEST[$cc[1]] ) )
			{
				if( !isset($cc[2]) ) { throw new InternalErrorException( 'CommandComment Null Pointer Error -> request array index' ); }
				$ret .= $_REQUEST[$cc[1]][$cc[2]];
			}
			else	{ $ret .= $_REQUEST[$cc[1]]; }

			return h($ret);
		}

		// valueでtimestampを表示する場合に使用するformatを指定出来る。
		function setTimeFormat(&$gm, $rec, $cc)
		{
			$ret = "";
			$gm->setTimeFormat(  str_replace(  Array( "!CODE000;","!CODE001;"), Array("/"," ") , $cc[1]) );

			return $ret;
		}

		function setTimeFormatOnce(&$gm, $rec, $cc)
		{
			$ret = "";
			$gm->setTimeFormatOnce(  str_replace(  Array( "!CODE000;","!CODE001;"), Array("/"," ") , $cc[1]) );

			return $ret;
		}

		// ユーザー情報を出力
		function login(&$gm, $rec, $cc)
		{
			global $NOT_LOGIN_USER_TYPE;
			global $loginUserType;
			global $loginUserRec;

			$ret = "";
			switch($cc[1])
			{
			case 'type':
				$ret = $loginUserType;
				break;
			default:
				if( $loginUserType != $NOT_LOGIN_USER_TYPE )
				{
					$tgm = GMList::getGM($loginUserType);
					$ret = ccProc::value( $tgm, $loginUserRec, $cc );
				}
				break;
			}

			return $ret;
		}

		// Command.phpで定義されているコメントコマンドを呼び出す事が出来る。
		function code(&$gm, $rec, $cc)
		{
			$ret  = "";
			$args = array_slice($cc,2);
			$e = new Command();
			$e->{$cc[1]}( $gm, $rec, $args );
			$ret .= $e->getBuffer();

			return $ret;
		}

		// Extension.phpで定義されているコメントコマンドを呼び出す事が出来る。
		function ecode(&$gm, $rec, $cc)
		{
			$ret  = "";
			$args = array_slice($cc,2);
			$e = new Extension();
			$e->{$cc[1]}( $gm, $rec, $args );
			$ret .= $e->getBuffer();

			return $ret;
		}


		// System.php内のSystemクラスで定義されているコメントコマンドを呼び出す事が出来る。
		function syscode(&$gm, $rec, $cc)
		{
			$ret  = "";
			$args = array_slice($cc,2);

			$sys  = SystemUtil::getSystem( isset($_GET["type"])?$_GET["type"]:null );

			$sys->{$cc[1]}( $gm, $rec, $args );
			$ret .= $sys->getBuffer();

			return $ret;
		}

		// ./module/以下に設置され./module/module.phpによりincludeされたモジュールファイル内で定義されたモジュールクラス内のメソッドを呼び出す事が可能。
		function mod(&$gm, $rec, $cc)
		{
			$ret  = "";
			$args = array_slice($cc,3);

			$class_name = 'mod_'.$cc[1];
			if( !class_exists( $class_name ) ){
				return $ret;
			}

			$sys = new $class_name();

			$sys->{$cc[2]}( $gm, $rec, $args );
			$ret .= $sys->getBuffer();

			return $ret;
		}

		// ./custom/view以下に設置されてincludeされたモジュールファイル内で定義されたモジュールクラス内のメソッドを呼び出す事が可能。
		function view(&$gm, $rec, $cc)
		{
			global $view_path;

			$ret  = "";
			$args = array_slice($cc,3);

			$class_name = $cc[1].'View';

			if( !class_exists( $class_name ) ){
				if( file_exists( $view_path.$class_name.'.php') )
				{
					include_once $view_path.$class_name.'.php';
					if ( !class_exists( $class_name ) ) {
						global $ALL_DEBUG_FLAG;
						if( $ALL_DEBUG_FLAG ){ d( '['.$cc[1].'View] not found.' ,'view');}
						return $ret;
					}
				}else{
					global $ALL_DEBUG_FLAG;
					if( $ALL_DEBUG_FLAG ){ d( '['.$cc[1].'View] not found.' ,'view');}
					return $ret;
				}
			}

			$sys = new $class_name();

			$sys->{$cc[2]}( $gm, $rec, $args );
			$ret .= $sys->getBuffer();

			return $ret;
		}

		// 引数に与えられた文字を計算式として解釈し、計算結果を返す。
		function calc(&$gm, $rec, $cc)
		{
			$ret  = "";
			$calc = join('',array_slice($cc,1));
			if( ! SystemUtil::is_expression($calc ) ){
				return $ret;
			}
			eval( '$ret = '.$calc.';' );

			return $ret;
		}

		// このコマンドは他のコマンドの前に付ける形で利用する事により、戻り値に含まれる半角スペースをエスケープした結果を返す。
		function escp(&$gm, $rec, $cc)
		{
			$ret = "";
			$cc  = array_slice($cc,1);
			$ret = str_replace( Array( '!CODE001;', ' ') , '!CODE101;', ccProc::controller($gm, $rec, $cc) );
			///$ret = str_replace( '!CODE001;' , '!CODE101;', ccProc::controller($gm, $rec, $cc) );
			return $ret;
		}

		function ent(&$gm, $rec, $cc)
		{
			$ret = "";
			$cc  = array_slice($cc,1);
			$ret = h( ccProc::controller($gm, $rec, $cc) );
			return $ret;
		}

		// このコマンドは他のコマンドの前に付ける形で利用する事により、戻り値をint型にcastして返す。
		function int(&$gm, $rec, $cc)
		{
			$ret = "";
			$cc  = array_slice($cc,1);
			$ret = (int)ccProc::controller($gm, $rec, $cc);

			return $ret;
		}
		// このコマンドは他のコマンドの前に付ける形で利用する事により、戻り値をint型にcastして返す。
		function bool(&$gm, $rec, $cc)
		{
			$ret			 = "";
			$cc = array_slice($cc,1);
			$ret = SystemUtil::convertBool(ccProc::controller($gm, $rec, $cc)) ? 'TRUE':'FALSE';

			return $ret;
		}
		// このコマンドは他のコマンドの前に付ける形で利用する事により、戻り値をurlencodeして返す。
		function urlenc(&$gm, $rec, $cc)
		{
			$ret			 = "";
			$cc = array_slice($cc,1);

			$ret = ccProc::controller($gm, $rec, $cc);

			if( FALSE !== strpos( $ret , '!CODE000;' ) || FALSE !== strpos( $ret , '!CODE001;' ) || FALSE !== strpos( $ret , '!CODE002;' ) )
			{
				$ret = urlencode(str_replace( array("!CODE000;","!CODE001;","!CODE002;"), array("/"," ","\\") , $ret ));
				$ret = str_replace( array("/"," ","\\"), array("!CODE000;","!CODE001;","!CODE002;"), $ret );
			}
			else
				{ $ret = urlencode( $ret ); }

			return $ret;
		}

		// このコマンドは他のコマンドの前に付ける形で利用する事により、戻り値の第1引数に指定された文字を第2引数に指定された文字に置換して返す。
		function rep(&$gm, $rec, $cc)
		{
			$ret			 = "";
			$search = $cc[1];
			$replace = $cc[2];
			$cc = array_slice($cc,3);
			$ret = str_replace( $search, $replace, ccProc::controller($gm, $rec, $cc));

			return $ret;
		}

		// このコマンドは他のコマンドの前に付ける形で利用する事により、戻り値の数値にカンマを付加する。
		function comma( &$gm , $rec , $cc ) //
		{
			$ret = '';
			$cc  = array_slice( $cc , 1 );
			$ret = ccProc::controller( $gm , $rec , $cc );
			$ret = number_format( floor( $ret ) ) . strstr( $ret , '.' );

			return $ret;
		}

		/**
		 * substituteコマンド。
		 * このコマンドは他のコマンドの前に付ける形で利用する事により、戻り値が空の場合に第一引数の値を出力する。
		 *
		 */
		function sub(&$gm, $rec, $cc)
		{

			$ret			 = "";
			$cc2 = array_slice($cc,2);
			$ret = ccProc::controller($gm, $rec, $cc2);

			if( !strlen($ret) ){
				$ret = $cc[1];
			}

			return $ret;
		}

		// このコマンドは他のコマンドの前に付ける形で利用する事により、戻り値のタグを除去する。
		function striptag( &$gm , $rec , $cc ) //
		{
			$ret = '';
			$cc = array_slice( $cc , 1 );
			$ret = ccProc::controller( $gm , $rec , $cc );
			$ret = strip_tags( $ret );

			return $ret;
		}

		// このコマンドは他のコマンドの前に付ける形で利用する事により、出力結果を簡易的にキャッシュする。
		function cache( &$gm , $rec , $cc ) //
		{
			$ret  = '';
			$time = $cc[ 1 ];
			$cc   = array_slice( $cc , 2 );
			$file = 'file/cc_cache/' . md5( implode( $cc , ' ' ) ) . 'cc';

			if( !is_file( $file ) || time() - $time > filemtime( $file ) )
			{
				$ret = ccProc::controller( $gm , $rec , $cc );

				file_put_contents( $file , $ret );
			}
			else
				{ $ret = file_get_contents( $file ); }

			return $ret;
		}

		/**
			@brief     CCの実行を非同期指定する。
			@attention ターゲットレコード等の設定の問題で、同期実行とは異なる結果が返る可能性があります。
		*/
		function async( &$gm , $rec , $cc ) //
		{
			global $controllerName;
			TemplateCache::$NoCache = true;

			if( 'preview' == strtolower( $controllerName ) ) //プレビュー画面の場合
			{
				array_shift( $cc );

				return ccProc::controller( $gm , $rec , $cc );
			}

			array_shift( $cc );

			$asyncToken = md5( rand() );

			$_SESSION[ 'async_cc_' . $asyncToken ] = '<!--# ' . implode( ' ' , $cc ) . ' #-->';

			$ret  = '<script data-async-cc-id="' . $asyncToken . '">';
			$ret .= '$( function(){ callASyncCC( "' . $asyncToken . '" ); } );';
			$ret .= '</script>';

			return $ret;
		}

		// $cc の内容を連結して出力
		function join(&$gm, $rec, $cc)
		{
			$ret = "";
			$cc  = array_slice($cc,1);
			$ret = join( '' , $cc );

			return $ret;
		}

		// 引数に与えられた文字を変数として解釈し、中身を返す。
		function val(&$gm, $rec, $cc)
		{
			$ret = "";

			if( ! preg_match( '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $cc[1] ) ){
				return $ret;
			}

			eval( 'global $'.$cc[1].'; $ret = $'.$cc[1].';' );

			if(is_bool($ret)){
				if( $ret )	 { $ret	 = 'TRUE'; }
				else		 { $ret	 = 'FALSE'; }
			}

			return $ret;
		}

		// このコマンドは他のtemplateをtemplate内に展開する事が出来る。
		// ただし、templateテーブルに「INCLUDE_DESIGN」ラベルを設定されたものに限る。
		function drawDesign(&$gm, $rec, $cc)
		{
			global $loginUserType;
			global $NOT_LOGIN_USER_TYPE;
			global $loginUserRank;

			$file = Template::getTemplate( $loginUserType , $loginUserRank , $cc[1] , 'INCLUDE_DESIGN' );

			$partkey = null;
			if( isset( $cc[2] ) ){ $partkey = $cc[2]; }

			if( ! strlen($file) ){
				$ret = "<br/><br/><br/>!include error! -> ".$cc[1]."<br/><br/><br/>";
			}else if( is_null($gm) ){
				if( $loginUserType == $NOT_LOGIN_USER_TYPE ){
					$ret = SystemUtil::getGMforType('system')->getString( $file , $rec , $partkey );
				}else{
					$ret = SystemUtil::getGMforType($loginUserType)->getString( $file , $rec , $partkey );
				}

			}else{
				$ret = $gm->getString( $file , $rec , $partkey );
			}

			return $ret;
		}

		function drawAdapt(&$gm, $rec, $cc)
		{
			global $loginUserType;
			global $NOT_LOGIN_USER_TYPE;
			global $loginUserRank;

			$file = $gm->getCurrentTemplate();

			$partkey = null;
			if( isset( $cc[1] ) ){ $partkey = $cc[1]; }

			if( ! strlen($file) ){
				$ret = "<br/><br/><br/>!adapt error! -> ".$cc[1]."<br/><br/><br/>";
			}else if( ! strlen($file) ){
				$ret = "<br/><br/><br/>!adapt part error! -> ".$cc[1]."<br/><br/><br/>";
			}else if( is_null($gm) ){
				if( $loginUserType == $NOT_LOGIN_USER_TYPE ){
					$ret = SystemUtil::getGMforType('system')->getString( $file , $rec , $partkey );
				}else{
					$ret = SystemUtil::getGMforType($loginUserType)->getString( $file , $rec , $partkey );
				}

			}else{
				$ret = $gm->getString( $file , $rec , $partkey );
			}

			return $ret;
		}

		//内部変換テーブルに従って絵文字を出力する
		function emoji(&$gm, $rec, $cc){
			global $EMOJI_CHARSET_MAP;
			global $terminal_type;

			$ret = '';

			if( !is_array($EMOJI_CHARSET_MAP) || !is_numeric($cc[1])){ return ""; }

			eval( '$ret = '. $EMOJI_CHARSET_MAP[ $cc[1] ].";" );
			return $ret;
		}

		/**
			@brief     次に呼び出されるコマンドコメントのために、挿入パラメータを設定する。
			@exception InvalidCCArgumentException 不正なパラメータを指定した場合。
			@details   パラメータは次の順で指定します。
				@li 0 挿入パラメータの名前。
				@li 1 挿入パラメータの値。
				@li 2 挿入パラメータを使用するコマンドコメント名。省略した場合は全てのコマンドコメントが参照可能です。
				@li 3 挿入パラメータの寿命。once/allのいずれかを指定します。onceパラメータは一度でも参照されると初期化されます。省略した場合はonceとなります。
			@param[in] $iGM_  GUIManagerオブジェクト。
			@param[in] $iRec_ レコードデータ。
			@param[in] $iCC_  コマンドコメントパラメータ。
			@attension コマンドコメントは個別にweaveに対応する必要があります。\n
					挿入パラメータの取得にはWeaveクラスを使用してください。
		*/
		function weave( &$iGM_ , $iRec_ , $iCC_ )
		{
			List( $ccName , $paramName , $paramValue , $targetName , $paramLife ) = $iCC_;

			if( !$paramName ) //挿入パラメータ名が指定されていない場合
				{ throw new InvalidCCArgumentException( '引数 $paramName は無効です' ); }

			if( !$targetName ) //対象コマンドコメント名が設定されていない場合
				{ $targetName = '*'; }

			if( $paramLife ) //挿入パラメータの寿命が設定されている場合
			{
				switch( $paramLife ) //設定値で分岐
				{
					case 'once' : //一度きり
					case 'all'  : //永久
						{ break; }

					default : //値の候補に一致しない場合
						{ throw new InvalidCCArgumentException( '引数 $paramLife は無効です[' . $paramLife . ']' ); }
				}
			}
			else //挿入パラメータの寿命が設定されていない場合
				{ $paramLife = 'once'; }

			Weave::Push( $paramName , $paramValue , $targetName , $paramLife );
		}

		/**
			@brief     挿入パラメータを削除する。
			@exception InvalidCCArgumentException 不正なパラメータを指定した場合。
			@details   パラメータは次の順で指定します。
				@li 0 削除する挿入パラメータ名。
				@li 1 挿入パラメータを使用するコマンドコメント名。省略した場合は全てのコマンドに対するパラメータを削除します。
			@param[in] $iGM_  GUIManagerオブジェクト。
			@param[in] $iRec_ レコードデータ。
			@param[in] $iCC_  コマンドコメントパラメータ。
		*/
		function clearWeave( &$iGM_ , $iRec_ , $iCC_ )
		{
			List( $ccName , $paramName , $targetName ) = $iCC_;

			if( !$paramName ) //挿入パラメータ名が指定されていない場合
				{ throw new InvalidCCArgumentException( '引数 $paramName は無効です' ); }

			Weave::Pop( $paramName , $targetName );
		}



	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//出力ではなくシステム側に作用する特殊なコメントコマンド
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		//templateの条件付きパーサー
		//C言語の条件付きコンパイル(#ifdef)みたいなもの
		//@return boolean(true/false)
		function ifbegin(&$gm, $rec, $cc)
		{
			global $PASSWORD_MODE;

			switch( $cc[1] ){
				case 'not':
				case '!':
					//条件の反転
					return ! ccProc::ifbegin($gm, $rec, array_slice($cc,1));
				case 'alias':
					$alias_gm = SystemUtil::getGMforType( $cc[2] );
					$db = $alias_gm->getDB();
					$alias_rec = $db->selectRecord( $cc[3] );

					return ccProc::ifbegin($alias_gm, $alias_rec, array_slice($cc,3));
				case 'bool':
				case 'boolean':
					$db = $gm->getDB();
					return SystemUtil::convertBool($db->getData( $rec , $cc[2] ));
				case 'intime'://指定カラムが指定期間内かどうか
					$db = $gm->getDB();
					$time = $db->getData( $rec , $cc[2] );
					$period = time() - $cc[3]*3600;
					return $time > $period;
				case 'val_intime'://指定カラムが指定期間内かどうか
					$period = time() - $cc[3]*3600;
					return $cc[2] > $period;
				case 'isget':
					//getにその引数が存在するかどうか。
					return isset($_GET[$cc[2]]) && strlen($_GET[$cc[2]]);
				case 'ispost':
					//postにその引数が存在するかどうか。
					return isset($_POST[$cc[2]]) && strlen($_POST[$cc[2]]);
				case 'issession':
					//sessionにその引数が存在するかどうか。
					TemplateCache::$NoCache = true;
					return isset($_SESSION[$cc[2]]) && strlen($_SESSION[$cc[2]]);
				case 'session':
					//sessionにその引数が存在するかどうか。存在した場合はboolで
					TemplateCache::$NoCache = true;
					return isset($_SESSION[$cc[2]]) ? SystemUtil::convertBool($_SESSION[$cc[2]]) : false;
				case 'nullcheck':
					$db = $gm->getDB();
					//第二引数に指定されたカラムが設定されているかどうか
					$cols = explode( '/', $cc[2]);
					foreach( $cols as $col ){
						if( !strlen( $db->getData( $rec, $col) ) ){
							return false;
						}
					}
					return true;
					break;
				case 'anycheck':
					$db = $gm->getCachedDB();
					//第二引数に指定されたカラムが設定されているかどうか
					$cols = explode( '/', $cc[2]);
					foreach( $cols as $col ){
						if( strlen( $db->getData( $rec, $col) ) ){
							return true;
						}
					}
					return false;
					break;
				case 'zerocheck'://int型版のnullcheck
					$db = $gm->getDB();
					//第二引数に指定されたカラムが設定されているかどうか
					$cols = explode( '/', $cc[2]);
					foreach( $cols as $col ){
						if(  $db->getData( $rec, $col) == 0 ){
							return false;
						}
					}
					return true;
					break;
				case 'eq':
				case 'equal':
				case '=':
					//第二引数のカラム名としたレコードの値と、第三引数に指定された値が一致するかどうか。
					$db = $gm->getDB();
					return ($db->getData( $rec , $cc[2] ) == $cc[3]);
				case '>':
					$db = $gm->getDB();
					return ($db->getData( $rec , $cc[2] ) > $cc[3]);
					break;
				case '>=':
					$db = $gm->getDB();
					return ($db->getData( $rec , $cc[2] ) >= $cc[3]);
					break;
				case '<':
					$db = $gm->getDB();
					return ($db->getData( $rec , $cc[2] ) < $cc[3]);
					break;
				case '<=':
					$db = $gm->getDB();
					return ($db->getData( $rec , $cc[2] ) <= $cc[3]);
					break;
				case 'val_equal':
				case 'val_eq':
				case 'val=':
					//第二、第三引数に指定された値が一致するかどうか。
					$check = isset($cc[3])?$cc[3]:'';
					return ($cc[2] == $check);
				case 'in':
					//第二引数のカラム名としたレコードの値が、"/"で分割された第三引数の文字群に含まれているかどうか。
					$db = $gm->getDB();
					$val = $db->getData( $rec , $cc[2] );
					$array = explode( '/', $cc[3] );
					foreach( $array as $data ){
						if(($val == $data) ){return true;}
					}
					return false;
				case 'val_in':
					//第二引数の値が、"/"で分割された第三引数の文字群に含まれているかどうか。
					$val = $cc[2];
					$array = explode( '/', $cc[3] );
					foreach( $array as $data ){
						if(($val == $data) ){return true;}
					}
					return false;
				case 'array_in':
					//第二引数のカラム名としたレコードの値を"/"で分割し、"/"で分割された第三引数の文字群に含まれているかどうか。
					$db = $gm->getDB();
					$vals = explode('/', $db->getData( $rec , $cc[2] ));
					$array = explode( '/', $cc[3] );
					foreach( $vals as $val )
					{
						foreach( $array as $data ){
							if(($val == $data) ){return true;}
						}
					}
					return false;
				case 'val_array_in':
					//第二引数の値を"/"で分割し、"/"で分割された第三引数の文字群に含まれているかどうか。
					$vals = explode('/', $cc[2]);
					$array = explode( '/', $cc[3] );
					foreach( $vals as $val )
					{
						foreach( $array as $data ){
							if(($val == $data) ){return true;}
						}
					}
					return false;
				case 'get_equal':
				case 'get=':
					//第二引数をGET引数の連想配列名とした値と、第三引数に指定された値が一致するかどうか。
					return isset($_GET[$cc[2]])?($_GET[$cc[2]]==$cc[3]):''==$cc[3];
				case 'post_equal':
					//第二引数をGET引数の連想配列名とした値と、第三引数に指定された値が一致するかどうか。
					return isset($_POST[$cc[2]])?($_POST[$cc[2]] == $cc[3]):''==$cc[3];
				case 'uri_match':
					return (preg_match('/'.str_replace( array("!CODE001;","!CODE000;","!CODE002;"), array(" ", "/", "\\") , $cc[2] ).'$/',$_SERVER['REQUEST_URI']) > 0);
					break;
				case 'uri_match_like':
					return (preg_match('/'.str_replace( array("!CODE001;","!CODE000;","!CODE002;"), array(" ", "/", "\\") , $cc[2] ).'/',$_SERVER['REQUEST_URI']) > 0);
					break;
				case 'val>':
					return ($cc[2] > $cc[3]);
					break;
				case 'val<':
					return ($cc[2] < $cc[3]);
					break;
				case 'val>=':
					return ($cc[2] >= $cc[3]);
					break;
				case 'val<=':
					return ($cc[2] <= $cc[3]);
					break;
				case 'mod_on':
					global $MODULES;
					return class_exists('mod_'.$cc[2]) || array_key_exists( $cc[2] , $MODULES );
				case 'mod_off':
					global $MODULES;
					return !class_exists('mod_'.$cc[2]) && !array_key_exists( $cc[2] , $MODULES );
				case 'match':
					return preg_match( '/' . $cc[3] . '/u' , $cc[2] );
				case 'match_e':
					if(empty($cc[3])){
						return empty($cc[2]);
					}else{
						return mb_ereg( $cc[3], $cc[2] ) !== FALSE;
					}
				case 'login':
					global $loginUserType;
					return $loginUserType == $cc[2];
				case 'isvariable':
					//GMのvariableにその値が存在するかどうか
					return isset($gm->variable[$cc[2]]);
				case 'global':
					global ${$cc[2]};
					return ${$cc[2]};
				case 'is_all':	//rec,Post,getのどこかにデータがあるかどうか
					$ret = (!empty($_GET[$cc[2]])) || (!empty($_POST[$cc[2]]));
					if( $ret ) { return $ret; }
					$db = $gm->getDB();
					$val = $db->getData( $rec , $cc[2] );
					return !empty($val);
					break;
				case 'system':
					$db = GMList::getDB( 'system' );

					$data = SystemUtil::getSystemData( $cc[2] );

					switch( $db->colType[$cc[2]] )
					{
						case 'boolean':
							//booleanならそのまま使う
							return $data;
						default:
							//文字列の場合は引数との比較
							return $data == $cc[3];
					}
					return false;
				case 'true':
					return true;
				case 'false':
					return false;
				case 'password_mode':
					return ( $cc[ 2 ] == $PASSWORD_MODE );
				case 'reminder_mode':
					return ( $cc[ 2 ] == $REMINDER_MODE );
				case 'script_name':
					global $controllerName;
					$script     = SystemInfo::GetScriptName();;
					$controller = strtolower( $controllerName );

					if( $cc[ 2 ] == $script || $cc[ 2 ] == $controller )
						{ return true; }

					if( $controller )
					{
						if( 'regist' == $cc[ 2 ] && 'register' == $controller )
							{ return true; }
						if( 'keygen' == $cc[ 2 ] && 'update' == $controller )
							{ return true; }
						if( 'thumb' == $cc[ 2 ] && 'thumbnail' == $controller )
							{ return true; }
					}

					return false;

				case 'inputtable':
					//lstでConst/AdminDataを設定しているカラムの確認
					global $loginUserType;
					global $controllerName;

					$registValidates = explode( '/' , $gm->colRegist[ $cc[ 2 ] ] );
					$editValidates   = explode( '/' , $gm->colEdit[ $cc[ 2 ] ] );

					$isRegist      = 'register' == strtolower( $controllerName);
					$isEdit        = 'edit' == strtolower( $controllerName );
					$isRegistConst = in_array( 'Const' , $registValidates ) || ( in_array( 'AdminData' , $registValidates ) && 'admin' != $loginUserType );
					$isEditConst   = in_array( 'Const' , $editValidates ) || ( in_array( 'AdminData' , $editValidates ) && 'admin' != $loginUserType );

					return !( ( $isRegist && $isRegistConst ) || ( $isEdit && $isEditConst ) );
				case 'ua_match' :
					return preg_match( '/' . str_replace( '/' , '\\/' , $cc[ 2 ] ) . '/' , $_SERVER[ 'HTTP_USER_AGENT' ] );
				default:
					global $ALL_DEBUG_FLAG;
					if( $ALL_DEBUG_FLAG ){ d( '['.$cc[1].'] not found.' ,'ifbegin');}
			}
			return false;
		}
	}
?>