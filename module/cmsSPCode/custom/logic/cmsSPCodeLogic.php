<?php
class cmsSPCodeLogic {

	static function convertSpecialChars($str,$type){
		global $REPLACE_CC;
		preg_match_all("/\[###[^\]]{0,}###\]/s",$str,$matchArr);
		foreach($matchArr[0] as $val){
			preg_match("/\[###--[^-]{0,}--###\]/s",$val,$matchArr2);
			if($matchArr2[0]!=$val){
				$str=str_replace($val, "", $str);
			}else{
				$okArr[]=$val;
			}
		}
		array_unique((array)$okArr);
		foreach((array)$okArr as $val){
			$str2=$val;
			$str2=preg_replace("/&lt;/","<",$str2);
			$str2=preg_replace("/&gt;/",">",$str2);

			$str2=preg_replace("/\[###--code/","<!--# code",$str2);
			$str2=preg_replace("/\[###--ecode/","<!--# ecode",$str2);

			if(array_key_exists($str2, $REPLACE_CC[$type]))
				$str2 = $REPLACE_CC[$type][$str2];
			else
				$str2 = preg_replace("/\[###--/","<!--# value ",$str2);

			$str2=preg_replace("/--###\]/"," #-->",$str2);
			$str=str_replace($val, $str2, $str);
		}
		return $str;
	}
}