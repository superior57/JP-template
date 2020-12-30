$(function(){
	var flag = $("input[name='reserve_flag']:checked").val() == "FALSE";
	switchDisabled(flag,$("#reserveConf *"));
	var get = getGetParam();

	$("table#sendInfo tr").each(function(i,ui){
		addMailEvent($(this));
	});

	$("#setTemplate").on("click",function(e){
		var templateID = $("select[name='template']").val();
		setValues(templateID);
	});

	$(".checkSend").on("click",function(e){
		id = getCheckboxValueList('id');
		if(id.length != 0 ){
			$("form[name='mailsend_form']").submit();
		}else{
			alert("送信先が選択されていません。");
		}
	});
})
$.fn.extend({
	insertAtCaret: function(v) {
	var o = this.get(0);
	o.focus();
	if (!jQuery.support.noCloneChecked) {
		var r = document.selection.createRange();
		r.text = v;
		r.select();
	} else {
		var s = o.value;
		var p = o.selectionStart;
		var np = p + v.length;
		o.value = s.substr(0, p) + v + s.substr(p);
		o.setSelectionRange(np, np);
	}
	}
});
function setValues(tid){
	jQuery.ajax({
		url: 'api.php',
		type: 'POST',
		dataType: "json",
		data: "c=mailTemplateApi&m=getMailTemplateData&tid=" + tid
	})
	.done(function(res){
		for (var key in res){
			$("[name='"+key+"']").val(res[key]);
		}
	})
	.fail(function(xml, status, e){});
}

function insertCode(elm,areaID){
	$("[name="+areaID+"]",elm).insertAtCaret($(".hensu",elm).val());
}

function switchDisabled(flag,disElement){
	if(flag)
		disElement.attr("disabled",true);
	else
		disElement.attr("disabled",false);
}

function addMailEvent(elm){
	elm.find("input.insertSub").on("click",function(e){
		insertCode(elm,"sub");
	});

	elm.find("input.insertMain").on("click",function(e){
		insertCode(elm,"main");
	});

	elm.find("input[name='reserve_flag']").on("change",function(e){
		var flag = this.value == "FALSE";
		switchDisabled(flag,$("#reserveConf *",elm));
	});

	elm.find("#sendTo").on("change",function(){
		if(this.value){
			window.location.href = "index.php?app_controller=register&type=mailTemplate&user_type="+this.value;
		}
	});
}

function changeDestination(elm){
	if(elm.value){
		var form = document.createElement( 'form' );
		document.body.appendChild( form );
		var receive = document.createElement( 'input' );
		receive.setAttribute( 'type' , 'hidden' );
		receive.setAttribute( 'name' , 'receive_id' );
		receive.setAttribute( 'value' , elm.value );
		var mailSend = document.createElement( 'input' );
		mailSend.setAttribute( 'type' , 'hidden' );
		mailSend.setAttribute( 'name' , 'mailSend' );
		mailSend.setAttribute( 'value' , true );
		var type = document.createElement( 'input' );
		type.setAttribute( 'type' , 'hidden' );
		type.setAttribute( 'name' , 'type' );
		type.setAttribute( 'value' , "mailSend" );
		form.appendChild( type );
		form.appendChild( receive );
		form.appendChild( mailSend );
		form.setAttribute( 'action' , 'regist.php' );
		form.setAttribute( 'method' , 'get' );
		form.submit();
	}
}