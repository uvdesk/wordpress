$uv=jQuery.noConflict();

(function($uv){
    $uv(document).ready(function (event) {
        // Clear Filters button
        if (document.getElementById('clear-filters')) {
            document.getElementById('clear-filters').addEventListener('click', function () {
                // Get the form
                const form = document.querySelector('form');
                // Reset all form fields
                form.reset();
                // Remove any query parameters from the URL
                const url = new URL(window.location.href);
                url.searchParams.delete('check-status');
                url.searchParams.delete('check-priority');
                url.searchParams.delete('fil-agent');
                url.searchParams.delete('filter-submit');
                // Reload the page without filters
                window.location.href = url.toString();
            });
        }
        $uv('.wk-delete-tkt-reply').on('click', function (event) {
            // Log the clicked element for debugging.
            if ($uv(event.target).hasClass('wk-delete-tkt-reply')) {
                let currentElm = $uv(this).closest('.tkt-replay');
                let thread_id = currentElm.data('thread-id');
                let tid = $uv(".uv-ticket-id").val();
                thread_id = parseInt(thread_id);
                // Additional validation before AJAX call.
                if (tid && thread_id) {
                    $uv.ajax({
                        type: 'POST',
                        url: apiScript.api_admin_ajax,
                        data: {
                            "action": "delete_thread_via_api",
                            "nonce": apiScript.api_nonce,
                            "ticket_id": tid,
                            "thread-id": thread_id
                        },
                        beforeSend: function() {
                            $uv(".uvuvdesk-pre-loader").show();
                        },
                        success: function (response) {
                            try {
                                let jsonObj = JSON.parse(response);
                                if (jsonObj.message) {
                                    currentElm.remove();
                                    alert(jsonObj.message);
                                } else {
                                    console.error('No message in response');
                                }
                            } catch (error) {
                                // console.error('Error parsing response:', error);
                                 $uv(".uvuvdesk-pre-loader").hide();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                            $uv(".uvuvdesk-pre-loader").hide();
                        }
                    });
                } else {
                    // console.error('Missing tid or thread_id');
                     $uv(".uvuvdesk-pre-loader").hide();
                }
            } else {
                // Existing toggle logic remains the same
                if ($uv(this).parent('div').hasClass('tkt-replay')) {
                    let display = $uv(this).next('div').css('display');
                    if (display == 'none') {
                        $uv(this).next('div').show();
                        $uv(this).find('.tkt-timestamp .wk-accord').removeClass('close');
                    } else {
                        $uv(this).next('div').hide();
                        $uv(this).find('.tkt-timestamp .wk-accord').addClass('close');
                    }
                }
            }
        });
    	var fileuploader = $uv('.labelWidget').clone()

    	$uv('#addFile').on('click', function () {
    		$uv(this).before(fileuploader.clone());
    	});

    	$uv( document ).on('click','.uv-uvdesk-remove-file',function(){

    		$uv(this).closest('div').remove();

    	});

    	$uv("#wk-sel-priority").on("change",function(evt){

    		priority=$uv(this).val();

    		ticket_checked=[];

    		ticket_checked=$uv(".uv-ticket-id").val();

    		ticket_checked= ticket_checked.toString();

    		if(priority && ticket_checked){
    			$uv.ajax({
    		      type: 'POST',
    		      url: apiScript.api_admin_ajax,
    		      data: {"action": "change_ticket_priority","nonce":apiScript.api_nonce,"priority":priority,"ticket_id":ticket_checked},
    		      beforeSend: function(){
    			     $uv(".uvuvdesk-pre-loader").show();
    			   },
    			   success: function(response) {
    			      	if(response.success==true){
    			      		location.reload();
    			      	}
    			      	else{
    			      		alert(response.data);
    			      	}
    			       	$uv(".uvuvdesk-pre-loader").hide();
    			     }
    		    });
    		}


    		evt.preventDefault();

    	});

    	$uv(document).on('change', '#uv-uvdesk-attachments', function () {

    	jQuery(this).siblings('.attach-file').addClass('close');

    	});

    	$uv(".wk-starred-ico").on("click",function(e){

    		tid=$uv(this).data('id');
    		strno =$uv(this).data('star-val');
    		if(strno == 1){
    			str_no = 0;
    		}else{
    			str_no = 1;
    		}
    		if (tid) {

    			$uv.ajax({
    			      type: 'POST',
    			      url: apiScript.api_admin_ajax,
    			      data: {"action": "toggle_the_starred","nonce":apiScript.api_nonce,"ticket_id":tid,"stared_no":str_no},
    			      beforeSend: function(){
    				     $uv(".uvuvdesk-pre-loader").show();
    				   },
    				   success: function(response)
    			      {
    				    if(response.success == true){
    							location.reload();
    							// $uv(".uvuvdesk-pre-loader").hide();
    						}

    			      }
    			});
    		}

    	});

    	$uv(document).on("click","#ajax-load-page",function(evt){
    		 page = $uv(evt.target).data('page');

    		if(page){
    			$uv.ajax({
    		      type: 'POST',
    		      url: apiScript.api_admin_ajax,
    		      data: {"action": "get_thread_data_customer","nonce":apiScript.api_nonce,"page_no":page},
    		      beforeSend: function(){
    			     $uv(".uvuvdesk-pre-loader").show();
    			   },
    			   success: function(response) {
    					if(response.success==true){
    						$uv("#ajax-load-page").remove();
    						$uv(document).find('#uv-desk-content-here-aj').prepend(response.data);
    			      	}
    			      	else{
    			      		alert(response.data);
    			      	}
    			       	$uv(".uvuvdesk-pre-loader").hide();
    			     }
    		    });
    		}

    		evt.preventDefault();
    	});

    });

})(jQuery);
