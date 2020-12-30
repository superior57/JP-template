<?php

	$moduleName = 'socialIcon';

	$MODULES[ $moduleName ] = Array(
		'version' => '1.0'                                  , ///<このモジュールのバージョン情報。
		'summary' => 'ソーシャルアイコン管理' , ///<このモジュールの説明。
	);

	/*
	<!--# mod socialIcon drawSocial   (!--# syscode drawTitle #--) #-->

	<table>
		<caption>システム設定</caption>
		<tr>
			<th style="width:220px;">ソーシャルアイコン</th>
			<td>
			<!--# form checkbox social_icon   mixi/hatena/twitter/facebook mixi/はてな/Twitter/Facebook #-->
			</td>
		</tr>
		<tr>
			<th>mixiチェックキー</th>
			<td>
			<!--# form text mixi_check 64 256  #-->
			<span class="hint">ソーシャルアイコンにてmixiを選択された場合はmixiチェックキーを入力してください</span>
			</td>
		</tr>
	</table>

	<table>
		<caption>システム設定</caption>
		<tr>
			<th style="width:220px;">ソーシャルアイコン</th>
			<td><!--# arrayReplace social_icon / mixi/hatena/twitter/facebook mixi/はてな/Twitter/Facebook #--></td>
		</tr>
		<tr>
			<th>mixiチェックキー</th>
			<td><!--# value mixi_check #--></td>
		</tr>
	</table>
	*/

	$ADD_LST[ "system" ][ 'socialIcon' ]    = '[socialIcon]/add/system.csv';

	include_once './module/socialIcon/socialIcon.php';

	$css_file_paths[ 'all' ][ 'socialIcon' ] = './module/socialIcon/common/css/socialIcon.css';
