<?php
/*******************************************************************************************************
 * <PRE>
 *
 * コメントコマンド実装クラス用ベースクラス
 * 共通メソッド保持クラス
 *
 * @author 吉岡幸一郎
 * @original 丹羽一智
 * @version 2.0.0
 *
 * </PRE>
 *******************************************************************************************************/

class command_base
{
    var $buffer = "";
    
    /**
     * 出力バッファを初期化。
     */
    function flushBuffer()	{ $this->buffer	 = ""; }

    /**
     * 出力バッファにデータを追加。
     */
    function addBuffer($str)
    {
        $this->buffer	 .= str_replace( Array("/"," "), Array("!CODE000;","!CODE001;"), $str );
    }
    
    /**
     * 出力バッファの内容を取得。
     * @return バッファの内容
     */
    function getBuffer(){
    	global $BUFFER_FILTER;
    	if( $BUFFER_FILTER != null ){
    		return $BUFFER_FILTER->filter($this->buffer);
    	}
    	return $this->buffer;
    }

}
?>