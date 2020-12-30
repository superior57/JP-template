<?php

class sitemapView extends command_base
{

    /**
     * サイトマップのリンクタグ生成する。
     *
     * サイトマップの作成とリンクタグ生成のフォームからの値を確認しリンクタグを生成する。
     *
     * @param GUIManager $gm   GUIManagerオブジェクト
     * @param array      $rec  レコードデータ
     * @param array      $args タイムスタンプ
     *
     * @return void
     */
    function drawLinks(&$gm, $rec, $args) {
        if (! Conf::checkData('sitemap', 'created', 0)) {
            if (Conf::checkData('sitemap', 'disp_flg', 1)) {
                // 生成された親サイトマップ数
                $sitemapFileCount = Conf::getData('sitemap', 'count');

                $buffer = '<link rel="alternate" type="application/rss+xml" title="sitemap" href="sitemap.xml" />'."\n";
                for ($i = 2; $i <= $sitemapFileCount; $i++) {
                    $buffer .= "<link rel='alternate' type='application/rss+xml' title='sitemap' href='sitemap{$i}.xml' />"."\n";
                }

                $this->addBuffer($buffer);
            }
        }
    }

}
