$uv=jQuery.noConflict();

(function($uv){

	$uv(document).ready(function(event){
        /**Filter view */
		$uv(".uv-uvdesk-filter-view").on("click",function(evt){
			elem = $uv(evt.target);
			field = elem.data('value');
			var order = elem.attr('data-order');
			page_link = $uv("#page_link").val();
			$uv('.selected-option').html(elem.html()) ;

			if(field){
				$uv.ajax({
			      type: 'POST',
			      url: apiScript.api_admin_ajax,
			      data: { "action": "sort_ticket_via_api", "nonce":apiScript.api_nonce, "field":field, "order":order, 'is_admin':false, 'page_link':page_link },
			      beforeSend: function() {
				     $uv(".uv-uvdesk-pre-loader").show();
				   },
				   success: function(response) {
				      	if(response.success==true){
				      		$uv(".uv-uvdesk-customer-ticket-section").empty();
				      		$uv(".uv-uvdesk-customer-ticket-section").append(response.data);
									if( order == 'asc' ){
										// elem.data('order','desc');
										elem.attr('data-order', 'desc')
									}else{
										// elem.data('order','asc');
										elem.attr('data-order', 'asc')
									}
									$uv('.uv-uvdesk-filter-view').css('display','none');
									$uv('.uv-uvdesk-down-up-arrow').removeClass('down');
				      	}
				      	else{
				      		alert(response.data);
				      	}
				       	$uv(".uv-uvdesk-pre-loader").hide();
				     }
			    });
			}

			evt.preventDefault();
		});

        /** Tab filter */
		$uv("#uv-uvdesk-tab-id-filter").on("click",function(evt){
            selected = $uv(evt.target).data('value');

			if(selected){
				$uv.ajax({
			      type: 'POST',
			      url: apiScript.api_admin_ajax,
			      data: {"action": "sort_customer_ticket_via_status","nonce":apiScript.api_nonce,"field":parseInt(selected)},
			      beforeSend: function(){
				     $uv(".uv-uvdesk-pre-loader").show();
				   },
				   success: function(response)
				    {
				      	if(response.success==true){
									$uv('#uv-uvdesk-tab-id-filter ul li').removeClass('tab-active');
				      		$uv(".uv-uvdesk-customer-ticket-section").empty();
									$uv(evt.target).addClass('tab-active');
				      		$uv(".uv-uvdesk-customer-ticket-section").append(response.data);

				      	}
				      	else{

				      		alert(response.data);

				      	}
				       	$uv(".uv-uvdesk-pre-loader").hide();
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
			      url: apiScript.api_admin_ajax,
			      data: {"action": "get_thread_data_customer","nonce":apiScript.api_nonce,"page_no":page},
			      beforeSend: function(){
				     $uv(".uv-uvdesk-pre-loader").show();
				   },
                    success: function (response) {
				      	if(response.success==true){
							$uv("#ajax-load-page").remove();
							$uv(document).find('#uv-desk-content-here-aj').prepend(response.data);
				      	}
				      	else if (response.data) {
				      		alert(response.data);
				      	} else {
                            // console('Something went wrong. Please try again.');
                        }
				       	$uv(".uv-uvdesk-pre-loader").hide();
				     }
			    });
			}

			evt.preventDefault();
		});

        /**Toggable the ticket**/
		$uv(".uv-star-ticket").on("click",function(e){
			tid=$uv(this).data('id');
			strno =$uv(this).data('star-val');
            str_no = strno === 1 ? 0 : 1;
            if (tid) {
				$uv.ajax({
				      type: 'POST',
				      url: apiScript.api_admin_ajax,
				      data: {"action": "toggle_the_starred","nonce":apiScript.api_nonce,"ticket_id":tid,"stared_no":str_no},
				      beforeSend: function(){
					     $uv(".uv-uvdesk-pre-loader").show();
					   },
					   success: function(response) {
					    if(response.success == true){
							location.reload();
							$uv(".uv-uvdesk-pre-loader").hide();
   						}
				      }
				});
			}
		});

        var fileuploader = $uv('.labelWidget').clone();
		var maxAppends = 2;
		var currentAppends = 0;
		$uv('#addFile').on('click', function () {
			if (currentAppends < maxAppends) {
				$uv(this).before(fileuploader.clone());
				currentAppends++;
			}
		});
		$uv(document).on('change', '#uv-uvdesk-attachments', function () {
    	    $uv(this).siblings('.attach-file').addClass('close');
		});

		$uv( document ).on('click','.uv-uvdesk-remove-file',function(){
			$uv(this).closest('div').remove();
		});

		$uv(".uv-uvdesk-filter-left").on("click",function(e){
			if( $uv('.uv-uvdesk-filter-view').css('display').toLowerCase() == 'block') {
				$uv('.uv-uvdesk-filter-view').css('display','none');
				$uv('.uv-uvdesk-down-up-arrow').removeClass('down');
			}else {
				$uv('.uv-uvdesk-filter-view').css('display','block');
				$uv('.uv-uvdesk-down-up-arrow').addClass('down');
			}
        })

        $uv(".wk-sel-agent").on("change",function(evt){
			agent=$uv(this).val();
			ticket_checked=[];
			ticket_checked=$uv(this).data('id');
			ticket_checked= ticket_checked.toString();

            if (agent && ticket_checked) {
				$uv.ajax({
			      type: 'POST',
			      url: apiScript.api_admin_ajax,
			      data: {"action": "change_ticket_agent","nonce":apiScript.api_nonce,"agent":agent,"ticket_id":ticket_checked},
			      beforeSend: function(){
				     $uv(".uv-uvdesk-pre-loader").show();
				   },
				   success: function(response) {
				      	if(response.success==true){
				      		location.reload();
				      	}
				      	else{
				      		alert(response.data);
				      	}
				       	$uv(".uv-uvdesk-pre-loader").hide();
				     }
			    });
			}

			evt.preventDefault();
		});

    });

})(jQuery);
