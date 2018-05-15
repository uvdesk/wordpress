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

		if ( $paged == 0 ) {
			$paged = 1;
		}

		$arr_sum = array(
			'sort'       => 't.id',
			'direction'  => 'asc',
			'actAsEmail' => $c_email,
			'actAsType'  => 'customer',
			'page'       => $paged,
		);

		if ( isset( $_GET['search'] ) ) {

			$arr_sum['search'] = $_GET['search'];

		}

		$data_api = UVDESK_API::get_customer_data_api( 'tickets.json', $arr_sum );

		if ( isset( $data_api->error ) || empty( $data_api ) ) {

				echo '<h1>Invalid Details.</h1><h3>Please contact Administrator.</h3>';

		} else {

			?>
				<div class="block-container">

					<div class="customer-ticket-wrapper">

						<a href=" <?php echo esc_url( site_url() . '/uvdesk/customer/create-ticket' ); ?>" class="create-ticket"><i class="fa fa-plus"></i> Create Ticket</a>
						<input type="hidden" value="<?php echo get_pagenum_link( 99999 );?>" id="page_link">
							<div class="filter-left">
								<div>
									<label id="filter-sel">Sort By - <span class="selected-option">Ticket Id</span></label><span class="down-up-arrow"></span>
									<div class="filter-view">
													<li data-value="t.id" data-order="asc" class="filter-by-id ">Ticket Id</li>
													<li data-value="name" data-order="asc" class="filter-by-agent">Agent Name</li>
									</div>
								</div>
							</div>

							<div class="pre-loader">
								<img class="ajax-loader-img"  src="<?php echo( esc_url( admin_url( 'images/spinner-2x.gif' ) ) ); ?>" alt="">
							</div>

					</div>

					<div class="tab-listing" id="tab-id-filter">
						<ul>
							<li data-value="1" class="tab-active" >Open</li>
							<li name="hi" data-value="2">Pending</li>
							<li data-value="6">Answered</li>
							<li data-value="3">Resolved</li>
						</ul>
						<form id='search-submit'>
							<?php

							if ( isset( $_GET['search'] ) ) {

								$ser_txt = $_GET['search'];

							} else {

								$ser_txt = 'Search';

							}
							?>
							<input class="search-bar" type='text' id="search-key" name='search' placeholder="<?php echo esc_html( $ser_txt ); ?>" >
						</form>
					</div>

						<div class="customer-ticket-section">
							<div class="tabs-table">
							<div class="table-container" id="ticket-table">
											<table class="table">
															<tr>
																<td class="check-col"></td>
																<td class="id-col">Id</td>
																<td class="reply-col">Reply</td>
																<td class="date-col">Date</td>
																<td class="subject-col">Subject</td>
																<td class="agent-name-col">Agent Name</td>
															</tr>
															<?php
															$count = 1;
															if ( ! empty( $data_api->tickets ) && isset( $data_api->tickets ) ) {

																foreach ( $data_api->tickets as $ticket_key => $ticket_value ) {

																		?>

																		<tr data-toggle="tooltip" data-placement="left" title="" class="Open 1 unread" data-original-title="Open" >
																				<td class="check-col">
																					<span class="priority-check" style="background-color:<?php echo esc_html( $ticket_value->priority->color ); ?>"></span>
																				</td>
																				<td class="id-col" >
																						<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>">
																							<?php echo '#' . esc_html( $ticket_value->id ); ?>
																						</a>
																				</td>
																				<td class="reply-col">
																						<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>">
																								<span class="badge badge-lg"><?php echo esc_html( $ticket_value->totalThreads ); ?></span>
																						</a>
																				</td>
																				<td class="date-col">
																					<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>">
																						<span class="date"><?php echo esc_html( $ticket_value->formatedCreatedAt ); ?></span>
																					</a>
																				</td>
																				<td class="subject-col">
																						<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>" class="subject">
																							<?php echo $ticket_value->subject; ?>
																						</a>
																						<span class="fade-subject"></span>
																				</td>

																				<td class="agent-name-col">

																						<a href="<?php echo site_url().'/uvdesk/customer/ticket/view/'.$ticket_value->incrementId;?>">

																						<?php
																						if(!empty($ticket_value->agent->name)){
																							echo $ticket_value->agent->name;
																						}
																						else {
																							echo 'Not Assigned';
																						} ?>

																						</a>
																				</td>

																		</tr>

															<?php $count++;
														}
														?>

												</table>
								</div>
						</div>
						<div class="col-sm-12">
							<div class="navigation">
							<?php

							$tot_post = $data_api->pagination->totalCount;

							$per_page = $data_api->pagination->numItemsPerPage;

							$last_page = $data_api->pagination->pageCount;

							function uv_pagination( $tot_post, $per_page, $last_page, $paged ) {

								$prev_arrow = 'Next&nbsp;&raquo;';

								$next_arrow = '&laquo;&nbsp;Previous';

								global $wp_query;

								if ( $tot_post > 0 ) {

										$total = $tot_post / $per_page;
								} else {

										$total = $last_page;

								}
								$big = 9999999999999; // need an unlikely integer.

								if ( $total > 1 ) {

									if ( get_option( 'permalink_structure' ) ) {

										$format = 'page/%#%/';

									} else {

										$format = '&paged=%#%';

									}

									echo paginate_links ( array(

										'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),

										'format'    => $format,

										'current'   => max( 1, $paged ),

										'total'     => ceil( $total ),

										'mid_size'  => 3,

										'type'      => 'list',

										'prev_text' => $next_arrow,

										'next_text' => $prev_arrow,

									) );

								}
								}

								echo "<nav class='uv-pagination'>";

								uv_pagination( $tot_post, $per_page, $last_page, $paged );

								echo '</div>';
							?>

							</div>

						</div>
					</div>


						<?php

				} else {

						?>
						<div class='table-container' id='ticket-table'>
							<div class='tabs-table'>
								<table class='table'>
									<tr >
										<td class='record-no'><span>No Record Found</span></td>
									</tr>
								</table>
							</div>
						</div>

						<?php

				}

			}
		}
				else{

						echo "<h1>Please Enter a valid Access Token<h1>";

				}

}
