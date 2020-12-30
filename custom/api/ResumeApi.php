<?php

	class mod_ResumeApi extends apiClass
	{

		function togglePublish(&$param){
			$id = $param["id"];
			$user_id = $param["user_id"];
			if(!empty($id) && !empty($user_id))
				{ resumeLogic::togglePublish($id, $user_id); }
			else
				{ throw new Exception();}
		}

		/**
		 * 子カラムの要素を出力
		 *
		 * @param tableName テーブル名
		 * @param parentCol 親IDが格納されているカラム名
		 * @param parent 親ID
		 */
		function getResumeData4Copy( &$param )
		{
			global $SYSTEM_CHARACODE;
			header('Content-Type: application/json;charset='.$SYSTEM_CHARACODE);

			$input=explode(",",$param["input"]);
			$select=explode(",",$param["select"]);
			$textarea=explode(",",$param["textarea"]);

			$db	 = GMList::getDB("resume");
			$rec = $db->selectRecord($param["id"]);

			foreach($input as $val){
				$inputArr=explode("/",$val);
				$type=$inputArr[0];
				$name=$inputArr[1];
				$buffer[]=$name."/".$db->getData($rec,$name);
			}

			echo implode(",",$buffer);
		}

	}

?>