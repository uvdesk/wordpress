$uv=jQuery.noConflict();

(function($uv){

	$uv(document).ready(function(event){

		$uv(".filter-view").on("click",function(evt){
			elem = $uv(evt.target);
			field = elem.data('value');
			var order = elem.attr('data-order');
			page_link = $uv("#page_link").val();
			$uv('.selected-option').html(elem.html()) ;
			
			if(field){
				$uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "sort_ticket_via_api","nonce":api_script.api_nonce,"field":field,"order":order,'is_admin':false,'page_link':page_link},
			      beforeSend: function(){
				     $uv(".pre-loader").show();
				   },
				   success: function(response)
				    {
				      	if(response.success==true){
				      		$uv(".customer-ticket-section").empty();
				      		$uv(".customer-ticket-section").append(response.data);
									if( order == 'asc' ){
										// elem.data('order','desc');
										elem.attr('data-order', 'desc')
									}else{
										// elem.data('order','asc');
										elem.attr('data-order', 'asc')
									}


									$uv('.filter-view').css('display','none');
									$uv('.down-up-arrow').removeClass('down');
				      	}
				      	else{

				      		alert(response.data);

				      	}
				       	$uv(".pre-loader").hide();
				     }
			    });
			}

			evt.preventDefault();
		});

		$uv("#tab-id-filter").on("click",function(evt){
			 selected = $uv(evt.target).data('value');
			if(selected){
				$uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "sort_customer_ticket_via_status","nonce":api_script.api_nonce,"field":parseInt(selected)},
			      beforeSend: function(){
				     $uv(".pre-loader").show();
				   },
				   success: function(response)
				    {
				      	if(response.success==true){
									$uv('#tab-id-filter ul li').removeClass('tab-active');
				      		$uv(".customer-ticket-section").empty();
									$uv(evt.target).addClass('tab-active');
				      		$uv(".customer-ticket-section").append(response.data);

				      	}
				      	else{

				      		alert(response.data);

				      	}
				       	$uv(".pre-loader").hide();
				     }
			    });
			}

			evt.preventDefault();
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
				    {		console.log(response);
				      	if(response.success==true){

									$uv("#ajax-load-page").remove();
									$uv(document).find('#content-here-aj').prepend(response.data);

				      	}
				      	else{

				      		alert(response.data);

				      	}
				       	$uv(".pre-loader").hide();
				     }
			    });
			}

			evt.preventDefault();
		});

		$uv(".uv-star-ticket").on("click",function(e){

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
								$uv(".pre-loader").hide();
							}

				      }
				});
			}

		});

		var fileuploader = $uv('.labelWidget').clone()

			$uv('#addFile').on('click', function () {

				$uv(this).before(fileuploader.clone());

			});


		$uv(document).on('change', '#attachments', function () {

    	jQuery(this).siblings('.attach-file').addClass('close');

		});

		$uv( document ).on('click','.remove-file',function(){

			$uv(this).closest('div').remove();

		});

		$uv(".filter-left").on("click",function(e){

			if( $uv('.filter-view').css('display').toLowerCase() == 'block') {

				$uv('.filter-view').css('display','none');
				$uv('.down-up-arrow').removeClass('down');

			}else {

				$uv('.filter-view').css('display','block');
				$uv('.down-up-arrow').addClass('down');

			}

		})

	});

})(jQuery);
