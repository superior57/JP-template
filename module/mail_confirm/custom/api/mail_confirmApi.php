<?php

	class mod_mail_confirmApi extends apiClass
	{
		function confirm( &$param )
		{
			// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
			global $loginUserType;
			global $LOGIN_ID;
			global $loginUserRank;
			global $MAILSEND_ADDRES;
			global $MAILSEND_NAMES;

			$gm = GMList::getGM($loginUserType);
			$db = $gm->getDB();
			$rec = $db->selectRecord($LOGIN_ID);
			$gm->setVariable("MAIL_ADDRESS",$param["address"]);

			$template = "./module/mail_confirm/template/pc/other/mail_contents/mail_confirm/mail_confirm.txt";

			Mail::send( $template , $MAILSEND_ADDRES, $param["address"], $gm, $rec, $MAILSEND_NAMES );

		}

	}

?>