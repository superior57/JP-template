<?php

	//★クラス //

	/**
		@brief   既定のデータ削除処理のモデル。
	*/
	class AppcUserDeleteModel extends AppDeleteModel //
	{
		function canResign(){
			global $LOGIN_ID;
			$this->canResign = cUserLogic::canResign($LOGIN_ID);
		}

		private $canResign = false;
	}
