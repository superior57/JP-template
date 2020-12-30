<?php

// $db->setTableDataUpdateで必要な模様
include_once 'include/templateCache.php';

$UPDATE_NAME = 'job_station_update';

$UPDATE_NAMES[] = $UPDATE_NAME;
$UPDATE_DESCRIPTION[$UPDATE_NAME] = "最寄り駅の登録操作変更に伴ない<br/>\n最寄り駅毎の都道府県を設定する。";

$UPDATE_CLASS[$UPDATE_NAME] = 'update_job_station';
$UPDATE_METHOD[$UPDATE_NAME] = 'initTrafficAdds';

class update_job_station
{
    /**
     * 既存テーブルに追加された都道府県カラムにデフォルト値を設定する
     */
    function initTrafficAdds()
    {
        // 中途求人テーブルの最寄り駅毎の都道府県カラムに設定
        $mDB = GMList::getDB('mid');
        $mTable = $mDB->getTable();
        $row = $mDB->getRow($mTable);

        for ($ri = 0; $ri < $row; $ri++) {
            $rec = $mDB->getRecord($mTable, $ri);
            
            // traffic1_addsからtraffic5_addsに設定
            for ($ti = 1; $ti <= 5; $ti++) {
                $line = $mDB->getData($rec, 'traffic' . $ti . '_line');
                $station = $mDB->getData($rec, 'traffic' . $ti . '_station');
                $addsID = SystemUtil::getTableData('station', $station, 'adds_id');
                
                // 路線のみ指定されている場合
                if (! $addsID && $line) {
                    $wpAdds = $mDB->getData($rec, 'work_place_adds');
                    $addsIDs = SystemUtil::getTableData('line', $line, 'adds_ids');
                    $list = explode('/', $addsIDs);
                    if (in_array($wpAdds, $list)) {
                        $addsID = $wpAdds;
                    } else {
                        $addsID = $list[0];
                    }
                }
                $mDB->setData($rec, 'traffic' . $ti . '_adds', $addsID);
            }
            $mDB->updateRecord($rec);
        }
        
        // 新卒求人テーブルの最寄り駅毎の都道府県カラムに設定
        $fDB = GMList::getDB('fresh');
        $fTable = $fDB->getTable();
        $row = $fDB->getRow($fTable);

        for ($ri = 0; $ri < $row; $ri++) {
            $rec = $fDB->getRecord($fTable, $ri);
            
            // traffic1_addsからtraffic5_addsに設定
            for ($ti = 1; $ti <= 5; $ti++) {
                $line = $fDB->getData($rec, 'traffic' . $ti . '_line');
                $station = $fDB->getData($rec, 'traffic' . $ti . '_station');
                $addsID = SystemUtil::getTableData('station', $station, 'adds_id');
                
                // 路線のみ指定されている場合
                if (! $addsID && $line) {
                    $wpAdds = $mDB->getData($rec, 'work_place_adds');
                    $addsIDs = SystemUtil::getTableData('line', $line, 'adds_ids');
                    $list = explode('/', $addsIDs);
                    if (in_array($wpAdds, $list)) {
                        $addsID = $wpAdds;
                    } else {
                        $addsID = $list[0];
                    }
                }
                $fDB->setData($rec, 'traffic' . $ti . '_adds', $addsID);
            }
            $fDB->updateRecord($rec);
        }
    }
}