<?php

include_once "./include/OutputLog.php";

/*******************************************************************************************************
 * <PRE>
 *
 * SQLデータベースシステム構築用モック
 * 実装時に継承する必要はなし
 *
 * @author 吉岡幸一郎
 * @original 丹羽一智
 * @version 2.0.0
 *
 * </PRE>
 *******************************************************************************************************/

interface DatabaseBase
{

	/**
	 * データベースを初期化します。
	 * @param $table テーブルデータ
	 * @param $index 取得するレコード番号
	 * @return レコードデータ
	 */
	function init(&$dbName, &$tableName, &$colName, &$colType, &$colSize, &$colExtend);


	/**
	 * その名前のカラムが存在するかを返します。
	 * @param $name 確認するカラム名
	 * @return 有無のboolean値
	 */
	function isColumn( $name );

	/**
	 * レコードを取得します。
	 * @param $table テーブルデータ
	 * @param $index 取得するレコード番号
	 * @return レコードデータ
	 */
	function getRecord($table, $index);

	/**
	 * レコードを取得します。
	 *
	 * @param $id 取得するレコードID
	 * @param $type 操作対象となるテーブルのtype(nomal/delete/all)
	 * @return レコードデータ。レコードデータが存在しない場合nullを返す。
	 */
	function selectRecord( $id , $type = null);

	/**
	 * テーブルから指定したレコードを削除します。
	 * @param table テーブルデータ
	 * @param rec 削除対象となるレコード
	 * @return テーブルデータ
	 */
	function pullRecord($table, $rec);

	/**
	 * データの内容を取得する。
	 * @param $rec レコードデータ
	 * @param $name カラム名
	 * @return 値
	 */
	function getData($rec, $name, $br = false);

	/**
	 * レコードの内容を更新する。
	 * DBファイルへの更新も含みます。
	 * @param $rec レコードデータ
	 */
	function updateRecord($rec);

	/**
	 * レコードの削除。
	 * DBファイルへの反映も行います。
	 * @param $rec レコードデータ
	 */
	function deleteRecord(&$rec);

	/**
	 * whereによって選択されるテーブルの行を削除します。
	 * @param $table テーブルデータ
	 * @return 行数
	 */
	function deleteTable($table);

	/**
	 * データをセットする。
	 * @param $rec レコードデータ
	 * @param $name カラム名
	 * @param $val 値
	 */
	function setData(&$rec, $name, $val);

	/**
	 * 簡易な演算を行ない、結果をセットする。
	 * カラムが数値型で無い場合は無効
	 *
	 * @param $rec レコードデータ
	 * @param $name カラム名
	 * @param $opp 演算子
	 * @param $name 値
	 */
	function setCalc(&$rec, $name , $opp , $val );

	/**
	 * 引数として渡されたtableの全行にデータをセットしてupdateする。
	 *
	 * @param $table 更新を行なうカラムの入ったtable
	 * @param $name カラム名
	 * @param $val 値
	 */
	function setTableDataUpdate(&$table, $name, $val);

	/**
	 * レコードにデータをまとめてセットします。
	 * @param $rec レコードデータ
	 * @param data データ連想配列（添え字はカラム名）
	 */
	function setRecord(&$rec, $data);

	/**
	 * 新しくレコードを取得します。
	 * デフォルト値を指定したい場合は
	 * $data['カラム名']の連想配列で初期値を指定してください。
	 * @param data 初期値定義連想配列
	 * @return レコードデータ
	 */
	function getNewRecord($data = null);

	function setFile(&$rec, $colname);

	/**
	 * レコードの追加。
	 * DBへの反映も同時に行います。
	 * @param $rec レコードデータ
	 */
	function addRecord(&$rec);

	/**
	 * DBが持つテーブルを取得します。
	 * @return テーブルデータ
	 * @param $type table type(nomal/delete/all)
	 */
	function getTable($type = null);

	/**
	 * テーブルの行数を取得します。
	 * @param $table テーブルデータ
	 * @return 行数
	 */
	function getRow($table);

	/**
	 * テーブルの検索を行います。
	 * 利用できる演算子は以下のものです。
	 * >, <	 不等号演算子
	 * =	 等号演算子
	 * !	 非等号演算子
	 * b	 ビトゥイーン演算子
	 * ビトゥイーン演算子の場合のみ$val2を指定します。
	 * @param $table テーブルデータ
	 * @param $name カラム名
	 * @param $opp 演算子
	 * @param $val 値１
	 * @param $val2 値２
	 */
	function searchTable(&$tbl, $name, $opp, $val, $val2 = null);

	/**
	 * 空のテーブルを返す。
	 * searchの結果を空にしたりする時に使用。
	 */
	/**
	 * @return unknown_type
	 */
	function getEmptyTable();

	/**
	 * レコードをソートします。
	 * @param $table テーブルデータ
	 * @param $name カラム名
	 * @param $asc 昇順・降順を 'asc' 'desc' で指定します。
	 */
	function sortTable(&$tbl, $name, $asc);

	/**
	 * テーブルの論理和。
	 * @param $table1 テーブルデータ
	 * @param $table2 テーブルデータ
	 * @return テーブルデータ
	 */
	function orTable(&$tbl, &$table2);

	/**
	 * テーブルの論理和。(可変引数対応
	 * @param $a テーブルデータの入った配列
	 * @return テーブルデータ
	 *
	 * func_get_argsでは参照を受けれない為配列にて
	 */
	function orTableM($a);

	/**
	 * テーブルの論理積。
	 * @param $table1 テーブルデータ
	 * @param $table2 テーブルデータ
	 * @return テーブルデータ
	 */
	function andTable(&$tbl, &$table2);

	/**
	 * ユニオンテーブルを作成します。
	 * ソート条件等はrTableのものを使用。
	 * @param $table テーブルデータ
	 * @param $name カラム名
	 * @param $asc 昇順・降順を 'asc' 'desc' で指定します。
	 */
	function unionTable(&$lTable, &$rTable, $colum = null);

	//テーブルの結合
	function joinTable( &$tbl, $b_name, $n_name, $b_col, $n_col );

	/**
	 * テーブルの $start 行目から $num 個取り出す。
	 * @param table テーブルデータ
	 * @param start オフセット
	 * @param num 数
	 */
	function limitOffset( $table, $start, $num );

	/**
	 * 暗黙IDの最大値を返す
	 */
	function getMaxID();

	/**
	 * 現在のテーブルから指定したcolumnの総合計を取得します。
	 */
	function getSum( $name, $table = null);

	/**
	 * 現在のテーブルのSQLを指定したcolumnでgroupbyし、指定カラムをsumした結果を付与したテーブルを返す。
	 * 実際の取得はgetRecored、getDataを使う
	 */
	function getSumTable( $sum_name, $group_name, $table = null, $opp = null, $val = null);

	/**
	 * 現在のテーブルのSQLを指定したcolumnでgroupbyし、指定カラムをcntした結果を付与したテーブルを返す。
	 * 実際の取得はgetRecored、getDataを使う
	 */
	function getCountTable( $name, $table = null, $opp = null, $val = null);

	//選択カラムを追加。  geCountTableなどでデータの欲しいカラムが表示されない時に有効
	function addSelectColumn( &$tbl, $name );

	//指定カラムのみ結果を重複を削除して返す
	function getDistinctColumn( $name , &$tbl);

	// 指定カラムのみ返す
	function getColumn( $name , &$tbl);

	function getClumnNameList();
}

/*******************************************************************************************************/
/*******************************************************************************************************/
/*******************************************************************************************************/
/*******************************************************************************************************/

/**
 * 改行コード（\n）を<br/> に置き換えます
 * @param $str 文字列
 */
function brChange($str){
	return str_replace(  "\r", "", str_replace( "\n", "<br/>", $str )  );
}

/**
 * <br/>を改行コード（\n） に置き換えます
 * @param $str 文字列
 */
function brChangeRe($str){
	return str_replace("<br/>", "\n", $str);
}
?>