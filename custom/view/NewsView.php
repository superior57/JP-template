<?PHP

	class NewsView extends command_base
	{
		/**
		 * ニュース一覧を表示
		 *
		 */
		function drawHeadline( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $loginUserRank;
			// **************************************************************************************
	
			$design	 = Template::getTemplate( $loginUserType, $loginUserRank, 'news', 'HEADLINE_DESIGN' );
			
			$category	 = 'news';
			$suffix		 = '';
			if( strlen($args[0]) )
			{
				$category = $args[0];
				$suffix = '_'.$args[0];
			}
			$max	 = 5;
			if( strlen($args[1]) ) { $max = $args[1]; }
			
			$db		 = GMList::getDB('news');

			$table	 = News::getTable();
			$table	 = $db->searchTable( $table, 'category', '=', $category );
			
			$row	 = $db->getRow($table);
			if( $max > $row )
			{
				$dummy = $max-$row;
				$max = $row;
			}
			
			$buffer	.= $gm->getString( $design, null, 'head'.$suffix );
			for( $i=0; $i<$max; $i++ )
			{
				$rec = $db->getRecord( $table, $i );
				
				$gm->setVariable( 'topic', News::getTopic( $db, $rec ) );			
				
				$buffer	.= $gm->getString( $design, $rec, 'list'.$suffix ); 
			}

			for( $i=0; $i<$dummy; $i++ ) { $buffer	.= $gm->getString( $design, $rec, 'dummy'.$suffix ); }
	
			$buffer	.= $gm->getString( $design, null, 'foot'.$suffix );
			
			$this->addBuffer( $buffer );
		
		}
		
		/**
		 * トピックを表示
		 *
		 */
		function drawTopic( &$gm, $rec, $args )
		{
			$db = GMList::getDB('news');
			
			$buffer = News::getTopic( $db, $rec );	

			$this->addBuffer( $buffer );
		}
	}

?>
