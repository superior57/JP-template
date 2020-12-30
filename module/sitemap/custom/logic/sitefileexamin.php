<?php

/**
 * サイトマップの作成、または追記をする関数群
 * ファイルの作成を子サイトマップと親サイトマップで分けている
 */
class fileexamin
{

    /**
     * 子サイトマップの作成、または追記
     *
     * @param string $filename サイトマップのファイル名
     * @param string $html     追記する内容
     *
     * @return void
     */
    function fileExamine($filename, $html = null)
    {
        // ** conf.php で定義した定数の中で、利用したい定数をココに列挙する。 *******************
        global $CHILD_SITEMAP_DIRECTORY;
        // **************************************************************************************

        $filePath = $CHILD_SITEMAP_DIRECTORY . $filename;
        if (! file_exists($filePath)) {
            self::fileWrite($filePath);
        } else {
            self::fileAddWrite($filePath, $html);
        }
    }

    /**
     * 子サイトマップを作成する
     *
     * @param string $filePath 子サイトマップのパス
     *
     * @return void
     */
    function fileWrite($filePath)
    {
        if (! $f = fopen($filePath, 'w')) {
            return;
        }

        $head = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $head .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        if (fwrite($f, $head) === false) {
            fclose($f);
            return;
        }

        fclose($f);
        SystemUtil::safe_chmod($filePath, 0666);
    }

    /**
     * 親サイトマップの作成、または追記
     *
     * @param string $filename サイトマップのファイル名
     * @param string $html     追記する内容
     *
     * @return void
     */
    function fileIndexExamine($filename, $html = null)
    {
        if (! file_exists($filename)) {
            self::fileIndexWrite($filename);
        } else {
            self::fileAddWrite($filename, $html);
        }
    }

    /**
     * 親サイトマップを作成する
     *
     * @param string $filename サイトマップのファイル名
     *
     * @return void
     */
    function fileIndexWrite($filename)
    {
        if (! $f = fopen($filename, 'w')) {
            return;
        }

        $head  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $head .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        if (fwrite($f, $head) === false) {
            fclose($f);
            return;
        }

        fclose($f);
        SystemUtil::safe_chmod($filename, 0666);
    }

    /**
     * サイトマップに追記する
     *
     * @param string $filePath サイトマップのパス
     * @param string $html     追記する内容
     *
     * @return void
     */
    function fileAddWrite($filePath, $html)
    {
        $f = fopen($filePath, "a");
        fwrite($f, $html);
        fclose($f);
    }

}
