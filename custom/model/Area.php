<?php

	class Area
	{
		static $areaRecList;

		/**
		 * 地域テーブルの指定IDレコードを返す
		 *
		 * @param id ID
		 * @return レコード
		 */
		static function getAreaRecord( $id )
		{
			if( isset( self::$areaRecList[$id] ) ) { return self::$areaRecList[$id]; }

			// まだレコードを取得していない場合は取得してから返す。
			$db	 = GMList::getDB('area');
			self::$areaRecList[$id] = $db->selectRecord($id);
			return self::$areaRecList[$id];
		}

		static function getAddsData( $id )
		{
			$db		= GMList::getDB('adds');
			$table	= $db->getTable();
			$table	= $db->searchTable(  $table, 'area_id', 'in', $id );
			$row = $db->getRow($table);
			return $db->getDataList($table, "id");
		}

		/**
		 * 地域テーブルの指定IDレコードの指定カラム情報を返す
		 *
		 * @param id ID
		 * @param column カラム名
		 * @return カラム値
		 */
		static function getAreaData( $id, $column )
		{
			$db	 = GMList::getDB('area');
			$rec = self::getAreaRecord( $id );

			return $db->getData( $rec, $column );
		}


		/**
		 * 市町村カラム要素の動的変更を行う際の都道府県カラムのselectboxeを出力
		 *
		 * @param colName カラム名
		 * @param childCol 子カラム名
		 * @param lineMode 路線検索JS用ステート
		 * @param countFlg 物件件数を表示するかどうかのフラグ
		 * @return CC
		 */
		function getAddsSelectCC( $colName, $childCol, $lineMode, $countFlg = false, $noneFlg = false, $dispFlg = false, $sortFlg = false )
		{
			$tableName	 = 'adds';
			$initial	 = '';

			// 選択肢生成に利用
			$valueCol	 = 'id';
			$indexCol	 = 'name';
			$noneIndex	 = '未選択';

			// jscode生成に利用
			$childTableName	 = 'add_sub';
			$childSearchCol	 = 'adds_id';

			$db		 = GMList::getDB($tableName);
			$table	 = $db->getTable();

			if( is_array($_GET['pos_word']) && count($_GET['pos_word']) > 0 )
				{ $table = $db->searchTable( $table, 'area_id', 'in', $_GET['pos_word'] ); }
			
			if( $dispFlg ) {
				$disp = Conf::getData( 'job', 'top_area_range');
				if ($disp == 'area') {
					$table = $db->searchTable( $table, 'area_id', '=', Conf::getData( 'job', 'def_area') );
				} else if ($disp == 'adds') {
					$table = $db->searchTable( $table, 'id', '=', Conf::getData( 'job', 'def_adds') );
				}
				$table = $db->searchTable( $table, 'disp', '=', true );
			}
			
			$table = $db->sortTable( $table, 'sort_rank', 'asc' );
			
			// 求人数が多い地域上に表示
			if($sortFlg) {
				$table = commonLogic::getSortTable($colName, $db, $table);
			}

			$countList = null;
			if( $countFlg ) { $countList = CountLogic::controller( $_GET['type'], 'adds' ); }

			$ccParam	 = Format::createCCParam( $db, $table, $valueCol, $indexCol, $noneIndex, $countList );
			$js			 = Area::createParentJsCode( $colName, $childCol, $childTableName, $childSearchCol, $lineMode, $countFlg, $noneFlg, $dispFlg );

			return '<!--# form option '.$colName.' '.$initial.' '.$ccParam['value'].' '.$ccParam['index'].' '.$js.' #-->';
		}

		/**
		 * 個別に最寄駅の都道府県を指定する際の都道府県カラムのselectboxを出力
		 *
		 * @param colName カラム名
		 * @param stationCol 駅カラム名
		 * @param lineMode 路線検索JS用ステート
		 * @param countFlg 物件件数を表示するかどうかのフラグ
		 * @return CC
		 */
		function getAddsSelectCCEx( $colName, $stationCol, $lineMode, $countFlg = false, $noneFlg = false, $dispFlg = false )
		{
			$tableName	 = 'adds';
			$initial	 = '(!--# alias station '.$stationCol.' id adds_id #--)';

			// 選択肢生成に利用
			$valueCol	 = 'id';
			$indexCol	 = 'name';
			$noneIndex	 = '未選択';

			// jscode生成に利用
			$childTableName	 = 'line';
			$childSearchCol	 = 'adds_ids';

			$db		 = GMList::getDB($tableName);
			$table	 = $db->getTable();

			if( is_array($_GET['pos_word']) && count($_GET['pos_word']) > 0 )
				{ $table = $db->searchTable( $table, 'area_id', 'in', $_GET['pos_word'] ); }
			
			if( $dispFlg ) {
				$disp = Conf::getData( 'job', 'top_area_range');
				if ($disp == 'area') {
					$table = $db->searchTable( $table, 'area_id', '=', Conf::getData( 'job', 'def_area') );
				} else if ($disp == 'adds') {
					$table = $db->searchTable( $table, 'id', '=', Conf::getData( 'job', 'def_adds') );
				}
				$table = $db->searchTable( $table, 'disp', '=', true );
			}

			$countList = null;
			if( $countFlg ) { $countList = CountLogic::controller( $_GET['type'], 'adds' ); }

			$ccParam	 = Format::createCCParam( $db, $table, $valueCol, $indexCol, $noneIndex, $countList );
			$js			 = Area::createParentJsCode( $colName, null, $childTableName, $childSearchCol, $lineMode, $countFlg, $noneFlg, $dispFlg );

			return '<!--# form option '.$colName.' '.$initial.' '.$ccParam['value'].' '.$ccParam['index'].' '.$js.' #-->';
		}

		/**
		 * 市町村カラム要素の動的変更を行うjscodeを出力
		 *
		 * @param colName 親カラム名
		 * @param childCol 子カラム名
		 * @param childTableName 子カラムのテーブル名
		 * @param childSearchCol 子テーブルにて親要素が格納されているカラム名
		 * @param lineMode 路線検索JS用ステート
		 * @param countFlg 物件件数を表示するかどうかのフラグ
		 * @return jsCode
		 */
		function createParentJsCode( $colName, $childCol, $childTableName, $childSearchCol, $lineMode, $countFlg, $noneFlg, $dispFlg )
		{ return 'onchange="loadAddSub(this,\''.$colName.'\',\''.$childCol.'\',\''.$childTableName.'\',\''.$childSearchCol.'\',\''.$lineMode.'\',\''.$countFlg.'\',\''.$noneFlg.'\',\''.$dispFlg.'\')"'; }


		/**
		 * 親都道府県カラムによって要素が動的に変わる際の市町村カラムのselectboxeを出力
		 *
		 * @param colName カラム名
		 * @param parentCol 親カラム名
		 * @param parent 親要素の初期値
		 * @param option ID等の追加要素
		 * @param countFlg 物件件数を表示するかどうかのフラグ
		 * @return CC
		 */
		function getAddSubSelectCC( $colName, $parentCol, $parent = '', $option = '', $countFlg = false, $dispFlg = false, $sortFlg = false )
		{
			$tableName	 = 'add_sub';

			// 選択肢生成に利用
			$valueCol	 = 'id';
			$indexCol	 = 'name';
			$noneIndex	 = '未選択';
			if(strlen($option)) { $noneIndex = ''; }

			// 親IDの指定。優先順位 POST>GET>引数
			if( strlen($_POST[$parentCol]) )	 { $parent = $_POST[$parentCol]; }
			else if( strlen($_GET[$parentCol]) ) { $parent = $_GET[$parentCol]; }
			$cc = '<!--# form option '.$colName.'   '.$noneIndex.' '.$option.' #-->';
			if( strlen($parent) )
			{
				$db		 = GMList::getDB($tableName);
				$table	 = $db->getTable();
				$table	 = $db->searchTable( $table, 'adds_id', '=', $parent );
				if( $dispFlg ) { $table = $db->searchTable( $table, 'disp', '=', true ); }
				$table = $db->sortTable( $table, 'sort_rank', 'asc' );
				
				// 求人数が多い地域上に表示
				if($sortFlg) {
					$table = commonLogic::getSortTable($colName, $db, $table);
				}

				// 登録件数を取得
				$countList = null;
				if( $countFlg ) { $countList = CountLogic::controller( $_GET['type'], 'add_sub' ); }

				$ccParam = Format::createCCParam( $db, $table, $valueCol, $indexCol, $noneIndex, $countList );
				$cc		 = '<!--# form option '.$colName.'  '.$ccParam['value'].' '.$ccParam['index'].' '.$option.' #-->';
			}


			return $cc;
		}


		/**
		 * 駅カラム要素の動的変更を行う際の路線カラムのselectboxeを出力
		 *
		 * @param colName カラム名
		 * @param childCol 子カラム名
		 * @param parentCol 親カラム名
		 * @param parent 親要素の初期値
		 * @param countFlg 物件件数を表示するかどうかのフラグ
		 * @return CC
		 */
		function getLineSelectCC( $colName, $childCol, $parentCol, $parent = '', $countFlg = false  )
		{
			$tableName	 = 'line';
			$initial	 = '';

			// 選択肢生成に利用
			$valueCol	 = 'id';
			$indexCol	 = 'name';
			$noneIndex	 = '未選択';

			// jscode生成に利用
			$childTableName	 = 'station';
			$childSearchCol	 = 'line_ids';
			$prefSearchCol	 = 'adds_id';

			$js			 = Area::createStationJsCode( $colName, $childCol, $childTableName, $childSearchCol, $parentCol, $prefSearchCol, $countFlg );

			// 親IDの指定。優先順位 POST>GET>引数
			if( strlen($_POST[$childCol]) )	     { $parent = SystemUtil::getTableData($childTableName, $_POST[$childCol], $prefSearchCol); }
			else if( strlen($_GET[$childCol]) )  { $parent = SystemUtil::getTableData($childTableName, $_GET[$childCol], $prefSearchCol); }
			else if( strlen($_POST[$parentCol]) ){ $parent = $_POST[$parentCol]; }
			else if( strlen($_GET[$parentCol]) ) { $parent = $_GET[$parentCol]; }

			$cc = '<!--# form option '.$colName.'   '.$noneIndex.' '.$js.' #-->';
			if( strlen($parent) )
			{
				$db		 = GMList::getDB($tableName);
				$table	 = $db->getTable();
				$table	 = $db->searchTable( $table, 'adds_ids', '=', '%'.$parent.'%' );

				// 登録件数を取得
				$countList = null;
				if( $countFlg ) { $countList = CountLogic::controller( $_GET['type'], 'line' ); }

				$ccParam	 = Format::createCCParam( $db, $table, $valueCol, $indexCol, $noneIndex, $countList );
				$cc	 =  '<!--# form option '.$colName.' '.$initial.' '.$ccParam['value'].' '.$ccParam['index'].' '.$js.' #-->';
			}

			return $cc;
		}


		/**
		 * 市町村カラム要素の動的変更を行うjscodeを出力
		 *
		 * @param colName 親カラム名
		 * @param childCol 子カラム名
		 * @param childTableName 子カラムのテーブル名
		 * @param childSearchCol 子テーブルにて親要素が格納されているカラム名
		 * @param prefCol 都道府県カラム名
		 * @param prefSearchCol 子テーブルにて都道府県要素が格納されているカラム名
		 * @param lineMode 路線検索JS用ステート
		 * @param countFlg 物件件数を表示するかどうかのフラグ
		 * @return jsCode
		 */
		function createStationJsCode( $colName, $childCol, $childTableName, $childSearchCol, $prefCol, $prefSearchCol, $countFlg )
		{ return 'onchange="loadStation(this,\''.$colName.'\',\''.$childCol.'\',\''.$childTableName.'\',\''.$childSearchCol.'\',\''.$prefCol.'\',\''.$prefSearchCol.'\',\''.$countFlg.'\')"'; }



		/**
		 * 路線カラムによって要素が動的に変わる際の駅カラムのselectboxeを出力
		 *
		 * @param colName カラム名
		 * @param parentCol 親カラム名
		 * @param prefCol 都道府県カラム名
		 * @param parent 親要素の初期値
		 * @param option ID等の追加要素
		 * @param countFlg 物件件数を表示するかどうかのフラグ
		 * @return CC
		 */
		function getStationSelectCC( $colName, $parentCol, $prefCol, $parent = '', $option = '', $countFlg = false )
		{
			$tableName	 = 'station';

			// 選択肢生成に利用
			$valueCol	 = 'id';
			$indexCol	 = 'name';
			$noneIndex	 = '未選択';
			if(strlen($option)) { $noneIndex = ''; }

			// 親IDの指定。優先順位 POST>GET>引数
			if( strlen($_POST[$parentCol]) )	 { $parent = $_POST[$parentCol]; }
			else if( strlen($_GET[$parentCol]) ) { $parent = $_GET[$parentCol]; }

			$cc = '<!--# form option '.$colName.'   '.$noneIndex.' '.$option.' #-->';
			if( strlen($parent) )
			{
				$prefId = '';
				if( strlen($_POST[$prefCol]) )		 { $prefId = $_POST[$prefCol]; }
				else if( strlen($_GET[$prefCol]) )	 { $prefId = $_GET[$prefCol]; }
				else if( strlen($_POST[$colName]) )	 { $prefId = SystemUtil::getTableData($tableName, $_POST[$colName], 'adds_id'); }
				else if( strlen($_GET[$colName]) )	 { $prefId = SystemUtil::getTableData($tableName, $_GET[$colName],  'adds_id'); }
				
				$db		 = GMList::getDB($tableName);
				$table	 = $db->getTable();
				$table	 = $db->searchTable( $table, 'line_ids', '=', '%'.$parent.'%' );
				$table	 = $db->searchTable( $table, 'adds_id', '=', '%'.$prefId.'%' );

				// 登録件数を取得
				$countList = null;
				if( $countFlg ) { $countList = CountLogic::controller( $_GET['type'], 'station' ); }

				$ccParam = Format::createCCParam( $db, $table, $valueCol, $indexCol, $noneIndex, $countList );
				$cc		 = '<!--# form option '.$colName.'  '.$ccParam['value'].' '.$ccParam['index'].' '.$option.' #-->';
			}

			return $cc;
		}

	}

?>