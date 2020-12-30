<?php

class nobodySystem extends System
{
    public function drawRegistForm(&$gm, $rec, $loginUserType, $loginUserRank)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $LOGIN_ID;
        // **************************************************************************************

        $this->setErrorMessage($gm[ $_GET['type'] ]);

        $jobID = empty($_GET["mid_id"]) ? $_GET["fresh_id"] : $_GET["mid_id"];

        // 応募出来るかチェック
        $check = entryLogic::checkApply(SystemUtil::getJobType($jobID), $jobID);
        $label = $check === true ? "REGIST_FORM_PAGE_DESIGN" : $check;

        if (! empty($_GET["mid_id"])) {
            $query["mid_id"] = $_GET["mid_id"];
        }
        if (! empty($_GET["fresh_id"])) {
            $query["fresh_id"] = $_GET["fresh_id"];
        }

        if ('normal' == WS_SYSTEM_SYSTEM_FORM_ACTON) {
            $action = 'index.php?app_controller=register&type=' . $_GET['type'] . "&" . http_build_query((array)$query);
        } elseif ('index' == WS_SYSTEM_SYSTEM_FORM_ACTON) {
            $action = 'index.php?app_controller=register&type=' . $_GET['type'] . "&" . http_build_query((array)$query);
        } else {
            $action = ' ';
        }

        switch ($_GET['type']) {
        default:
            // 汎用処理
            if ($gm[$_GET['type']]->maxStep >= 2) {
                Template::drawTemplate($gm[ $_GET['type'] ], $rec, $loginUserType, $loginUserRank, $_GET['type'], $label . $_POST['step'], $action);
            } else {
                Template::drawTemplate($gm[ $_GET['type'] ], $rec, $loginUserType, $loginUserRank, $_GET['type'], $label, $action);
            }
            break;
        }
    }

    public function drawRegistCheck(&$gm, $rec, $loginUserType, $loginUserRank)
    {
        if (! empty($_GET["mid_id"])) {
            $query["mid_id"] = $_GET["mid_id"];
        }
        if (! empty($_GET["fresh_id"])) {
            $query["fresh_id"] = $_GET["fresh_id"];
        }

        if ('normal' == WS_SYSTEM_SYSTEM_FORM_ACTON) {
            $action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ] . "&" . http_build_query((array)$query);
        } elseif ('index' == WS_SYSTEM_SYSTEM_FORM_ACTON) {
            $action = 'index.php?app_controller=register&type=' . $_GET[ 'type' ] . "&" . http_build_query((array)$query);
        } else {
            $action = ' ';
        }

        switch ($_GET['type']) {
        default:
            // 汎用処理
            Template::drawTemplate($gm[$_GET['type']], $rec, $loginUserType, $loginUserRank, $_GET['type'], 'REGIST_CHECK_PAGE_DESIGN', $action);
            break;
        }
    }

    public function registCheck(&$gm, $edit, $loginUserType, $loginUserRank)
    {
        // チェック処理
        self::$checkData->generalCheck($edit);
        $data = self::$checkData->getData();

        if (! $edit) {
            self::$checkData->checkNull("sub", array());
            self::$checkData->checkNull("message", array());
        }

        // エラー内容取得
        return self::$checkData->getCheck() && parent::registCheck($gm, $edit, $loginUserType, $loginUserRank);
    }


    public function registComp(&$gm, &$rec, $loginUserType, $loginUserRank)
    {
        $db = $gm[$_GET["type"]]->getDB();
        $nobodyID = $db->getData($rec, "id");

        $jobID = empty($_GET["mid_id"]) ? $_GET["fresh_id"] : $_GET["mid_id"];
        $jobType = SystemUtil::getJobType($jobID);
        $jobOwner = SystemUtil::getTableData($jobType, $jobID, "owner");

        $thread_id = threadLogic::getThreadID($jobOwner, $nobodyID);
        $sub = h($_POST["sub"]);
        $message = h($_POST["message"]);
        $file = "index.php?app_controller=info&type=nobody&id=" . $nobodyID;

        $messageID = messageLogic::regist($thread_id, "entry", $sub, $message, $file, $nobodyID);
        $entryRec = entryLogic::regist($jobType, $jobID, $jobOwner, $nobodyID, $messageID);

        pay_jobLogic::addApplyLog($entryRec);
        JobLogic::updateApplyPos($jobType, $jobID);

        MailLogic::EntryNotice("nobody", $entryRec);

        nobodyLogic::setNBID($nobodyID);

        // お祝い金申請をエントリー時からでもできるように
        $entryID = Entry::getID($jobID, $nobodyID);
        $termType = SystemUtil::getTableData($jobType, $jobID, "term_type");

        if (Conf::checkData("charges", "gift", $termType)) {
            $itemsForm = SystemUtil::getTableData($jobType, $jobID, "work_style");
            $giftCost = SystemUtil::getTableData("items_form", $itemsForm, "gift");

            GiftLogic::regist($nobodyID, $entryID, $giftCost);
        }
    }
}
