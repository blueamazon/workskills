jQuery(document).ready(function($){

	$('#course_curriculum').on('active',function(){
		var unit_id = '';
		$('#add-h5p').text(default_wplms_h5p_strings.add_wplms_h5p);
		$('#add-h5p').on('click',function () {
			var unit_id = $(this).closest('.element_overlay').find('#save_element_button').attr('data-id');
			var $wrapper;
	      	// Open ThickBox
	      	tb_show($(this).attr('title'), '#TB_inline?inlineId=h5p-nope');
	      	$('#TB_window').addClass('wplms-h5p-insertion');
	      	$('#TB_window').css('overflow','auto');
	      	$('#TB_ajaxContent').css('overflow','initial');
	      	if ($wrapper === undefined) {
		        // Create new data view
		        var table = '<div style="margin:15px;" id="h5p-insert-content"><table style="width: 100%;" class="wp-list-table widefat fixed"><thead><tr><th role="button" tabindex="0">'+default_wplms_h5p_strings.title+'</th><th class="h5p-insert-link"></th></tr></thead>';
		        $.each(wplms_h5p_contents,function(k,v){
		        	table += '<tr><td>'+v.title+'</td><td><button class="button small wplms-h5p-insert" data-id="'+v.id+'" data-unit_id="'+unit_id+'">'+default_wplms_h5p_strings.insert+'</button></td></tr>';
		        });
		        table += '</table><div class="wplms_h5p_contents_pagination">';
		        if(wplms_h5p_contents.length > 10)
		        	table += '<a class="button small wplms_h5p_mext" data-page="2">'+default_wplms_h5p_strings.next+'</a>';
		        table +='</div></div>';
	 	        $wrapper = $('#TB_ajaxContent').append(table);
		    }
		     else {
		        // Append existing data view
		        $wrapper.appendTo('#TB_ajaxContent');
		     }
	      return false;
	      
	  	});

  		$('body').on('click','.wplms_h5p_contents_pagination a',function () {
	        // Inserting content
	       	var $this = $(this);
	       	$this.prepend('<i class="fa fa-spinner animated spin"></i>');
	       	$.ajax({
            type: "POST",
            url: ajaxurl,
            dataType:'json',
            data: { action: 'wplms_h5p_get_contents', 
                    security: default_wplms_h5p_strings.security,
                    page:$this.attr('data-page')
                  },
            cache: false,
            success:function (data){
            	jQuery('#h5p-insert-content table tbody').children( 'tr' ).remove();
            	$('#h5p-insert-content').find('.wplms_h5p_contents_pagination').find('a').remove();
            	var new_trs = '';
            	$.each(data.contents,function(k,v){
            		new_trs += '<tr><td>'+v.title+'</td><td><button class="button small wplms-h5p-insert" data-id="'+v.id+'" data-slug="new-h5p-update">'+default_wplms_h5p_strings.insert+'</button></td></tr>';
            	});
            	jQuery('#h5p-insert-content table tbody').append(new_trs);
            	if(data.paging != 'null' && typeof data.paging !== 'undefined' && data.paging != null){
                		data.paging.previous = parseInt(data.paging.previous);
                		data.paging.next = parseInt(data.paging.next);
                    if(data.paging.previous != 'null' && typeof data.paging.previous !== 'undefined' && data.paging.previous != null && data.paging.previous > 0){
                     
                       $('#h5p-insert-content').find('.wplms_h5p_contents_pagination').append(
                      '<a class="button small" data-page='+data.paging.previous+'">'+default_wplms_h5p_strings.previous+'</a>'
                      );
                    }
                    if(data.paging.next != 'null' && typeof data.paging.next !== 'undefined' && data.paging.next != null && data.paging.next > 0){
                       $('#h5p-insert-content').find('.wplms_h5p_contents_pagination').append(
                      '<a class="button small" data-page="'+data.paging.next+'">'+default_wplms_h5p_strings.next+'</a>'
                      );
                    }
                   
              }

            }
        	});
  		});
  		
	});
	$('body').on('click','.wplms-h5p-insert',function () {
    // Inserting content
        var contentId = $(this).data('id');
        var unit_id = $(this).data('unit_id');
        var win = window.dialogArguments || opener || parent || top; 
	 			win.send_to_editor('[wplms_h5p id="' + contentId + '" unit_id="'+unit_id+'"]');

        $('#TB_window').removeClass('h5p-insertion');
        tb_remove();
	});	
});