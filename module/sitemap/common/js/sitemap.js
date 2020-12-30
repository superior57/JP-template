function updatesitemap() {
    var flag = confirm('サイトマップを生成します｡');
    if (!flag) {
        return;
    }
    connectsitemapApi("updatesitemap");
}

function deletesitemap() {
    var flag = confirm('サイトマップを削除します｡');
    if (!flag) {
        return;
    }
    connectsitemapApi("deletesitemap");
}

var sitemap_lock_process = false;

function connectsitemapApi($iMethod_) {
    if (sitemap_lock_process !== false) {
        return;
    }
    sitemap_lock_process = true;
    jQuery.ajax({
        url: 'api.php',
        type: 'POST',
        dataType: 'json',
        data: 'm=' + $iMethod_ + '&c=sitemapApi',
    }).done(function (res) {
		if($iMethod_ == 'updatesitemap'){
			if(res.status=='OK'){
				alert('サイトマップファイルの生成が完了しました。\nページを更新します。');
			}else{
				alert(res.mes);
			}
		}else{
			if(res.status=='OK'){
				alert('サイトマップファイルの削除が完了しました。\nページを更新します。');
			}else{
				alert(res.mes);
			}
		}
        location.href = "index.php?app_controller=edit&type=sitemap_conf&id=ADMIN";
    }).always(function (res) {
        sitemap_lock_process = false;
    }).fail(function (xml, status, e) {
        alert('通信に失敗しました。');
    });
}
