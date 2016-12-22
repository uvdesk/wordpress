<?php 

if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

}

function admin_ticket_view(){    

	$ticket_id=intval(get_query_var('tid'));
	 

    $uvdesk_access_token=get_option('uvdesk_access_token');
    
    if(!empty($uvdesk_access_token)){
	       
           $ticket_details=UVDESK_API::get_customer_data_api('ticket/'.$ticket_id.'.json');  


           if(isset($ticket_details->error) || empty($ticket_details)){

                echo "<h1>Invalid Details.</h1><h3>Please contact Administrator.</h3>";
           
            }else{
 
            	if(!empty($ticket_details->ticket->status->name) && isset($ticket_details->ticket->status->name)){
            	
            		$ticket_status_name=$ticket_details->ticket->status->name;
            		
            	}
            	if(!empty($ticket_details->ticket->priority->name) && isset($ticket_details->ticket->priority->name)){
            	
            		$ticket_priority=$ticket_details->ticket->priority->name;
            		
            	}
            	
            	if(!empty($ticket_details->ticket->group->name) && isset($ticket_details->ticket->group->name)){
            	
            		$ticket_group_name=$ticket_details->ticket->group->name;
            		
            	}
            	if(!empty($ticket_details->ticket->type->name) && isset($ticket_details->ticket->type->name)){
            	
            		$ticket_type_name=$ticket_details->ticket->type->name; 
            		
            	}
            	
             
            	if(!empty($ticket_details->ticket->agent->detail->agent->name) && isset($ticket_details->ticket->agent->detail->agent->name)){
            	
            		$ticket_agentname=$ticket_details->ticket->agent->detail->agent->name;
            		
            	}

            	if(!empty($ticket_details->ticketTotalThreads) && isset($ticket_details->ticketTotalThreads)){
            	
            		$ticket_totalthreads=$ticket_details->ticketTotalThreads;
            		
            	}

            	if(!empty($ticket_details->ticket->formatedCreatedAt) && isset($ticket_details->ticket->formatedCreatedAt)){
            	
            		$ticket_created=$ticket_details->ticket->formatedCreatedAt;
            		
            	}
            	if(!empty($ticket_details->ticket->customer->detail->customer->name) && isset($ticket_details->ticket->customer->detail->customer->name)){
            	
            		$customer_name=$ticket_details->ticket->customer->detail->customer->name;
            		
            	}
                if(!empty($ticket_details->ticket->customer->detail->customer->name) && isset($ticket_details->ticket->customer->detail->customer->name)){
                
                    $customer_name=$ticket_details->ticket->customer->detail->customer->name;
                    
                }
            	if(!empty($ticket_details->ticket->customer->email) && isset($ticket_details->ticket->customer->email)){
            	
            		$customer_email=$ticket_details->ticket->customer->email;
            		 
            	}
            	if(!empty($ticket_details->createThread->reply) && isset($ticket_details->createThread->reply)){
            	
            		$created_thread=$ticket_details->createThread->reply; 
            		 
            		
            	} 
            	
            	$ticket_thread=UVDESK_API::get_customer_data_api('ticket/'.$ticket_details->ticket->id.'/threads.json');   
             
            	?>
                <div class="body content-wrap">
                   <?php if ( isset( $_POST['uvdesk_thread_nonce'] )) {
                        if (! wp_verify_nonce( $_POST['uvdesk_thread_nonce'], 'uvdesk_thread_nonce_action')) {
                            
                           print 'Sorry, your nonce did not verify.';

                           exit;
                        
                        } else { 

                           if(isset($_POST['agent_email']) && !empty($_POST['agent_email']) && isset($_POST['threadType']) && !empty($_POST['threadType']) && isset($_POST['status']) && !empty($_POST['status']) && isset($_POST['thread_desc']) && !empty($_POST['thread_desc'])){
                                        $sdt=explode(',',$_POST['status']);
                                        if(empty($_FILES['attachments']['type'][0])){  
                                            $thread_status=UVDESK_API::post_thread_data_api('ticket/'.$sdt[1].'/threads.json',array('threadType'=>$_POST['threadType'],'reply'=>$_POST['thread_desc'],'status'=>$sdt[0],'actAsType'=>'agent','actAsEmail'=>$_POST['agent_email']));                 
                                            $thread_status=json_decode($thread_status);
                                        }
                                        else{
                                            $thread_status=UVDESK_API::post_thread_data_api_with_attachment('ticket/'.$sdt[1].'/threads.json',array('threadType'=>$_POST['threadType'],'reply'=>$_POST['thread_desc'],'status'=>$sdt[0],'actAsType'=>'agent','actAsEmail'=>$_POST['agent_email']),$_FILES['attachments']);                 
                                            $thread_status=json_decode($thread_status);   
                                        }
                                        echo '<div class="alert alert-success alert-fixed alert-load">
                                            <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
                                            <span>
                                                <i class="fa fa-check pull-left" aria-hidden="true"></i>
                                                '.$thread_status->message.'
                                            </span>
                                        </div>'; ?>
                                    <script>
                                     setTimeout(function() {
                                        jQuery(".alert-fixed").fadeOut()
                                    }, 4000);
                                    </script>    
                <?php
                           }
                        }
                    }
                    ?>
                <img class="ajax-loader-img" style="display:none" src="https://cdn.uvdesk.com/uvdesk/images/aa1b406.gif" alt="">
                <div id="container">
                    <div id="content">
                        <div class="container-fluid">
                            <div class="sidebar-button" data-target=".sidebar.left.ticket" data-view=".col2-main.ticket-view-page">
                                <p><i class="fa fa-angle-right" aria-hidden="true"></i></p>
                            </div>
                            <div class="sidebar left ticket">
                                <div class="col-sm-12">
                                    
                                    <div id="sticky-header1">
                                        <div class="panel panel-default tag-list-block">
                                            <div class="panel-heading">
                                                <h4 class="panel-title">Ticket Tags</h4>
                                            </div>
                                            <div class="panel-body">
                                                <?php if(!empty($ticket_details->ticket->tags)) :
            									            		
            					            		foreach ($ticket_details->ticket->tags as $tag_key => $tag_value) :
            					            			
            					            				echo '<div class="btn-group"><a class="btn btn-sm btn-default" href="/en/member/tickets#tag/'.$tag_value->id.'" data-tag-id='.$tag_value->id.'>'.$tag_value->name.'</a><button class="btn btn-sm btn-default delete-tag" type="button"><span class="text-danger glyphicon glyphicon-remove"></span></button></div>';	
            					            		endforeach;
            					            		
            					            		else:
            					            			echo '<label class="no-item">There is no tag available for this ticket.</label>';
            					            		endif; 
            					            
            					            		?>
                                            </div>
                                            <input type="text" class="form-control panel-input tag-input" name="name" placeholder="Add a tag ...">
                                        </div>
                                        
                                      
                                        
                                    </div>
                                </div>
                            </div>

                            <div class="col2-main ticket-view-page" id="ticket-view-page">
                                <div class="col-sm-12">
                                    <div class="block-container">
                                         
                                        <div class="col-sm-12 ticket-info-block">
                                            <div class="left-info col-sm-5">
                                                <label class="subject">
                                                    <span class="ticket-id" data-tid="<?php echo $ticket_details->ticket->id; ?>">
            							                    		#<?php echo $ticket_details->ticket->id; ?>
            							                    	</span>
                                                    <?php echo $ticket_details->ticket->subject; ?>
                                                </label>
                                            </div>
                                            <div class="right-info col-sm-7">
                                                <div class="pull-right">
                                                    <?php if(!empty($ticket_priority)) : ?>
                                                    <span class="badge priority" data-toggle="tooltip" data-placement="top" data-original-title="Priority">
            															
            															<?php echo $ticket_priority; ?>

            														</span>
                                                    <?php endif; ?>
                                                    <?php if(!empty($ticket_status_name)) : ?>
                                                    <span class="badge status" data-toggle="tooltip" data-placement="top" data-original-title="Status">
            													
            															<?php echo $ticket_status_name; ?>
            													
            														</span>
                                                    <?php endif; ?>
                                                    <?php if(!empty($ticket_group_name)) : ?>
                                                    <span class="badge group" data-toggle="tooltip" data-placement="top" data-original-title="Group">
            													
            															<?php echo $ticket_group_name; ?>
            													
            														</span>
                                                    <?php endif; ?>
                                                    <?php if(!empty($ticket_type_name)) : ?>
                                                    <span class="badge type" data-toggle="tooltip" data-placement="top" data-original-title="Type">
            															<?php echo $ticket_type_name; ?>
            														</span>
                                                    <?php endif; ?>
                                                    <?php if(!empty($ticket_totalthreads)) : ?>
                                                    <span class="badge" data-toggle="tooltip" data-placement="top" data-original-title="Threads">
            														<?php echo $ticket_totalthreads; ?>
            														</span>
                                                    <?php endif; ?>
                                                    <?php if(!empty($ticket_agentname)) : ?>
                                                    <span class="agent">
            								                    		<span class="badge" data-toggle="tooltip" data-placement="top" data-original-title="Agent">
            																
            																<i class="fa fa-user"></i>
            															</span>
                                                    <span class="name" title="Amit Chauhan">
            									                    		<?php echo $ticket_agentname;?>
            															</span>
                                                    </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 ticket-message-block">
                                            <div class="ticket-create">
                                                <div class="col-sm-12 thread-created-info">
                                                    <span class="pull-left">
            													</span>
                                                    <?php if(!empty($customer_name)) : ?>
                                                    <span class="info">
            												
            														<?php echo $customer_name; ?> 
            														
            														Created a ticket
            														
            														<?php if (!empty($attchedfiles)) :
            														  
            															echo "and attached 1 file(s)";
            															
            														endif; ?>

            													</span>
                                                    <?php endif; ?>
                                                    <?php if(!empty($ticket_created)) : ?>
                                                    <span class="text-right date">
            													
            														<?php echo $ticket_created; ?>
            													
            													</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-sm-12">
                                                    <div class="pull-left">
                                                        <a style="float: left;margin-top:5px;">
                                                            <span class="round-tabs three">
                					                    	<?php	if(!empty($ticket_details->ticket->customer->smallThumbnail) && isset($ticket_details->ticket->customer->smallThumbnail)) : 
                                                                        echo '<img src="'.$ticket_details->ticket->customer->smallThumbnail.'">';
                                                                    else :
                                                                        echo '<img src="https://cdn.uvdesk.com/uvdesk/images/d94332c.png">';
                                                                    endif; ?>
            						                        </span>
                                                        </a>
                                                    </div>
                                                    <div class="thread-body">
                                                        <?php if(!empty($customer_name)) : ?>
                                                        <div class="thread-info">
                                                            <div class="thread-info-row first">
                                                                <a>
                                                                    <strong>

            							                    			<?php echo $customer_name; ?>
            							                    		
            							                    		</strong>
                                                                </a>
                                                                <span style="display: inline-block; vertical-align: middle; margin-top: 2px; color: #314876; font-weight: 600;word-break: break-all;">
            								                    	<i class="fa fa-chevron-left"></i>
            															<?php echo $customer_email; ?>
            															
            														<i class="fa fa-chevron-right"></i>
            													</span>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php endif; ?>
                                                        
                                                        <?php if(!empty($created_thread)) : ?>
                                                        
                                                        <div class="message" style="padding: 0px; margin-top: 5px;">
                                                        
                                                            <?php echo $created_thread; ?>
                                                        
                                                        </div>
                                                        
                                                        <?php endif; ?>
                                                        
                                                         <?php 

                                                             if(!empty($ticket_details->createThread->attachments)) :  ?>
                                                                
                                                                <div class="thread-attachments">
                            
                                                                    <div class="attachment">
                                                            
                                                                        <?php foreach ($ticket_details->createThread->attachments as $attchment_key => $attchment_value) : 

                                                                            $anamea=$attchment_value->name;
                                                                        
                                                                            $aid=$attchment_value->id;
                                                                            
                                                                            $final_attachement=UVDESK_API::get_attachment_data_api('ticket/attachment/'.$aid.'.json'); 
                                                                                
                                                                            $aname=end((explode('.', $anamea)));  
                                                                        ?>
                                                                        
                                                                            <a href="<?php file_put_contents($anamea,$final_attachement); ?>" target="_blank">
                                                                                <i class="fa fa-file zip" title="" data-toggle="tooltip" data-original-title="<?php echo $attchment_value->name; ?>">
                                                                                    <span>
                                                                                        <?php echo $aname; ?>
                                                                                    </span>
                                                                                </i>
                                                                            </a>

                                                                        <?php endforeach; ?>
                                                                    
                                                                    </div>
                                                                    
                                                                 </div>

                                                            <?php     endif; ?> 

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="thread-pagination">
                                                  
                                                <span></span>
                                            </div>
                                            <div class="ticket-thread">
                                                <?php	
                                                        foreach ($ticket_thread->threads as $thread_key => $thread_value) :  	?>
                                                    <div class="thread">
                                                        <div class="col-sm-12 thread-created-info">
                                                            <span class="info">
            													
            																
            																<a href="#thread/<?php echo $thread_value->id; ?>" id="thread<?php echo $thread_value->id; ?>" class="copy-thread-link">#<?php echo $thread_value->id; ?></a>
            																<?php if(isset( $thread_value->user->detail->agent)){

                                                                                    echo $thread_value->user->detail->agent->name;
                                                                                
                                                                                }
                                                                                else{
                                                                                
                                                                                    echo $thread_value->user->detail->customer->name;   
                                                                                
                                                                                }?>
            																replied					
            																
            																
            															</span>
                                                            <span class="text-right date">
            														
            																<?php echo $thread_value->formatedCreatedAt; ?>
            														
            															</span>
                                                        </div>
                                                        <div class="col-sm-12">
                                                            <div class="pull-left">
                                                                <a style="float: left;margin-top:5px">
                                                                    <span class="round-tabs three">
            													         <?php
                                                                            if(isset( $thread_value->user->smallThumbnail) && !empty( $thread_value->user->smallThumbnail)){

                                                                                    echo '<img src="'.$thread_value->user->smallThumbnail.'">';
                                                                                
                                                                                }
                                                                                else{
                                                                                
                                                                                    echo '<img src="https://cdn.uvdesk.com/uvdesk/images/e09dabf.png">';
                                                                                
                                                                                }?>        
            																			 
            										                </span>
                                                                </a>
                                                            </div>
                                                            <div class="thread-body">
                                                                <div class="thread-info">
                                                                    <div class="thread-info-row first">
                                                                        <a style="float: left;margin-top:5px">
                                                                            <strong> 
            																			<?php if(isset( $thread_value->user->detail->agent)){
            																				echo $thread_value->user->detail->agent->name;
            																			}
            																			else{
            																				echo $thread_value->user->detail->customer->name;	
            																			}?>
            														                	</strong>
                                                                        </a>
                                                                        <label class="user-type agent">
                                                                            <?php if(isset( $thread_value->user->detail->agent)){
            															                				echo "Agent";
            															                			}
            															                			else{
            															                				echo "Customer";
            															                			}				    ?>
                                                                        </label>
                                                                      
                                                                    </div>
                                                                    <div class="thread-info-row">
                                                                    </div>
                                                                </div>
                                                                <div class="message reply agent">
                                                                    <div class="main-reply">
                                                                        <?php 
            																		 	echo $thread_value->reply;
            																		 ?>
                                                                    </div>
                                                                </div>

                                                                <?php 
                                                                	 if(!empty($thread_value->attachments)) :  
                                                                       ?>
            	                                                   
            	                                                    <div class="thread-attachments">
            							
            																<div class="attachment">
            														
            																	<?php foreach ($thread_value->attachments as $attchment_key => $attchment_value) : 
            																		$aid=$attchment_value->id; 
                                                                                     $anamea=$attchment_value->name;
                                                                        
                                                                            $aid=$attchment_value->id; 
                                                                            
                                                                            $aname=end((explode('.', $anamea)));  
                                                                        ?>
                                                                        
                                                                            <a href="<?php echo site_url()."/uvdesk/download/".$aid; ?>" target="_blank">
                                                                                <i class="fa fa-file zip" title="" data-toggle="tooltip" data-original-title="<?php echo $attchment_value->name; ?>"> 
                                                                                    <span>
                                                                                        <?php echo $aname; ?>
                                                                                    </span>
                                                                                </i>
                                                                            </a>

            																	<?php endforeach; ?>
            																
            																</div>
            															
            														</div>

            													<?php endif; ?>	
                                                    
                                                            </div>
                                                    
                                                        </div>
                                                    
                                                    </div>

                                                    <?php	endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 ticket-form-block">
            								    <div class="col-sm-12">
            								        <div class="pull-left">
            								            <a style="float: left;margin-top:5px;" href="#">
            								                <span class="round-tabs three">
            										               <?php
                                                                    if(isset( $thread_value->user->smallThumbnail) && !empty( $thread_value->user->smallThumbnail)){

                                                                            echo '<img src="'.$thread_value->user->smallThumbnail.'">';
                                                                        
                                                                        }
                                                                        else{
                                                                        
                                                                            echo '<img src="https://cdn.uvdesk.com/uvdesk/images/e09dabf.png">';
                                                                        
                                                                        }?>    
            										        </span>
            								            </a>
            								        </div>
            								        <div class="tab-block form-div">
            								            <div class="ticket-form">
            								                <div class="thread-info-row">
            								                    <a href="#">
            								                        <strong><?php echo $ticket_details->ticket->agent->detail->agent->name; ?></strong>
            								                    </a>
            								                </div>
            								            </div>
            								           
            								            <div class="tab-content">
            								                <div role="tabpanel" class="tab-pane active" id="reply">
            								                   
            								                    <form class="col-sm-12" enctype="multipart/form-data" method="post" action="">
            								                    	
            								                    	<?php wp_nonce_field( 'uvdesk_thread_nonce_action', 'uvdesk_thread_nonce' ); ?> 
            								                    	
            								                    	<input type="hidden" name="agent_email" value="<?php echo $ticket_details->ticket->agent->email; ?>"> 
            								                        <input type="hidden" name="threadType" value="reply">
            								                        <input type="hidden" name="status" class="reply-status" value="1,<?php echo $ticket_details->ticket->id; ?>">
            								                         
            								                         <?php 

            								                         $settings = array(



            																	'media_buttons' => false, // show insert/upload button(s)



            																	'textarea_name' => 'thread_desc',		



            																	'textarea_rows' => get_option('default_post_edit_rows', 10),



            																	'tabindex' => '',



            																	'teeny' => false, 



            																	'dfw' => false,



            																	'tinymce' => true, /* load TinyMCE, can be used to pass settings directly to TinyMCE using an array()*/



            																	'quicktags' => false /* load Quicktags, can be used to pass settings directly to Quicktags using an array()*/



            																	);

            								                         echo wp_editor('','product_desc',$settings);

            								                         ?>
             
            								                        <div class="form-group attachments">
            								                            <div class="labelWidget">
            								                                <input id="attachments" class="fileHide" type="file" enableremoveoption="enableRemoveOption" decoratecss="attach-file" decoratefile="decorateFile" infolabeltext="+ Attach File" infolabel="right" name="attachments[]">
            								                                <label class="attach-file pointer">
            								                                </label>
            								                                <i class="fa fa-times remove-file pointer"></i>
            								                            </div>
            								                            <span id="addFile" class="label-right pointer">Attach File</span>
            								                        </div>
            								                        <div class="col-sm-12 dropup">
            								                      
            								                            <div class="btn-group dropup reply-status-dropup">
            								                                <button class="btn btn-md btn-info pull-left submit" type="submit">
            								                                    Reply
            								                                </button>
            								                                
            								                            </div>
            								                        </div>
            								                    </form>
            								                </div>
            								              
            								            </div>
            								        </div>
            								    </div>
            								</div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <?php


        }
    }
    else{
        echo "<h1>Please Enter a valid Access Token</h1>";
    }	
}
