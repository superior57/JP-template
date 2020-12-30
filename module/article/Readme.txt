articleモジュール

[概要]
･パーツ（アイテム）単位で記事を作成するモジュール

■使用方法
・管理ツールから「テンプレート更新」をクリックします。
・「article」「article_parts」の初期化をクリックします。
・$THIS_TABLE_REGIST_USER[ $EDIT_TYPE ] で指定したユーザと管理者のメニューで自分が作成した画面用と公開されている記事用のURLを追加
自分用
search.php?type=article&run=true&self
公開用
search.php?type=article&run=true

※新規作成の以下のURLをクリックすると、パーツとの兼ね合いでDBに初期データが追加されます。（追加キャンセルはできません）
regist.php?type=article