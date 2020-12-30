var SendCommon = function(){};

SendCommon.prototype = {
	recID			:null,			//DM送信のレコードID
	receive_list	:null,			//送信ID配列
	sendTotalCnt	:0,				//送信総数

	set				:null,				//現在のポインタ
	now_pt			:0,				//現在のポインタ

	success_id		:new Array(),	//送信成功したID配列
	success_cnt		:0,				//送信成功件数
	through_id		:new Array(),	//スルーしたID配列
	through_cnt		:0,				//送信スルー件数
	faled_id		:new Array(),	//送信失敗したID配列
	faled_cnt		:0,				//送信失敗件数
	sendCompleteFlg	:false,			//送信完了フラグ
	abortFlg		:false,			//中断フラグ

	init:function(){
		this.clear();

		this.recID = $("input:hidden[name='id']").val();
		this.receive_list = $("input:hidden[name='list_id']").val().split("/");
		this.set = $("input:hidden[name='set']").val();
		this.sendTotalCnt = this.receive_list.length;
		this.bindEvent();
	},
	sendStart:function(){
		this.init();
		if(!this.existsList()){
			alert("送信を開始出来ませんでした");
			this.abort();
			return;
		}
		$("#progressbar").reportprogress(0);

		this.sendMail();
	},
	sendMail:function(){
		this.setProgress();
		var receive = this.receive_list.shift();
		var self = this;
		jQuery.ajax({
			cache: false,
			url: "api.php",
			type: 'POST',
			dataType: "json",
			data: 'c=mailSendApi&m=send&id=' + self.recID + '&receive_id=' + receive
		})
		.done(function(res){
			if(res.faled){
				self.faled_cnt++;
				self.faled_id.push(receive);
			}else if(res.through){
				self.through_cnt++;
				self.through_id.push(receive);
			}else{
				self.success_cnt++;
				self.success_id.push(receive);
			}
		})
		.fail(function(xhr,status,thrown){
			self.faled_cnt++;
			self.faled_id.push(receive);
		})
		.always(function(xhr, status){
			self.now_pt++;
			self.nextMail();
		});
	},
	nextMail:function(){
		if(this.now_pt < this.sendTotalCnt  && (this.set > this.now_pt || this.set == 0) && this.abortFlg == false){
			this.sendMail();
		}else{
			this.sendCompleteFlg = true;
			this.sendComp();
		}
	},
	sendComp:function(){
		this.setProgress();
		$("input:hidden[name='list_id']").val(this.receive_list.join("/"));

		var self = this;
		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "text",
			data: 'c=mailSendApi&m=complete&id=' + this.recID + '&success_id=' + this.success_id.join("/") + '&through_id=' + this.through_id.join("/") + '&faled_id=' + this.faled_id.join("/")
		})
		.done(function(res){
			$('#main_message').html( ' メールの送信が完了しました。<br><br>'+self.sendTotalCnt + '件中' + self.success_cnt +'件のメールの送信に成功、'+self.through_cnt+'件が送信除外されました。');
			self.drawList();
		})
		.fail(function(){});
	},
	drawList:function(){
		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "json",
			data: 'c=mailSendApi&m=drawSendList&id=' + this.recID + '&current=0'
		})
		.done(function(res){
			$("#sendList").html(res["html"]);
		})
		.fail(function(){});
},
	setProgress:function(){
		$('#progressbar').reportprogress((this.now_pt/this.sendTotalCnt)*100);
		$('#mail_total').html( this.sendTotalCnt+"件" );
		$('#mail_count').html( this.now_pt+"件/"+this.sendTotalCnt+"件" );
		$('#mail_success').html( this.success_cnt+"件/"+this.sendTotalCnt+"件" );
		$('#mail_through').html( this.through_cnt+"件/"+this.sendTotalCnt+"件" );
		$('#mail_faled').html( this.faled_cnt+"件/"+this.sendTotalCnt+"件" );
	},
	clear:function(){
		this.recID 			= null;
		this.receive_list 	= null;
		this.sendTotalCnt 	= 0;
		this.set 			= null;
		this.now_pt 		= 0;
		this.success_id 	= new Array();
		this.success_cnt 	= 0;
		this.through_id 	= new Array();
		this.through_cnt 	= 0;
		this.faled_id 		= new Array();
		this.faled_cnt		= 0;
		this.sendCompleteFlg = false;
		this.abortFlg		= false;
	},
	abort:function(){
		var self = this;
		jQuery.ajax({
			url: 'api.php',
			type: 'POST',
			dataType: "text",
			data: 'c=mailSendApi&m=reSendComp&id=' + this.recID + '&success_id=' + this.success_id.join("/") + '&through_id=' + this.through_id.join("/") + '&faled_id=' + this.faled_id.join("/")
		}).done(function(res){
				$('#main_message').html( ' メールの送信が完了しました。<br><br>'+self.sendTotalCnt + '件中' + self.success_cnt +'件のメールの送信に成功、'+self.through_cnt+'件が送信除外されました。');
				self.drawList();
		}).fail(function(){

		}).always(function(xhr, status){
				self.abortFlg = true;
		});

	},
	bindEvent:function(){
		var self = this;
		$(window).on('beforeunload', function(event) {
			if (!self.sendCompleteFlg && !self.abortFlg) {
				self.abort();
				$(this).off(event);
;
				event.returnValue = 'ブラウザ操作により処理が途中終了しました。';
				return 'ブラウザ操作により処理が途中終了しました。';
			}
		});
	},
	existsList:function(){
		return $.grep(this.receive_list, function(e){return e !== "";}).length != 0;
	}
};