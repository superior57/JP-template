<?php

class CsvLogic
{
		var $readHandle;
		var $filenameData;
		var $filenameCol;
		var $colList;

		var $dataList;
		var $idList;

	
		/**
		 * csvをシステムで利用しやすい配列にして取得
		 * 
		 * @param data_csv データが保存されているcsvファイル
		 * @param col_csv データ列のカラムを指定するcsvファイル
		 * @param id_name 新規と既存を識別するためのカラム名
		 */
		function __construct( $data_csv, $col_csv, $id_name )
		{
			$this->filenameData = $data_csv;
			$this->filenameCol = $col_csv;
			if( !$this->initColList() ) { return; }

//			$data = $this->readData(); // 1行目はカラムなので捨てる
			while( $data = $this->readData() )
			{
				$id = $data[$id_name];
				$this->dataList[$id] = $data;

				$this->idList[] = $id;
				ksort($this->idList);
				array_multisort($this->idList, SORT_ASC, $this->dataList);
			}

		}

		// データ配列を返す
		function getDataList() { return $this->dataList; }
		// 編集テーブルを取得するためのID配列を返す
		function getIdList() { return $this->idList; }


		/**
		 * カラムリストをセット
		 * 
		 * @return セットできた場合trueを返す
		 */
		function initColList()
		{
			$handle = fopen( $this->filenameCol , 'rb' );
			if( !$handle ) { return false; }

			while( $readLine = rtrim( fgets( $handle ) ) )
			{
				$readLine = explode( ',' , $readLine );
				$this->colList[] = $readLine[ 0 ];
			}

			return true;
		}


		/*		csvが読み込み可能か調べる		*/
		function readable()
		{
			if( !$this->readHandle ) { $this->readHandle = fopen( $this->filenameData , 'rb' ); }
			return ( $this->readHandle ? true : false );
		}



		/*		csvを1行ずつ読んで連想配列にして返す		*/
		/*		定型処理 : 引数はありません		*/
		/*		rt : 連想配列又はnull		*/
		function readData()
		{
			if( !$this->readable() ) { return null; }

			$readLine = self::fget( $this->readHandle );
			if( !$readLine )  { return null; }

			$result = array();
			if(count($this->colList) != count($readLine)){
				$readLine = array_slice($readLine, 2);
			}
			$cnt = count( $readLine );
			for( $i=0; $i<$cnt; $i++ )
			{ $result[$this->colList[$i]] = $readLine[$i]; }

			return $result;
		}


		/*		ファイルポインタから行を取得し、CSVフィールドを処理する		*/
		/*		参考URL : http://yossy.iimp.jp/wp/?p=56		*/
		/*		p0 : ファイルハンドル		*/
		/*		p1 : 読み込みサイズ		*/
		/*		p2 : 区切り文字		*/
		/*		p3 : 囲み文字		*/
		/*		rt : 配列又はfalse		*/
		static function fget ( &$handle , $length = null , $d = ',' , $e = '"' )
		{
			global $SYSTEM_CHARACODE;

			$d = preg_quote( $d );
			$e = preg_quote( $e );

			$_line = '';
			$eof = false;

			while ( !$eof )
			{
				$_line   .= ( empty( $length ) ? fgets( $handle ) : fgets( $handle , $length ) );
				$itemcnt  = preg_match_all('/' . $e . '/' , $_line , $dummy );

				if( $itemcnt % 2 == 0 )
					$eof = true;
			}
			$_line	  = mb_convert_encoding( $_line, $SYSTEM_CHARACODE, 'SJIS');

			if( empty( $_line ) ) return false;

			$_csv_line    = preg_replace( '/(?:\r\n|[\r\n])?$/' , $d , trim( $_line ) );
			$_csv_pattern = '/(' . $e . '[^' . $e . ']*(?:' . $e . $e . '[^' . $e . ']*)*' . $e . '|[^' . $d . ']*)' . $d . '/';

			preg_match_all( $_csv_pattern , $_csv_line , $_csv_matches );

			$_csv_data = $_csv_matches[ 1 ];

			for($_csv_i = 0 ; $_csv_i < count($_csv_data) ; $_csv_i++ )
			{
				$_csv_data[ $_csv_i ] = preg_replace('/^' . $e . '(.*)' . $e . '$/s' , '$1' , $_csv_data[ $_csv_i ] );
				$_csv_data[ $_csv_i ] = str_replace( $e . $e , $e , $_csv_data[ $_csv_i ] );
			}

			return $_csv_data;
		}


		/**
		 * fileがcsvファイルかどうか返す
		 *
		 * @param md5 メールアドレスのmd5
		 * @return メールアドレス
		 */
		static function checkFile( $file )
		{
			$csv_types = array( 'application/x-octet-stream',
								'application/octet-stream',
								'application/vnd.ms-excel',
								'application/x-csv',
								'text/csv',
								'text/comma-separated-values' );
			
			$check = true;
			if( array_search( $file[ 'type' ], $csv_types ) === false ) { $check = false; }
			else if( substr( $file[ 'name' ], -4 ) != '.csv' )			{ $check = false; }
	
			return $check;
		}


}
