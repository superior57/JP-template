<?php

	class commonLogic
	{

		//ソート用のクラスメソッド
		static function getSortTable($colName, &$db, &$table) {
			$subname = 'ad';
			$mode = viewMode::getViewMode();
			$subdb = GMList::getDB($mode);
			$subtable = $subdb->getTable();
			$subtable = JobLogic::getTable( $mode, $subtable );
			$table = $db->outerjoinTableSubQuery('left', $table, $subtable, $subname, 'id', $colName);
			$table = $db->getCountTable($colName, $table, true, '>', 0);
			$table = $db->sortTable( $table, 'cnt', 'desc' );
			return $table;
		}

		/**
			@brief     単純なテーブル選択フォームのコマンドコメントコードを生成する。
			@exception InvalidArgumentException $iStyle_ に不明な種類を指定した場合。
			@param[in] $iDB_       Databaseオブジェクト。
			@param[in] $iTable_    Tableオブジェクト。
			@param[in] $iStyle_    生成するフォームの種類(option/radio/checkbox)
			@param[in] $iName_     生成するフォームの名前。
			@param[in] $iNameCol_  要素の見出しにするカラム。
			@param[in] $iValueCol_ 要素の値にするカラム。
			@return    生成されたフォームのコマンドコメントコード。
		*/
		function getTableFormCC( $iDB_ , $iTable_ , $iStyle_ , $iName_ , $iNameCol_ , $iValueCol_ )
		{
			$row    = $iDB_->getRow( $iTable_ );
			$names  = Array();
			$values = Array();

			for( $i = 0 ; $i < $row ; ++$i ) //全ての行を処理
			{
				$rec   = $iDB_->getRecord( $iTable_ , $i );
				$name  = $iDB_->getData( $rec , $iNameCol_ );
				$value = $iDB_->getData( $rec , $iValueCol_ );
				$name  = str_replace( '\\' , '\\\\' , $name );
				$name  = str_replace( '/' , '\\/' , $name );
				$value = str_replace( '\\' , '\\\\' , $value );
				$value = str_replace( '/' , '\\/' , $value );

				$names[]  = $name;
				$values[] = $value;
			}

			switch( $iStyle_ ) //フォームの種類で分岐
			{
				case 'option' :
				{
					array_unshift( $names , '未選択' );
					array_unshift( $values , '' );

					return '<!--# form option ' . $iName_ . '  ' . implode( '/' , $values ) . ' ' . implode( '/' , $names ) . ' #-->';
				}

				case 'radio' :
				{
					array_unshift( $names , '未選択' );
					array_unshift( $values , '' );

					return '<!--# form radio ' . $iName_ . '   ' . implode( '/' , $values ) . ' ' . implode( '/' , $names ) . ' #-->';
				}

				case 'checkbox' :
				{
					if( !count( $values ) ) //配列が空の場合
						{ return ''; }

					return '<!--# form checkbox ' . $iName_ . '   ' . implode( '/' , $values ) . ' ' . implode( '/' , $names ) . ' #-->';
				}

				default : //その他
					{ throw new InvalidArgumentException( '引数 $iStyle_ は無効です[' . $iStyle_ . ']' ); }
			}
		}
	}
