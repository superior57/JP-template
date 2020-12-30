<?php

	//★クラス //

	/**
		@brief 既定の管理ツールのキャッシュ削除処理のモデル。
	*/
	class AppClearCacheModel extends AppBaseModel //
	{
		function clearCache() //
		{
			$targets = Array(
				'./file/cc_cache' ,
				'./file/thumbs' ,
				'./file/tmp' ,
				'./templateCache' ,
			);

			foreach( $targets as $dir )
				{ self::DeleteDir( $dir ); }
		}

		private static function DeleteDir( $iDir )
		{
			$handle = opendir( $iDir );
			$empty  = true;

			while( $file = readdir( $handle ) )
			{
				if( '.' == $file || '..' == $file )
					{ continue; }

				if( '.htaccess' == $file )
				{
					$empty = false;

					continue;
				}

				if( is_dir( $iDir . '/' . $file ) )
				{
					$childEmpty = self::deleteDir( $iDir . '/' . $file );

					if( $childEmpty )
						{ rmdir( $iDir . '/' . $file ); }
				}
				else
				{
					$updateTime = filemtime( $iDir . '/' . $file );

					if( 60 * 60 > time() - $updateTime )
					{
						$empty = false;

						continue;
					}

					unlink($iDir . '/' . $file );
				}
			}

			return $empty;
		}
	}
