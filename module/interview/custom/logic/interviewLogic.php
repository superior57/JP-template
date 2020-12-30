<?php

class interviewLogic
{

    static function getTable($db = null, $table = null, $param = null, $userType = null)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        global $ACTIVE_ACCEPT;
        // **************************************************************************************
        
        if (is_null($db)) {
            $db = GMList::getDB(self::getType());
        }
        if (is_null($table)) {
            $table = $db->getTable();
        }
        if (is_null($userType)) {
            $userType = $loginUserType;
        }
        if (is_null($param)) {
            $param = array();
        }
        
        $table = self::searchPublish($db, $table, $param, $userType); // ユーザーの有効性をチェック
        
        switch ($loginUserType) {
            case "admin":
                break;
            default:
                $table = $db->searchTable($table, 'activate', '=', $ACTIVE_ACCEPT);
                break;
        }
        return $table;
    }

    static function searchPublish($db, $table, $param, $userType)
    {
        $search = false;
        
        switch ($userType) {
            case "admin":
                break;
            default:
                $search = true;
        }
        
        if ($search) {
            $table = $db->searchTableSubQuery($table, "owner", 'in', cUserLogic::getActivateIdTable($userType, null));
        }
        
        return $table;
    }

    private function getType()
    {
        return "interview";
    }
}