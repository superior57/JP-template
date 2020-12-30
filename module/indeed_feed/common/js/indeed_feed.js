function updateIndeedFeed() {
    var flag = confirm('Indeed XMLフィードを更新します｡');
    if (!flag) {
        return;
    }
    connectIndeedFeedApi("updateIndeedFeed");
}

function connectIndeedFeedApi($iMethod_) {
    jQuery.ajax({
        url: 'api.php',
        type: 'POST',
        dataType: 'text',
        data: 'm=' + $iMethod_ + '&c=indeed_feedApi',
    }).done(function (res) {
        alert('Indeed XMLフィードの更新が完了しました。\nページを更新します。');
        location.reload();
    }).fail(function (xml, status, e) {
        alert('通信に失敗しました。');
    });
}
