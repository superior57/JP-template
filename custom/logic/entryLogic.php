<?php

class entryLogic
{
    // 求人に応募があるかどうか
    public static function existsApply($itemsID)
    {
        return Entry::getTotalEntry($itemsID) > 0;
    }

    // 指定の求人にユーザーが応募済みかどうか
    public static function isApply($userID, $itemsID)
    {
        return Entry::getTotalEntry($itemsID, $userID) > 0;
    }

    public static function getID($userID, $itemsID)
    {
        return Entry::getID($itemsID, $userID);
    }

    // statusカラムの値が正常かどうか
    public static function isStatus($status)
    {
        $db = GMList::getDB("entry_progress");
        $table = $db->getTable();
        $table = $db->searchTable($table, "id", "=", $status);
        return $db->existsRow($table);
    }

    public static function checkApply($type, $id)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        global $LOGIN_ID;
        // **************************************************************************************

        $db = GMList::getDB($type);
        $rec = $db->selectRecord($id);
        $check = true;

        if (! JobLogic::checkApply($type, $id)) {
            // 期限切れまたは定員を満たし応募不可
            return "LIMIT_OVER";
        }

        if (! JobLogic::checkDisp($type, $rec)) {
            // 閲覧権限なし
            return "NOT_PUBLISHED";
        }

        switch ($loginUserType) {
        case 'nUser':
            if (self::isApply($LOGIN_ID, $id)) {
                // 応募済み
                return "APPLIED";
            }
            break;
        case 'nobody':
            if (Conf::checkData('job', 'nobody_apply', 'off') || ! JobLogic::checkNobodyDisp($type, $rec)) {
                // 応募権限なし
                return "NOT_GRANT_REGIST_PAGE_DESIGN";
            }
            break;
        }
        return $check;
    }

    public static function paymentGift($id)
    {
        $db = GMList::getDB(self::getType());
        $rec = $db->selectRecord($id);
        $db->setData($rec, "gift", true);
        $db->updateRecord($rec);
    }

    public static function regist($itemsType, $itemsID, $itemsOwner, $entryUser, $messageID)
    {
        $db = GMList::getDB(self::getType());
        $rec = $db->getNewRecord();
        $db->setData($rec, "items_type",  $itemsType);
        $db->setData($rec, "items_id",    $itemsID);
        $db->setData($rec, "items_owner", $itemsOwner);
        $db->setData($rec, "entry_user",  $entryUser);
        $db->setData($rec, "status",      "START");
        $db->setData($rec, "message_id",  $messageID);
        $db->setData($rec, "gift",        0);
        $db->setData($rec, "regist",      time());
        $db->addRecord($rec);

        return $rec;
    }

    public function getType()
    {
        return "entry";
    }
}
