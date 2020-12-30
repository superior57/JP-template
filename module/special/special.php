<?php

class mod_special extends command_base
{

    public function doAsyncUpdate(&$gm, $rec, $args)
    {
        $db = GMList::getDB('page');
        $table = $db->getTable();
        if (isset($_GET['p'])) { // 名前で指定されている場合
            $table = $db->searchTable($table, "name", "=", $_GET["p"]);
            $rec = $db->getRecord($table, 0);
        } elseif (isset($_GET['id'])) { // IDで指定されている場合
            $rec = $db->selectRecord($_GET["id"]);
        }

        $c_type = $db->getData($rec, "c_type");
        $id = $db->getData($rec, "id");

        if ($args[0] && $args[0] > 0) {
            $requestCount = $args[0];
            $maxDoCount = 100;

            $splitCount = ceil($requestCount / $maxDoCount);
            $html = "<script>$(function(){ doUpdateQuery('".$c_type."','".$id."','".(0*$maxDoCount)."','".$maxDoCount."','".$splitCount."'); });</script>\n";
        } else {
            return;
        }

        $this->addBuffer($html);
    }

    public function drawSearchEmbed(&$gm, $rec, $args)
    {
        $db = GMList::getDB('page');
        $table = $db->getTable();
        if (isset($_GET['p'])) { // 名前で指定されている場合
            $table = $db->searchTable($table, "name", "=", $_GET["p"]);
            $rec = $db->getRecord($table, 0);
        } elseif (isset($_GET['id'])) { // IDで指定されている場合
            $rec = $db->selectRecord($_GET["id"]);
        }

        $c_type = $db->getData($rec, "c_type");
        $id = $db->getData($rec, "id");

        // 非会員の「検討中リストに追加」ボタン用
        $pageName = $db->getData($rec, "name");

        if (! $_GET["add"] || $_GET["add"] == 0) {
            $this->addBuffer($gm->getCCResult($rec, '<div class="main" id="job_special"><!--# async code embedSearch type='.$c_type.'&run=true&special='.$id.'&special_PAL[]=match+like&embedID=job_special&authority=(!--# get authority #--)&add=(!--# get add #--)&page_name=' . $pageName . ' #--><p data-async-loader>求人情報を読み込んでいます...</p></div>'));
        } else {
            $this->addBuffer('<div class="main" id="job_special"></div>');
        }
    }

    public function drawSearchEmbed4SP(&$gm, $rec, $args)
    {
        $db = GMList::getDB('page');
        $table = $db->getTable();
        if (isset($_GET['p'])) { // 名前で指定されている場合
            $table = $db->searchTable($table, "name", "=", $_GET["p"]);
            $rec = $db->getRecord($table, 0);
        } elseif (isset($_GET['id'])) { // IDで指定されている場合
            $rec = $db->selectRecord($_GET["id"]);
        }

        $c_type = $db->getData($rec, "c_type");
        $id = $db->getData($rec, "id");

        // 非会員の「検討中リストに追加」ボタン用
        $pageName = $db->getData($rec, "name");

        if (! $_GET["add"] || $_GET["add"] == 0) {
            $this->addBuffer($gm->getCCResult($rec, '<div class="main" id="job_special"><!--# async code embedSearch type='.$c_type.'&run=true&special='.$id.'&special_PAL[]=match+like&embedID=job_special&authority=(!--# get authority #--)&add=(!--# get add #--)&page_name=' . $pageName . ' #--><p data-async-loader>求人情報を読み込んでいます...</p></div>'));
        } else {
            $this->addBuffer('<div class="main" id="job_special"></div>');
        }
    }

    /**
     * 特集ページリンクを表示
     */
    public function drawLink4SP(&$gm, $rec, $args)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $LOGIN_ID;
        global $loginUserType;
        global $loginUserRank;
        // **************************************************************************************

        $design = Template::getTemplate($loginUserType, $loginUserRank, 'page', 'LINK_DESIGN_SP');
        $db = GMList::getDB('page');
        $table = $db->getTable();
        $table = $db->searchTable($table, 'authority', '=', '%'.$loginUserType.'%');
        $table = $db->searchTable($table, 'link', '!', 'no');
        $table = $db->searchTable($table, 'mode', '=', 'special');
        $table = $db->searchTable($table, 'c_type', '=', viewMode::getViewMode());
        $table = $db->searchTable($table, 'open', '=', true);
        $table = $db->sortTable($table, 'link_sort', 'asc');

        $row = $db->getRow($table);
        if ($row > 0) {
            $buffer .= $gm->getString($design, $rec, 'head');
            for ($i = 0; $i < $row; $i++) {
                $rec = $db->getRecord($table, $i);
                $buffer .= $gm->getString($design, $rec, $db->getData($rec, 'link'));
            }
            $buffer .= $gm->getString($design, $rec, 'foot');
        }

        $this->addBuffer($buffer);
    }
}
