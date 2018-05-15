$uv=jQuery.noConflict();

(function($uv){

	$uv(document).ready(function(event){

    $uv('.replay-inline').on('click',function(event){

			if( $uv(event.target).hasClass('wk-delete-tkt-reply') ){

				currentElm = $uv(this).closest('.tkt-replay');
				thread_id=$uv(this).closest('.tkt-replay').data('thread-id');
				tid=$uv(".uv-ticket-id").val();
				thread_id=parseInt(thread_id);
				if (thread_id && tid) {

					$uv.ajax({
					      type: 'POST',
					      url: api_script.api_admin_ajax,
					      data: {"action": "delete_thread_via_api","nonce":api_script.api_nonce,"ticket_id":tid,"thread-id":thread_id},
					      beforeSend: function(){
						     $uv(".pre-loader").show();
						   },
						   success: function(response)
					      {
					      	jsonObj=JSON.parse(response);

						    if(jsonObj.message){
						    	currentElm.remove();
						    	alert(jsonObj.message);
							  	// $uv(".pre-loader").hide();
							}

					      }
					});
				}

			}
			else{

      if( $uv(this).parent('div').hasClass('tkt-replay') ){

        display = $uv(this).next('div').css('display');

        if(display == 'none'){
          $uv(this).next('div').show();
          $uv(this).find('.tkt-timestamp .wk-accord').removeClass('close');
        }else{
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

		$uv( document ).on('click','.remove-file',function(){

			$uv(this).closest('div').remove();

		});

		$uv(".wk-sel-agent").on("change",function(evt){

			agent=$uv(this).val();

			ticket_checked=[];

			ticket_checked=$uv(this).data('id');

			ticket_checked= ticket_checked.toString();

			if(agent && ticket_checked){
				$uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "change_ticket_agent","nonce":api_script.api_nonce,"agent":agent,"ticket_id":ticket_checked},
			      beforeSend: function(){
				     $uv(".pre-loader").show();
				   },
				   success: function(response)
				    {

				      	if(response.success==true){
				      		location.reload();
				      	}
				      	else{

				      		alert(response.data);

				      	}
				       	// $uv(".pre-loader").hide();
				     }
			    });
			}
			else{
				alert("select ticket to change status");
			}

			evt.preventDefault();

		});

		$uv("#wk-sel-priority").on("change",function(evt){

			priority=$uv(this).val();

			ticket_checked=[];

			ticket_checked=$uv(".uv-ticket-id").val();

			ticket_checked= ticket_checked.toString();

			if(priority && ticket_checked){
				$uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "change_ticket_priority","nonce":api_script.api_nonce,"priority":priority,"ticket_id":ticket_checked},
			      beforeSend: function(){
				     $uv(".pre-loader").show();
				   },
				   success: function(response)
				    {

				      	if(response.success==true){
				      		location.reload();
				      	}
				      	else{

				      		alert(response.data);

				      	}
				       	// $uv(".pre-loader").hide();
				     }
			    });
			}
			else{
				alert("select ticket to change priority");
			}

			evt.preventDefault();

		});

		$uv(document).on('change', '#attachments', function () {

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
				      url: api_script.api_admin_ajax,
				      data: {"action": "toggle_the_starred","nonce":api_script.api_nonce,"ticket_id":tid,"stared_no":str_no},
				      beforeSend: function(){
					     $uv(".pre-loader").show();
					   },
					   success: function(response)
				      {
					    if(response.success == true){
								location.reload();
								// $uv(".pre-loader").hide();
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
			      url: api_script.api_admin_ajax,
			      data: {"action": "get_thread_data_customer","nonce":api_script.api_nonce,"page_no":page},
			      beforeSend: function(){
				     $uv(".pre-loader").show();
				   },
				   success: function(response)
				    {
							if(response.success==true){

									$uv("#ajax-load-page").remove();
									$uv(document).find('#content-here-aj').prepend(response.data);

				      	}
				      	else{

				      		alert(response.data);

				      	}
				       	// $uv(".pre-loader").hide();
				     }
			    });
			}

			evt.preventDefault();
		});

  });

})(jQuery);
