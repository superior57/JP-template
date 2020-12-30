<?php

class mod_session_keeperApi extends apiClass
{

	function logout($params)
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $loginUserType;
		// **************************************************************************************

		SystemUtil::logout($loginUserType);
	}
}

?>