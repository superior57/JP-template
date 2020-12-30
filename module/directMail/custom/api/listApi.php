<?php
	class mod_listApi extends apiClass{

		function add($param){
			global $LIST_MAX_REGIST;
			set_time_limit( 0 );

			$lid = $param["list_id"];
			$user_type = SystemUtil::getTableData("list", $lid, "user_type");

			$uDB = GMList::getDB($user_type);
			$uTable = $this->creatTable($param["form"]);
			$now = DMList::getUserCnt($lid);
			if($LIST_MAX_REGIST == 0)
				{ DMList::regist($uDB, $uTable, $lid); }
			else{
				$limit = $LIST_MAX_REGIST - $now;
				$uTable = $uDB->limitOffset($uTable, 0, $limit);
				DMList::regist($uDB, $uTable, $lid);
			}
		}

		//$tableオブジェクトをGET_クエリカラ生成
		private function creatTable($str){
			global $gm;
			global $loginUserType;
			global $loginUserRank;
            global $magic_quotes_gpc;
			parse_str($str, $array);

			$_GET["type"] = $array["type"];

			$sr	= new Search(  $gm[$array["type"]], $array["type"] );
			$db		 = $gm[$array["type"]]->getDB();

			$sys	 = SystemUtil::getSystem( $array["type"] );

			if( $magic_quotes_gpc || $db->char_code != 'sjis' )
				$sr->setParamertorSet($array);
			else
				$sr->setParamertorSet(addslashes_deep($array));
			$sys->searchResultProc( $gm, $sr, $loginUserType, $loginUserRank );

			$table	 = $sr->getResult();

			$sys->searchProc( $gm, $table, $loginUserType, $loginUserRank );

			return $table;
		}

		function show(&$param){
			global $loginUserType;
			global $loginUserRank;
			global $LIST_OFFSET;

			$design = Template::getTemplate($loginUserType, $loginUserRank, $this->getType(), "RESERVE_USER_LIST");
			$id = $param["id"];
			$user_type = $param["usertype"];
			$current = $param["current"];

			$gm = GMList::getGM($this->getType());
			$db = $gm->getDB();
			$rec = $db->selectRecord($id);
			$user_ids = mod_list::getMailReceiveList($id);

			$gm->setVariable("userType", $user_type);
			for($i=0;$i<$LIST_OFFSET;$i++){
				if(isset($user_ids[$current*$LIST_OFFSET+$i]) && strlen($user_ids[$current*$LIST_OFFSET+$i])){
					$gm->setVariable("userID", $user_ids[$current*$LIST_OFFSET+$i]);
					$buffer .= $gm->getString($design,$rec,"list");
				}else{
					$json["end"] = "true";
				}
			}

			$json["html"] = $buffer;
			print json_encode($json);
		}


		function change($param){
			global $loginUserType;
			if($loginUserType != "admin") return;

			$type = $param["type"];
			$id = explode("/", $param["id"]);
			$val=$param["val"];

			if($val){
				$db = GMList::getDB($type);
				$table = $db->getTable();
				$table = $db->searchTable($table,"id","in",$id);
				$row = $db->getRow($table);

				DMList::regist($db,$table,$val);
				echo $row;
			}
		}



		//個別削除
		function deleteUser($param){
			global $loginUserType;
			set_time_limit( 0 );

			if($loginUserType != "admin") return;

			$type = $param["type"];
			$id = explode("/", $param["id"]);
			$val=$param["val"];

			if($val){
				$db = GMList::getDB($type);
				$table = $db->getTable();
				$table = $db->searchTable($table,"id","in",$id);
				$row=$db->getRow($table);

				DMList::delete($db,$table,$val);
			}
		}

		function deleteAll($param){
			global $loginUserType;
			set_time_limit( 0 );

			if($loginUserType != "admin") return;

			$type = $param["type"];
			$val=$param["val"];

			if($val){
				$db = GMList::getDB($type);
				$table = $db->getTable();
				$table = $db->searchTable($table,"list_id","=","%{$val}%");
				DMList::delete($db,$table,$val);
			}
		}

		function getType(){
			return "list";
		}
	}