<?php

cron_master::setCron('attentionNoticeMid',   'noticeCron', 'attentionMid');
cron_master::setCron('attentionNoticeFresh', 'noticeCron', 'attentionFresh');
cron_master::setCron('userLimitNoticeMid',   'noticeCron', 'userLimitMid');
cron_master::setCron('userLimitNoticeFresh', 'noticeCron', 'userLimitFresh');

class noticeCron
{

    function userLimitMid()
    {
        self::userLimitCommon("mid");
    }

    function userLimitFresh()
    {
        self::userLimitCommon("fresh");
    }

    /**
     * 中途/新卒利用期間課金の契約切れ手前のものを、企業と管理者にメールで通知
     *
     * @param string $type 求人種別
     *
     * @return void
     */
    function userLimitCommon($type)
    {
        // 課金の設定で「利用期間課金：使用する」に設定されているか
        if (Conf::checkData("charges", "user_limit", "off")) {
            return;
        }

        // 課金の設定で「利用期間の更新案内通知」にチェックがあるか
        $conf = Conf::getData("charges", "user_limit_ad_notice");
        if (empty($conf)) {
            return;
        }
        $dayList = explode("/", $conf);

        $db = GMList::getDB("pay_job");
        $table = pay_jobLogic::getLsatTerm($type);

        $time   = time();
        $y      = date('Y', $time);
        $m      = date('m', $time);
        $d      = date('d', $time);

        if (strlen($dayList[0]) == 0) {
            return;
        }

        foreach ($dayList as $day) {
            // 契約テーブルから、利用期限が◯日前の契約情報を抽出
            $limitTable = DateUtil::setSearchDay($db, $table, $y, $m, $d + $day, 'limits');
            if ($db->existsRow($limitTable)) {
                MailLogic::userLimitNotice($type, $limitTable, $day);
            }
        }
    }

    function attentionMid()
    {
        self::attentionCommon("mid");
    }

    function attentionFresh()
    {
        self::attentionCommon("fresh");
    }

    /**
     * 中途/新卒求人のおすすめ掲載で契約切れ手前のものを、企業と管理者にメールで通知
     *
     * ※ 契約テーブルは見ていないので、企業がおすすめ掲載申請をせず、
     *    管理者がおすすめ掲載設定をした場合も通知対象となる。
     *
     * @param string $type 求人種別
     *
     * @return void
     */
    function attentionCommon($type)
    {
        // 課金の設定で「おすすめ掲載：使用する」に設定されているか
        if (Conf::checkData("charges", "attention", "off")) {
            return;
        }

        // 課金の設定で「おすすめ課金の更新案内通知」にチェックがあるか
        $conf = Conf::getData("charges", "attention_ad_notice");
        if (empty($conf)) {
            return;
        }
        $dayList = explode("/", $conf);

        $db = GMList::getDB($type);
        $table = JobLogic::getTable($type);

        $time   = time();
        $y      = date('Y', $time);
        $m      = date('m', $time);
        $d      = date('d', $time);

        if (strlen($dayList[0]) == 0) {
            return;
        }

        foreach ($dayList as $day) {
            // 求人テーブルから、利用期限が◯日前の求人情報を抽出
            $limitTable = DateUtil::setSearchDay($db, $table, $y, $m, $d + $day, 'attention_time');
            if ($db->existsRow($limitTable)) {
                // 通知
                MailLogic::attentionAdNotice($type, $limitTable, $day);
            }
        }
    }

}
