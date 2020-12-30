(function() {
    var lockSendFlg = true;
    var beforeText = '';
    function sendTestMailAjax(element) {
        if(lockSendFlg) {
			$('#log').html('');
            beforeText = $(element).text();
            $(element).text($(element).data('send-text'));
            $(element).prop('disabled', lockSendFlg);
            lockSendFlg = false;

            var $form = $(element).closest('form');
            var obj = serializer($form);
            obj['c'] = 'smtpApi';
            obj['m'] = 'sendTestMail';
//            obj['user_api_token'] = $USER_API_TOKEN;
            $.ajax({
                url: 'api.php',
                type: 'POST',
                dataType: 'json',
                scriptCharset: 'UTF-8',
                data: obj,
                timeout: 0
            }).done(function($res) {
                switch($res.result) {
                    case 'success':
                    case 'error':
						$('#log').html($res.log);
                        alert($res.message);
                        break;
                    default:
                        alert('送信エラー');
                }
            }).fail(function() {
                alert('送信エラー');
            }).always(function() {
                $(element).text(beforeText);
                $(element).prop('disabled', lockSendFlg);
                lockSendFlg = true;
            });
        }
    }
    function serializer(target_form) {
        var object = {};
        var array = target_form.serializeArray();
        $.each(array, function() {
            if (object[this.name] !== undefined) {
                if (!object[this.name].push) {
                    object[this.name] = [object[this.name]];
                }
                object[this.name].push(this.value || '');
            } else {
                object[this.name] = this.value || '';
            }
        });
    
        return object;
    };
    window.sendTestMailAjax = sendTestMailAjax;
})();