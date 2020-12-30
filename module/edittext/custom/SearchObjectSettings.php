<?php
class SearchObjectSettings {
    public static $SearchList = array(
        'areaID' => array(
            'area',
            'id',
            'name'
        ),
        'work_place_adds' => array(
            'adds', //対応したテーブル名
            'id', //work_place_addssから取り出されるパラメータ値と対応するカラム
            'name' //テーブルから取り出すカラム
        ),
        'work_place_add_sub' => array(
            'add_sub',
            'id',
            'name'
        ),
        'category' => array(
            'items_type',
            'id',
            'name'
        ),
        'work_style' => array(
            'items_form',
            'id',
            'name'
        ),
        'addition' => array(
            'job_addition',
            'id',
            'name'
        ),
        'free' => array(
            '->', // -> の場合SearchObjectUtilのメソッドを呼び出す
            'getFreeWord', //SearchObjectUtilにあるメソッド名
            '' //メソッドを使う場合必要ないので空
        )
    );
    public static $SearchListJP = array(
        'areaID' =>  'エリア',
        'work_place_adds' =>  '都道府県', //パラメータの日本語名
        'work_place_add_sub' =>  '市町村',
        'category' =>  '職種',
        'work_style' => '雇用形態',
        'addition' => '特徴',
        'free' => 'キーワード'
    );

    /**
     * 初期データが存在しない場合初期データファイルを追加する
     * ここに初期データを追加する
     */
    public static function init() {
        SearchValueManager::mkdir();
        if(!SearchValueManager::existsTypeFile('mid_title')) {
            $svm = new SearchValueManager('mid_title', '求人を検索', '中途titleタグ用', array(
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('category', 'items_type'),
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%4\$sの%5\$sの求人 - %2\$s %3\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('category', 'items_type'),
                        new SearchParameter('addition', 'job_addition')
                    ),
                    "%4\$sの%5\$sの求人 - %2\$s %3\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('category', 'items_type')
                    ),
                    "%4\$sの求人 - %2\$s %3\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%4\$sの求人 - %2\$s %3\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub')
                    ),
                    "%2\$s %3\$sの求人"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('category', 'items_type'),
                    ),
                    "%3\$sの求人 - %2\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%3\$sの求人 - %2\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('addition', 'job_addition')
                    ),
                    "%3\$sの求人 - %2\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('areaID', 'area')
                    ),
                    "%2\$sの求人"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds')
                    ),
                    "%2\$sの求人"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('category', 'items_type')
                    ),
                    "%2\$sの求人"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%2\$sの求人"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('addition', 'job_addition')
                    ),
                    "%2\$sの求人"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('free', '->', 'getFreeWord'),
                    ),
                    "%2\$sの求人"
                )
            ));
            $svm->setOther('rowNum', 10);
            $svm->setOther('addText', '');
            $svm->save();
        }
        if(!SearchValueManager::existsTypeFile('mid_desc')) {
            $svm = new SearchValueManager('mid_desc', 'あなたにあった求人を検索！　お仕事情報を検索できます。', '中途descriptionタグ用', array(
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('category', 'items_type'),
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%2\$s %3\$sの%4\$sの%5\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('category', 'items_type'),
                        new SearchParameter('addition', 'job_addition')
                    ),
                    "%2\$s %3\$sの%4\$sの%5\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('category', 'items_type')
                    ),
                    "%2\$s %3\$sの%4\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%2\$s %3\$sの%4\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub')
                    ),
                    "%2\$s %3\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('category', 'items_type'),
                    ),
                    "%2\$sの%3\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%2\$sの%3\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('addition', 'job_addition')
                    ),
                    "%2\$sの%3\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('areaID', 'area')
                    ),
                    "%2\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds')
                    ),
                    "%2\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('category', 'items_type')
                    ),
                    "%2\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%2\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('addition', 'job_addition')
                    ),
                    "%2\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('free', '->', 'getFreeWord'),
                    ),
                    "%2\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                )
            ));

            $svm->setOther('rowNum', 10);
            $svm->setOther('addText', '%1$s件の検索結果から');
            $svm->save();
        }
        if(!SearchValueManager::existsTypeFile('fresh_title')) {
            $svm = new SearchValueManager('fresh_title', '求人を検索', '新卒titleタグ用', array(
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('category', 'items_type'),
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%4\$sの%5\$sの求人 - %2\$s %3\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('category', 'items_type'),
                        new SearchParameter('addition', 'job_addition')
                    ),
                    "%4\$sの%5\$sの求人 - %2\$s %3\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('category', 'items_type')
                    ),
                    "%4\$sの求人 - %2\$s %3\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%4\$sの求人 - %2\$s %3\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub')
                    ),
                    "%2\$s %3\$sの求人"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('category', 'items_type'),
                    ),
                    "%3\$sの求人 - %2\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%3\$sの求人 - %2\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('addition', 'job_addition')
                    ),
                    "%3\$sの求人 - %2\$s"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('areaID', 'area')
                    ),
                    "%2\$sの求人"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds')
                    ),
                    "%2\$sの求人"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('category', 'items_type')
                    ),
                    "%2\$sの求人"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%2\$sの求人"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('addition', 'job_addition')
                    ),
                    "%2\$sの求人"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('free', '->', 'getFreeWord'),
                    ),
                    "%2\$sの求人"
                )
            ));
            $svm->setOther('rowNum', 10);
            $svm->setOther('addText', '');
            $svm->save();
        }
        if(!SearchValueManager::existsTypeFile('fresh_desc')) {
            $svm = new SearchValueManager('fresh_desc', 'あなたにあった求人を検索！　お仕事情報を検索できます。', '新卒descriptionタグ用', array(
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('category', 'items_type'),
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%2\$s %3\$sの%4\$sの%5\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('category', 'items_type'),
                        new SearchParameter('addition', 'job_addition')
                    ),
                    "%2\$s %3\$sの%4\$sの%5\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('category', 'items_type')
                    ),
                    "%2\$s %3\$sの%4\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub'),
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%2\$s %3\$sの%4\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_place_add_sub', 'add_sub')
                    ),
                    "%2\$s %3\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('category', 'items_type'),
                    ),
                    "%2\$sの%3\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%2\$sの%3\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds'),
                        new SearchParameter('addition', 'job_addition')
                    ),
                    "%2\$sの%3\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('areaID', 'area')
                    ),
                    "%2\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_place_adds', 'adds')
                    ),
                    "%2\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('category', 'items_type')
                    ),
                    "%2\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('work_style', 'items_form')
                    ),
                    "%2\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('addition', 'job_addition')
                    ),
                    "%2\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                ),
                new SearchContainer(
                    array(
                        new SearchParameter('free', '->', 'getFreeWord'),
                    ),
                    "%2\$sの求人を検索！　%1\$s中途採用・新卒採用の求人情報を検索できます。"
                )
            ));

            $svm->setOther('rowNum', 10);
            $svm->setOther('addText', '%1$s件の検索結果から');
            $svm->save();
        }
    }
}