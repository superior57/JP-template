<?php

	class LogicClassAutoloader {

		private $whitelist = array();
		private $logic_path = "./custom/logic/";

		public function __construct() {
			$this->whitelist = array( 'MailLogic','autoMailLogic','resumeLogic','pay_jobLogic','cUserLogic','UserLogic','CountLogic','GiftLogic',
					'JobLogic','threadLogic','messageLogic','entryLogic','nUserLogic','bankAccountLogic','giftLogic','entryLogic','commonLogic','midLogic','freshLogic','billLogic' );

			spl_autoload_register(array($this, 'loader'));
		}
		private function loader($className) {

			if( array_search( $className, $this->whitelist  ) !== FALSE ){
				include_once $this->logic_path.$className . '.php';
			}
		}
	}
	$autoloader = new LogicClassAutoloader();