<?php

include_once "./include/base/CheckDataBase.php";

/*******************************************************************************************************
 * <PRE>
 *
 * 入力内容チェッククラス
 *
 * @original 丹羽一智
 * @author   吉岡幸一郎
 * @version  2.0.0
 *
 * </PRE>
 *******************************************************************************************************/

class CheckData extends CheckDataBase
{
    public function __construct(&$gm, $edit, $loginUserType, $loginUserRank, $type)
    {
        parent::__construct($gm, $edit, $loginUserType, $loginUserRank, $type);
    }

    /*
     * 以下に拡張メソッドを記述
     */

    public function checkNullFlagGetParam($name, $args)
    {
        for ($i = 0; isset($_GET[$args[$i]]); $i += 2) {
            if ($_GET[$args[$i]] != $_GET[$args[$i + 1]]) {
                return $this->check;
            }
        }
        return call_user_func(array($this, 'check' . $args[2]), $name, array_slice($args, 3));
    }

    // 引数で指定した条件を見たす場合にチェック(複数条件指定可能
    public function checkNullInFlag($name, $args)
    {
        if (! isset($args[0]) || ! isset($args[1])) {
            return $this->check;
        } else {
            for ($i = 0; isset($args[$i]); $i += 2) {
                $ex = (array)$this->data[$args[$i]];
                if (! isset($ex) || ! in_array($args[$i+1], (array)$ex)) {
                    return $this->check;
                }
            }
        }
        return $this->checkNull($name, $args);
    }

    public function checkFreshLimit($name)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $LOGIN_ID;
        // **************************************************************************************

        $db = GMList::getDB("pay_job");
        $table = $db->getTable();
        $table = $db->searchtable($table, "owner",   "=", $LOGIN_ID);
        $table = $db->searchtable($table, "label",   "=", $this->data["label"]);
        $table = $db->searchtable($table, "pay_flg", "=", true);
        $table = $db->sortTable($table, "shadow_id", "desc");
        $table = $db->limitOffset($table, 0, 1);

        if ($db->existsRow($table)) {
            $rec = $db->getFirstRecord($table);

            $current_limit = date("Y", $db->getdata($rec, "limits"));
            switch ($this->data[$name]) {
            case "FT001":
                $yearly = SystemUtil::getYearly();
                break;
            case "FT002":
                $yearly = SystemUtil::getYearly("next");
                break;
            }
            if ($current_limit >= $yearly) {
                $this->addError($name . '_FRESH', null, $name);
            }
        }
        return $this->check;
    }

    public function checkKatakana($name, $args)
    {
        if (isset($this->data[$name]) && $this->data[$name] != null) {
            if (! preg_match("/^[ァ-ヶ]+$/u", $this->data[$name])) {
                $this->addError($name . '_KATAKANA', null, $name);
            }
        }
        return $this->check;
    }

    public function checkExistApply($name, $args)
    {
        $id = $this->data["id"];

        $db     = SystemUtil::getGMforType($_GET['type'])->getDB();
        $rec    = $db->selectRecord($id);
        $origin = $db->getData($rec, $name);

        if ($this->gm->colType[$name] == "boolean") {
            $lhs = SystemUtil::convertBool($origin);
            $rhs = SystemUtil::convertBool($_POST[$name]);
        } else {
            $lhs = $origin;
            $rhs = $_POST[$name];
        }
        $db = GMList::getDB("entry");
        $table = $db->getTable();
        $table = $db->searchTable($table, "items_id", "=", $id);
        if ($db->existsRow($table) && $lhs != $rhs) {
            $this->addError($name . '_EXIST_APPLY', null, $name);
        }
        return $this->check;
    }

    public function checkNullFile($name, $args)
    {
        if ($_FILES[$name]['error'] == UPLOAD_ERR_NO_FILE) {
            if (!$_POST[$name . '_filetmp']) {
                $this->addError($name . '_FILE_NULL');
            }
        }
        return $this->check;
    }

    //登録件数リミットチェック
    public function limitCheck($owner, $limit)
    {
        $db = $this->gm->getDB();
        $table = $db->getTable();
        $table = $db->searchTable($table, 'owner',      '=', $owner);
        $table = $db->searchTable($table, 'delete_flg', '=', false);
        $row = $db->getRow($table);

        if ($row >= $limit) {
            $this->addError($name . 'limit_check');
        }
        return $this->check;
    }

    public function checkOpenResume($name, $args)
    {
        $id = $this->data["id"];
        $owner = $this->data["owner"];
        $publish = $this->data["publish"];
        $db = $this->gm->getDB();
        $table = $db->getTable();
        $table = $db->searchTable($table, "owner",   "=", $owner);
        $table = $db->searchTable($table, "publish", "=", "on");
        $exists = $db->existsRow($table);

        $rec = $db->getFirstRecord($table);

        $open = $id == $db->getData($rec, "id");

        if (! $exists && $publish != "on") {
            $this->addError($name . "_open", null, $name);
        }

        if ($open && $publish == "off") {
            $this->addError($name . "_open", null, $name);
        }

        return $this->check;
    }

    // 同じメールアドレスで応募済みか、既に会員登録済みならばエラー
    public function checkIsApply4nb($name, $args)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $THIS_TABLE_IS_USERDATA;
        global $TABLE_NAME;
        global $gm;
        // **************************************************************************************

        $mail = $this->data['mail'];
        $itemsID = empty($_GET["mid_id"]) ? $_GET["fresh_id"] : $_GET["mid_id"];

        if (isset($mail)) {
            if (nobodyLogic::isApply($mail, $itemsID)) {
                $this->addError('mail_dup', null, $name);
            }

            // 既に会員登録済みかチェック
            $max = count($TABLE_NAME);
            for ($i = 0; $i < $max; $i++) {
                if ($THIS_TABLE_IS_USERDATA[$TABLE_NAME[$i]]) {
                    $db = $gm[$TABLE_NAME[$i]]->getDB();
                    $table = $db->getTable();
                    $table = $db->searchTable($table, 'mail', '=', $mail);
                    if ($db->existsRow($table)) {
                        $this->addError('mail_dup', null, $name);
                        break;
                    }
                }
            }
        }

        return $this->check;
    }
}
