●●●●●●●●●●●●DBへのIndex設置に関する仕様書●●●●●●●●●●

●ファイルパス

lst以下のパスと同等のものを使用する。
lst/user/cUser.csvに定義されるDBの場合、index/user/cUser.csvにIndexの定義が記述される。


●各種項目

・1列目 index_name
各index毎に付与する任意のindex名。
何の為のindexであるかが分かりやすい名前にする事。
制約：index_nameは各テーブル毎で一意でなければならない。

・2列目 preset_name
preset_nameはpresetの設定のindexを作成するものである。
3列目以降は無視される。
もしくはカラム列のみ使う

・3列目 index_type
行にかかるパラメータ、presetが設定されてる場合は無視される。
具体的には [UNIQUE|FULLTEXT|空値]のどれか
空の場合は INDEXになる

・4列目以降、偶数列 col_name[1-n]	(col_name1〜n)
対象となるカラム名。
指定された順序で設定される。

・5列目以降、奇数列 param[1-n]		(param1〜n)
preset_nameが省略されている場合に適用する、indexのパラメータ
マニュアル設定用。

記述方法は以下。


●プリセット
search_word:スペースで切られた検索用ワードで使用する。
chebkbox:/で切られた項目をFULLTEXTサーチに利用する。
regist:日付による範囲検索などを利用する場合に必要とされるインデックス


●諸注意
※各テーブルで最高 32 個のインデックスが使用可能である。各インデックスは、1 から 16 個のカラムまたはカラムの一部で構成される。
※インデックスの最大幅は MySQLのデフォルト値で 500 バイト。
※インデックスでは、CHAR 型または VARCHAR 型のカラムのプリフィックスを使用することができる。

※インデックス何でもいいから貼れば高速化されるわけではなく、挙動を想定して設置する必要がある。
	考えなしに「とりあえず貼る」みたいな事はしない事。

※idに対するindexは自動で貼られています。

ALTER TABLE template ADD UNIQUE 'system_id' ( 'id' )

UNIQUE INDEX 'system_id' ( 'id' )


●csv構造
index_name,preset_name,col_name1,param1,col_name2,param2