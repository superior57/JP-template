<?PHP

class ConfView extends command_base
{
	/**
	 * コンフィグ情報を表示
	 *
	 * @param type 取得するテーブル
	 * @param col 取得するカラム
	 * @return 設定
	 */
	function drawData( &$gm, $rec, $args )
	{
		$type = $args[0];
		$col = $args[1];
		$this->addBuffer( Conf::getData( $type, $col ) );
	}

	function checkData( &$gm, $rec, $args )
	{
		$type = $args[0];
		$col = $args[1];
		$val = $args[2];

		$result = false;
		$checkList = explode( '/', Conf::getData( $type, $col ) );
		foreach( $checkList as $check ) { if( $check == $val ) { $result = true; } }

		$this->addBuffer($result ? "TRUE":"FALSE");
	}


}

?>