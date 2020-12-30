<?php

$UPDATE_NAME = 'PageFileReName';

$UPDATE_NAMES[] = $UPDATE_NAME;
$UPDATE_DESCRIPTION[$UPDATE_NAME] = "ページ名表記で保存されているページファイルをID表記のファイル名に変更する。";

$UPDATE_CLASS[$UPDATE_NAME] = 'PageFileReName';
$UPDATE_METHOD[$UPDATE_NAME] = 'update';

class PageFileReName
{
    /**
     * 既存テーブルに追加された都道府県カラムにデフォルト値を設定する
     */
    public function update()
    {
        global $page_path;

        $db = GMList::getDB('page');
        $table = $db->getTable();
        $row = $db->getRow($table);

        $extList = array(
            '.dat',
            '.mob.dat',
            '.sp.dat'
        );

        $success = 0;
        $error = 0;

        for($i = 0; $i < $row; $i++) {
            $rec = $db->getRecord($table, $i);
            $pageName = $db->getData($rec, 'name');
            $id = $db->getData($rec, 'id');
            
            foreach($extList as $ext) {
                $namePath = $page_path.$pageName.$ext;
                $idPath = $page_path.$id.$ext;

                if(file_exists($namePath) && !file_exists($idPath)) {
                    rename($namePath, $idPath);
                }
            }
        }
    }
}