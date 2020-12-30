<?php
class EntryView extends command_base{

	//指定ユーザーが指定企業へのエントリーが一件以上あるかどうか
	function haveUserEntry(&$gm, $rec, $args){
		List($cID,$userID) = $args;
		if(!is_null(Entry::getApplyItemsID($cID,$userID)))
			$this->addBuffer("TRUE");
		else
			$this->addBuffer("FALSE");
	}


	function drawWaitApplicantCnt( &$gm, $rec, $args ){
		List($itemsID) = $args;
		$row = Entry::getWaitApplicant($itemsID);
		$this->addBuffer($row);
	}

	function hasEntry( &$gm, $rec, $args ){
		List($itemsID) = $args;
		$result = entryLogic::existsApply($itemsID) ? "TRUE":"FALSE";
		$this->addBuffer($result);
	}

	function drawIsApply( &$gm, $rec, $args ){
		List($userID,$itemsID) = $args;
		$result = entryLogic::isApply($userID,$itemsID) ? "TRUE" : "FALSE";
		$this->addBuffer($result);
	}

    /**
     * 「この求人に応募した人はこんな求人にも応募しています」を描画する
     *
     * @param GUIManager $gm   GUIManagerオブジェクト
     * @param array      $rec  レコードデータ
     * @param array      $args 求人ID、求人種別
     *
     * @return void
     */
    function drawOtherEntryJobList(&$gm, $rec, $args)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        global $loginUserRank;
        // **************************************************************************************

        List($itemsID, $itemsType) = $args;
        $max = 5;

        $template = Template::getTemplate($loginUserType, $loginUserRank, $itemsType, 'OTHER_ENTRY_LIST');
        $jGM = GMList::getGM($itemsType);

        // 1. 指定の求人IDに対する応募履歴から、応募者IDリストを取得する
        $db = GMList::getDB($this->getType());
        $table = $db->getTable();
        $table = $db->searchTable($table, 'items_id', '=', $itemsID);
        $entryUserList = $db->getDataList($table, 'entry_user');

        // その条件に一致する求人情報はありませんでした。
        if (is_null($entryUserList)) {
            $buffer = $jGM->getString($template, null, 'failed');
            $this->addBuffer($buffer);
            return;
        }


        // 2. 取得した応募者IDリストで応募履歴を検索し、求人IDリストを取得する(指定の求人IDを除く)
        $table = $db->getTable();
        $table = $db->searchTable($table, 'entry_user', 'in', $entryUserList);
        $table = $db->searchTable($table, 'items_id',   '!',  $itemsID);
        $itemsIDList = $db->getDataList($table, 'items_id');

        // その条件に一致する求人情報はありませんでした。
        if (is_null($itemsIDList)) {
            $buffer = $jGM->getString($template, null, 'failed');
            $this->addBuffer($buffer);
            return;
        }


        // 3. 求人IDリストを閲覧可能な求人のみにする
        $jDB = GMList::getDB($itemsType);
        $jTable = JobLogic::getTable($itemsType);
        $jTable = $jDB->searchTable($jTable, 'id', 'in', $itemsIDList);
        $jTable = $jDB->sortTable($jTable, 'regist', 'desc');

        $jRow = $jDB->getRow($jTable);

        // 描画
        if ($jRow == 0) {
            $buffer = $jGM->getString($template, null, 'failed');
        } else {
            $buffer = $jGM->getString($template, null, 'head');
            for ($i = 0; $i < $jRow && $i < $max; $i++) {
                $jRec = $jDB->getRecord($jTable, $i);
                $buffer .= $jGM->getString($template, $jRec, 'list');
            }
            $buffer .= $jGM->getString($template, null, 'foot');
        }

        $this->addBuffer($buffer);
    }

    /**
     * 求人に応募可能かどうかを出力する
     *
     * @param job_id 求人ID
     * @return TRUE(応募可能)/LIMIT_OVER(定員オーバー)/NOT_PUBLISHED(閲覧権限なし)/
     *         APPLIED(応募済み)/NOT_GRANT_REGIST_PAGE_DESIGN(応募権限なし)
     */
    function canEntry(&$gm, $rec, $args)
    {
        List ($job_id) = $args;
        $result = entryLogic::checkApply(SystemUtil::getJobType($job_id), $job_id);

        if ($result === true) {
            $result = "TRUE";
        }

        $this->addBuffer($result);
    }

	function getType(){
		return "entry";
	}
}