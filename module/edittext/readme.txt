動的にヘッダーのタイトル(title)や説明(description)タグの文章を変更するためのモジュールです



### 初期設定 ##################

まず管理者アカウントにて
「other.php?key=edittext_list&type=edittext」
にアクセスしてください。
一応file/edittextフォルダに初期値として

mid_title
mid_desc
fresh_title
fresh_dec

という条件ファイルを用意していますが
ファイルが無かった場合一度初期化してください（初期化ボタン）

初期化は存在ファイルについては初期化しないので、
全て初期化する場合は、一度ファイルをすべて削除してください。



### 動的タイトルの適用方法 ##################

検索結果テンプレート(SearchResultFormat.htmlまたはSearchFaled.html)
のコマンドコメント<!--# syscode searchResult #-->より下に
<!--# syscode setTitle (!--# mod edittext drawMakeText ファイル名 #--) #-->
というコマンドコメントを配置して頂ければ、検索結果に応じて設定した条件で
動的にタイトルを表示させることができます。

例：
mid_titleとmid_descという条件ファイルを使用して動的タイトルを表示して
サイト名を後ろにつける場合は<!--# syscode searchResult #-->より下に

<!--# syscode setTitle (!--# mod edittext drawMakeText mid_title #--)｜(!--# code getData system ADMIN site_title #--) #-->
<!--# syscode setDescription (!--# mod edittext drawMakeText mid_desc #--) #-->

というコマンドコメントを配置していただくと

<title>オープン・WEB系SE・プログラマの求人 - 東京都 中央区｜JC2</title>
<meta name="description" content="東京都 中央区のオープン・WEB系SE・プログラマの求人を検索！　中途採用・新卒採用の求人情報を検索できます。">

のようなタイトルと説明タグが表示されます。