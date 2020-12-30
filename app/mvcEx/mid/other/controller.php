<?php

	//★クラス //

	/**
		@brief 既定の静的ページのコントローラ。
	*/
	class AppmidOtherController extends AppOtherController //
	{
		function __construct() //
		{
			parent::__construct();
			switch($_GET["key"]){
				case "AccessCount";
					$pv = Conf::getData("job","pv");
					if($pv == "admin")
						$isAccept = in_array($this->model->loginUserType,array("admin"));
					elseif($pv == "owner")
						$isAccept = in_array($this->model->loginUserType,array("admin","cUser"));
					else
						$isAccept = false;

					Concept::IsTrue($isAccept)->OrThrow();
					break;
			}
		}
	}
