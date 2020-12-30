<?PHP

	class Format
	{
		/**
		 * 渡されたテーブルデータをjson形式で返す
		 *
		 * @param db DBオブジェクト
		 * @param table jsonフォーマットで出力するテーブル
		 * @param valueCol 値として使用するカラム。未入力時はid
		 * @param indexCol インデックスとして使用するカラム。未入力時はname
		 * @param noneIndex 未指定時のindex
		 * @return jsonフォーマットのデータ
		 */
		function createJsonData( $db, $table, $valueCol = 'id', $indexCol = 'name', $noneIndex = '', $count = null )
		{
			$row	 = $db->getRow($table);
			
			$flg	 = false;
			if( is_array($count) ) { $flg = true; }
	
			$elements = Array();

			$json	 = '{ '."\n";
			if( strlen($noneIndex) ) { $elements[]	= '"":"'.$noneIndex.'"'; }
			for($i=0; $i<$row; $i++)
			{
				$rec	 = $db->getRecord( $table, $i );
				$value	 = $db->getData($rec, $valueCol);
				$index	 = $db->getData($rec, $indexCol);
				if($flg) 
				{
					if( (int)$count[$value] > 0 )
					{
						$index .= '('.(int)$count[$value].')';
						$elements[] = '"'.$value.'":"'.$index.'"';
					}
				}
				else { $elements[]	.= '"'.$value.'":"'.$index.'"'; }
			}

			$json .= implode( $elements , ',' . "\n" );
			$json .= "\n }";
	
			return $json;
		}
		
		
		/**
		 * 渡されたテーブルデータをCCパラメータ用の配列で返す
		 *
		 * @param db DBオブジェクト
		 * @param table パラメータを出力するテーブル
		 * @param valueCol 値として使用するカラム。未入力時はid
		 * @param indexCol インデックスとして使用するカラム。未入力時はname
		 * @param noneIndex 未指定時のindex
		 * @return 配列データ
		 */	
		function createCCParam( $db, $table, $valueCol = 'id', $indexCol = 'name', $noneIndex = '', $count = null )
		{
			if(strlen($noneIndex))
			{
				$result["value"] = "/";
				$result["index"] = $noneIndex."/";
			}
	
			$flg	 = false;
			if( is_array($count) ) { $flg = true; }
			
			$row	 = $db->getRow($table);
			for($i=0;$i<$row;$i++)
			{
				$rec	 = $db->getRecord( $table, $i );
				$value	 = $db->getData($rec, $valueCol);
				$index	 = $db->getData($rec, $indexCol);
				if($flg)
				{
					if( (int)$count[$value] > 0 )
					{
						$index .= '('.(int)$count[$value].')';
						$result["value"] .= $value."/";
						$result["index"] .= $index."/";
					}
				}
				else
				{
					$result["value"] .= $value."/";
					$result["index"] .= $index."/";
				}
			}
			$result["value"] = substr( $result["value"], 0, -1 );
			$result["index"] = substr( $result["index"], 0, -1 );
			
			return $result;
		}
		
		
		/**
		 * 子カラム要素の動的変更を行うjscodeを出力
		 *
		 * @param colName 親カラム名
		 * @param childCol 子カラム名
		 * @param childTableName 子カラムのテーブル名
		 * @param childSearchCol 子テーブルにて親要素が格納されているカラム名
		 * @return 配列データ
		 */	
		function createParentJsCode( $colName, $childCol, $childTableName, $childSearchCol )
		{ return 'onchange="loadChild(this,\''.$colName.'\',\''.$childCol.'\',\''.$childTableName.'\',\''.$childSearchCol.'\')"'; }
	
	}
	
?>