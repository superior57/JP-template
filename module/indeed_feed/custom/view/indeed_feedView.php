<?php

class indeed_feedView extends command_base
{
    /**
     * Indeed XMLフィードの作成日時をGMTで表示する
     *
     * @param GUIManager $gm   GUIManagerオブジェクト
     * @param array      $rec  レコードデータ
     * @param array      $args タイムスタンプ
     *
     * @return void
     */
    public function drawGMT(&$gm, $rec, $args)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $loginUserType;
        global $loginUserRank;
        // **************************************************************************************

        List($time) = $args;
        $buffer = gmdate("D, d M Y H:i:s", $time);
        $buffer .= " GMT";

        $this->addBuffer($buffer);
    }

    private function getType()
    {
        return "indeed_feed";
    }
}
