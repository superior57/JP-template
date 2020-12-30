<?php

$UPDATE_NAME = 'ResetApplyPos';

$UPDATE_NAMES[] = $UPDATE_NAME;
$UPDATE_DESCRIPTION[$UPDATE_NAME] = "サイト上の求人情報の応募上限到達フラグをセットし直す。";

$UPDATE_CLASS[$UPDATE_NAME] = 'ResetApplyPos';
$UPDATE_METHOD[$UPDATE_NAME] = 'update';

class ResetApplyPos
{

    /**
     * サイト上の求人情報の応募上限到達フラグをセットし直す
     *
     * @return void
     */
    public function update()
    {
        // 中途求人
        $mDB = GMList::getDB('mid');
        $mTable = $mDB->getTable();
        $mRow = $mDB->getRow($mTable);

        for ($i = 0; $i < $mRow; $i++) {
            $rec = $mDB->getRecord($mTable, $i);
            $itemsID = $mDB->getData($rec, 'id');
            JobLogic::updateApplyPos('mid', $itemsID);
        }

        // 新卒求人
        $fDB = GMList::getDB('fresh');
        $fTable = $fDB->getTable();
        $fRow = $fDB->getRow($fTable);

        for ($i = 0; $i < $fRow; $i++) {
            $rec = $fDB->getRecord($fTable, $i);
            $itemsID = $fDB->getData($rec, 'id');
            JobLogic::updateApplyPos('fresh', $itemsID);
        }
    }

}
