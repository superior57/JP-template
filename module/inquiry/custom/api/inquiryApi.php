<?php

	class mod_inquiryApi extends apiClass
	{

		function request(&$param)
		{
			$json = array(
				"result" => "ok",
				"errorMessage" => ""
			);

			if (! strlen($param["request"])) {
				$json["result"] = "ng";
				$json["errorMessage"] = "お問い合わせ内容が入力されていません。";
				print json_encode($json);
				return;
			}

			$db = GMlist::getDB("inquiry");

			$rec = $db->getNewRecord();
			$db->setData($rec, "sub", "改善要望、ご意見、ご感想");
			$db->setData($rec, "note", h($param["request"]));
			$db->setData($rec, "name", "ご意見箱");
			$db->setData($rec, "mail", SystemUtilBase::getTableData("system", "ADMIN", "mail_address"));
			$db->setData($rec, "regist", time());
			$db->addRecord($rec);

			print json_encode($json);
		}
	}

?>