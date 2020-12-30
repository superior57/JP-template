PHPMailerを利用してSMTP経由でメールを送るためのモジュールです
元々のメール用のクラスinclude/Mail.phpが静的クラスなため
継承での利用が出来ないので、分岐処理を少々加えて利用します。

PHPMailerをアップグレードする際はcomposer.jsonを書き換えてください。

PHPMailerのオブジェクトにオプション設定を加えたい場合は
custom/SMTPSettings.phpの$OPTIONSの配列に
PHPMailerのプロパティに追加したい設定を書いてください。

HTMLメールを利用する際はメールテンプレートに<!--# readhead main #-->パートの他に

<!--# readhead main_html #-->
HTMLメール文
<!--# readend #-->

を追記してください。
CSSはgmailにも送るなら要素インラインスタイルを使用したほうがいいです。