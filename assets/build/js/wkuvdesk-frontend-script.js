$uv=jQuery.noConflict();

(function($uv){

	$uv(document).ready(function(event){
        /**Filter view */
		$uv(".wkuvdesk-filter-view").on("click",function(evt){
			elem = $uv(evt.target);
			field = elem.data('value');
			var order = elem.attr('data-order');
			page_link = $uv("#page_link").val();
			$uv('.selected-option').html(elem.html()) ;

			if(field){
				$uv.ajax({
			      type: 'POST',
			      url: wkuvdesk_api_script.api_admin_ajax,
			      data: { "action": "sort_ticket_via_api", "nonce":wkuvdesk_api_script.api_nonce, "field":field, "order":order, 'is_admin':false, 'page_link':page_link },
			      beforeSend: function() {
				     $uv(".wkuvdesk-pre-loader").show();
				   },
				   success: function(response) {
				      	if(response.success==true){
				      		$uv(".wkuvdesk-customer-ticket-section").empty();
				      		$uv(".wkuvdesk-customer-ticket-section").append(response.data);
									if( order == 'asc' ){
										// elem.data('order','desc');
										elem.attr('data-order', 'desc')
									}else{
										// elem.data('order','asc');
										elem.attr('data-order', 'asc')
									}
									$uv('.wkuvdesk-filter-view').css('display','none');
									$uv('.wkuvdesk-down-up-arrow').removeClass('down');
                            }

				       	$uv(".wkuvdesk-pre-loader").hide();
				     }
			    });
			}

			evt.preventDefault();
		});

        /** Tab filter */
		$uv("#wkuvdesk-tab-id-filter").on("click",function(evt){
            selected = $uv(evt.target).data('value');

			if(selected){
				$uv.ajax({
			      type: 'POST',
			      url: wkuvdesk_api_script.api_admin_ajax,
			      data: {"action": "sort_customer_ticket_via_status","nonce":wkuvdesk_api_script.api_nonce,"field":parseInt(selected)},
			      beforeSend: function(){
				     $uv(".wkuvdesk-pre-loader").show();
				   },
				   success: function(response)
				    {
				      	if(response.success==true){
							$uv('#wkuvdesk-tab-id-filter ul li').removeClass('tab-active');
				      		$uv(".wkuvdesk-customer-ticket-section").empty();
							$uv(evt.target).addClass('tab-active');
				      		$uv(".wkuvdesk-customer-ticket-section").append(response.data);

				      	}

				       	$uv(".wkuvdesk-pre-loader").hide();
				     }
			    });
			}

			evt.preventDefault();
		});

        /** Load More */
		$uv(document).on("click","#ajax-load-page",function(evt){
    		 page = $uv(evt.target).data('page') || 1;
			if(page){
				$uv.ajax({
			      type: 'POST',
			      url: wkuvdesk_api_script.api_admin_ajax,
			      data: {"action": "get_thread_data_customer","nonce":wkuvdesk_api_script.api_nonce,"page_no":page},
			      beforeSend: function(){
				     $uv(".wkuvdesk-pre-loader").show();
				   },
                    success: function (response) {
				      	if(response.success==true){
							$uv("#ajax-load-page").remove();
							$uv(document).find('#wkuvdesk-content-here-aj').prepend(response.data);
				      	}

				       	$uv(".wkuvdesk-pre-loader").hide();
				     }
			    });
			}

			evt.preventDefault();
		});

        /**Toggable the ticket**/
		$uv(".wkuvdesk-star-ticket").on("click",function(e){
			tid=$uv(this).data('id');
			strno =$uv(this).data('star-val');
            str_no = strno === 1 ? 0 : 1;
            if (tid) {
				$uv.ajax({
				      type: 'POST',
				      url: wkuvdesk_api_script.api_admin_ajax,
				      data: {"action": "toggle_the_starred","nonce":wkuvdesk_api_script.api_nonce,"ticket_id":tid,"stared_no":str_no},
				      beforeSend: function(){
					     $uv(".wkuvdesk-pre-loader").show();
					   },
					   success: function(response) {
					    if(response.success == true){
							location.reload();
							$uv(".wkuvdesk-pre-loader").hide();
   						}
				      }
				});
			}
		});

        var fileuploader = $uv('.labelWidget').clone();
		var maxAppends = 2;
		var currentAppends = 0;
		$uv('#wkuvdesk-addFile').on('click', function () {
			if (currentAppends < maxAppends) {
				$uv(this).before(fileuploader.clone());
				currentAppends++;
			}
		});
		$uv(document).on('change', '#wkuvdesk-attachments', function () {
    	    $uv(this).siblings('.attach-file').addClass('close');
		});

		$uv( document ).on('click','.wkuvdesk-remove-file',function(){
			$uv(this).closest('div').remove();
		});

		$uv(".wkuvdesk-filter-left").on("click",function(e){
			if( $uv('.wkuvdesk-filter-view').css('display').toLowerCase() == 'block') {
				$uv('.wkuvdesk-filter-view').css('display','none');
				$uv('.wkuvdesk-down-up-arrow').removeClass('down');
			}else {
				$uv('.wkuvdesk-filter-view').css('display','block');
				$uv('.wkuvdesk-down-up-arrow').addClass('down');
			}
        })

        $uv(".wkuvdesk-agent").on("change",function(evt){
			agent=$uv(this).val();
			ticket_checked=[];
			ticket_checked=$uv(this).data('id');
			ticket_checked= ticket_checked.toString();

            if (agent && ticket_checked) {
				$uv.ajax({
			      type: 'POST',
			      url: wkuvdesk_api_script.api_admin_ajax,
			      data: {"action": "change_ticket_agent","nonce":wkuvdesk_api_script.api_nonce,"agent":agent,"ticket_id":ticket_checked},
			      beforeSend: function(){
				     $uv(".wkuvdesk-pre-loader").show();
				   },
				   success: function(response) {
				      	if(response.success==true){
				      		location.reload();
				      	}

				       	$uv(".wkuvdesk-pre-loader").hide();
				     }
			    });
			}

			evt.preventDefault();
		});

    });

})(jQuery);

jQuery(document).ready(function ($) {
    setTimeout(
        function() {
            $( '.wkuvdesk-alert-fixed' ).fadeOut();
        },
        4000
    );
	$('#wkuvdesk-create-ticket-form').on('submit', function(e) {
		var isValid = true;
		$('.error-message').text('').css('color', 'red');

		if (!$('#type').val()) {
			$('#type-error').text(wkuvdesk_api_script.type_error);
			isValid = false;
		}

		if (!$('#subject').val()) {
			$('#subject-error').text(wkuvdesk_api_script.subject_error);
			isValid = false;
		}

		if (!$('#reply').val()) {
			$('#reply-error').text(wkuvdesk_api_script.reply_error);
			isValid = false;
		}

		var fileInput = $('#wkuvdesk-attachments');
		if (fileInput[0].files.length > 0) {
			var file = fileInput[0].files[0];
			var allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/png', 'image/jpeg', 'image/gif', 'application/zip', 'application/x-rar-compressed'];
			var maxSize = 20 * 1024 * 1024; // 20MB

			if (!allowedTypes.includes(file.type)) {
				$('#file-error').text(wkuvdesk_api_script.file_type_error);
				isValid = false;
			}

			if (file.size > maxSize) {
				$('#file-error').text(wkuvdesk_api_script.file_size_error);
				isValid = false;
			}
		}

		if (!isValid) {
			e.preventDefault();
		}
	});
});
