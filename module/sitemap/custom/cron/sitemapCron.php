<?php

// サイトマップファイル作成
class sitemapCron
{
    function updatesitemap()
    {
        SitemapLogic::create();
    }
}
