<?php 

if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly

}

/**
 * Handle frontend forms
 *
 * @class 		UVDESK_API_Form_Handler
 * @version		1.0.0
 * @package		uvdesk_app/include/
 * @category	Class
 * @author 		webkul
 */  

function wk_customer_dashboard(){  

	$uvdesk_access_token=get_option('uvdesk_access_token');
	
	if(!empty($uvdesk_access_token)){

		$current_user=wp_get_current_user();

		$c_email=$current_user->user_email;

		$paged=get_query_var('paged'); 
	 
		$data_api=UVDESK_API::get_customer_data_api('tickets.json',array('sort'=>'t.id','direction'=>'asc','search'=>$c_email)); 
		
		if(isset($data_api->error) || empty($data_api)){

				echo "<h1>Invalid Details.</h1><h3>Please contact Administrator.</h3>";
		}else{
	   
			?>
				<a href="<?php echo site_url().'/uvdesk/customer/create-ticket';?>" class="create-ticket"><i class="fa fa-plus"></i> Create Ticket</a>
				<div class="block-container">
					<div class="customer-ticket-wrapper">
				    	<div class="col-sm-12 clearfix">
					        <div class="filter form-horizontal">
					            <div class="col-sm-8">
					                <div class="filter-left">
					                    <label>Sort By - </label>
					                    <div class="btn-group bootstrap-select">
					                        <button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button">
					                            <span class="selected-option-label pull-left">Id</span>
					                            <span class="caret"></span>
					                        </button>
					                        <ul class="dropdown-menu sort-records">
					                            <li><a href="#" data-field="t.id" class="active desc ">Ticket Id</a></li>
					                            <li><a href="#" data-field="name" class="">Agent Name</a></li>
					                        </ul>
					                    </div>
					                </div>

					            </div>
					            <div class="col-sm-4">
					            	<div class="text-right">
						               <div class="filter-right">
						                    <label>Status - </label>
						                     
					                        <select class="selectpicker filter-by-status" id="status-filter" tabindex="-98">
					                            <option value="">Filter By Status</option>
					                            <option value="1">Open</option>
					                            <option value="2">Pending</option>
					                            <option value="6">Answered</option>
					                            <option value="3">Resolved</option>
					                            <option value="4">Closed</option>
					                            <option value="5">Spam</option>
					                        </select> 

						                </div>
						            </div>    
					            </div>
					        </div>
				        </div>
				    </div>
				    <div class="customer-ticket-section">
					    <div class="col-sm-12">
					        <div class="table-container clearfix" id="ticket-table">
					            <table class="table">
					                <colgroup>
					                    <col class="id">
					                    <col class="subject">
					                    <col class="details">
					                    <col class="agent-name">
					                </colgroup>
					                <tbody>

					                    <?php  
					                    	if(!empty($data_api->tickets) && isset($data_api->tickets)){
					                    		 
					                    		 foreach ($data_api->tickets as $ticket_key => $ticket_value) { ?>
														
				                    		 		<tr data-toggle="tooltip" data-placement="left" title="" class="Open 1 unread" data-original-title="Open">
								                        <td class="id" style="border-left: 3px solid #337ab7;">
								                            <a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>">
													    		<?php echo $ticket_value->incrementId; ?>
													    	</a>
								                        </td>
								                        <td class="subject">
								                            <a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>" class="subject">
													    		<?php echo $ticket_value->subject; ?>
													    	</a>
								                            <span class="fade-subject"></span>
								                        </td>
								                        <td class="details">
								                            <a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>">
								                                <span class="date"><?php echo $ticket_value->formatedCreatedAt; ?></span>
								                                <span class="badge badge-lg">
																	<?php echo $ticket_value->customer->name; ?>
																</span>
								                                <span class="badge badge-lg"><?php echo $ticket_value->totalThreads; ?></span>
								                            </a>
								                        </td>
								                        <td class="agent-name">
								                            <a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>">
								        		
												        		<?php echo $ticket_value->agent->name; ?>
													        	
												        	</a>
								                        </td>
								                      
								                    </tr>

					                    <?php   }
					                    	
					                    	}

					                    ?>
					                    
					                </tbody>
					            </table>
					        </div>
					    </div>
					    <div class="col-sm-12">
					        <div class="navigation">

		    				<?php	
		    				$tot_post=$data_api->pagination->totalCount;
		    				$per_page=$data_api->pagination->numItemsPerPage; 
		    				$last_page=$data_api->pagination->pageCount;
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
							    echo "</div>";
							    ?>
							
							</div>

					    </div>
				</div>    
			</div>
	 
			<?php
 
			}
		}	  
        else{

            echo "<h1>Please Enter a valid Access Token<h1>";
            
        }  
	
}