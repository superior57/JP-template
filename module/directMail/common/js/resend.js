var Resend = function(){};
$.extend(Resend.prototype,SendCommon.prototype,{

	eventButton		:null,

	sendStart:function(elm){
		this.eventButton = elm;
		var flag = confirm ( "再送を開始します。" );
		if(flag){
			this.init();
			if(!this.existsList()){
				this.abort();
				alert("送信を開始出来ませんでした");
				return;
			}
			$(".info_table").show();
			$('#main_message').html('');
			$("#progressbar").reportprogress(0);
			$(this.eventButton).val("処理しています...").attr("disabled","disabled");
			this.sendMail();
		}else
			{ alert("キャンセルしました。"); }
	},
    sendComp:function(){
        this.setProgress();
        $("input:hidden[name='list_id']").val(this.faled_id.join("/"));

        if(this.faled_cnt == 0)
            { $(this.eventButton).remove(); }
        else
            { $(this.eventButton).removeAttr("disabled").val("未送信分を再送する"); }

        var self = this;
        jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "text",
			data: 'c=mailSendApi&m=reSendComp&id=' + this.recID + '&success_id=' + this.success_id.join("/") + '&through_id=' + this.through_id.join("/") + '&faled_id=' + this.faled_id.join("/")
		})
		.done(function(res){
			$('#main_message').html( ' メールの送信が完了しました。<br><br>'+self.sendTotalCnt + '件中' + self.success_cnt +'件のメールの送信に成功、'+self.through_cnt+'件が送信除外されました。');
			self.drawList();
			$(self.eventButton).removeAttr("disabled").val("未送信分を再送する");
		})
		.fail(function(){})
		.always(function(){
			$("span#DM_through").text(parseInt($("span#DM_through").text()) + self.through_cnt);
			$("span#DM_success").text(parseInt($("span#DM_success").text()) + self.success_cnt);
		});
    },
    abort:function(){
        var self = this;
        jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "text",
			data: 'c=mailSendApi&m=reSendComp&id=' + this.recID + '&success_id=' + this.success_id.join("/") + '&through_id=' + this.through_id.join("/") + '&faled_id=' + this.faled_id.join("/")
		})
		.done(function(res){
			$('#main_message').html( ' メールの送信が完了しました。<br><br>'+self.sendTotalCnt + '件中' + self.success_cnt +'件のメールの送信に成功、'+self.through_cnt+'件が送信除外されました。');
			self.drawList();
			$(self.eventButton).removeAttr("disabled").val("未送信分を再送する");
		})
		.fail(function(){})
		.always(function(xhr, status){
			self.abortFlg = true;
		});
    }


});

(function(){
	$(function(){
		$("#resendButton").click(function(){
			var resend = new Resend();
			resend.sendStart(this);
		});
	})
})();