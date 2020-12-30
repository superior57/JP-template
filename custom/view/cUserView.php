<?php
class cUserView extends command_base{

	//指定の利用プラン(中途、新卒)に課金しているか
	function isCharge( &$_gm, $_rec, $_args ){
		List($type,$id) = $_args;
		switch($type)
		{
			case "mid":
			case "fresh":
				$isCharged = SystemUtil::getTableData("cUser",$id,"charging_{$type}");
				break;
			default:
				$isCharged = false;
				break;
		}
		$result = $isCharged? "TRUE" : "FALSE";
		$this->addBuffer($result);
	}

    /**
     * 求職者トップページ「新着の求人企業」を描画する(最大5件)
     *
     * @param GUIManager $_gm   GUIManagerオブジェクト
     * @param array      $_rec  レコードデータ
     * @param array      $_args なし
     *
     * @return void
     */
    function drawNewRecruitList(&$_gm, $_rec, $_args)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        global $loginUserRank;
        // **************************************************************************************

        $design = Template::getTemplate($loginUserType, $loginUserRank, "other_cuser", "SEARCH_EMBED_DESIGN");

        $db = GMList::getDB($this->getType());
        $table = $db->getTable();
        $table = $db->sortTable($table, "regist", "desc");
        $table = $db->searchTable($table, "image", "!", ""); // 企業イメージ画像が未設定の場合は表示しない
        $row = $db->getRow($table);

        // 該当する企業が1件もない場合
        if ($row == 0) {
            $str .= $_gm->getString($design, null, "failed");
            $this->addBuffer($str);
            return;
        }

        $str .= $_gm->getString($design, null, "head");
        $listCnt = 0;
        for ($i = 0; $i < $row; $i ++) {
            $rec = $db->getRecord($table, $i);

            $jType = viewMode::getViewMode();
            $jDB = GMList::getDB($jType);
            $jTable = JobLogic::getTable($jType);
            $jTable = $jDB->searchTable($jTable, "owner", "=", $db->getData($rec, "id"));
            $jRow = $jDB->getRow($jTable);

            // 公開中の求人情報がある場合、リストに追加
            if ($jRow > 0) {
                $_GET["type"] = "cUser";
                $str .= $_gm->getString($design, $rec, "list");
                ++ $listCnt;
            }

            // 最大5件まで
            if ($listCnt == 5) {
                break;
            }
        }
        $str .= $_gm->getString($design, null, "foot");

        // リストに企業情報が追加されなかった場合
        if ($listCnt == 0) {
            $str = $_gm->getString($design, null, "failed");
        }

        $this->addBuffer($str);
    }

	function drawJobCharges( &$gm, $rec, $args )
	{
		// ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
		global $LOGIN_ID;
		// **************************************************************************************
		List($owner ,$label) = $args;

		if( !strlen($owner) ) { $owner = $LOGIN_ID; }

		$this->addBuffer( cUserLogic::getJobCharges($owner,$label) );
	}


	function canResign( &$_gm, $_rec, $_args ){
		List($id) = $_args;
		$result = cUserLogic::canResign($id)?"TRUE":"FALSE";
		$this->addBuffer($result);
	}

	function getType(){
		return "cUser";
	}
}