<?php

class indeed_feedLogic
{
    /**
     * 現在サイト上に掲載中の求人情報から、Indeed XMLフィードを生成する
     *
     * @return void
     */
    public static function updateIndeedFeed()
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $CONF_FEED_ENABLE;       // フィードを更新する場合はtrue
        global $CONF_FEED_TABLES;       // フィードを生成するテーブル種別
        global $CONF_FEED_OUTPUT_DIR;   // フィードを出力するディレクトリ
        // **************************************************************************************

        self::updateCreated();

        $fp = fopen($CONF_FEED_OUTPUT_DIR . "indeed.xml", 'wb');
        if ($fp) {
            $gm = GMList::getGM(self::getType());
            $template = Template::getTemplate('nobody', 1, self::getType(), "INDEED_FEED_DESIGN");

            // ヘッダー
            fputs($fp, $gm->getString($template, null, 'head'));

            // 中途と新卒を分けないと、なぜか中途が2回描画される
            $mGM = GMList::getGM("mid");
            $mDB = $mGM->getDB();
            $mTable = JobLogic::getTable("mid", $mTable, null, "nobody");
            $mRow = $mDB->getRow($mTable);
            $mGM->setVariable("TYPE", "mid");
            for ($i = 0; $mRow > $i; ++ $i) {
                $mRec = $mDB->getRecord($mTable, $i);
                fputs($fp, $mGM->getString($template, $mRec, "list"));
            }

            // 新卒求人
            $fGM = GMList::getGM("fresh");
            $fDB = $fGM->getDB();
            $fTable = JobLogic::getTable("fresh", $fTable, null, "nobody");
            $fRow = $fDB->getRow($fTable);
            $fGM->setVariable("TYPE", "fresh");
            for ($i = 0; $fRow > $i; ++ $i) {
                $fRec = $fDB->getRecord($fTable, $i);
                fputs($fp, $fGM->getString($template, $fRec, "list"));
            }

            // フッター
            fputs($fp, $gm->getString($template, null, 'foot'));
            fclose($fp);
        }
    }

    // フィード作成日時を更新
    private function updateCreated()
    {
        $db = GMList::getDB(self::getType());
        $rec = $db->selectRecord("ADMIN");
        $db->setData($rec, "created", time());
        $db->updateRecord($rec);
    }

    private function getType()
    {
        return "indeed_feed";
    }
}
