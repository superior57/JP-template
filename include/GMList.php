<?PHP

/*******************************************************************************************************
 * <PRE>
 * 
 * 再利用用のGUIマネージャ生成クラス。
 * ※注意点：ここで生成されるGUIManagerに属するSQLDatabaseは、他メッソド内でも使われる可能性が高い為、キャッシュの継続保持性に保証がない。
 *         その為、コアで使う場合はSystemUtil::getGMforTypeを利用するか、グローバルな$gmを利用すべきである。
 * 
 * @author 勝連
 * @version 1.0.0
 * 
 * </PRE>
 *******************************************************************************************************/

class GMList
{
	static $gmList;
	
	/**
	 * GMオブジェクトを取得する
	 * 
	 * @param name テーブル名。
	 * @return GUIManager GMオブジェクト。
	 */
	static function getGM( $name )
	{
		if( !isset( self::$gmList[$name] ) ) { self::$gmList[$name] = SystemUtil::getGMforType($name); }
		
		return self::$gmList[$name];
	}
	
	/**
	 * DBオブジェクトを取得する
	 * 
	 * @param name テーブル名。
	 * @return SQLDatabase DBオブジェクト。
	 */
	static function getDB( $name )
	{
		if( !isset( self::$gmList[$name] ) ) { self::$gmList[$name] = SystemUtil::getGMforType($name); }
		
		return self::$gmList[$name]->getDB();
	}
	
}

?>