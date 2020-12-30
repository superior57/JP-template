<?php

	/*******************************************************************************************************
	 * <PRE>
	 * 
	 * WEBAPIの呼び出しを行なうクラスのベースクラス
	 * 
	 * @author 吉岡 幸一郎
	 * @version 1.0.0
	 * 
	 * </PRE>
	 *******************************************************************************************************/

//Base Class
class WebAPIConnection{
    var $body = null;
    var $xml_obj = null;
    
    function request($host,$url,$method){

    
        $head = Array( "Host"=>$host );
    
        $status = HttpUtil::http_request($method,$url,"1.0",$head,$host);
        switch( substr( $status["Status-Code"] , 0 , 1) ){
            default:
                $this->body = $status["entity-body"];
                break;
            case '9':
                return false;
        }
        return true;
    }
    
    function getXMLObject(){
        if(!is_null($this->body))
            $this->xml_obj = simplexml_load_string($this->body);
        return $this->xml_obj;
    }

}
?>