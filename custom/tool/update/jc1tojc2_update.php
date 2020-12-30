<?php
include_once "custom/head_main.php";

$UPDATE_NAME = 'jc1tojc2';

$UPDATE_NAMES[] = $UPDATE_NAME;
$UPDATE_DESCRIPTION[$UPDATE_NAME] = <<< EOD
JC1からJC2へデータの取り込み.
<script type="text/javascript">
$(function(){
	var tk = $('input[type=submit][value="適用する"]');
	if($('input[type=submit][value="パッチの一覧に戻る"]').length>0 && tk.length>0){
		var tgt = $('td:contains("JC1")');
		tgt.html(tgt.html().replace('取り込み.','取り込み<br />詳細は<a href="module/jc1tojc2/Readme.txt" target="_blank">説明</a>をお読みください。<br/>準備ができましたら、「適用する」のボタンを押してください。'));
	}
	if(tk.length>0){
		tk.parent('form').submit( function(e){
			var btn = $(this).find('input[type=submit]:focus');
			btn.val('取り込み中……')
			btn.prop('disabled', 'disabled');
		});
	}
});
</script>
EOD;
$UPDATE_CLASS[$UPDATE_NAME] = "update_jc1tojc2";
$UPDATE_METHOD[$UPDATE_NAME] = "import";

class update_jc1tojc2 {

	/**
	 * ファイルPATHの再帰的取得
	 * @param type $dir
	 * @return type
	 */
	function getFile($dir) {

		$list = array();
		$files = glob($dir);

		foreach ($files as $file) {
			if (is_dir($file)) {
				$_files = self::getFile($file . '/*');
				$list = array_merge($list, $_files);
			} else if (is_file($file)) {
				$bname = strtolower(basename($file, '.csv'));
				$list[$bname] = $file;
			}
		}
		return $list;
	}

	function import() {
		global $JC1TOJC2;

		set_time_limit( 0 );
		ini_set("max_execution_time", 0);

		$jc1 = array();


		$jc1['path'] = 'import/';
		$jc1['lst_path'] = 'lst/';
		$jc1['tdb_path'] = 'tdb/';

		if(!is_dir($jc1['path'].$jc1['tdb_path'])){
			print realpath('./').'/'.$jc1['path'] . '<br /><b style="color:red;">JobCube1のデータが見付かりません。<br />データを配置してください</b><br />';
			return;
		}


		$jc1['tdb_list'] = self::getFile($jc1['path'] . $jc1['tdb_path'] . '*');
		$jc1['lst_list'] = self::getFile($jc1['path'] . $jc1['lst_path'] . '*');

		$num = 1;
		if ($_GET['num'] > 0) {
			$num = $_GET['num'];
		}

		foreach ($JC1TOJC2 as $type => $jc1_csv) {
			$class_name = ucfirst($type) . 'ImportLogic';
			if (!class_exists($class_name)) {
				$class_name = $type . 'ImportLogic';
				if (!class_exists($class_name)) {
					print '<span style="color:#BBBBBB;">'."Skip: {$type}</span><br />\n";
					$class_name = 'ImportLogic';
				}
			}else{
				print "Import: {$type}<br />\n";
			}
			$il = new $class_name($jc1['tdb_list'][$jc1_csv], $jc1['lst_list'][$jc1_csv]);
			$countList = $il->action();
		}
		print "\n";
	}

}

?>