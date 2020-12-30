<?php

	include_once "./include/base/WSBase.php";

	/**
		@brief   挿入パラメータ管理クラス。
		@details コマンドコメントの挿入パラメータを管理します。
		@author  松木 昌平
		@version 1.0
		@ingroup SystemComponent
	*/
	class Weave extends WSBase
	{
		//■パラメータ変更

		/**
			@brief     挿入パラメータを追加する。
			@exception InvalidArgumentException 不正な引数を指定した場合。
			@param[in] $iName_   挿入パラメータ名。
			@param[in] $iValue_  挿入パラメータの値。
			@param[in] $iCCName_ 挿入パラメータを使用するコマンドコメント名。
			@param[in] $iLife_   挿入パラメータの寿命。
		*/
		static function Push( $iName_ , $iValue_ , $iCCName_ , $iLife_ )
		{
			Concept::IsString( $iName_ )->Orthrow( 'InvalidCCArgument' );
			Concept::IsTrue( strlen( $iName_ ) )->Orthrow( 'InvalidCCArgument' );
			self::$Parameters[ $iName_ ][] = Array( 'value' => $iValue_ , 'ccName' => $iCCName_ , 'life' => $iLife_ );
		}

		//■パラメータ取得

		/**
			@brief     パラメータを取得する。
			@exception InvalidArgumentException 不正な引数を指定した場合。
			@param[in] $iName_   挿入パラメータ名。
			@param[in] $iCCName_ コマンドコメント名。
			@return    パラメータ配列または空配列。
			@attension 一つのコマンド内で、メソッドを複数回同じ引数で呼び出さないようにしてください。\n
			           onceパラメータは取得時点で削除されるため、2回目以降は異なる配列が返る可能性があります。
		*/
		static function Get( $iName_ , $iCCName_ )
		{
			$returnParams  = Array(); //取り出す挿入パラメータ
			$inheritParams = Array(); //残す挿入パラメータ

			foreach( self::$Parameters as $key => $params ) //挿入パラメータを処理
			{
				if( $iName_== $key ) //挿入パラメータ名が一致する場合
				{
					foreach( $params as $param ) //個別の挿入パラメータを処理
					{
						$matchCCName = ( $iCCName_ == $param[ 'ccName' ] );
						$useWildCard = ( '*' == $param[ 'ccName' ] );

						if( $matchCCName || $useWildCard ) //コマンドコメント名が一致する場合
						{
							$returnParams[] = $param[ 'value' ];

							if( 'all' == $param[ 'life' ] ) //寿命が無制限の場合
								{ $inheritParams[ $key ][] = $param; }
						}
						else //コマンドコメント名が一致しなかった場合
							{ $inheritParams[ $key ][] = $param; }
					}
				}
				else //挿入パラメータ名が一致しない場合
					{ $inheritParams[ $key ] = $params; }
			}

			self::$Parameters = $inheritParams;

			return $returnParams;
		}

		//■変数
		static private $Parameters = Array(); ///<挿入パラメータ配列。
	}

?>