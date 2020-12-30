<?php

	//★クラス //

	/**
		@brief テーブルへのレコード挿入タスクをまとめて処理するクラス。
	*/
	class InsertScheduler //
	{
		//■処理 //

		/**
			@brief  全てのタスクを処理する。
			@retval true  タスクの処理に成功した場合。
			@retval false タスクの処理に失敗した場合。
		*/
		function flush() //
		{
			foreach( $this->tasks as $name => $rows ) //全てのタスクを処理
			{
				if( !count( $rows ) ) //行がない場合
					{ continue; }

				if( !Query::InsertRecord( $name , $this->columns[ $name ] , $rows ) ) //レコードの挿入に失敗した場合
					{ return false; }

				$this->tasks[ $name ] = Array();
			}

			return true;
		}

		/**
			@brief     挿入タスクを追加する。
			@param[in] $iTableName テーブル名。
			@param[in] $iColumns   カラム設定配列。
			@param[in] $iRow       レコードデータ。
			@retval    true  タスクの追加に成功した場合。
			@retval    false タスクの追加またはflushに失敗した場合。
			@remarks   挿入タスクが1000件を超えると自動的にflushが呼び出されます。
		*/
		function push( $iTableName , $iColumns , $iRow ) //
		{
			if( !array_key_exists( $iTableName , $this->tasks ) ) //タスク配列が作られていない場合
				{ $this->tasks[ $iTableName ] = Array(); }

			$this->tasks[ $iTableName ][] = $iRow;
			$this->columns[ $iTableName ] = $iColumns;

			if( 1000 <= count( $this->tasks[ $iTableName ] ) ) //タスクが1000件を超えた場合
				{ return $this->flush(); }

			return true;
		}

		//■変数 //
		private $tasks   = Array(); ///<タスク配列。
		private $columns = Array(); ///<カラム設定配列。
	}
