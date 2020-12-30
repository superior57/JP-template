<?php

class JobLogic
{
    /**
     * 初期化条件をセットしたテーブルを返す
     *
     * @param string $type     求人種別
     * @param Table  $table    テーブルデータ
     * @param array  $param    検索パラメータ
     * @param string $userType ユーザー種別
     *
     * @return Table $table 条件をセットしたテーブル
     */
    public static function getTable($type, $table = null, $param = null, $userType = null)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $LOGIN_ID;
        global $loginUserType;
        global $magic_quotes_gpc;
        // **************************************************************************************

        if ($loginUserType == "admin" && $_GET["authority"]) {
            $loginUserType = $_GET["authority"];
        }

        $gm = GMList::getGM($type);
        $db = $gm->getDB();

        if (! isset($table)) {
            $table = $db->getTable();
        }
        if (! isset($userType)) {
            $userType = $loginUserType;
        }

        if (isset($param)) {
            $sr = new Search($gm, $type);

            if ($magic_quotes_gpc) {
                $sr->setParamertorSet($param);
            } else {
                $sr->setParamertorSet(addslashes_deep($param));
            }

            $table = $sr->getResult();
        }

        $table = self::searchLimit($db, $table, $param, $userType);          // 有効期限をチェック
        $table = self::searchPublish($db, $table, $param, $userType, $type); // ユーザーの有効性をチェック
        $table = self::searchHistry($db, $table, $param);                    // 閲覧履歴から絞込み
        $table = self::searchSystemPublish($db, $table, $param, $userType);  // 非会員に対する公開設定の絞込み
        $table = self::searchArea($db, $table, $param);

        switch ($userType) {
        case 'cUser':
            $table = $db->searchTable($table, 'owner',      '=', $LOGIN_ID);
            $table = $db->searchTable($table, 'delete_flg', '=', false);
            break;
        case 'nUser':
        case 'nobody':
            // 管理者、クライアントの公開フラグチェック
            $table = $db->searchTable($table, 'activate',   '=', 4);
            $table = $db->searchTable($table, 'publish',    '=', 'on');
            $table = $db->searchTable($table, 'delete_flg', '=', false);

            // 応募上限チェック
            $table = $db->searchTable($table, 'apply_pos',  '=', false);
            break;
        }

        return $table;
    }

    public function searchSystemPublish($db, $table, $param, $userType)
    {
        if ($userType != 'nobody') {
            return $table;
        }

        switch (Conf::getData('job', 'publish')) {
        case 'all':
            break;
        case 'nuser':
            // 未ログイン時一覧に非表示の場合のみ絞り込み
            if (Conf::getData('job', 'nuser_disp') != 'on') {
                $table = $db->getEmptyTable();
            }
            break;
        case 'select':
            // 未ログイン時一覧に非表示の場合のみ絞り込み
            if (Conf::getData('job', 'nuser_disp') != 'on') {
                $table = $db->searchTable($table, 'limitation', '=', false);
            }
            break;
        }

        return $table;
    }

    /**
     * 入力値を元にパラメータを生成しセット
     *
     * @param Database $db   DBオブジェクト
     * @param array    $rec  レコードデータ
     * @param string   $type 求人種別
     *
     * @return void
     */
    public function setParam($db, &$rec, $type)
    {
        $db->setData($rec, 'edit', time());

        $charges = cUserLogic::getJobCharges($db->getData($rec, 'owner'), $_GET["type"]);
        if ($charges != 'malti') {
            $db->setData($rec, 'term_type', $charges);
        }

        // 応募上限到達フラグをセット
        Job::setApplyPos($db, $rec);

        // 求人の特徴の名称を検索用に保存
        $additionName = SystemUtil::getNameList('job_addition', $db->getData($rec, 'job_addition'));
        $job_addition_label = '';
        if (is_array($additionName)) {
            $job_addition_label = implode("/", $additionName);
        }
        $db->setData($rec, 'job_addition_label', $job_addition_label);
    }


    /**
     * 電車関連の検索条件をセットして返す
     *
     * @param Database $db    DBオブジェクト
     * @param Table    $table テーブルデータ
     * @param array    $param 検索パラメータ
     *
     * @return Table $table 検索条件をセットしたテーブル
     */
    public static function searchTrain($db, $table, $param)
    {
        $tmp = $db->getTable();

        if (strlen($param['traffic_station'])) {
            // 駅が指定されている場合
            $table1 = $db->searchTable($tmp, 'traffic1_station', '=', $param['traffic_station']);
            $table2 = $db->searchTable($tmp, 'traffic2_station', '=', $param['traffic_station']);
            $table3 = $db->searchTable($tmp, 'traffic3_station', '=', $param['traffic_station']);
            $table4 = $db->searchTable($tmp, 'traffic4_station', '=', $param['traffic_station']);
            $table5 = $db->searchTable($tmp, 'traffic5_station', '=', $param['traffic_station']);

            $table1 = $db->orTable($table1, $table5);
            $table2 = $db->orTable($table2, $table4);
            $tmpTable  = $db->orTable($table1, $db->orTable($table2, $table3));

            $table = $db->andTable($table, $tmpTable);
        } elseif (strlen($param['traffic_line'])) {
            // 路線までしか指定されていない場合
            $table1 = $db->searchTable($tmp, 'traffic1_line', '=', $param['traffic_line']);
            $table2 = $db->searchTable($tmp, 'traffic2_line', '=', $param['traffic_line']);
            $table3 = $db->searchTable($tmp, 'traffic3_line', '=', $param['traffic_line']);
            $table4 = $db->searchTable($tmp, 'traffic4_line', '=', $param['traffic_line']);
            $table5 = $db->searchTable($tmp, 'traffic5_line', '=', $param['traffic_line']);

            $table1 = $db->orTable($table1, $table5);
            $table2 = $db->orTable($table2, $table4);
            $tmpTable  = $db->orTable($table1, $db->orTable($table2, $table3));

            $table = $db->andTable($table, $tmpTable);
        }

        return $table;
    }


    public static function searchArea($db, $table, $param)
    {
        if ($param['areaID'][0]==null) {
            return $table;
        }

        $adds = array();
        $adds = Area::getAddsData($param['areaID']);

        foreach ($adds as $add) {
            $_GET['addsID'][] = $add;
        }

        return $db->searchTable($table, 'work_place_adds', 'in', $adds);
    }

    public static function searchLimit($db, $table, $param, $userType)
    {
        if (in_array($userType, array("nUser","nobody"))) {
            $onTable  = $db->searchTable($table,   'use_limit_time_apply', '=', true);
            $onTable  = $db->searchTable($onTable, 'limits',               '>', time());
            $offTable = $db->searchTable($table,   'use_limit_time_apply', '=', false);
            return $db->orTable($onTable, $offTable);
        }

        if (isset($param['limit'])) {
            switch ($param['limit'][0]) {
            case 'on':
                $onTable  = $db->searchTable($table,   'use_limit_time_apply', '=', true);
                $onTable  = $db->searchTable($onTable, 'limits',               '>', time());
                $offTable = $db->searchTable($table,   'use_limit_time_apply', '=', false);
                $table    = $db->orTable($onTable, $offTable);
                break;
            case 'off':
                $table  = $db->searchTable($table, 'use_limit_time_apply', '=', true);
                $table  = $db->searchTable($table, 'limits',               '<', time());
                break;
            }
        }

        return $table;
    }

    public static function searchPublish($db, $table, $param, $userType, $type)
    {
        $search = false;

        switch ($userType) {
        case "admin":
        case "cUser":
            if ($param['publish_now'] == "on") {
                $search = true;
            }
            break;
        default:
            $search = true;
            break;
        }
        if ($search) {
            $table = $db->searchTableSubQuery($table, "owner", 'in', cUserLogic::getActivateIdTable($userType, $type));
        }

        return $table;
    }

    public static function searchHistry($db, $table, $param)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        // **************************************************************************************

        if (! in_array($loginUserType, array("nUser","nobody"))) {
            return $table;
        }

        if ($param["history"] == "on") {
            $table = $db->searchTable($table, 'id', 'in', explode(",", $_COOKIE[viewMode::getViewMode()]));
        }

        return $table;
    }

    public static function sortTotal($db, $table, $param)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        // **************************************************************************************

        if (! $loginUserType == "admin") {
            return $table;
        }

        if ($param["sortEx"] == "PV") {
            $iDB = GMList::getDB("count");
            $iTable = $iDB->getTable();

            $iTable = $iDB->searchTable($iTable, 'c_type', '=', $param['type']);
            $iTable = $iDB->searchTable($iTable, 'total', '>', 0);
            $table = $db->outerJoinTableSubQuery("left", $table, $iTable, "count", "id", "owner");

            $table = $db->sortTable($table, "total", "desc");
        }
        return $table;
    }

    /**
     * 応募可能かどうかを返す
     *
     * @param string $type 求人種別
     * @param string $id   求人ID
     *
     * @return boolean $result 応募可能な場合true
     */
    public function checkApply($type, $id)
    {
        $result = true;

        // 応募最大数に達していないか確認
        $result = self::checkApplyNumber($type, $id);

        if ($result) {
            // 有効期限が切れていないか確認
            $result = self::checkLimit($type, $id);
        }

        return $result;
    }

    /**
     * 応募上限に達していないかを返す
     *
     * @param string $type 求人種別
     * @param string $id   求人ID
     *
     * @return boolean $result 応募可能な場合true
     */
    public function checkApplyNumber($type, $id)
    {
        $rec = Job::getRecord($type, $id);
        if (! isset($rec)) {
            return false;
        }

        $db  = GMList::getDB($type);
        $result = true;

        if (SystemUtil::convertBool($db->getData($rec, 'use_max_apply'))) {
            $result = $db->getData($rec, 'max_apply') > Entry::getWaitApplicant($id);
        }

        return $result;
    }

    /**
     * 期限が切れていないかを返す
     *
     * @param string $type 求人種別
     * @param string $id   求人ID
     *
     * @return boolean $result 応募可能な場合true
     */
    public function checkLimit($type, $id)
    {
        $rec = Job::getRecord($type, $id);

        if (! isset($rec)) {
            return false;
        }

        $db  = GMList::getDB($type);

        $result = true;

        if ($db->getData($rec, 'use_limit_time_apply')) {
            if (time() > $db->getData($rec, 'limits')) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * 求人が登録上限に達していないかチェックする
     *
     * @param string $owner 求人オーナーID
     *
     * @return boolean $result 応募可能な場合true
     */
    public function checkRegistMax($owner)
    {
        $result = true;

        $max = Conf::getData('job', 'max');
        if ($max > 0) {
            $db = GMList::getDB($_GET["type"]);

            $table = $db->getTable();
            $table = $db->searchTable($table, 'owner', '=', $owner);
            $table = $db->searchTable($table, 'delete_flg', '=', false);

            $row = $db->getRow($table);
            $result = ($row < $max);
        }

        return $result;
    }

    /**
     * 閲覧権限をチェック
     *
     * @param string $type 求人種別
     * @param array  $rec  求人レコードデータ
     *
     * @return boolean $result 応募可能な場合true
     */
    public function checkDisp($type, $rec)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        global $LOGIN_ID;
        global $ACTIVE_ACCEPT;
        // **************************************************************************************

        $db = GMList::getDB($type);
        $id = $db->getData($rec, "id");
        $owner = $db->getData($rec, "owner");
        $result = true;
        switch ($loginUserType) {
        case 'admin':
            break;
        case 'cUser':
            if ($db->getData($rec, 'owner') != $LOGIN_ID) {
                $result = false;
            }
            break;
        default:
            $existsSocut = messageLogic::getScoutCnt($owner, $LOGIN_ID, $id) > 0;
            if ($db->getData($rec, 'publish') != "on" && !$existsSocut) {
                $result = false;
            } elseif ($db->getData($rec, 'use_limit_time_apply') && $db->getData($rec, 'limits') < time()) {
                $result = false;
            } elseif (SystemUtil::getTableData('cUser', $db->getData($rec, 'owner'), 'activate') != $ACTIVE_ACCEPT) {
                $result = false;
            }
            break;
        }

        return $result;
    }

    /**
     * 会員限定関関連の閲覧権限をチェック
     *
     * @param string $type 求人種別
     * @param array  $rec  求人レコードデータ
     *
     * @return boolean $result 応募可能な場合true
     */
    public function checkNobodyDisp($type, $rec)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        // **************************************************************************************

        $result = true;
        if ($loginUserType != 'nobody') {
            return $result;
        }

        switch (Conf::getData('job', 'publish')) {
        case 'all':
            break;
        case 'nuser':
            $result = false;
            break;
        case 'select':
            $db = GMList::getDB($type);
            $result = !$db->getData($rec, 'limitation');
            break;
        }

        return $result;
    }

    /**
     * 応募情報に控えるパラメータを返す
     *
     * @param string $type 求人種別
     * @param string $id   求人ID
     *
     * @return array $param パラメータ配列
     */
    public function getParamForEntry($type, $id)
    {
        $db = GMList::getDB($type);
        $rec = $db->selectRecord($id);

        $colList = array('owner', 'term_type', 'work_style');
        foreach ($colList as $col) {
            $param[$col] = $db->getData($rec, $col);
        }

        $charges = cUserLogic::getJobCharges($db->getData($rec, 'owner'), $type);
        if ($charges != 'malti') {
            $param['term_type'] = $charges;
        }

        return $param;
    }

    /**
     * 指定した求人情報の応募上限到達フラグを更新する
     *
     * @param string $type 求人種別
     * @param string $id   求人ID
     *
     * @return void
     */
    public static function updateApplyPos($type, $id)
    {
        $jDB = GMList::getDB($type);
        $jRec = $jDB->selectRecord($id);
        Job::setApplyPos($jDB, $jRec);
        $jDB->updateRecord($jRec);
    }

    //案件テーブルのおすすめ決済を無効にする
    public static function cancelAttention($type, $id)
    {
        $jDB = GMList::getDB($type);
        $jRec = $jDB->selectRecord($id);
        $jDB->setData($jRec, "attention", false);
        $jDB->setData($jRec, "attention_time", 0);
        $jDB->updateRecord($jRec);
    }

    public static function searchFreeword($db, $table, $param)
    {
        // フリーワードが指定されている場合
        if (strlen($param['free'])) {
            $freeList = explode(' ', str_replace('　', ' ', $param['free']));
            foreach ($freeList as $free) {
                $free = '%' . $free . '%';

                $table1 = $db->searchTable($table, 'id', '=', $free);
                $table2 = $db->searchTable($table, 'name', '=', $free);
                $table3 = $db->searchTable($table, 'work_place_label', '=', $free);
                $table4 = $db->searchTable($table, 'job_pr', '=', $free);
                $table5 = $db->searchTable($table, 'salary_label', '=', $free);
                $table6 = $db->searchTable($table, 'work_detail', '=', $free);
                $table7 = $db->searchTable($table, 'apply_detail', '=', $free);
                $table8 = $db->searchTable($table, 'transport', '=', $free);

                $cDB = GMList::getDB('cUser');
                $cTable = $cDB->getTable();
                $cTable = $cDB->searchTable($cTable, 'name', '=', $free);
                $idList = $cDB->getDataList($cTable, 'id');

                $table1 = $db->orTable($table1, $table8);
                $table2 = $db->orTable($table2, $table7);
                $table3 = $db->orTable($table3, $table6);
                $table4 = $db->orTable($table4, $table5);

                if (isset($idList)) {
                    $table9 = $db->searchTable($table, 'owner', 'in', $idList);
                    $tmpTable[] = $db->orTable($table1, $db->orTable($table2, $db->orTable($table3, $db->orTable($table4, $table9))));
                } else {
                    $tmpTable[] = $db->orTable($table1, $db->orTable($table2, $db->orTable($table3, $table4)));
                }
            }

            while (count($tmpTable) > 0) {
                $table = $db->andTable($table, array_shift($tmpTable));
            }
        }

        return $table;
    }

    function isHandle($type)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        // **************************************************************************************

        // 求人情報の設定で、求人サービス取扱対象で無い場合はエラー
        if ($loginUserType != 'admin') {
            $typeCheck = Conf::getData("job", "type_check");
            $service = explode('/', $typeCheck);
            return in_array($type, $service);
        }
        return true;
    }

	/**
	 * 掲載期限とおすすめ掲載の日時を 23:59:59 に調整
	 * @param type $rec
	 */
	function AdjustDayEndTime($rec){

		$db = GMList::getDB($_GET['type']);
		$adjust = FALSE;
		if($db->getData($rec, 'use_limit_time_apply')!=0){
			$lt = $db->getData($rec,'limits');
			if($lt != 0){
				$lt = SystemUtil::createEpochTime($lt, 'de');
				$db->setData($rec, 'limits', $lt);
				$adjust = TRUE;
			}
		}
		if($db->getData($rec, 'attention')!=0){
			$at = $db->getData($rec,'attention_time');
			if($at != 0){
				$at = SystemUtil::createEpochTime($at, 'de');
				$db->setData($rec, 'attention_time', $at);
				$adjust = TRUE;
			}
		}
		if($adjust){
			$db->updateRecord($rec);
		}
		return;
	}
}
