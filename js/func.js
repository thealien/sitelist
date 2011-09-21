function isValidEmail (email, strict){
    if (!strict) {
        email = email.replace(/^\s+|\s+$/g, '');
    }
    return (/^([a-z0-9_\-]+\.)*[a-z0-9_\-]+@([a-z0-9][a-z0-9\-]*[a-z0-9]\.)+[a-z]{2,4}$/i).test(email);
}
$(function(){

    $('#bm-btn').click(function(){
        alert('Перетяните кнопку букмарклета на панель вашего браузера'); return false;    
    });
    
    $('#cat-list li.cat-item').hover(
        function(){
            $(this).children(':first').addClass('hover');
        },
        function(){
            $(this).children(':first').removeClass('hover');
        }
    );
    
    $('a.t-up.active,a.t-down.active').click(function(){
        var vote = $(this).hasClass('t-up') ? 1 : -1;
        var p = $(this).parent().parent();
        var id = p.attr('id');
        id = id.split('site');
        if (!id[1]) {
            return false;
        }
        id = parseInt(id[1], 10);
        $.ajax({
            url: "/ajax/vote/",
            type: "POST",
            cache: false,
            data: ({
                link_id: id,
                vote: vote
            }),
            dataType: "json",
            success: function(data){
                if (data.rate!==false && data.votes!==false) {
                    if (data.rate > 0) {
                        data.rate = '+' + data.rate;
                    }
                    p.find('a.' + p.attr('id')).removeClass('active').attr('title', 'Вы уже голосовали');
                    p.children('li.' + p.attr('id')).text(data.rate).attr('title', 'Рейтинг: ' + data.rate + ' [голосов: ' + data.votes + ']');
                }
            }
        });
    });
    
    $('.genScreenBtn').click(function(){
        var linkId = $('#linkId').attr('value');
        if (linkId) {
            linkId = parseInt(linkId, 10);
            $('.ajaxProgress').show();
            $.ajax({
                url: "/ajax/screenshot/"+linkId,
                type: "POST",
                cache: false,
                data: ({
                    link_id: linkId
                }),
                dataType: "json",
                success: function(data){
                    if (data.result) {
                        document.location = document.location;
                    }
                    else {
                        $('.ajaxProgress').hide();
                        alert('Нет, попробуй еще раз.');
                    }
                }
            });
        }
    });
    
    var linkId = $('#linkId');
    if(linkId.length){
        linkId = parseInt(linkId.attr('value'), 10);
        if ($('#ci_is_old').length > 0) {
        if (linkId) {
            $.ajax({
                url: "/ajax/yandex/",
                type: "POST",
                cache: false,
                data: ({
                    link_id: linkId
                }),
                dataType: "json",
                success: function(data){
                    if (data.result) {
                        $('#yandex_ci').text(data.value);
                    }
                }
            });
        }
    }
    
    if ($('#pr_is_old').length) {
        if (linkId) {
            $.ajax({
                url: "/ajax/google/",
                type: "POST",
                cache: false,
                data: ({
                    link_id: linkId
                }),
                dataType: "json",
                success: function(data){
                    if (data.result) {
                        $('#google_pr').text(data.value);
                    }
                }
            });
        }
    }
       $('a.thumbnail.fancy').fancybox({
        'transitionIn': 'elastic',
        'transitionOut': 'elastic',
        'speedIn': 600,
        'speedOut': 200,
        'overlayShow': true,
        'titleShow' : false
        }); 
       
    }
    
    
    
    
	
	
    $('#cont_feedback').click(function(){
        $('#feedback_btn').click();
    });
	
	// Отправка фидбека
    $('#feedback form').submit(function(){
        $.ajax({
            type: "POST",
            url: "/ajax/feedback/",
            dataType: 'json',
            data: $(this).serialize(),
            cache: false,
            success: function(data){
				if(data)
                if(data.error){
                    $('#feedback-result').text('Ошибка: '+ data.message);
                }
                else{
					$('#feedback .fields').replaceWith('<p><b>Сообщение успешно отправлено.</b></p>');
                    //$('#feedback-result').text('Сообщение успешно отправлено!');
                }
            }
        });
        return false;
    });
	/**
	 * Управление сайтом/подборками
	 */
	// Кнопка редактирования/добавления сайта в подборки
	$('a.edit-collection-btn').live('click', function(){
		var panel = this;
		var linkId = $('#linkId').attr('value');
		if(!linkId) return false;
		$.ajax({
            url:'/ajax/GetEditSiteCollectionPanel/',
            data: {
				link_id: linkId
			},
            type: 'post',
            success: function(data){
				if(data){
				    $('div.collect-add-panel').html(data).slideDown();
                    $(panel).parent().slideUp();	
				}
            }
        });
		return false;
	});
	
	// Кнопка редактирования/добавления сайта в подборки
    $('a.cancel-edit-collection-btn').live('click', function(){
		$('div.collect-add-panel').slideUp();
        $('div.collect-info').slideDown();
		return false; 
		
		var panel = this;
        var linkId = $('#linkId').attr('value');
        if(!linkId) return false;
        $.ajax({
            url:'/ajax/GetInfoSiteCollectionPanel/',
            data: {
                link_id: linkId
            },
            type: 'post',
            success: function(data){
                if(data){
                    $('div.collect-add-panel').slideUp();
                    $('div.collect-info').slideDown();  
                }
            }
        });
        return false;
    });
	
	$('div.linksgroup div.site').hover(
	function(){
		$(this).addClass('hover');
	},
	function(){
		$(this).removeClass('hover');
	});
	
	
	$('#broken-site .send-btn a.send-btn-link').click(function(){
		var linkId = $('#linkId').attr('value');
		var btn = this;
		$.ajax({
            url:'/ajax/SendBrokenSite/',
            data: {link_id: linkId},
            type: 'post',
            success: function(data){
                if(data){
					$(btn).parent().html('Спасибо, мы проверим!');
				}
            }
        });
        return false;
	});

});

function sendComment(form){
    $.ajax({
        url:'/ajax/CommentAdd/',
        data: form.serialize(),
        type: 'post',
		dataType:'text',
        success: function(data){
			data = $.parseJSON(data);
			if(data){
				if(!data.error){
				    $('#comments').prepend(data.message);	
					$('textarea.desc', form).val('');
					$('#errors').html('');
				}
				else{
					$('#errors').html(data.message);
					$('img', form).click();
				}
			}
        }
    });
    return false;
}

function saveSiteCollections(form){
	$.ajax({
		url:'/ajax/SaveSiteCollection/',
		data: form.serialize(),
		type: 'post',
		success: function(data){
			$('div.collect-info').html(data).slideDown();
			$('div.collect-add-panel').slideUp();
		}
	});
	return false;
}

function deleteSiteFromCollection(link, collection){
	if(!confirm('Удалить сайт из подборки?')) return false;
	$.ajax({
        url:'/ajax/deleteSiteFromCollection/',
        data: {
			link_id: link,
			collection_id: collection
		},
        type: 'post',
        success: function(data){
            if(data){
				$('#site_'+link).slideUp();
			}
        }
    });
    return false;
}
