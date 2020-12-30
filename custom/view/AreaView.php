<?PHP

	class AreaView extends command_base
	{
		/**********************************************************************************************************
		 *　登録時に利用
		 **********************************************************************************************************/
	
		/**
		 * 地域セレクトボックスを表示
		 *
		 */
		function drawSelectboxAddress( &$gm, $rec, $args )
		{
			$prefectursCol	 = $args[0];
			$addSubCol		 = $args[1];
			$lineMode		 = $args[2];
			$dispFlg		 = ( $args[3] == 'disp' );
			
		$preCC		 = Area::getAddsSelectCC( $prefectursCol, $addSubCol, $lineMode, false, false, $dispFlg );
		$addSubCC	 = Area::getAddSubSelectCC( $addSubCol, $prefectursCol, '', '', false, $dispFlg );
			
			$buffer	 = $gm->getCCResult( $rec, $preCC );
			$buffer	.= $gm->getCCResult( $rec, $addSubCC );
			
			$this->addBuffer( $buffer );
		}
		
		
		/**
		 * 路線・駅セレクトボックスを表示
		 *
		 */
		function drawSelectboxTrain( &$gm, $rec, $args )
		{
			$lineCol		 = $args[0];
			$stationCol		 = $args[1];
			$prefCol		 = $args[2];

			$prefId = '';
			if( strlen($_POST[$prefCol]) )		 { $prefId = $_POST[$prefCol]; }
			else if( strlen($_GET[$prefCol]) )	 { $prefId = $_GET[$prefCol]; }

			$addsCC		 = Area::getAddsSelectCCEx( $prefCol, $stationCol, 'regist', false, false, true );
			$lineCC		 = Area::getLineSelectCC( $lineCol, $stationCol, $prefCol );
			$stationCC	 = Area::getStationSelectCC( $stationCol, $lineCol, $prefCol, '' );
			
			$buffer	 = $gm->getCCResult( $rec, $addsCC );
			$buffer	.= $gm->getCCResult( $rec, $lineCC );
			$buffer	.= $gm->getCCResult( $rec, $stationCC );
			
			$this->addBuffer( $buffer );
		}
		
		
		/**********************************************************************************************************
		 *　検索時に利用
		 **********************************************************************************************************/
		
		/**
		 * 都道府県セレクトボックスを表示
		 *
		 */
		function drawSelectboxAdds( &$gm, $rec, $args )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			// **************************************************************************************

			$prefectursCol	 = $args[0];
			$addSubCol		 = $args[1];
			$lineMode		 = $args[2];
			$noneFlg		 = SystemUtil::convertBool($args[3]);
			$dispFlg		 = ( $args[4] == 'disp' );
			$sortFlg		 = isset($args[5]) ? SystemUtil::convertBool($args[5]) : false;
			$countFlg		 = isset($args[6]) ? SystemUtil::convertBool($args[6]) : false;

			if( !isset($_GET['adds']) && $loginUserType != 'admin' ) { $_POST['adds'] = SystemUtil::getSystemData('adds_default'); }
			
			$preCC	 = Area::getAddsSelectCC( $prefectursCol, $addSubCol, $lineMode, $countFlg, $noneFlg , $dispFlg, $sortFlg);
			
			$buffer	 = $gm->getCCResult( $rec, $preCC );
			
			$this->addBuffer( $buffer );
		}
		
		
		/**
		 * 市区町村セレクトボックスを表示
		 *
		 */
		function drawSelectboxAddSub( &$gm, $rec, $args )
		{
			$prefectursCol	 = $args[0];
			$addSubCol		 = $args[1];
			$option			 = $args[2];
			$dispFlg		 = ( $args[3] == 'disp' );
			$sortFlg		 = isset($args[4]) ? SystemUtil::convertBool($args[4]): false;
			$countFlg		 = isset($args[5]) ? SystemUtil::convertBool($args[4]): false;

			$addSubCC	 = Area::getAddSubSelectCC( $addSubCol, $prefectursCol, '', $option, $countFlg, $dispFlg, $sortFlg );
			
			$buffer	.= $gm->getCCResult( $rec, $addSubCC );
			
			$this->addBuffer( $buffer );
		}
		
		
		/**
		 * 路線セレクトボックスを表示
		 *
		 */
		function drawSelectboxLine( &$gm, $rec, $args )
		{
			$lineCol		 = $args[0];
			$stationCol		 = $args[1];
			$prefCol		 = $args[2];
			
			$lineCC		 = Area::getLineSelectCC( $lineCol, $stationCol, $prefCol, '', true );
			
			$buffer	 = $gm->getCCResult( $rec, $lineCC );
			
			$this->addBuffer( $buffer );
		}
		
		
		/**
		 * 駅セレクトボックスを表示
		 *
		 */
		function drawSelectboxStation( &$gm, $rec, $args )
		{
			$lineCol		 = $args[0];
			$stationCol		 = $args[1];
			$prefCol		 = $args[2];
			$option			 = $args[3];
	
			$stationCC	 = Area::getStationSelectCC( $stationCol, $lineCol, $prefCol, '', $option, true );
			
			$buffer	.= $gm->getCCResult( $rec, $stationCC );
			
			$this->addBuffer( $buffer );
		}	
		
	}

?>