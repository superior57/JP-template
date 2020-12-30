<?php

class SitemapLogic
{
    static $xml  = 'sitemap.xml';
    static $STATIC_URL_FLG = false;
    static $renameCount = 2;

	static private function setKind($kind = array()){
        // 子サイトマップのリスト
        $kind['mid']            = array('name' => '求人を探す',       'url' => 'index.php?app_controller=search&type=mid&run=true');
        $kind['adds']           = array('name' => '地域から探す',     'url' => 'index.php?app_controller=search&type=mid&run=true');
        $kind['items_form']     = array('name' => '勤務形態から探す', 'url' => 'index.php?app_controller=search&type=mid&run=true');
        $kind['items_type']     = array('name' => '職種から探す',     'url' => 'index.php?app_controller=search&type=mid&run=true');
        $kind['job_addition']   = array('name' => '特徴から探す',     'url' => 'index.php?app_controller=search&type=mid&run=true');

        $kind['fresh']          = array('name' => '新卒求人を探す',   'url' => 'index.php?app_controller=search&type=fresh&run=true');
        $kind['f_adds']         = array('name' => '地域から探す',     'url' => 'index.php?app_controller=search&type=fresh&run=true');
        $kind['f_items_form']   = array('name' => '勤務形態から探す', 'url' => 'index.php?app_controller=search&type=fresh&run=true');
        $kind['f_items_type']   = array('name' => '職種から探す',     'url' => 'index.php?app_controller=search&type=fresh&run=true');
        $kind['f_job_addition'] = array('name' => '特徴から探す',     'url' => 'index.php?app_controller=search&type=fresh&run=true');

        $kind['interview']      = array('name' => '企業インタビュー', 'url' => 'index.php?app_controller=search&type=interview&run=true');
        $kind['company']        = array('name' => '企業検索',         'url' => 'index.php?app_controller=search&type=cUser&run=true');
        $kind['other']          = array('name' => '会員登録',         'url' => 'index.php?app_controller=other&key=CheckValidityForm&type=nUser');
		return $kind;
	}
	/**
     * サイトマップを作成する
     *
     * @return void
     */
    static function create()
    {
		global $controllerName;
	
        // 静的URL機能が有効かチェック
        self::$STATIC_URL_FLG = isset($GLOBALS['STATIC_URL_FLG']) ? $GLOBALS['STATIC_URL_FLG'] : false;

        // 子サイトマップのリスト
		$kind = self::setKind();

        // 子サイトマップに出力するURLのリスト
        $list['mid']            = self::getJobList('mid');
        $list['adds']           = self::getAddsList();
        $list['items_form']     = self::getCategoryList('items_form');
        $list['items_type']     = self::getCategoryList('items_type');
        $list['job_addition']   = self::getCategoryList('job_addition');

        $list['fresh']          = self::getJobList('fresh');
        $list['f_adds']         = self::getAddsList('fresh');
        $list['f_items_form']   = self::getCategoryList('items_form', 'fresh');
        $list['f_items_type']   = self::getCategoryList('items_type', 'fresh');
        $list['f_job_addition'] = self::getCategoryList('job_addition', 'fresh');

        $list['interview']      = self::getInterviewList();
        $list['company']        = self::getCompanyList();
        $list['other']          = self::getOtherList();

        self::createXml(self::$xml, $kind, $list);
    }

    /**
     * XMLサイトマップを作成する
     *
     * @param string $file ファイル名
     * @param array  $kind 子サイトマップのリスト
     * @param array  $list 子サイトマップに出力するURLのリスト
     *
     * @return void
     */
    function createXml($file, $kind, $list)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $HOME;
        global $CHILD_SITEMAP_DIRECTORY;
        // **************************************************************************************

        // 既存の親サイトマップを削除
        if (file_exists(self::$xml)) {
            unlink(self::$xml);
        }
        foreach (glob('sitemap*[0-9].*xml') as $filename) {
            unlink($filename);
        }

        // 既存の子サイトマップを削除
		if(strlen($CHILD_SITEMAP_DIRECTORY) === 0){
			foreach ((array)$kind as $name => $data) {
				foreach(glob($name.'sitemap*.xml') as $filename){
	                unlink($filename);
				}
			}
		}else{
			if (file_exists($CHILD_SITEMAP_DIRECTORY)) {
				foreach (glob($CHILD_SITEMAP_DIRECTORY . '*.xml') as $filename) {
					unlink($filename);
				}
				rmdir($CHILD_SITEMAP_DIRECTORY);
			}
			// 子サイトマップのディレクトリを作成
			mkdir($CHILD_SITEMAP_DIRECTORY);
		}

        // 子サイトマップを作成
        foreach ((array)$kind as $name => $data) {

            // 子サイトマップに先頭URLを出力する
            if (strlen($data['url']) > 0) {
                $filename = $name . self::$xml;
                fileexamin::fileExamine($filename);
                $buffer = '';
                $buffer .= '  <url>' . "\n";
                $buffer .= '    <loc>' . $HOME . h($data['url']) . '</loc>' . "\n";
                $buffer .= '  </url>' . "\n";
                fileexamin::fileExamine($filename, $buffer);
            }

            $urlCount = 0;

            // 子サイトマップにURLを出力する
            foreach ((array)$list[$name] as $data) {
				$max_url_count = Conf::getData('sitemap', 'max_url_count');
				// 1ファイルあたりの最大URL数に達したら、次の子サイトマップへ
                if ($urlCount >= $max_url_count) {
                    $filename = self::getNextSitemap($filename);
                    // カウントをリセット
                    $urlCount = 0;
                }

                // URLを追記
                $buffer = '';
                $buffer .= '  <url>' . "\n";
                $buffer .= '    <loc>' . $HOME . h($data['url']) . '</loc>' . "\n";
                $buffer .= '  </url>' . "\n";
                fileexamin::fileExamine($filename, $buffer);
                // 現在の子サイトマップのURL数
                $urlCount++;
            }

            // 終了タグを追記
            fileexamin::fileExamine($filename, '</urlset>' . "\n");

            $urlCount = 0;
            self::$renameCount = 2;
        }

        // 親サイトマップを作成
        self::createSitemapIndex();

        // サイトマップ作成日時を記録
        // 「サイトマップの設定」のメッセージ分岐に使用
        Conf::update(self::_getType(), 'created', time());

        // 親サイトマップのファイル数を記録
        Conf::update(self::_getType(), 'count', self::$renameCount - 1);
    }

    /**
     * 最大URL数に達した子サイトマップを閉じ、次の子サイトマップ名を渡す
     *
     * @param string $filename 子サイトマップのファイル名
     *
     * @return string $nextFilename 次の子サイトマップのファイル名
     */
    function getNextSitemap($filename)
    {
        // 終了タグを追記
        fileexamin::fileExamine($filename, '</urlset>' . "\n");

        // 次のサイトマップを作成
        $nextFilename = preg_replace("/[0-9]/", "", $filename);
        $nextFilename = str_replace(".xml", self::$renameCount . ".xml", $nextFilename);
        self::$renameCount++;
        fileexamin::fileExamine($nextFilename);

        return $nextFilename;
    }

    /**
     * ルートに親サイトマップを作成する
     *
     * @return void
     */
    function createSitemapIndex()
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $HOME;
        global $CHILD_SITEMAP_DIRECTORY;
        // **************************************************************************************

        $sitemapUrlCount = 0;

        // 親サイトマップを作成
        $filename = self::$xml;
        fileexamin::fileIndexExamine($filename);

		$fileList = glob($CHILD_SITEMAP_DIRECTORY . '*.xml');
		natsort($fileList);
        $max_url_count = Conf::getData('sitemap', 'max_url_count');
        
        $fileList[] = 'index.php';
        // 親サイトマップにURLを出力する
        foreach ( $fileList as $file) {
            // 1ファイルあたりの最大URL数に達したら、次のサイトマップへ

            if( $file == 'sitemap.xml' ) { continue; }

            if ($sitemapUrlCount >= $max_url_count) {
                $filename = self::getNextSitemapIndex($filename);
                // カウントをリセット
                $sitemapUrlCount = 0;
            }

            // URLを追記
            if (is_file($file)) {
                $buffer = '';
                $buffer .= '  <sitemap>' . "\n";
                $buffer .= '    <loc>' . $HOME . h($file) . '</loc>' . "\n";
                $buffer .= '  </sitemap>' . "\n";
                fileexamin::fileIndexExamine($filename, $buffer);
				// 現在のサイトマップのURL数
				$sitemapUrlCount++;
            }
        }

        // 終了タグを追記
        fileexamin::fileIndexExamine($filename, '</sitemapindex>' . "\n");
    }

    /**
     * 最大URL数に達した親サイトマップを閉じ、次の親サイトマップ名を渡す
     *
     * @param string $filename 親サイトマップのファイル名
     *
     * @return string $nextFilename 次の親サイトマップのファイル名
     */
    function getNextSitemapIndex($filename)
    {
        // 終了タグを追記
        fileexamin::fileIndexExamine($filename, '</sitemapindex>' . "\n");

        // 次のサイトマップを作成
        $nextFilename = preg_replace("/[0-9]/", "", $filename);
        $nextFilename = str_replace(".xml", self::$renameCount . ".xml", $nextFilename);
        self::$renameCount++;
        fileexamin::fileIndexExamine($nextFilename);

        return $nextFilename;
    }

    /**
     * 「求人」一覧を配列で取得
     *
     * @param string $jobType 求人種別(mid/fresh)
     *
     * @return array $list 子サイトマップに出力するURLのリスト
     */
    function getJobList($jobType = 'mid')
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        // **************************************************************************************

        if (! in_array($jobType, array('mid', 'fresh'))) {
            return;
        }

        $db = GMList::getDB($jobType);
        $table = JobLogic::getTable($jobType, null, null, "nobody");
        $table = $db->sortTable($table, 'edit', 'desc');
        $table = $db->limitOffset($table, 0, Conf::getData('sitemap', 'max_job_count'));

        $row = $db->getRow($table);
        $list = array();

        for ($i = 0; $i < $row; $i++) {
            $rec = $db->getRecord($table, $i);
            if (self::$STATIC_URL_FLG) {
                $list[] = array(
                    'url'  => SystemUtil::getStaticURL($db->getData($rec, 'id'), $jobType, 'info'),
                    'name' => $db->getData($rec, 'name')
                );
            } else {
                $list[] = array(
                    'url'  => 'index.php?app_controller=info&type=' . $jobType . '&id=' . $db->getData($rec, 'id'),
                    'name' => $db->getData($rec, 'name')
                );
            }
        }

        return $list;
    }

    /**
     * 「企業」一覧を配列で取得
     *
     * @return array $list 子サイトマップに出力するURLのリスト
     */
    function getCompanyList()
    {
        $db = GMList::getDB('cUser');
        $table = cUserLogic::getTable(null, null, "nobody");
		// cUserLogic::getActiveUser()で fresh と mid の契約有効cUserが
		// 選択されるで、両方契約していると二重になる。
		$table = $db->getDistinct($table);
        $table = $db->sortTable($table, 'regist', 'desc');
        $table = $db->limitOffset($table, 0, 1000);

        $row = $db->getRow($table);
        $list = array();

        for ($i = 0; $i < $row; $i++) {
            $rec = $db->getRecord($table, $i);
            if (self::$STATIC_URL_FLG) {
                $list[] = array(
                    'url'  => SystemUtil::getStaticURL($db->getData($rec, 'id'), 'cUser', 'info'),
                    'name' => $db->getData($rec, 'name')
                );
            } else {
                $list[] = array(
                    'url'  => 'index.php?app_controller=info&type=cUser&id=' . $db->getData($rec, 'id'),
                    'name' => $db->getData($rec, 'name')
                );
            }
        }

        return $list;
    }

    /**
     * 「地域から探す」の絞り込み検索のリンクを配列で取得
     *
     * @param string $jobType 求人種別
     *
     * @return array $list 子サイトマップに出力するURLのリスト
     */
    function getAddsList($jobType = 'mid')
    {
        $db = GMList::getDB('adds');
        $table = $db->getTable();
        $table = $db->sortTable($table, 'id', 'asc');

        $row = $db->getRow($table);
        $list = array();

        for ($i = 0; $i < $row; $i++) {
            $rec = $db->getRecord($table, $i);
            if (self::$STATIC_URL_FLG) {
                $list[] = array(
                    'url'  => SystemUtil::getStaticURL($db->getData($rec, 'id'), $jobType),
                    'name' => $db->getData($rec, 'name')
                );
            } else {
                $list[] = array(
                    'url'  => 'index.php?app_controller=search&type=' . $jobType . '&run=true&work_place_adds=' . $db->getData($rec, 'id') . '&work_place_adds_PAL[]=match+comp',
                    'name' => $db->getData($rec, 'name')
                );
            }
        }

        return $list;
    }

    /**
     * 「勤務形態/職種/特徴から探す」の絞り込み検索のリンクを配列で取得
     *
     * @param string $categoryType カテゴリー種別(items_form/items_type/job_addition)
     * @param string $jobType      求人種別
     *
     * @return array $list 子サイトマップに出力するURLのリスト
     */
    function getCategoryList($categoryType, $jobType = 'mid')
    {
        $columnNameList = array(
            'items_form'   => 'work_style',
            'items_type'   => 'category',
            'job_addition' => 'addition'
        );
        if (! array_key_exists($categoryType, $columnNameList)) {
            return;
        }
        $columnName = $columnNameList[$categoryType];

        $db = GMList::getDB($categoryType);
        $table = $db->getTable();
        $table = $db->sortTable($table, 'sort_rank', 'asc');

        $row = $db->getRow($table);
        $list = array();

        for ($i = 0; $i < $row; $i++) {
            $rec = $db->getRecord($table, $i);
            if (self::$STATIC_URL_FLG) {
                $list[] = array(
                    'url'  => SystemUtil::getStaticURL($db->getData($rec, 'id'), $jobType),
                    'name' => $db->getData($rec, 'name')
                );
            } else {
                $list[] = array(
                    'url'  => 'index.php?app_controller=search&type=' . $jobType . '&run=true&' . $columnName . '=' . $db->getData($rec, 'id') . '&' . $columnName . '_PAL[]=match+or',
                    'name' => $db->getData($rec, 'name')
                );
            }
        }

        return $list;
    }

    /**
     * 「インタビュー」一覧を配列で取得
     *
     * @return array $list 子サイトマップに出力するURLのリスト
     */
    function getInterviewList()
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $ACTIVE_ACCEPT;
        // **************************************************************************************

        $db = GMList::getDB('interview');
        $table = $db->getTable();
        $table = $db->searchTable($table, 'activate', '=', $ACTIVE_ACCEPT);
        $table = $db->sortTable($table, 'regist', 'desc');

        $row = $db->getRow($table);
        $list = array();

        for ($i = 0; $i < $row; $i++) {
            $rec = $db->getRecord($table, $i);
            $list[] = array(
                'url'  => 'index.php?app_controller=info&type=interview&id=' . $db->getData($rec, 'id'),
                'name' => $db->getData($rec, 'name')
            );
        }

        return $list;
    }

    /**
     * その他ページ一覧を配列で取得
     *
     * @return array $list 子サイトマップに出力するURLのリスト
     */
    function getOtherList()
    {
        $list = array();

        $list[] = array(
            'url'  => 'login.php',
            'name' => 'ログイン'
        );
        $list[] = array(
            'url'  => 'index.php?app_controller=search&run=true&type=mid&sort=regist&sort_PAL[]=desc&attention[]=1&attention_CHECKBOX=&attention_PAL[]=match+or',
            'name' => 'オススメの求人'
        );
        $list[] = array(
            'url'  => 'index.php?app_controller=search&run=true&type=fresh&sort=regist&sort_PAL[]=desc&attention[]=1&attention_CHECKBOX=&attention_PAL[]=match+or',
            'name' => 'オススメの求人'
        );
        $list[] = array(
            'url'  => 'index.php?app_controller=search&type=news&run=true&category=news&category_PAL[]=match+comp',
            'name' => 'お知らせ'
        );
        $list[] = array(
            'url'  => 'index.php?app_controller=page&p=qanda',
            'name' => 'よくある質問'
        );

        return $list;
    }

	static function delete(){
		global $CHILD_SITEMAP_DIRECTORY;

        // 既存の親サイトマップを削除
        if (file_exists(self::$xml)) {
			SystemUtil::safe_chmod(self::$xml, 0666);
			unlink(self::$xml);
        }
        foreach (glob('sitemap*[0-9].*xml') as $filename) {
			SystemUtil::safe_chmod($filename, 0666);
            unlink($filename);
        }

        // 既存の子サイトマップを削除
		$kind = self::setKind();
		if(strlen($CHILD_SITEMAP_DIRECTORY) === 0){
			foreach ((array)$kind as $name => $data) {
				foreach(glob($name.'sitemap*.xml') as $filename){
	                unlink($filename);
				}
			}
		}else{
			if (file_exists($CHILD_SITEMAP_DIRECTORY)) {
				foreach (glob($CHILD_SITEMAP_DIRECTORY . '*.xml') as $filename) {
					unlink($filename);
				}
				rmdir($CHILD_SITEMAP_DIRECTORY);
			}
		}

        // サイトマップのタグ表示設定クリア
        Conf::update(self::_getType(), 'disp_flg', 0);
		
		// サイトマップ作成日時を記録
        // 「サイトマップの設定」のメッセージ分岐に使用
        Conf::update(self::_getType(), 'created', 0);

        // 親サイトマップのファイル数を記録
        Conf::update(self::_getType(), 'count', 0);

		return;
	}

    /**
     * テーブル名を返却する
     *
     * @return string テーブル名
     */
    private function _getType()
    {
        return 'sitemap';
    }
}
