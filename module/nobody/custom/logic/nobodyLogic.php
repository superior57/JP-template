<?php
class nobodyLogic
{
    public static $registSessionName = "nbID";

    /**
     * 非会員応募に使用したメールアドレス、求人IDから非会員IDを取得する
     *
     * @param string $mail    メールアドレス
     * @param string $itemsID 求人ID
     *
     * @return string 非会員ID
     */
    public static function getID($mail, $itemsID)
    {
        $db = GMList::getDB(self::getType());
        $table = $db->getTable();
        $table = $db->searchTable($table, "mail", "=", $mail);
        $ids = $db->getDataList($table, 'id');

        if ($db->getRow($table) == 1) {
            $rec = $db->getFirstRecord($table);
            return $db->getData($rec, "id");
        } elseif ($db->existsRow($table)) { // 非会員ユーザーが複数の求人に応募している場合
            $eDB = GMList::getDB('entry');
            $eTable = $eDB->getTable();
            $eTable = $eDB->searchTable($eTable, "entry_user", "in", $ids);
            $eTable = $eDB->searchTable($eTable, "items_id",   "=",  $itemsID);

            $rec = $eDB->getFirstRecord($eTable);
            return $eDB->getData($rec, "entry_user");
        }
        return false;
    }

    //モジュールを未使用時の処理
    public static function invalidModuleSearch($db, &$table)
    {
        $table = $db->searchTable($table, "owner", "!", "NB%");
    }

    //応募情報を使って会員情報をセットする
    public static function registInit($id)
    {
        $db = GMList::getDB(self::getType());
        $rec = $db->selectRecord($id);
        if (!$rec) {
            return ;
        }
        foreach ($rec as $key => $val) {
            $_GET[$key] = $val;
        }
    }

    /**
     * 非会員がお祝い金申請時、申請フォームのURLを非会員にメールで通知
     *
     * @param string $giftID お祝い金ID
     *
     * @return void
     */
    public function giftActivate($giftID)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $MAILSEND_ADDRES;
        global $MAILSEND_NAMES;
        global $loginUserType;
        global $loginUserRank;
        // **************************************************************************************

        $gm = GMList::getGM("gift");
        $db = $gm->getDB();
        $rec = $db->selectRecord($giftID);
        $owner = $db->getData($rec, "owner");
        $mail = SystemUtil::getTableData("nobody", $owner, "mail");

        $hash = sha1($giftID . $mail);
        $gm->setVariable("hash", $hash);

        $design = Template::getTemplate($loginUserType, $loginUserRank, 'gift', 'GIFT_ACTIVATE_MAIL');
        Mail::send($design, $MAILSEND_ADDRES, $mail, $gm, $rec, $MAILSEND_NAMES);
    }

    //アクティベートコードをチェックする
    public static function giftActivateCheck($giftID, $md5)
    {
        $db = GMList::getDB("gift");
        $rec = $db->selectRecord($giftID);
        $owner = $db->getData($rec, "owner");
        $mail = SystemUtil::getTableData("nobody", $owner, "mail");

        $hash = sha1($giftID . $mail);
        return $hash == $md5;
    }

    //応募情報から履歴書データを登録する
    public static function registResume($nID, $rec)
    {
        $nbDB = GMList::getDB("nobody");

        $db = GMList::getDB("resume");
        $db->setData($rec, "owner",   $nID);
        $db->setData($rec, "label",   "標準");
        $db->setData($rec, "adds_id", $nbDB->getData($rec, "adds"));
        $db->setData($rec, "publish", "on");
        $db->addRecord($rec);
    }

    //セッションにnbIDをセット
    public static function setNBID($nbID)
    {
        $_SESSION[self::$registSessionName] = $nbID;
    }

    //セッションからnbIDを破棄
    public static function clearNBID()
    {
        if (! empty($_SESSION[self::$registSessionName])) {
            unset($_SESSION[self::$registSessionName]);
        }
    }

    //LOGIN_IDにnobodyのidをセットする
    public static function setUserID($userID)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $LOGIN_ID;
        global $loginUserType;
        // **************************************************************************************

        if ($loginUserType == "nobody") {
            $LOGIN_ID = $userID;
        }
    }

    //LOGIN_IDにnullをセット
    public static function resetUserID()
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        global $LOGIN_ID;
        // **************************************************************************************

        if ($loginUserType == "nobody") {
            $LOGIN_ID = null;
        }
        unset($_SESSION["giftID"]);
        unset($_SESSION["nobodyID"]);
    }

    public static function searchEntry($db, $table, $param)
    {
        $noDB = GMList::getDB("nobody");
        $noTable = $noDB->getTable();
        $noTable = $noDB->searchTable($noTable, "name", "=", "%{$param["entry_user_name"]}%");
        $noData = $noDB->getDataList($noTable, "id");

        $nDB = GMList::getDB("nUser");
        $nTable = $nDB->getTable();
        $nTable = $nDB->searchTable($nTable, "name", "=", "%{$param["entry_user_name"]}%");
        $nData = $nDB->getDataList($nTable, "id");

        $noData = is_null($noData) ? array() : $noData;
        $nData = is_null($nData) ? array() : $nData;
        $users = array_merge($noData, $nData);
        if (count($users) == 0) {
            return $db->getEmptyTable();
        }

        return $db->searchTable($table, "entry_user", "in", $users);
    }

    // 指定の求人にユーザーが応募済みかどうか
    public static function isApply($mail, $itemsID)
    {
        $userID = self::getID($mail, $itemsID);
        if (! $userID) {
            return false;
        }
        return entryLogic::isApply($userID, $itemsID);
    }

    public function getType()
    {
        return "nobody";
    }
}
