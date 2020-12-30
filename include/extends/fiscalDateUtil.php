<?php
class fiscalDateUtil{
	private $current = 0;
	private $closeDay = null;
	private $nextExeDate = 0;

	/**
	 * @param int $close
	 */
	function __construct($close){
		$this->closeDay = $close;
	}

	//現在時刻を設定する
	function setCurrent($time = null){
		if(!empty($time))
			$this->current = $time;
		else{
			$this->current = time();
		}
		$this->setNextCloseTime();
	}

	//今日が締め日かどうか
	function onClose(){
		if($this->closeDay == 0){
			$result = $this->getNow("d") == $this->getNow("t");
		}else{
			$result = $this->getNow("d") == $this->closeDay;
		}
		return $result;
	}

	//デバッグ用
	function f($time){
		return date("Y/m/d H:i:s",$time);
	}

	//締め日の設定値を取得
	function getCloseDay(){
		return $this->closeDay;
	}

	//実行時の日付を取得
	function getNow($type = null){
		switch($type){
			case "y":
				$result = date("Y",$this->current);
				break;
			case "m":
				$result = date("n",$this->current);
				break;
			case "d":
				$result = date("j",$this->current);
				break;
			case "t":
				$result = date("t",$this->current);
				break;
			default:
				$result =  $this->current;
				break;
		}
		return $result;
	}

	//currentを基準とした次回締め日のTSを取得する
	function getClose($type = null){
		switch($type){
			case "y":
				$result = date("Y",$this->nextExeDate);
				break;
			case "m":
				$result = date("n",$this->nextExeDate);
				break;
			case "d":
				$result = date("j",$this->nextExeDate);
				break;
			case "t":
				$result = date("t",$this->nextExeDate);
				break;
			default:
				$result = $this->nextExeDate;
				break;
		}
		return $result;
	}

	//指定の年月度の範囲のTSを取得する
	function getRange($year,$month){
		if((is_null($year) && $year == "") || (is_null($month) && $month == "")) return false;

		if($this->closeDay == 0){
			$range["s"] = mktime(0,0,0,$month-1,1,$year);
			$range["e"] = mktime(23,59,59,$month-1,date("t",mktime(0,0,0,$month-1,1,$year)),$year);
		}else{
			$range["s"] = mktime(0,0,0,$month-1,$this->closeDay+1,$year);
			$range["e"] = mktime(23,59,59,$month,$this->closeDay,$year);
		}

		return $range;
	}

	//currentを基準に次回の締め日のTSをセットする
	private function setNextCloseTime(){
		if($this->closeDay == 0){
			if($this->getNow("t") == $this->getNow("d")){
				$this->nextExeDate = strtotime("last day of next month",$this->getNow());
			}else{
				$this->nextExeDate = mktime(0,0,0,$this->getNow("m"),$this->getNow("t"),$this->getNow("y"));
			}
		}else{
			if($this->closeDay <= $this->getNow("d")){
				$this->nextExeDate = mktime(0,0,0,$this->getNow("m")+1,$this->closeDay,$this->getNow("y"));
			}else{
				$this->nextExeDate = mktime(0,0,0,$this->getNow("m"),$this->closeDay,$this->getNow("y"));
			}
		}
	}

}