<?php

	//★クラス //

	/**
		@brief   既定の静的ページのモデル。
	*/
	class AppedittextOtherModel extends AppOtherModel //
	{
		/**
		 * @brief 簡易更新処理を実行する。
		*/
		function doQuickUpdate() //
		{
			if( 'true' == $_POST[ 'post' ] ) //処理要求がある場合
			{
				ConceptSystem::CheckAuthenticityToken()->OrThrow( 'IllegalTokenAccess' );
				$method = 'otherProc';
				if(method_exists($this->sys, $method)) {
					$this->sys->{$method}( $this->loginUserType , $this->loginUserRank );
				}
			}
			if(isset($_GET[ 'file' ])) {
				SearchObjectStack::setStack(SearchValueManager::load($_GET[ 'file' ]));
			}
		}

		function __construct() //
		{
			parent::__construct();
			$this->sys = self::getSystem( $_GET[ 'type' ] );
		}

		protected function getSystem($type) {
			$systemPath = 'module/'.$type.'/custom/system/'.$type.'System.php';
			if(file_exists($systemPath)) {
				include_once $systemPath;
				$className = $type.'System';
				if( class_exists('edittextSystem')) {
					return new $className();
				}
			}
			return null;
		}
		var $sys = null;
	}
