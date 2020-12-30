<?PHP

/////////////////////mod tag

	class News
	{	
		/**
		 * 初期化条件をセットしたテーブルを返す
		 *
		 * @param table 初期化条件をセットするテーブル。
		 * @return 条件をセットしたテーブル。
		 */
		function getTable( $table = null )
		{
			global $loginUserType;
			
			$db = GMList::getDB('news');
			
			if( !isset($table) ) { $table = $db->getTable(); }
			
			if($loginUserType != 'admin')
			{
				$table	 = $db->searchTable( $table, 'authority', '=', '%'.$loginUserType .'%' ); // 自分の種別のもののみ
				$table	 = $db->searchTable( $table, 'regist', '<', time() );	 // 未来の日時で登録されたものは表示しない
				$table	 = $db->searchTable( $table, 'state', '=', 1 );			 // 公開フラグの立っているもののみ
			}
			$table	 = $db->sortTable( $table, 'regist', 'desc' );
			$table	 = $db->sortTable( $table, 'shadow_id', 'desc', true );
			
			return $table;
		}
	
		
		/**
		 * トピックを組み立てて返す
		 *
		 * @param db newsdb。
		 * @param rec トピックを組み立てるレコード。
		 * @return トピック。
		 */
		function getTopic( $db, $rec )
		{
			switch( $db->getData( $rec, 'link_to' ) )
			{
			case 0: // リンク無し
				$url = NULL;
				break;
			case 1: // 本文へ誘導
				$url = 'index.php?app_controller=info&type=news&id='.$db->getData( $rec, 'id' );
				break;
			case 2: // 任意のURI
				$url = $db->getData( $rec, 'url' );
				break;
			}
			
			$topic	 = $db->getData( $rec, 'topic' );
			if(isset($url))
			{
				switch( $db->getData( $rec, 'link_type' ) )
				{
				case 0: // リンクメッセージを用意
					$topic	.= '　<a href="'.$url.'">'.$db->getData( $rec, 'link_message' ).'</a>';
					break;
				case 1: // トピックにリンク
					$topic	 = '<a href="'.$url.'">'.$topic.'</a>';
					break;
				}
			}

			return $topic;
		}

	}

?>