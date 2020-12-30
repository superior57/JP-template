<?php
	use Abraham\TwitterOAuth\TwitterOAuth;

	class TwitterLogic //
	{
		static function OAuthConnect() //
		{
			global $ConsumerKey;
			global $ConsumerSecret;
			global $OAuthToken;
			global $OAuthSecret;

			self::$OAuth  = new TwitterOAuth( $ConsumerKey , $ConsumerSecret , $OAuthToken , $OAuthSecret );
		}

		static function Post( $iMessage ) //
		{
			if( !self::$OAuth )
				{ self::OAuthConnect(); }

			if( self::$run )
				$result = self::$OAuth->post( "statuses/update", array( 'status' => $iMessage ) );

			return $result;
		}

		static private $run = false;

		/**
		 * @var $OAuth TwitterOAuth
		 */
		static private $OAuth  = null;
	}
