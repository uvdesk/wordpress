<?php 

if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

}

function customer_ticket_view(){    

    $ticket_id=intval(get_query_var('tid'));

	$paged=intval(get_query_var('paged'));
    
     $uvdesk_access_token=get_option('uvdesk_access_token');
    
    if(!empty($uvdesk_access_token)){       
    
        $ticket_details=UVDESK_API::get_customer_data_api('ticket/'.$ticket_id.'.json');     
        
         if(isset($ticket_details->error) || empty($ticket_details)){
           
             echo "<h1>Invalid Details.</h1><h3>Please contact Administrator.</h3>";
               
            }
            else {


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
             

                if(!empty($ticket_details->ticket->formatedCreatedAt) && isset($ticket_details->ticket->formatedCreatedAt)){
                
                    $ticket_created=$ticket_details->ticket->formatedCreatedAt;
                    
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
                if (!empty($paged)) {
                    
                        $ticket_thread=UVDESK_API::get_customer_data_api('ticket/'.$ticket_details->ticket->id.'/threads.json');  
                    } 
                    else{

                        $ticket_thread=UVDESK_API::get_customer_data_api('ticket/'.$ticket_details->ticket->id.'/threads.json',array('page'=>$paged));  

                    }   

                 // echo "<pre>";var_dump($ticket_thread);  
             
                ?>
            <div class="body content-wrap">
                <img class="ajax-loader-img" style="display:none" src="https://cdn.uvdesk.com/uvdesk/images/aa1b406.gif" alt="">
                <div id="container">
                    <div id="content">
                        <div class="container-fluid">
                            <div class="sidebar-button" data-target=".sidebar.left.ticket" data-view=".col2-main.ticket-view-page">
                                <p><i class="fa fa-angle-right" aria-hidden="true"></i></p>
                            </div>
                             <div class="sidebar left collaborator-list-block big-screen">
                                <div class="panel panel-default  border-block">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            Customer                        </h4>
                                    </div>
                                    <div class="panel-body">
                                        <span class="round-tabs two">
                                            <?php  

                                            if(isset($ticket_details->ticket->customer->smallThumbnail)) : 
                                                echo '<img class="border" src='.$ticket_details->ticket->customer->smallThumbnail.'>';
                                            else :
                                                echo '<img class="border" src="https://s3-ap-southeast-1.amazonaws.com/cdn.uvdesk.com/website/75/phpx0xPfN.jpg">';
                                            endif;
                                        ?>
                                        </span>
                                       <?php if(!empty($customer_name)) : 
                                            echo "<a>".$customer_name."</a>";
                                         endif; ?>
                                    </div>
                                </div>
                                 <div class="information-block">
                                    <div class="panel panel-default">
                                        <div class="panel-body">
                                            <label class="information-block-title">
                                                <span class="badge badge-warning">
                                                    <i class="fa fa-info"></i>
                                                </span>
                                                Help and Information                </label>
                                                <div class="information-item">
                                                    <label>Ticket</label>
                                                    <p>A ticket is the support request submitted by the customers to inquire about their problems.</p>
                                                </div>
                                                <div class="information-item">
                                                    <label>Ticket creation</label>
                                                    <p>The moment when the users enter their basic details, they get registered with UVdesk services and confirmation mail regarding the account activation is sent to their ID’s. They have to click on the link provided for setting the password.</p>
                                                </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col2-main ticket-view-page" id="ticket-view-page">
                                <div class="col-sm-12">
                                    <div class="block-container">
                                        <div class="col-sm-12 ticket-info-block clearfix">
                                            <div class="pull-left">
                                                <label class="information-block-title">
                                                    #<?php echo $ticket_details->ticket->id; ?>
                                                </label>
                                            </div>
                                            <div class="ticket-info">
                                                <div class="ticket-info-row first">
                                                    <a class="subject">
                                                        <?php echo $ticket_details->ticket->subject; ?>
                                                    </a>
                                                </div>
                                                <div class="ticket-info-row span-det">
                                                    <a href="#wp-product_desc-wrap" class="label label-primary scrollPage">
                                                        <i class="fa fa-reply"></i>
                                                        Reply                           </a>
                                                    <?php if(!empty($ticket_created)) : 
                                                    echo '<span class="badge badge-lg">'
                                                        .$ticket_created.
                                                    '</span>'; 
                                                     endif;
                                                     
                                                    ?>
                                                    
                                                    <?php if(isset($ticket_details->ticketTotalThreads)) : ?>
                                                    
                                                    <span class="badge badge-lg">
                                                        <?php echo $ticket_details->ticketTotalThreads.' Replies'; ?>
                                                    </span>
                                                    
                                                    <?php endif; ?>
                                                    
                                                    <?php if(!empty($ticket_details->ticket->group->name)) : 
                                                    echo '<span class="badge badge-lg">'
                                                            .$ticket_details->ticket->group->name.
                                                    '</span>';
                                                    
                                                    endif; ?>
                                                    
                                                    <?php if(!empty($ticket_details->ticket->priority->name)) : 

                                                        echo '<span class="badge badge-lg">'.$ticket_details->ticket->priority->name.'</span>';

                                                    endif; ?>    

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
                            					                    			<img src="https://cdn.uvdesk.com/uvdesk/images/d94332c.png">
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
                                                            
                                                        if(!empty($thread_value->attachments)) :  ?>
                                                           
                                                            <div class="thread-attachments">
                                
                                                                    <div class="attachment">
                                                            
                                                                        <?php foreach ($thread_value->attachments as $attchment_key => $attchment_value) : 
                                                                            $aname=$attchment_value->name;
                                                                            $aname=end((explode('.', $aname)));  
                                                                        ?>
                                                                        
                                                                            <a href="<?php echo $attchment_value->path; ?>" target="_blank">
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
                                           
                                            <div class="ticket-thread">
                                                <?php		
            											foreach ($ticket_thread->threads as $thread_key => $thread_value) : ?>
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
            													                    
            															<?php if(isset( $thread_value->user->smallThumbnail) && !empty( $thread_value->user->smallThumbnail)){

                                                                                    echo '<img src="'.$thread_value->user->smallThumbnail.'">';
                                                                                
                                                                                }
                                                                                else{
                                                                                
                                                                                    echo '<img src="https://cdn.uvdesk.com/uvdesk/images/e09dabf.png">';
                                                                                
                                                                                }

                                                                                ?>				
            																	
            										                    
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
            																		$aname=$attchment_value->name;
            																		$aname=end((explode('.', $aname)));  
            																	?>
            																	
            																		<a href="<?php echo $attchment_value->path; ?>" target="_blank">
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
            								                        <strong><?php echo $ticket_details->ticket->customer->detail->customer->name; ?></strong>
            								                    </a>
            								                </div>
            								            </div>
            								           
            								            <div class="tab-content">
            								                <div role="tabpanel" class="tab-pane active" id="reply">
            								                   
            								                    <form class="col-sm-12" enctype="multipart/form-data" method="post" action="">
            								                    	
            								                    	<?php wp_nonce_field( 'uvdesk_thread_nonce_action', 'uvdesk_thread_nonce' ); ?> 
            								                    	
            								                    	<input type="hidden" name="customer_email" value="<?php echo $ticket_details->ticket->customer->email; ?>"> 
            								                        <input type="hidden" name="threadType" value="reply">
            								                        <input type="hidden" name="status" class="reply-status" value="1,<?php echo $ticket_details->ticket->id; ?>">
            								                         
            								                         <?php 

            								                         $settings = array(



            																	'media_buttons' => false, // show insert/upload button(s)



            																	'textarea_name' => 'thread_desc',		



            																	'textarea_rows' => get_option('default_post_edit_rows', 10),

                                                                                'paste_data_images' => true,

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
            								                            <span id="addFile" class="label-right pointer">+ Attach File</span>
            								                        </div>
            								                        <div class="col-sm-12 dropup">
            								                      
            								                            <div class="btn-group dropup reply-status-dropup">
            								                                <button class="btn btn-md btn-info pull-left submit" type="submit" name="submit-thread">
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

                                <div class="col-sm-12">
                                                    <div class="navigation">

                                                    <?php   
                                                    $tot_post=$ticket_thread->pagination->totalCount;
                                                    $per_page=$ticket_thread->pagination->numItemsPerPage; 
                                                    $last_page=$ticket_thread->pagination->pageCount; 
                                                    function uv_pagination($tot_post,$per_page,$last_page,$paged) { 
                                                            $prev_arrow = 'Next&nbsp;&raquo;';

                                                            $next_arrow = '&laquo;&nbsp;Previous';

                                                            global $wp_query;

                                                            if($tot_post>0)

                                                            {

                                                                $total=$tot_post/$per_page;
                                                            }

                                                            else

                                                            {

                                                                $total = $last_page;

                                                            }          
                                                            $big = 9999999999999; // need an unlikely integer 
                                                            
                                                            if( $total > 1 )  {

                                                                 if( !$current_page = $paged )

                                                                     $current_page = 1;

                                                                 if( get_option('permalink_structure') ) {

                                                                     $format = 'page/%#%/';

                                                                 } else {

                                                                     $format = '&paged=%#%';

                                                                 }

                                                                echo paginate_links(array(

                                                                    'base'          => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),

                                                                    'format'        => $format,

                                                                    'current'       => max( 1, $paged ),

                                                                    'total'         => ceil($total),

                                                                    'mid_size'      => 3,

                                                                    'type'          => 'list',

                                                                    'prev_text'     => $next_arrow,

                                                                    'next_text'     => $prev_arrow,

                                                                 ) );

                                                            }

                                                        } 
                                                        echo "<nav class='uv-pagination'>";
                                                            uv_pagination($tot_post,$per_page,$last_page,$paged);
                                                        echo "</nav>";
                                                        ?>
                                                    
                                                    </div>

                                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
                
                if(isset($_POST['submit-thread'])){
                	if ( isset( $_POST['uvdesk_thread_nonce'] )) {
                		if (! wp_verify_nonce( $_POST['uvdesk_thread_nonce'], 'uvdesk_thread_nonce_action')) {
                			
                		   print 'Sorry, your nonce did not verify.';

                		   exit;
                		
                		} else {
                		   
                		   if(isset($_POST['customer_email']) && !empty($_POST['customer_email']) && isset($_POST['threadType']) && !empty($_POST['threadType']) && isset($_POST['status']) && !empty($_POST['status']) && isset($_POST['thread_desc']) && !empty($_POST['thread_desc'])){
            		   				$sdt=explode(',',$_POST['status']);
                                      $reply=$_POST['thread_desc']; 
                                     if(empty($_FILES['attachments']['type'])){
                                       
             						     $thread_status=UVDESK_API::post_thread_data_api('ticket/'.$sdt[1].'/threads.json',array('threadType'=>$_POST['threadType'],'reply'=>$reply,'status'=>$sdt[0],'actAsType'=>'customer','actAsEmail'=>$_POST['customer_email'])); 		   		
                                     }
                                     else{

                                         $thread_status=UVDESK_API::post_thread_data_api('ticket/'.$sdt[1].'/threads.json',array('threadType'=>$_POST['threadType'],'reply'=>$reply,'status'=>$sdt[0],'actAsType'=>'customer','actAsEmail'=>$_POST['customer_email']),$_FILES['attachments']); 

                                     }

            						$thread_status=json_decode($thread_status);
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
            	}

             }
       }
        else{
            echo "<h1>Please Enter a valid Access Token</h1>";
        }    
}
