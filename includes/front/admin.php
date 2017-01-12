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

function admin_dashboard(){  

	$paged=get_query_var('paged');
	
	$search=get_query_var('search'); 

	$uvdesk_access_token=get_option('uvdesk_access_token');
	
	if(!empty($uvdesk_access_token)){
		
		if(!empty($paged)){

			$data_api=UVDESK_API::get_customer_data_api('tickets.json',array('sort'=>'t.id','direction'=>'asc','page'=>$paged));  	 

		}
		elseif(!empty($search)){
			
			$data_api=UVDESK_API::get_customer_data_api('tickets.json',array('sort'=>'t.id','search'=>$search,'direction'=>'asc'));  	 

		}
		elseif(!empty($paged) && !empty($search)){
			
			$data_api=UVDESK_API::get_customer_data_api('tickets.json',array('sort'=>'t.id','search'=>$search,'direction'=>'asc','page'=>$paged));  	 

		}
		else{
			
			$data_api=UVDESK_API::get_customer_data_api('tickets.json',array('sort'=>'t.id','direction'=>'asc'));  	 

		}

		if(isset($data_api->error) || empty($data_api)){

				echo "<h1>Invalid Details.</h1><h3>Please contact Administrator.</h3>";
		}else{

			$data_api_groups=UVDESK_API::get_customer_data_api('groups.json');

			$data_api_members=UVDESK_API::get_customer_data_api('members.json',array('sort'=>'name','fullList'=>'true'));  
			
			foreach ($data_api->tabs as $key => $value) {
				
				$tabs_arr[]=json_decode(json_encode($value),TRUE);
			}  

			?>

			<div class="body content-wrap ticket-list">
				<div class="alert alert-success alert-fixed alert-load">
		            <button class="close" type="button" data-dismiss="alert" aria-hidden="true">×</button>
		            <span>
		                 
		            </span>
		        </div>

		            <div id="container">
		                <div id="content">
		            		<div class="container-fluid">
		                    	<div class="sidebar-button" data-target=".sidebar.left.ticket" data-view=".col2-main.ticket-view">
									<p><i class="fa fa-angle-right" aria-hidden="true"></i></p>
								</div>
								<div class="sidebar left ticket custom-collapse support-mailbox">
								    <div class="col-sm-12">
								        <div class="panel panel-default">
								            <div class="panel-heading">
								                <h4 class="panel-title">
								                	Tickets	                </h4>
								            </div>
								            <div class="panel-body">
								            	<strong>
								            		<i class="fa fa-inbox"></i>
								            		Support Mailbox	            	</strong>
								            	<div class="ticket-label-block">
													<ul class="list-block predefined-label-list">
														 
														<?php if(!empty($data_api->labels->predefind)) : 
															  $sclass='';
															  $class='';
															  foreach ($data_api->labels->predefind as $pre_key => $pre_value) :
																if($pre_key=='new'){
																	$class='class="active"';
																}		
																elseif($pre_key=='unassigned'){
																	$sclass="badge-danger";
																}
																else{
																	$sclass="badge-success";

																}
																	echo '<li $class>
															        		<a href="#'.$pre_key.'">
																    			'.$pre_key.'	    			
																    			<span class="badge '.$sclass.'">'.$pre_value.'</span>
																    		</a>
															        	</li>';
																
															  	endforeach;

															  	endif;	
															?>
														
											        </ul>
											    	 
												</div> 
											
											</div>

										</div>
									</div>
									<div class="col-sm-12">
								        <div class="panel panel-default">
								            <div class="panel-heading">
								                <h4 class="panel-title">Filter Tickets</h4>
								            </div>
								            <div class="panel-body">
								            	<ul class="list-block filter-list">
								            		<li>
								                		<div class="form-group">
											                <label for="agent-filter-input">
											                	Assigned to				                
											                </label>
											                <div class="dropdown" id="agent-filter">
											                	<span class="tags"></span>
											                	<input type="text" class="form-control dropdown-toggle" data-type="agent" id="agent-filter-input" data-toggle="dropdown" aria-expanded="false">
											                			
										                			<ul class="dropdown-menu">
																	<?php 
																		if(!empty($data_api_members) && isset($data_api_members)) :
																	
																		foreach ($data_api_members as $member_key => $member_value) :
																		 	
																		 	echo "<li data-id='".$member_value->id."'><a><span class='text'>".$member_value->name."</span></a></li>";
																	
																		 endforeach;

																		endif;  
																	?>
																	<li class="no-results" style="display: none;">
																			No result found										
																		</li>
																	</ul>
											                </div>
											            </div>
								                	</li>
									                <li>
								                		<div class="form-group">
											                <label for="customer-filter-input">
											                	Customer				                </label>
											                <div class="dropdown" id="customer-filter">
											                	<span class="tags"></span>
											                	<i class="fa fa-spinner fa-spin" aria-hidden="true" style="display: none;"></i>
											                	<input type="text" class="form-control dropdown-toggle" data-type="customer" id="customer-filter-input" data-toggle="dropdown" aria-expanded="false">
											                	<ul class="dropdown-menu">
																	 
																</ul>
											                </div>
											            </div>
								                	</li>
								                	<li>
								                		<div class="form-group">
											                <label for="group-filter-input">
											                	Group				                </label>
											                <div class="dropdown" id="group-filter">
											                	<span class="tags"></span>
											                	<input type="text" class="form-control dropdown-toggle" data-type="group" id="group-filter-input" data-toggle="dropdown" aria-expanded="false">
											                	<ul class="dropdown-menu">
																	<?php foreach ($data_api_groups->groups as $group_data) :
																			
																		echo '<li data-id='.$group_data->id.'>
																			<a>
																				<span class="text">'.$group_data->name.'</span>
																			</a>
																		</li>';	
																		
																		endforeach; ?>
																		
																		<li class="no-results" style="display: none;">
																			No result found									
																		</li>
																</ul>
											                </div>
											            </div>
								                	</li>
								                	<li>
								                		<div class="form-group">
											                <label for="team-filter-input">
											                	Team				                </label>
											                <div class="dropdown" id="team-filter">
											                	<span class="tags"></span>
											                	<input type="text" class="form-control dropdown-toggle" data-type="team" id="team-filter-input" data-toggle="dropdown" aria-expanded="false">
											                	<ul class="dropdown-menu">
																	<?php 

																		if(!empty($data_api->team) && isset($data_api->team)) :
																	
																		foreach ($data_api->team as $team_key => $team_value) :
																		 	
																		 	echo "<li data-id='".$team_value->id."'><a><span class='text'>".$team_value->name."</span></a></li>";
																	
																		 endforeach;

																		endif;  
																	?>
																 
																	<li class="no-results" style="display: none;">
																		No result found										
																	</li>
																</ul>
											                </div>
											            </div>
								                	</li>
								                	<li>
								                		<div class="form-group">
											                <label for="priority-filter-input">
											                	Priority				                </label>
											                <div class="dropdown" id="priority-filter">
											                	<span class="tags"></span>
											                	<input type="text" class="form-control dropdown-toggle" data-type="priority" id="priority-filter-input" data-toggle="dropdown" aria-expanded="false">
											                	<ul class="dropdown-menu">
										                			<li data-id="1">
																		<a>
																			<span class="text">Low</span>
																		</a>
																	</li> 
																	<li data-id="2">
																		<a>
																			<span class="text">Medium</span>
																		</a>
																	</li>
																	<li data-id="3">
																		<a>
																			<span class="text">High</span>
																		</a>
																	</li>
																	<li data-id="4">
																		<a>
																			<span class="text">Urgent</span>
																		</a>
																	</li>
																	
																	<li class="no-results" style="display: none;">
																		No result found
																	</li>
																</ul>
											                </div>
											            </div>
											        </li>
								                	<li>
								                		<div class="form-group">
											                <label for="type-filter-input">
											                	Type				                </label>
											                <div class="dropdown" id="type-filter">
											                	<span class="tags"></span>
											                	<input type="text" class="form-control dropdown-toggle" data-type="type" id="type-filter-input" data-toggle="dropdown" aria-expanded="false">
											                	<ul class="dropdown-menu">
										                			<li data-id="4">
																		<a>
																			<span class="text">PreSale Query</span>
																		</a>
																	</li>
																	<li data-id="5">
																		<a>
																			<span class="text">Support</span>
																		</a>
																	</li>
																	<li data-id="6">
																		<a>
																			<span class="text">Shopify</span>
																		</a>
																	</li>
																	<li data-id="12">
																		<a>
																			<span class="text">Customization</span>
																		</a>
																	</li>
																	<li data-id="13">
																		<a>
																			<span class="text">Cs-Cart</span>
																		</a>
																	</li>
																	<li data-id="14">
																		<a>
																			<span class="text">Jatayu</span>
																		</a>
																	</li>
																	<li data-id="15">
																		<a>
																			<span class="text">Joomla</span>
																		</a>
																	</li>
																	<li data-id="16">
																		<a>
																			<span class="text">Magento</span>
																		</a>
																	</li>
																	<li data-id="17">
																		<a>
																			<span class="text">Mobikul</span>
																		</a>
																	</li>
																	<li data-id="18">
																		<a>
																			<span class="text">Opencart</span>
																		</a>
																	</li>
																	<li data-id="19">
																		<a>
																			<span class="text">OpenErp/ Odoo</span>
																		</a>
																	</li>
																	<li data-id="20">
																		<a>
																			<span class="text">PrestaShop</span>
																		</a>
																	</li>
																	<li data-id="21">
																		<a>
																			<span class="text">SalesForce</span>
																		</a>
																	</li>
																	<li data-id="22">
																		<a>
																			<span class="text">WordPress</span>
																		</a>
																	</li>
																	<li data-id="23">
																		<a>
																			<span class="text">X-Cart</span>
																		</a>
																	</li>
																	<li data-id="24">
																		<a>
																			<span class="text">Qloapps</span>
																		</a>
																	</li>
																	<li class="no-results" style="display: none;">
																		No result found										
																	</li>
																</ul>
											                </div>
											            </div>
								                	</li>
								                	<li>
								                		<div class="form-group">
											                <label for="tag-filter-input">
											                	Tag				                </label>
											                <div class="dropdown" id="tag-filter">
											                	<span class="tags"></span>
											                	<i class="fa fa-spinner fa-spin" aria-hidden="true" style="display: none;"></i>
											                	<input type="text" class="form-control dropdown-toggle" data-type="tag" id="tag-filter-input" data-toggle="dropdown" aria-expanded="false">
											                	<ul class="dropdown-menu">
																	<li class="no-results">
																		Type atleast 2 letters										
																	</li>
																</ul>
											                </div>
											            </div>
								                	</li>
								                </ul>
								            </div>
								        </div>
								    </div>

			    				 
		    		    		</div>

			<div class="col2-main ticket-view">
				<div class="col-sm-12">
					<div class="block-container">
						<div class="modal fade" id="quickViewModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
						  	<div class="modal-dialog modal-sm" role="document" style="width:1000px">
						    	<div class="modal-content">
					      			<div class="modal-body" style="padding:0px">
										
						     		</div>
						    	</div>
						  	</div>
						</div>
						<br>
						<div class="col-sm-12 filter-form-div">
							<div class="select-all-box">
							    <div class="icheckbox_square-blue">
						            <input type="checkbox" class=mass-action-checkbox style="position: absolute; top: -10%; left: -10%; display: block; width: 120%; height: 120%; margin: 0px; padding: 0px; border: 0px; opacity: 0; background: rgb(255, 255, 255);">
						            <ins class=iCheck-helper></ins>
						        </div>
							</div>
							<div class="mass-action-block">
							    <div class="property-block pull-left">
							        <div class="agents" style="float: left;margin-right: 5px;">
							             
							                <select class="selectpicker" id="mass-agent" data-live-search="true" title="Assign to" tabindex="-98">
							                    <option class="bs-title-option" value="">Assign to</option>
							                <?php 
							                    if(!empty($data_api_members)) :
												
													foreach ($data_api_members as $member_key => $member_value) :

														if(empty($member_value->smallThumbnail)){
													 		$image='https://cdn.uvdesk.com/uvdesk/images/e09dabf.png';
													 	}
													 	else{
													 		$image=$member_value->smallThumbnail;
													 	}

													 ?>

							                    		<option value="<?php echo $member_value->id; ?>" data-content="<span class='round-tabs two'><img  src='<?php echo $image;?>'/></span><div class='name'><?php echo $member_value->name; ?></div>"></option>
							                    <?php 
							                    	
							                    	endforeach; 
							                    	
							                    	endif;
							                    ?>	 
							                   
							                </select> 
							        </div>
							        
							        <select class="selectpicker" id="mass-status" title="Move to" tabindex="-98">
						                <option class="bs-title-option" value="">Move to</option>
						                <option value="1">Open</option>
						                <option value="2">Pending</option>
						                <option value="6">Answered</option>
						                <option value="3">Resolved</option>
						                <option value="4">Closed</option>
						                <option value="5">Spam</option>
							        </select> 
							         
						          
							        
							        <select class="selectpicker" id="mass-priority" title="Priority" tabindex="-98">
						               
						                <option class="bs-title-option" value="">Priority</option>
						               
						               	<option value="1" >Low</option>
						                <option value="2" data-custom-style="background-color:#337ab7;border-color:#337ab7;color:#fff">Medium</option>
						                <option value="3" data-custom-style="background-color:#f0ad4e;border-color:#f0ad4e;color:#fff">High</option>
						                <option value="4" data-custom-style="background-color:#d9534f;border-color:#d9534f;color:#fff">Urgent</option>

							        </select>
							        
							        <select class="selectpicker" id="mass-label" title="Label" tabindex="-98">
						            
						                <option class="bs-title-option" value="">Label</option>
						            
						                <?php 
						                	if(!empty($data_api->labels->custom)) : 
						                   		foreach ($data_api->labels->custom as $label_key => $label_value) : ?>
							                    	 <option value="<?php echo $label_value->id; ?>"><?php echo $label_value->name; ?></option>
							       				                    
						                    <?php 
							                   endforeach; 

							                endif; ?> 

							        </select> 

							        <button class="btn btn-default" id="mass-delete" style="margin-left: 4px;">Delete </button>
							    </div>
							    <div class="trashed-block pull-left" style="display: none;">
							        <button class="btn btn-default" id="mass-restore-ticket" style="margin-left: 4px;">
							            Restore </button>
							        <button class="btn btn-default" id="mass-delete-forever" style="margin-left: 4px;">
							            Delete Forever </button>
							    </div>
							</div>

							<div class="filter form-horizontal ticket">
								<div class="pull-left form-filter-container sort-by">
				            		<label>Sort By - </label>
									<div class="btn-group bootstrap-select">
										<button data-toggle="dropdown" class="btn btn-default dropdown-toggle" type="button">
											<span class="selected-option-label pull-left">Last Replied</span>
											<span class="caret"></span>
										</button>
										<ul class="dropdown-menu sort-records">
											
											<li><a href="#" data-field="t.id" class="">Ticket Id</a></li>

											<li><a href="#" data-field="t.updatedAt" class="active desc ">Last Replied</a></li>

											<li><a href="#" data-field="agentName" class="">Assign To</a></li>

										    <li><a href="#" data-field="c.email" class="">Customer Email</a></li>

										    <li><a href="#" data-field="name" class="">Customer Name</a></li>
										</ul>
									</div>
									  
									</div>
									<div class="pre-loader">
										
		            					<img class="ajax-loader-img" style="display:none" src="https://cdn.uvdesk.com/uvdesk/images/aa1b406.gif" alt="">

									</div>

								<form action="" method="get"> 

					                <div class="pull-right form-search-container">
					                	<div class="form-search search-only">
						                    <i class="search-icon glyphicon glyphicon-search"></i>
						                    <input type="text" class="form-control search-query" name="search" id="search-ticket" placeholder="Search tickets ...">
						                </div>
						            </div>
					            </form>
					        </div>
						</div>
						<div class="admin-ticket-view">
							<div class="col-sm-12">
								 
								<ul class="ticket-tabs-list"> 
									<div>
								    	<li class="open active " data-id="1" style="border-top:3px solid #337ab7">
								    		<i class="fa fa-inbox"></i>
											<span class="name">Open</span>
											<span class="badge">
												
												<?php echo $tabs_arr[0];?>
												
											</span>
										</li>
								    	<li class="open  " data-id="2">
								    		<i class="fa fa-exclamation-triangle"></i>
											<span class="name">Pending</span>
											<span class="badge">
												
												<?php echo $tabs_arr[1];?>
												
											</span>
										</li>
								    	<li class="open   last" data-id="6">
								    		<i class="fa fa-lightbulb-o"></i>
											<span class="name">Answered</span>
											<span class="badge">
												
												<?php echo $tabs_arr[5];?>
												
											</span>
										</li>
								    	<li class="open  " data-id="3">
								    		<i class="fa fa-check-circle"></i>
											<span class="name">Resolved</span>
											<span class="badge">
												
												<?php echo $tabs_arr[2];?>
												
												
											</span>
										</li>
								    	<li class="open  " data-id="4">
								    		<i class="fa fa-minus-circle"></i>
											<span class="name">Closed</span>
											<span class="badge">
												
												<?php echo $tabs_arr[3];?>
												
											</span>
										</li>
								    	<li class="open  " data-id="5">
								    		<i class="fa fa-ban"></i>
											<span class="name">Spam</span>
											<span class="badge">
												
												<?php echo $tabs_arr[4];?>
												
											</span>
										</li>
								    	</div>
										 
								</ul>
								<div class="panel panel-default table-container" id="ticket-table">
									<table class="table">
										<colgroup>
											<col class="quick-link">
											<col class="id">
											<col class="customer-name">
											<col class="subject">
											<col class="details">
											<col class="agent-name">
										</colgroup>
					                	<tbody>
					                	<?php
					                		foreach ($data_api->tickets as $data_key => $data_value) :  
					                			if(!empty($data_value->group) && isset($data_value->group)){
					                				$group_name='<span class="badge badge-lg group">'.$data_value->group.'</span>';
					                			}else{
					                				$group_name='';
					                			}
					                			if(!empty($data_value->agent->name) && isset($data_value->agent->name)){

					                				$agent_data ='<i class="fa fa-user" aria-hidden=true></i><a class=semibold title='.$data_value->agent->name.'>'.$data_value->agent->name.'</a>';
					                			}
					                			
					                			else{
					                				$agent_data ='<button class="btn btn-md btn-info edit-ticket-agent"><i class="fa fa-plus-circle"></i>Agent</button><div class="btn-group bootstrap-select agents dropu dropup">
					                				<select class="selectpicker agents" data-live-search="true" title="Assign to" tabindex="-98"><option class="bs-title-option" value="">Assign to</option>
								    				</select></div>';
								    			}
								    			
								    			if(!empty($data_value->priority->name)){
								    				$priority=$data_value->priority->color;
								    			}
								    			else{
								    				$priority='#5cb85c';
								    			}
					                		?>
					                			
						                		<tr data-toggle=tooltip data-placement=left title class="Low 1" data-original-title=Low>
													    <td class=quick-link style="border-left: 3px solid <?php echo $priority;?>">
													        <div class=icheckbox_square-blue>
													            <input type=checkbox value="<?php echo $data_value->id; ?>" class=mass-action-checkbox style="position: absolute; top: -10%; left: -10%; display: block; width: 120%; height: 120%; margin: 0px; padding: 0px; border: 0px; opacity: 0; background: rgb(255, 255, 255);">
													            <ins class=iCheck-helper></ins>
													        </div>
													        <i class="fa fa-television source" aria-hidden=true></i><a href class=mark-star><i class="fa fa-star"></i></a><a class="bold quick-view" href data-id="<?php echo $data_value->id; ?>"><span class="badge badge-lg badge-primary"><i class="fa fa-bolt"></i></span></a>
													    </td>
													    <td class=id><a href="<?php echo site_url().'/uvdesk/admin/ticket/view/'.$data_value->incrementId; ?>">#<?php echo $data_value->incrementId; ?></a></td>
													    <td class=customer-name><a href="<?php echo site_url().'/uvdesk/admin/ticket/view/'.$data_value->incrementId; ?>" title="<?php echo $data_value->customer->name?>"><?php echo $data_value->customer->name; ?></a></td>
													    <td class=subject><a href="<?php echo site_url().'/uvdesk/admin/ticket/view/'.$data_value->incrementId; ?>"><?php echo $data_value->subject; ?></a><span class=fade-subject></span></td>
													    <td class=details><a href="<?php echo site_url().'/uvdesk/admin/ticket/view/'.$data_value->incrementId; ?>"><span class=date><?php echo $data_value->formatedCreatedAt;?></span><span class="badge badge-lg">1</span><?php echo $group_name; ?></a></td>
													    <td class=agent-name><?php echo $agent_data; ?></td>
													    <td class=responsive-data>
													        <ul class=list-block>
													            <li class=subject><a href="<?php echo site_url().'/uvdesk/admin/ticket/view/'.$data_value->incrementId; ?>" class=bold><?php echo $data_value->subject; ?></a> </li>
													            <li class=customer-info> <a class="bold ellipsis-name"><?php echo $data_value->customer->name;?></a> </li>
													            <li class="agent-info pull-left"> <span class="ticket-d pull-left">#<?php echo $data_value->incrementId; ?></span> <span class=agent> <i class="fa fa-user" aria-hidden=true></i> <span class=assign-text> Assigned To - &nbsp; </span> <a class=semibold> <?php echo $data_value->agent->name;?></a> </span>
													            </li>
													            <li class=info> <span class="badge badge-lg badge-default"><?php echo $data_value->group;?></span> <span class="badge badge-lg badge-default"><?php echo $data_value->formatedCreatedAt; ?></span> <span class="badge badge-lg badge-default"><?php echo $data_value->totalThreads;?> Replies</span> </li>
													            <li class=priority> <span class="label priority-label" style="background-color: #5cb85c; border: 1px solid #5cb85c"><?php echo $data_value->priority->name;?></span> </li>
													        </ul>
													    </td>
												</tr>
											
											<?php endforeach; ?>
					                		
					                		<tr style="text-align: center;" id="ticket-loader"><td colspan="6">No Record Found</td></tr>

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

								                'current'       => max( 1,$paged ),

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
				</div>
			</div>
				 
			 
		                                                                                                                                                </div>
		                    </div>
		                </div>
		            </div>
		            <div class="modal fade" id="confirm-modal" tabindex="-1" role="dialog" aria-labelledby="confirm-modal">
		                <div class="modal-dialog modal-sm" role="document">
		                    <div class="modal-content">
		                        <div class="modal-header">
		                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
		                            <h4 class="modal-title" id="confirm-modal">Confirm Delete ?</h4>
		                        </div>
		                        <div class="modal-body">
		                            Deletion is irreversible, please be careful.                        </div>
		                        <div class="modal-footer">
		                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		                            <button type="button" class="btn btn-danger delete-entity">Yes</button>
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