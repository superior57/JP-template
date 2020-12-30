<?php

	class mod_JobApi extends apiClass
	{
		/**
		 * 求人の公開設定を変更する
		 *
		 * @id 求人ID
		 * @mode on:掲載 off:非掲載
		 */
		function changePublish( &$param )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			// **************************************************************************************

			if( $loginUserType == 'cUser' )
			{
				$db	 = GMList::getDB('job');
				$rec = $db->selectRecord($param['id']);
				switch($param['mode'])
				{
				case 'on': $publish = true; break;
				case 'off': $publish = false; break;
				}

				if(isset($rec))
				{
					$db->setData( $rec, 'publish', $publish );
					$db->updateRecord( $rec );
				}
			}

		}


		/**
		 * 求人の課金方式を設定する
		 *
		 * @charges apply/employment
		 */
		function setCharges( &$param )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			// **************************************************************************************

			if( $loginUserType == 'admin' )
			{
				$cnt = Job::setCharges($param['charges']); // 求人の課金方式を一括設定する
				print $cnt;
			}

		}

	}

?>