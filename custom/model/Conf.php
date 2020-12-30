<?php

class Conf
{
	static $recList;

	/**
	 * コンフィグ情報を取得
	 *
	 * @param type 取得するテーブル
	 * @param col 取得するカラム
	 * @return 設定
	 */
	static function getData( $type, $col )
	{
		$db = GMList::getDB($type.'_conf');
		if( !isset(self::$recList[$type]) ) { self::$recList[$type] = $db->selectRecord('ADMIN'); }

		return $db->getData( self::$recList[$type], $col );
	}


	/**
	 * コンフィグ情報に当該値があるか確認
	 *
	 * @param type 取得するテーブル
	 * @param col 取得するカラム
	 * @param val 存在するか確認する値
	 * @return 存在する場合trueを返す
	 */
	static function checkData( $type, $col, $val )
	{
		$result = false;
		$checkList = explode( '/', self::getData( $type, $col ) );
		foreach( $checkList as $check ) { if( $check == $val ) { $result = true; } }

		return $result;
	}

	static function update( $type, $col, $val ){
		$db = GMList::getDB($type.'_conf');
		$rec = $db->selectRecord("ADMIN");
		$db->setData($rec,$col,$val);
		$db->updateRecord($rec);
	}
}

?>