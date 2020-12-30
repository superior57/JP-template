<?php

class clipLogic
{

    /**
     * レコードを削除
     *
     * @param c_type 検討中リストの対象テーブル名
     * @param c_id 検討中リストの対象ID
     */
    static function delete($c_type, $c_id)
    {
        $user_id = self::getUserId();
        
        $db = GMList::getDB(self::getType());
        $rec = self::getRecord($user_id, $c_type, $c_id);
        if (isset($rec)) {
            $db->deleteRecord($rec);
        }
    }

    /**
     * レコードを取得
     *
     * @param user_id 検討中リストの所有ユーザーID
     * @param c_type 検討中リストの対象テーブル名
     * @param c_id 検討中リストの対象ID
     * @return rec レコードデータ
     */
    function getRecord($user_id, $c_type, $c_id)
    {
        $db = GMList::getDB(self::getType());
        
        $table = $db->getTable();
        $table = $db->searchTable($table, 'user_id', '=', $user_id);
        $table = $db->searchTable($table, 'c_type', '=', $c_type);
        $table = $db->searchTable($table, 'c_id', '=', $c_id);
        
        $rec = null;
        if ($db->getRow($table) > 0) {
            $rec = $db->getRecord($table, 0);
        }
        
        return $rec;
    }

    /**
     * ユーザーIDを取得、非会員の検討中リストは非対応
     *
     * @return id ユーザーID
     */
    function getUserId()
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $LOGIN_ID;
        global $loginUserType;
        global $NOT_LOGIN_USER_TYPE;
        // **************************************************************************************
        
        $id = "none";
        if ($loginUserType != $NOT_LOGIN_USER_TYPE) {
            $id = $LOGIN_ID;
        }
        return $id;
    }

    function getType()
    {
        return "clip";
    }
}