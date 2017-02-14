$uv=jQuery.noConflict();

(function($uv){

	$uv(document).ready(function(){


		$uv(document).on("click",".block-container .ticket-tabs-list li",function(){  
			$uv(this).siblings("li").removeClass("active").end().addClass("active");
			tab_id=$uv(this).data("id");
			$uv(this).siblings("li").removeAttr("style");
			if (tab_id=='1') {
				$uv(this).css("border-top","3px solid #337ab7");
			}
			else if (tab_id=='2') {
				$uv(this).css("border-top","3px solid #d9534f");
			}
			else if (tab_id=='3') { 
				$uv(this).css("border-top","3px solid #5cb85c");
			}
			else if (tab_id=='4') {
				$uv(this).css("border-top","3px solid #767676");
			}
			else if (tab_id=='5') {
				$uv(this).css("border-top","3px solid #00A1F2");
			}
			else if (tab_id=='6') {
				$uv(this).css("border-top","3px solid #F1BB52");
			}
			
			if(tab_id && $uv.isNumeric(tab_id)){
				$uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "get_data_via_api","nonce":api_script.api_nonce,"tab_id":tab_id},
			      beforeSend: function(){
				     $uv(".ajax-loader-img").show();
				   },
				   success: function(response)
			      {   
				    if(response && response.success==true){
	 					$uv(".admin-ticket-view").empty();
				 		$uv(".admin-ticket-view").append(response.data);
					    $uv(".ajax-loader-img").hide();    
				    }
				    else{
				    	alert("data Not available for this tab");	
				    }
			      }
			    });

			}
			else{
				alert("This is not an integer value..!")
			}

		});


		// Move ticket to status

		$uv("#mass-status").on("change",function(evt){
			selected=$uv(this).val();
			
			ticket_checked=[];

			ticket_checked=$uv(".table tbody tr").find(".icheckbox_square-blue input:checkbox:checked").map(function(){
			 	return $uv(this).val();
			 }).get();
			
			// ticket_checked=JSON.stringify(ticket_checked); 
			ticket_checked= ticket_checked.toString(); 
			// console(ticket_checked);
			if(selected && ticket_checked){ 
				$uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "change_ticket_status","nonce":api_script.api_nonce,"status":selected,"ticket_id":ticket_checked},
			      beforeSend: function(){
				     $uv(".ajax-loader-img").show();
				   },
				   success: function(response)
				    {    
				      		 
				      	if(response.success==true){
				      		location.reload();
				      	}
				      	else{
				      		
				      		alert(response.data);

				      	}
				       	$uv(".ajax-loader-img").hide();
				     }
			    });
			}
			else{
				alert("select ticket to change status");
			}

			evt.preventDefault();

		});

		$uv("#mass-priority").on("change",function(evt){
			
			priority=$uv(this).val(); 	
			ticket_checked=[];

			ticket_checked=$uv(".table tbody tr").find(".icheckbox_square-blue input:checkbox:checked").map(function(){
			 	return $uv(this).val();
			 }).get();
			 
			ticket_checked= ticket_checked.toString();  

			if(priority && ticket_checked){ 
				$uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "change_ticket_priority","nonce":api_script.api_nonce,"priority":priority,"ticket_id":ticket_checked},
			      beforeSend: function(){
				     $uv(".ajax-loader-img").show();
				   },
				   success: function(response)
				    {     

				      	if(response.success==true){
				      		location.reload(); 
				      	}
				      	else{
				      		
				      		alert(response.data);

				      	}
				       	$uv(".ajax-loader-img").hide();
				     }
			    });
			}
			else{
				alert("select ticket to change status");
			}

			evt.preventDefault();

		});

		$uv("#mass-agent").on("change",function(evt){
			
			agent=$uv(this).val(); 	
			ticket_checked=[];

			ticket_checked=$uv(".table tbody tr").find(".icheckbox_square-blue input:checkbox:checked").map(function(){
			 	return $uv(this).val();
			 }).get();
			 
			ticket_checked= ticket_checked.toString();  

			if(agent && ticket_checked){ 
				$uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "change_ticket_agent","nonce":api_script.api_nonce,"agent":agent,"ticket_id":ticket_checked},
			      beforeSend: function(){
				     $uv(".ajax-loader-img").show();
				   },
				   success: function(response)
				    {     
				    	console.log(response);
				      	if(response.success==true){
				      		location.reload(); 
				      	}
				      	else{
				      		
				      		alert(response.data);

				      	}
				       	$uv(".ajax-loader-img").hide();
				     }
			    });
			}
			else{
				alert("select ticket to change status");
			}

			evt.preventDefault();

		}); 

		$uv("#mass-label").on("change",function(evt){
			
			label=$uv(this).val();

			ticket_checked=[];

			ticket_checked=$uv(".table tbody tr").find(".icheckbox_square-blue input:checkbox:checked").map(function(){
			 	return $uv(this).val();
			 }).get();
			 
			ticket_checked= ticket_checked.toString();  

			if(label && ticket_checked){ 
				$uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "change_ticket_label","nonce":api_script.api_nonce,"label":label,"ticket_id":ticket_checked},
			      beforeSend: function(){
				     $uv(".ajax-loader-img").show();
				   },
				   success: function(response)
				    {     
				    	console.log(response);
				      	if(response.success==true){
				      		location.reload(); 
				      	}
				      	else{
				      		
				      		alert(response.data);

				      	}
				       	$uv(".ajax-loader-img").hide();
				     }
			    });
			}
			else{
				alert("select ticket to change status");
			}

			evt.preventDefault();

		}); 
		
		// Assignment done //

 
		$uv('.tag-input').on('keypress', function (e) {
			var keycode = (e.keyCode ? e.keyCode : e.which);
			if(keycode=='13'){
				currentElm=$uv(this);
	       	 //get textbox value
		        tag_val=currentElm.val();
				tid=$uv(".ticket-id").data('tid'); 

		            tid=$uv(".ticket-id").data('tid'); 
		            if(tag_val && tid){
		            	 $uv.ajax({
					      type: 'POST',
					      url: api_script.api_admin_ajax,
					      data: {"action": "update_tag_via_api","nonce":api_script.api_nonce,"ticket_id":tid,"tag":tag_val},
					      beforeSend: function(){
						     $uv(".ajax-loader-img").show();
						   },
						   success: function(jsonObj)
					      {  
					      	 jsonObj=JSON.parse(jsonObj.data);  
					      	 if(jsonObj.tag){
							     if(jsonObj.msg){
							     	currentElm.val('');
							     	$uv(".tag-input").prev('.panel-body').append('<div class="btn-group"><a class="btn btn-sm btn-default" data-tag-id='+jsonObj.tag.id+' href="/en/member/tickets#tag/'+jsonObj.tag.id+'">'+jsonObj.tag.name+'</a><button class="btn btn-sm btn-default delete-tag" type="button"><span class="text-danger glyphicon glyphicon-remove"></span></button></div>'); 
					      	 		
					      	 		$uv(".ajax-loader-img").hide();
							     }
						     }

					      }
					    });
		            } 
			}
	   });

		$uv("#sticky-header1").on("click",".delete-tag",function(){
			currentElm=$uv(this).parent('.btn-group');
			tag_id=$uv(this).prev('a').data('tag-id');
			tid=$uv(".ticket-id").data('tid');  
			tag_id=parseInt(tag_id);
			
			if (tag_id && tid) {
				
				$uv.ajax({
				      type: 'POST',
				      url: api_script.api_admin_ajax,
				      data: {"action": "delete_tag_via_api","nonce":api_script.api_nonce,"ticket_id":tid,"tag-id":tag_id},
				      beforeSend: function(){
					     $uv(".ajax-loader-img").show();
					   },
					   success: function(response)
				      {  
				      	jsonObj=JSON.parse(response); 
					      	
					    if(jsonObj.message){
					    	currentElm.remove();
					    	alert(jsonObj.message);
						  	$uv(".ajax-loader-img").hide();
						}	 

				      }
				});	
			}
				
		});


		$uv(".support-mailbox .predefined-label-list li a").on("click",function(evt){

			hrefVal=$uv(this).attr('href');				
			hrefVal=hrefVal.replace("#","");
			if (hrefVal) {
				
				$uv.ajax({
				      type: 'POST',
				      url: api_script.api_admin_ajax,
				      data: {"action": "get_data_hash_via_api","nonce":api_script.api_nonce,"hash":hrefVal},
				      beforeSend: function(){
					     $uv(".ajax-loader-img").show();
					   },
					   success: function(response){   
					  		$uv(".admin-ticket-view").empty();
					 		$uv(".admin-ticket-view").append(response.data);
						    $uv(".ajax-loader-img").hide();  
				      }
				});	
			} 
				
		});

		$uv(".filter-list #agent-filter-input").focus(function(){
				   
      //   	 $uv.ajax({
		    //   type: 'POST',
		    //   url: api_script.api_admin_ajax,
		    //   data: {"action": "list_members_via_api","nonce":api_script.api_nonce},
		    //   beforeSend: function(){
			   //   $uv(".ajax-loader-img").show();
			   // },
			   // success: function(response)
		    //   {    
		    //   	final_data=response.data;
		    //   	  if(response){
			   //    	  $uv('#agent-filter ul').empty();
			   //    	  $uv.each(final_data,function(i,val){
			   //    	  	member_det='<li data-id='+val.id+'><a><span class="text">'+val.name+'</span></a></li>';
			   //    	  	$uv('#agent-filter ul').append(member_det);
			   //    	  });
			   //   	$uv(".ajax-loader-img").hide();

		    //   	  	}

		    //  	 }
		    // }); 

		});

		$uv(".support-mailbox").on("click","#agent-filter ul li",function(){ 
			 
			 mid=$uv(this).data('id');  

			 mid=parseInt(mid);

			 title = $uv(this).text();

             text = '<span class="tag label label-info" data-id='+mid+'>'+title+'<i class="fa fa-times remove"></i></span>';
             
             $uv("#agent-filter span.tags").html(text);
			 
			 if(mid){ 
	        	 $uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "filter_members_via_api","nonce":api_script.api_nonce,"member_id":mid},
			      beforeSend: function(){
				     $uv(".ajax-loader-img").show();
				   },
				   success: function(response)
			      {    
			      		$uv(".admin-ticket-view").empty();
				 		$uv(".admin-ticket-view").append(response.data);
					    $uv(".ajax-loader-img").hide(); 
			       

			     	 }
			    }); 
			 }

		});

		// Customer Filter

	 	 $uv("#customer-filter").on("click", function() {
            $uv.ajax({
                type:'POST',
                url: api_script.api_admin_ajax,
                data: {"action": "get_customer_data", "nonce":api_script.api_nonce},
                beforeSend: function(){
                    $uv(".ajax-loader-img").show();
                },
                success: function(response){ 
                    if(response.data){
                        $uv('#customer-filter ul').empty();
                        $uv.each(response.data.customers,function(i,val){

                            $uv('#customer-filter ul').append('<li data-id='+val.id+'><a><span class="text">'+val.name+'</span></a></li>');

                        });

                        $uv(".ajax-loader-img").hide();
                    }
                }
            });
        });

        $uv("#customer-filter").on("click", "ul li", function(){
            c_id = $uv(this).data('id');
            title = $uv(this).text();
            text = '<span class="tag label label-info" data-id='+c_id+'>'+title+'<i class="fa fa-times remove"></i></span>';
            $uv("#customer-filter span.tags").html(text);
            if(c_id){
	            $uv.ajax({
	                type:'POST',
	                url: api_script.api_admin_ajax,
	                data: {"action": "get_data_via_customer", "customer_filter":c_id,"nonce":api_script.api_nonce},
	                beforeSend: function(){
	                    $uv(".ajax-loader-img").show();
	                },
	                success: function(response){ 

	                    $uv(".admin-ticket-view").empty();
	                     $uv(".admin-ticket-view").append(response.data);
	                    $uv(".ajax-loader-img").hide();
	                }
	            });
            }
        });

        // Group Filter

	 	 $uv("#group-filter").on("click", function() { 
            $uv.ajax({
                type:'POST',
                url: api_script.api_admin_ajax,
                data: {"action": "get_group_data", "nonce":api_script.api_nonce},
                beforeSend: function(){
                    $uv(".ajax-loader-img").show();
                },
                success: function(response){ 
                console.log(response); 
                    if(response.data){
                        $uv('#group-filter ul').empty();
                        $uv.each(response.data.groups,function(i,val){

                            $uv('#group-filter ul').append('<li data-id='+val.id+'><a><span class="text">'+val.name+'</span></a></li>');

                        });

                        $uv(".ajax-loader-img").hide();
                    }
                }
            });
        });

        $uv("#group-filter").on("click", "ul li", function(){
            g_id = $uv(this).data('id');
            title = $uv(this).text();
            text = '<span class="tag label label-info" data-id='+g_id+'>'+title+'<i class="fa fa-times remove"></i></span>';
            $uv("#group-filter span.tags").html(text);
            if(g_id){
	            $uv.ajax({
	                type:'POST',
	                url: api_script.api_admin_ajax,
	                data: {"action": "get_data_via_group", "group_filter":g_id,"nonce":api_script.api_nonce},
	                beforeSend: function(){
	                    $uv(".ajax-loader-img").show();
	                },
	                success: function(response){ 
	                	console.log(response);
	                    $uv(".admin-ticket-view").empty();
	                     $uv(".admin-ticket-view").append(response.data);
	                    $uv(".ajax-loader-img").hide();
	                }
	            });
            }
        });

		$uv("#priority-filter").on("click", "ul li", function(){
            data = $uv(this).data('id');
            title = $uv(this).text();
            if (data==1)
                h = '<span style="background-color:#5cb85c" class="tag label label-info" data-id="'+data+'">'+title+'<i class="fa fa-times remove"></i></span>';
            else if (data==2)
                h = '<span style="background-color:#337ab7" class="tag label label-info" data-id="'+data+'">'+title+'<i class="fa fa-times remove"></i></span>';
            else if (data==3)
                h = '<span style="background-color:#f0ad4e" class="tag label label-info" data-id="'+data+'">'+title+'<i class="fa fa-times remove"></i></span>';
            else
                h = '<span style="background-color:#d9534f" class="tag label label-info" data-id="'+data+'">'+title+'<i class="fa fa-times remove"></i></span>';
            $uv("#priority-filter span.tags").html(h);
            $uv.ajax({
                type:'POST',
                url: api_script.api_admin_ajax,
                data: {"action": "get_data_via_priority", "priority_filter":data},
                beforeSend: function(){
                    $uv(".ajax-loader-img").show();
                },
                success: function(response){
                        $uv(".admin-ticket-view").empty();
                         $uv(".admin-ticket-view").append(response.data);
                        $uv(".ajax-loader-img").hide();
                }
            });
        });

        $uv("#type-filter").on("click", "ul li", function(){
            data = $uv(this).data('id');
            title = $uv(this).text();
            h = '<span class="tag label label-info" data-id='+data+'>'+title+'<i class="fa fa-times remove"></i></span>';
            $uv("#type-filter span.tags").html(h);
            $uv.ajax({
                type:'POST',
                url: api_script.api_admin_ajax,
                data: {"action": "get_data_via_type", "type_filter":data},
                beforeSend: function(){
                    $uv(".ajax-loader-img").show();
                },
                success: function(response){
                        $uv(".admin-ticket-view").empty();
                         $uv(".admin-ticket-view").append(response.data);
                        $uv(".ajax-loader-img").hide();
                }
            });
        });

        $uv(".filter-list").on("click", "i.remove", function(){
        	currentElm=$uv(this);
            $uv.ajax({
                type:'POST',
                url: api_script.api_admin_ajax,
                data: {"action": "get_default_data", "nonce":api_script.api_nonce},
                beforeSend: function(){
                    $uv(".ajax-loader-img").show();
                },
                success: function(response){
                        currentElm.parent().parent("span.tags").empty();
                        $uv(".admin-ticket-view").empty();
                         $uv(".admin-ticket-view").append(response.data);
                        $uv(".ajax-loader-img").hide();
                }
            });
        });

        // Team Filter


        $uv("#team-filter").on("click", "ul li", function(){
            team = $uv(this).data('id');
            title = $uv(this).text();
            text = '<span class="tag label label-info" data-id='+team+'>'+title+'<i class="fa fa-times remove"></i></span>';
            $uv("#team-filter span.tags").html(text);
            if(team){
	            $uv.ajax({
	                type:'POST',
	                url: api_script.api_admin_ajax,
	                data: {"action": "get_data_via_team", "team_filter":team,"nonce":api_script.api_nonce},
	                beforeSend: function(){
	                    $uv(".ajax-loader-img").show();
	                },
	                success: function(response){
	                	console.log(response);
	                    $uv(".admin-ticket-view").empty();
	                     $uv(".admin-ticket-view").append(response.data);
	                    $uv(".ajax-loader-img").hide();
	                }
	            });
            	
            }
        });

       // team filter done	



        $uv("#tag-filter").on("click", function() {
            $uv.ajax({
                type:'POST',
                url: api_script.api_admin_ajax,
                data: {"action": "get_tag_data", "nonce":api_script.api_nonce},
                beforeSend: function(){
                    $uv(".ajax-loader-img").show();
                },
                success: function(response){
                   
                    if(response.data){
                        $uv('#tag-filter ul').empty();
                        $uv.each(response.data.tags,function(i,val){

                            $uv('#tag-filter ul').append('<li data-id='+val.id+'><a><span class="text">'+val.name+'</span></a></li>');

                        });
                        $uv(".ajax-loader-img").hide();
                    }
                }
            });
        });

        $uv("#tag-filter").on("click", "ul li", function(){
            data = $uv(this).data('id');
            title = $uv(this).text();
            h = '<span class="tag label label-info" data-id='+data+'>'+title+'<i class="fa fa-times remove"></i></span>';
            $uv("#tag-filter span.tags").html(h);
            $uv.ajax({
                type:'POST',
                url: api_script.api_admin_ajax,
                data: {"action": "get_data_via_tag", "tag_filter":data,"nonce":api_script.api_nonce},
                beforeSend: function(){
                    $uv(".ajax-loader-img").show();
                },
                success: function(response){
                	console.log(response);
                    $uv(".admin-ticket-view").empty();
                     $uv(".admin-ticket-view").append(response.data);
                    $uv(".ajax-loader-img").hide();
                }
            });
        });



		$uv(document).on("click",".block-container .icheckbox_square-blue .mass-action-checkbox",function(){ 
			 
			 $uv(this).parent().toggleClass('checked');

		 	 if($uv(this).parent().parent().hasClass('select-all-box')){
			 	if($uv(this).parent().hasClass('checked')){
			 		$uv(".block-container").find(".mass-action-checkbox").prop('checked', this.checked);
			 		$uv(".block-container").find(".mass-action-checkbox").parent().addClass('checked');
			 	}
			 	else{
			 		$uv(".block-container").find(".mass-action-checkbox").prop('checked', false);
			 		$uv(".block-container").find(".mass-action-checkbox").parent().removeClass('checked');
			 	}
		 	}
			 mCheck=$uv(".block-container").find(".mass-action-checkbox").is(function(){
			 	return this.checked;
			 });
			 if(mCheck){ 

			 	$uv(".filter-form-div .mass-action-block").show();
			 	$uv(".filter-form-div .form-horizontal.ticket").hide();
			 	
			 }
			 else{
			 	 
			 	$uv(".filter-form-div .mass-action-block").hide();
			 	$uv(".filter-form-div .form-horizontal.ticket").show();

			 } 
			 if($uv('#ticket-table input:checkbox:checked').length==$uv('#ticket-table input:checkbox').length){ 
			 	$uv(".select-all-box .icheckbox_square-blue .mass-action-checkbox").prop('checked', this.checked);
			 	$uv(".select-all-box .icheckbox_square-blue .mass-action-checkbox").parent().addClass('checked');
			 }
			 else{

			 	$uv(".select-all-box .icheckbox_square-blue .mass-action-checkbox").prop('checked',false);
			 	$uv(".select-all-box .icheckbox_square-blue .mass-action-checkbox").parent().removeClass('checked');
			 }

		});
 
		// Assign label to multiple tickets

		$uv("#mass-delete").on("click",function(){ 
			ticket_checked=[];
			 ticket_checked=$uv(".table tbody tr").find(".icheckbox_square-blue input:checkbox:checked").map(function(){
			 	return $uv(this).val();
			 }).get();
			  
			 if(ticket_checked){ 
	        	 $uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "delete_ticket_via_api","nonce":api_script.api_nonce,"ticket_id":ticket_checked},
			      beforeSend: function(){
				     $uv(".ajax-loader-img").show();
				   },
				   success: function(response)
				    {    
				      	if(response.success==true){
				      		
				      		deleteElms=$uv(".table tbody tr").find(".icheckbox_square-blue input:checkbox:checked");
	        	 			
	        	 			$uv.each(deleteElms,function(i,val){  
	        	 				$uv(val).closest("tr").remove();
	        	 			});
				      		alert(response.data.message);

				      	}
				      	else{
				      		
				      		alert(response.data);

				      	}
				       	$uv(".ajax-loader-img").hide();
				     }
			    }); 
			 } 

		});

		// $uv("#mass-priority").on("change",function(){
		// 	selected_elm=$uv(this).value(); 
		// 	ticket_checked=[];
		// 	 ticket_checked=$uv(".table tbody tr").find(".icheckbox_square-blue input:checkbox:checked").map(function(){
		// 	 	return $uv(this).val();
		// 	 }).get();
			  
		// 	 if(ticket_checked){ 
	 //        	 $uv.ajax({
		// 	      type: 'POST',
		// 	      url: api_script.api_admin_ajax,
		// 	      data: {"action": "delete_ticket_via_api","nonce":api_script.api_nonce,"ticket_id":ticket_checked},
		// 	      beforeSend: function(){
		// 		     $uv(".ajax-loader-img").show();
		// 		   },
		// 		   success: function(response)
		// 		    {   
		// 		      	if(response.success==true){
				      		
		// 		      		deleteElms=$uv(".table tbody tr").find(".icheckbox_square-blue input:checkbox:checked");
	        	 			
	 //        	 			$uv.each(deleteElms,function(i,val){  
	 //        	 				$uv(val).closest("tr").remove();
	 //        	 			});
	 //        	 			$uv(".ticket-list .alert-fixed span").empty();
	 //        	 			$uv(".ticket-list .alert-fixed span").append('<i class="fa fa-check pull-left" aria-hidden="true"></i>'+response.data.message);
		// 		      		$uv(".ticket-list .alert-fixed").fadeIn();

		// 		      	}
		// 		      	else{
				      		
		// 		      		alert(response.data);

		// 		      	}
		// 		       	$uv(".ajax-loader-img").hide();
		// 		     }
		// 	    }); 
		// 	 } 

		// });
		


		// Customer Ajax request 

		$uv(".sort-records li a").on("click",function(evt){
			field=$uv(this).data('field'); 
			if(field){
				$uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "sort_ticket_via_api","nonce":api_script.api_nonce,"field":field,'is_admin':api_script.is_admin},
			      beforeSend: function(){
				     $uv(".ajax-loader-img").show();
				   },
				   success: function(response)
				    {    
				      	if(response.success==true){
				      		$uv(".admin-ticket-view").empty(); 
				      		$uv(".admin-ticket-view").append(response.data);
				      		$uv(".customer-ticket-section").empty(); 
				      		$uv(".customer-ticket-section").append(response.data);
				      	}
				      	else{
				      		
				      		alert(response.data);

				      	}
				       	$uv(".ajax-loader-img").hide();
				     }
			    });
			}

			evt.preventDefault();
		});

		$uv("#status-filter").on("change",function(evt){
			var selected = $uv(this).val();

			if(selected){
				$uv.ajax({
			      type: 'POST',
			      url: api_script.api_admin_ajax,
			      data: {"action": "sort_customer_ticket_via_status","nonce":api_script.api_nonce,"field":parseInt(selected)},
			      beforeSend: function(){
				     $uv(".ajax-loader-img").show();
				   },
				   success: function(response)
				    {    
				      	if(response.success==true){
				      		$uv(".customer-ticket-section").empty(); 
				      		$uv(".customer-ticket-section").append(response.data);

				      	}
				      	else{
				      		
				      		alert(response.data);

				      	}
				       	$uv(".ajax-loader-img").hide();
				     }
			    });
			}

			evt.preventDefault();
		});

		$uv('.selectpicker').selectpicker({
			  style: 'btn-info',
			  size: 10
		});

		 

	});

})(jQuery);