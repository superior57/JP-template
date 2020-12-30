$(function(){

/*****************************************************************************
 * 修正・追加表示非表示切り替え
 *****************************************************************************/
    $(document).on('mouseenter','[data-'+article_property.part_head+']', function(){
        if($(this).find('[data-'+article_property.edit_form+']').css('display') != 'none' || 
           $(this).find('[data-'+article_property.add_part_body+']').length > 0 )
        {
            $(this).find('[data-'+article_property.add_icon+']').hide();
            $(this).find('[data-'+article_property.part_tool+']').hide();
            $(this).find('[data-'+article_property.items_part+']').hide();
        }else if($(this).find('[data-'+article_property.items_part+']').css('display') != 'none')
        {    }
        else{
            $(this).find('[data-'+article_property.add_icon+']').show();
            $(this).find('[data-'+article_property.part_tool+']').show();
            $(this).find('[data-'+article_property.part_tool+']').find('[class$=_part]').show();
            if( $(this).next('[data-'+article_property.part_head+']').length == 0)
            {
                $(this).find('[data-'+article_property.tool_last+']').hide();
                $(this).find('[data-'+article_property.tool_down+']').hide();
            }
            if( $(this).prev('[data-'+article_property.part_head+']').length == 0)
            {
                $(this).find('[data-'+article_property.tool_top+']').hide();
                $(this).find('[data-'+article_property.tool_up+']').hide();
            }
        }
    }),
    $(document).on('mouseleave','[data-'+article_property.part_head+']', function(){
        if($(this).find('[data-'+article_property.edit_form+']').css('display') != 'none')
        {
            $(this).find('[data-'+article_property.part_tool+']').hide();
        }
        else if($(this).find('[data-'+article_property.items_part+']').css('display') != 'none')
        {    }
        else{
            $(this).find('[data-'+article_property.add_icon+']').hide();
            $(this).find('[data-'+article_property.part_tool+']').hide();
            $(this).find('[data-'+article_property.items_part+']').hide();
        }
    });

/*****************************************************************************
 * Sortable設定（アイテムのソート設定）
 *****************************************************************************/
    $('#'+article_property.part_list).sortable({
        placeholder: 'sort_highlight',
        cancel: '[data-'+article_property.add_part+']',
        start: function(ev, obj){
            //removePartForm();
            $('#'+article_property.part_list+' [data-'+article_property.part_head+'] [data-'+article_property.add_icon+']').hide();
            $('[data-'+article_property.part_tool+']').hide();
        },
        stop: function(ev, obj){
            var part = obj.item;
            loadTweet(part);
            $('#'+article_property.part_list+' [data-'+article_property.part_head+'] [data-'+article_property.add_icon+']').show();
            $('[data-'+article_property.part_tool+']').show();
            resetTools(part);
            partsIdList();
        },
        sort: function(ev, obj){
            $('#'+article_property.part_list+' [data-'+article_property.part_head+']'+' [data-'+article_property.add_icon+']').hide();
            $('[data-'+article_property.part_tool+']').hide();
        }
    });
    $('#'+article_property.part_list).bind('click.sortable mousedown.sortable',function(ev){
        ev.target.focus();
    });

/*****************************************************************************
 * 編集中に誤ってソートを実行しないように対応
 *****************************************************************************/
    $(document).on('mouseover','[data-'+article_property.view+']', function(){
        $('#'+article_property.part_list).sortable("enable");
    }),
    $(document).on('mouseout','[data-'+article_property.view+']', function(){
        $('#'+article_property.part_list).sortable("disable");
    });

    $(document).on('mouseover','[data-'+article_property.add_part_body+']', function(){
        $('#'+article_property.part_list).sortable("disable");
    }),
    $(document).on('mouseout','[data-'+article_property.add_part_body+']', function(){
        $('#'+article_property.part_list).sortable("enable");
    });

/*****************************************************************************
 * アイテムの上下入れ替え
 *****************************************************************************/
    $(document).on("click","[data-"+article_property.tool_top+"]",function(){
        var self = $(this).parents('[data-'+article_property.part_head+']');
        if( self.prev('[data-'+article_property.part_head+']').length > 0)
        {
            $('#'+article_property.part_list).prepend(self);

            loadTweet(self);
            partsIdList();
        }
        resetTools(self);
    });

    $(document).on("click",'[data-'+article_property.tool_up+']',function(){
        var self = $(this).parents('[data-'+article_property.part_head+']');
        if( self.prev('[data-'+article_property.part_head+']').length > 0)
        {
            self.prev('[data-'+article_property.part_head+']').before(self);

            loadTweet(self);
            partsIdList();
        }
        resetTools(self);
    });

    $(document).on("click",'[data-'+article_property.tool_down+']',function(){
        var self = $(this).parents('[data-'+article_property.part_head+']');
        if( self.next('[data-'+article_property.part_head+']').length > 0)
        {
            self.next('[data-'+article_property.part_head+']').after(self);

            loadTweet(self);
            partsIdList();
        }
        resetTools(self);
    });

    $(document).on("click",'[data-'+article_property.tool_last+']',function(){
        var self = $(this).parents('[data-'+article_property.part_head+']');
        if( self.next('[data-'+article_property.part_head+']').length > 0)
        {
            $('#'+article_property.part_list).append(self);

            loadTweet(self);
            partsIdList();
        }
        resetTools(self);
    });

/*****************************************************************************
 * 入力フォームを表示
 *****************************************************************************/
    $(document).on("click",'[data-'+article_property.part_add_frm+']',function(){
        var type = $(this).attr('data-type');

        var self = $(this).parents('[data-'+article_property.add_part+']');
        drawAddForm(type, self);
    });


/*****************************************************************************
 * 追加
 *****************************************************************************/
    $(document).on("click",'[data-'+article_property.part_add_btn+']',function(){
        var self = $(this).parents('[data-'+article_property.part_head+']' );
        var $sysForm = $('#'+article_property.original_form);
        var original_id  = $sysForm.find('[name=id]').val();
        self.find('[data-'+article_property.error_view+']').empty();

        var $inputs = self.find('input[type="file"]');
        $inputs.each(function(_, input) {
            if (input.files.length > 0) return;
            $(input).prop('disabled', true);
        });

        var formData = new FormData(this.form);
        formData.append("original_id", original_id);
        sendPartsData(formData,'regist').done(function(result) {
            var __ = self.next();
            if( result.status == 'success' ) {
                self.before(result.html);
                closedPartForm(self);
                partsIdList();
                scrollToParts(__.prev());
            }
            else {
                self.find('[data-'+article_property.error_view+']').text(result.error)
            }
        }).fail(function(result) {
            closedPartForm(self);
        }).always(function(){

        });
    });

/*****************************************************************************
 * 更新
 *****************************************************************************/
    $(document).on("click",'[data-'+article_property.update+']',function(){
        var self = $(this).parents('[data-'+article_property.part_head+']' );
        var $sysForm = $('#'+article_property.original_form);
        var original_id  = $sysForm.find('[name=id]').val();
        self.find('[data-'+article_property.error_view+']').empty();

        var $inputs = self.find('input[type="file"]');
        $inputs.each(function(_, input) {
            if (input.files.length > 0) return;
            $(input).prop('disabled', true);
        });

        var formData = new FormData(this.form);
        formData.append("original_id", original_id);
        sendPartsData(formData,'update').done(function(result) {
            var __ = self.next();
            if( result.status == 'success' ) {
                self.before(result.html);
                self.remove();
                scrollToParts(__.prev());
            }
            else {
                self.find('[data-'+article_property.error_view+']').text(result.error)
            }
        }).fail(function(result) {
            closedPartForm(self);
        }).always(function(){
            partsIdList();
        });
    });

/*****************************************************************************
 * 追加キャンセル
 *****************************************************************************/
    $(document).on("click",'[data-'+article_property.close_form+']',function(){
        var self = $(this).parents('[data-'+article_property.part_head+']' );
        closedPartForm(self);
    });

/*****************************************************************************
 * 削除
 *****************************************************************************/
    $(document).on("click",'[data-'+article_property.tool_del+']',function(){
        var self = $(this).parents('[data-'+article_property.part_head+']' );
        var $sysForm = $('#'+article_property.original_form);
        var original_id  = $sysForm.find('[name=id]').val();
        self.find('[data-'+article_property.error_view+']').empty();

        var selfForm = self.find('form');
        var p_id = selfForm.find('[name=parts_id]').val();
        var p_type = selfForm.find('[name=part_type]').val();

        var data = new FormData
        data.append('original_id', original_id);
        data.append('part_type', p_type);
        data.append('parts_id', p_id);
        data.append('m', 'deleteParts');
        data.append('c', 'articleApi');

        articleAjax(data).done(function(result){
            if( result.status == 'success' ) {
                self.remove();
                partsIdList();
            }
            else {
                articleAjaxSendFail();
            }
        }).fail(function(result) {
            articleAjaxSendFail();
        }).always(function(){
        });
    });

/*****************************************************************************
 * URLの有無
 *****************************************************************************/
    $(document).on("click",'[data-'+article_property.url_check+']',function(){
        var self = $(this).parents('[data-'+article_property.part_head+']');
        var form = self.find('form');
        var type = form.find('[name="part_type"]').val();
        var url = form.find('[name="body_url"]').val();

        var data = new FormData
        data.append('url', url);
        data.append('type', type);
        data.append('m', 'getHttpStatusCode');
        data.append('c', 'articleApi');

        articleAjax(data).done(function(result){
            if( result.status != 'success' ) {
                alert('URL先が確認できません。');
            }
            else {
                if( typeof result.src !== "undefined" ) {
                    var data = scraping( result.src );
                    form.find('[name="body_title"]').val(data['title']);
                    form.find('[name="body_description"]').val(data['description']);
                }
            }
        }).fail(function(result) {
           alert('URL先が確認できません。');
        }).always(function(){
        });
    });


/*****************************************************************************
 * URL画像の有無
 *****************************************************************************/
    $(document).on("click",'[data-'+article_property.img_url_chk+']',function(){
        var self = $(this).parents('[data-'+article_property.part_head+']' );
        var formData = new FormData(this.form);
        var type = formData.get('part_type');
        var url = $(this).find( '[name=url]' );
        checkImageURL( url, self, type );
    });


/*****************************************************************************
 * 動画URLおよび内容確認
 *****************************************************************************/
    $(document).on('click','[data-'+article_property.mv_url_chk+']', function() {
        var self = $(this).parents('[data-'+article_property.part_head+']' );
        var form = self.find('form');
        $(this).nextAll('.drawMovie').empty();
        var url = self.find("[name=body_url]").val();
        var string = url;
        var insert = $(this);

        delPartsErrorMessage(self);

        var prm = new FormData
        prm.append('url', url);
        prm.append('type', 'move');
        prm.append('m', 'getHttpStatusCode');
        prm.append('c', 'articleApi');

        articleAjax(prm).done(function(result){
            if( result.status != 'success' ) {
                alert('URL先が確認できません。');
            }
            else {
                if( typeof result.src !== "undefined" ) {
                    var data = checkYoutubeUrl(string);
                    var key = data['key'];
                    string = data['src'];

                    if( key == null || key[1] == null){
                        var msg = 'URLに誤りがある可能性があります。動画のURLを確認してください。';
                        addPartsErrorMessage(insert, msg, '', 'after');
                        return ;
                    }
                    var movetag = '<div class="youtube"><iframe width="420" height="315" src="//www.youtube.com/embed/'+ key[1] +'?rel=0&wmode=transparent" frameborder="0" allowfullscreen></iframe></div>';
                    insert.nextAll('.drawMovie').append(movetag);

                    var data = scraping( result.src );
                    form.find('[name="body_title"]').val(data['title']);
                }
            }
        }).fail(function(result) {
           alert('URL先が確認できません。');
        }).always(function(){
        });
    });

/*****************************************************************************
 * 追加アイテム欄の非表示
 *****************************************************************************/
    $(document).on('click', '[data-'+article_property.items_part_close+']', function(){

        var self = $(this).parents('[data-'+article_property.part_head+']');
        self.find('[data-'+article_property.items_part+']').hide();
        self.find('[data-'+article_property.add_part_body+']').remove();
        self.find('[data-'+article_property.add_icon+']').show();
        self.find('[data-'+article_property.part_tool+']').show();
        if ( self.length>0 ) { $(this).parent().hide(); }
    });

/*****************************************************************************
 * 追加アイテム欄の表示
 *****************************************************************************/
    $(document).on('click', '[data-'+article_property.add_icon+']', function(){
        var self = $(this).parents('[data-'+article_property.part_head+']');
        self.find('[data-'+article_property.items_part+']').show();
        $('[data-'+article_property.add_icon+']').hide();
        self.find('[data-'+article_property.part_tool+']').hide();
    });

/*****************************************************************************
 * 画像のURL指定
 *****************************************************************************/
    $(document).on("click","[data-"+article_property.thum_chk+"]",function(){
        if($("[name=url_img_used]").prop("checked"))
        {
            $('#'+article_property.thum_url_img).show();
        }else{
            $('#'+article_property.thum_url_img).hide();
        }
    });
});

/* 編集 */
$(document).on("click","[data-"+article_property.tool_edit+']',function(){
    $(this).parent('[data-'+article_property.part_tool+']').hide();
    var self = $(this).parents('[data-'+article_property.part_head+']');
    self.find('[data-'+article_property.add_icon+']').hide();
    var view_parts = self.find('[data-'+article_property.view+']');
    var edit_form = self.find('[data-'+article_property.edit_form+']');
    edit_form.show();
    self.find('[data-'+article_property.add_icon+']').hide();
    self.find('[data-'+article_property.items_part+']').hide();
    edit_form.append('<span data-'+article_property.e_flg+'></span>');

    var part_type = edit_form.find('[name="part_type"]').val();

    switch(part_type){
        case 'head':
        case 'text':
        case 'quote':
        case 'html':
        case 'link':
            view_parts.hide();
            break;
        case 'image':
            var clone = view_parts.find('img').clone(true);
            view_parts.hide();
            var obj = $('<div />');
            obj.attr('data-'+article_property.view_clone,article_property.view_clone);
            obj2 = $('<div />');
            obj2.attr('class','item_picture');
            obj2.append(clone);
            obj.append(obj2);
            edit_form.find('[name="part_type"]').after(obj);
            break;
        case 'move':
            var clone = view_parts.find('.drawMovie').clone(true);
            view_parts.hide();
            var obj = $('<div />');
            obj.attr('data-'+article_property.view_clone,article_property.view_clone);
            obj.append(clone);
            edit_form.find('h4').after(obj);
            break;
        }
});

/*****************************************************************************
 * 編集反映
 *****************************************************************************/
$(document).on("click",'[data-'+article_property.close_edit+']',function(){

    var self_part = $(this).parents('[data-'+article_property.part_head+']');
    delPartsErrorMessage(self_part);

    // ダミーで表示しているものを削除
    self_part.find('[data-'+article_property.view_clone+']').remove();

    self_part.find('[data-'+article_property.e_flg+']').remove();
    self_part.find('[data-'+article_property.part_tool+']').show();
    self_part.find('[data-'+article_property.add_icon+']').show();
    self_part.find('[data-'+article_property.view+']').show();
    self_part.find("[data-"+article_property.edit_form+"]").hide();

    scrollToParts(self_part);

});

/*****************************************************************************
 * タグの追加
 *****************************************************************************/
$(document).on('click','[data-'+article_property.add_tag+']',function(){
    var $sysForm = $('#'+article_property.original_form);
    var original_id  = $sysForm.find('[name=id]').val();
	var add_tag = $('[name=tag]').val();
	var tag_list = $('[name=tag_list]').val();
	if(add_tag.length < 1){
		alert('タグを入力して下さい');
		return ;
	}
	else if(add_tag.length > 20){
		alert('タグは20字以内にして下さい。');
		return ;
	}

    var data = new FormData
    data.append('original_id', original_id);
    data.append('m', 'addTag');
    data.append('c', 'articleApi');
    data.append('word', add_tag);

    articleAjax(data).done(function(result){
        if( result.status == 'success' ) {
            addTagList(result.word);
        }
        else {
            alert(result.msg);
        }
    }).fail(function(result) {
        articleAjaxSendFail();
    }).always(function(){
        $('[name=tag]').val('');
    });

});

$(document).on('click','[data-'+article_property.tagDel+']',function(){
    var del_tag = $(this).attr('data-'+article_property.tagDel);
    var self = $(this).parent('li');
    var $sysForm = $('#'+article_property.original_form);
    var original_id  = $sysForm.find('[name=id]').val();

    var data = new FormData
    data.append('original_id', original_id);
    data.append('m', 'delTag');
    data.append('c', 'articleApi');
    data.append('word', del_tag);

    articleAjax(data).done(function(result){
        if( result.status == 'success' ) {
            self.remove();
        }
        else {
            alert(result.msg);
        }
    }).fail(function(result) {
        articleAjaxSendFail();
    }).always(function(){
    });
});
