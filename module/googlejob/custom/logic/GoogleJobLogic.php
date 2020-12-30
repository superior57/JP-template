<?php
final class GoogleJobLogic {
    private static $jobRecord = null; // 求人レコード
    private static $employemntList = null; // 雇用形態リスト id => employmentType

    /**
     * 求人レコードをセットする
     */
    public static function setJobRecord(array $rec) {
        self::$jobRecord = $rec;
    }

    /**
     * セットされた求人レコードを返す
     * レコードがセットされてなければ例外
     */
    public static function getJobRecord()
    {
        if(!self::existsJobRecord()) {
            throw new LogicException('求人レコードがセットされていません');
        }
        return self::$jobRecord;
    }

    /**
     * 求人レコードが存在するか？
     */
    public static function existsJobRecord()
    {
        return !is_null(self::$jobRecord);
    }

    /**
     * 雇用形態をjson文字列でセット
     */
    public static function setEmploymentByJson($json)
    {
        $list = json_decode($json, true);
        self::setEmployment(is_null($list) ? array() : $list);
    }

    /**
     * 雇用形態をセット
     */
    public static function setEmployment(array $list)
    {
        self::$employemntList = $list;
    }

    /**
     * 雇用形態をIDで取得
     */
    public static function getEmployment($id)
    {
        if(isset(self::$employemntList[$id])) {
            return self::$employemntList[$id];
        }
        return GoogleJobSettings::EMPLOYMENT_TYPE_OTHER;
    }

    /**
     * 雇用形態が存在するか？
     */
    public static function existsEmployment()
    {
        return !is_null(self::$employemntList);
    }
}
