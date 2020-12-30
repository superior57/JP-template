<?php
/**
	@file
	@brief このファイルはドキュメントの記述サポートのためだけに存在しています。
*/

/**
	@defgroup System
	@brief    フレームワークの基本機能。
	@note     このカテゴリに属する機能は、通常カスタマイズで変更する必要はありません。
	@{
		@defgroup SystemFlow
		@brief    基本機能の内、システムフローに組み込まれているもの(手動で呼び出さないもの)。
		@note     このカテゴリに属する機能は、原則として変更する必要はありません。

		@defgroup SystemComponent
		@brief    基本機能の内、フローに直接関与しないもの。
		@note     このカテゴリに属する機能は、通常カスタマイズで変更する必要はありません。

		@defgroup SystemAPI
		@brief    基本機能の内、手動で呼び出すタイプのもの。
		@note     このカテゴリに属する機能は、通常カスタマイズで変更する必要はありません。

		@defgroup Utility
		@brief    冗長な記述やよく使う処理のプリセット。
		@note     このカテゴリに属する機能は、通常カスタマイズで変更する必要はありません。

		@defgroup Information
		@brief    システムの設定や情報を取得する。
		@note     このカテゴリに属する機能は、カスタマイズ内容によっては変更を要するかもしれません。

		@defgroup CommandComment
		@brief    コマンドコメント定義。
		@note     このカテゴリに属する機能は、通常カスタマイズで変更する必要はありません。
	@}

	@defgroup Exclusive
	@brief    パッケージ固有機能。
	@{
		@defgroup PackageInformation
		@brief    パッケージ固有の設定や情報を取得する。
		@note     このカテゴリに属する機能は、カスタマイズ内容によっては変更を要するかもしれません。
	@}

	@defgroup Custom
	@brief    カスタマイズ機能。
	@{
		@defgroup Behavior
		@brief    システムの振る舞いを制御するコード。
		@note     カスタマイズにおいて最も頻繁に変更する部分です。

		@defgroup CustomUtility
		@brief    冗長な記述やよく使う処理のプリセット。

		@defgroup CustomCommandComment
		@brief    コマンドコメント定義。

		@defgroup Configure
		@brief    システムの設定を記述したファイル。

		@defgroup Module
		@brief    拡張モジュール。
	@}

	@defgroup Sub
	@brief    互換/代替定義。

	@defgroup Exception
	@brief    例外処理。
*/
?>