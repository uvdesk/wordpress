<?php
/**
 * WK_UVDESK_Customer handler.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WK_UVDESK\Templates\Front;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WK_UVDESK\Helper;
use WK_UVDESK\Includes;


/** Check class exists or not */
if ( ! class_exists( 'WK_UVDESK_Customer' ) ) {
	/**
	 * WK_UVDESK_Customer class.
	 */
	class WK_UVDESK_Customer {
		/**
		 * Instance variable
		 *
		 * @var $instance
		 */
		protected static $instance = null;

		/**
		 * Constructor.
		 *
		 * @return void
		 */
		public function __construct() {
			add_shortcode( 'uvdesk', array( $this, 'wk_uvdesk_customer_dashboard' ) );
		}

		/**
		 * This is a singleton page, access the single instance just using this method.
		 *
		 * @return object
		 */
		public static function get_instance() {
			if ( ! static::$instance ) {
				static::$instance = new self();
			}
			return static::$instance;
		}

		/**
		 * Shortcode function.
		 *
		 * @return void
		 */
		public function wk_uvdesk_customer_dashboard() {
			$uvdesk_access_token = get_option( 'uvdesk_access_token', '' );

			if ( ! empty( $uvdesk_access_token ) ) {
				$current_user = wp_get_current_user();
				$c_email      = $current_user->user_email;
				$paged        = get_query_var( 'paged' );
				$paged        = max( 1, $paged );

				// Initialize filter array.
				$arr_sum = array(
					'sort'       => esc_attr( 't.id' ),
					'direction'  => esc_attr( 'asc' ),
					'actAsEmail' => $c_email,
					'actAsType'  => esc_attr( 'customer' ),
					'page'       => $paged,
				);

				// Add search filter if exists.
				if ( ! empty( filter_input( INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ) {
					$arr_sum['search'] = filter_input( INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				}

				// Add user ID filter if exists.
				if ( ! empty( filter_input( INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT ) ) ) {
					$arr_sum['user_id'] = filter_input( INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT );
				}

				// Call the API with the filter parameters.
				$data_api = Helper\WK_UVDESK_Api_Handler::wk_uvdesk_get_customer_data_api( 'tickets.json', $arr_sum );

				if ( isset( $data_api->error ) || empty( $data_api ) ) {
					echo '<h4>' . esc_html( $data_api->error_description ) . '</h4><h3>' . esc_html__( 'Please contact Administrator.', 'wk-uvdesk' ) . '</h3>';
				} else {
					// Display the tickets.
					?>
			<div class="uv-uvdesk-block-container">
					<div class="customer-ticket-wrapper">
						<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/create-ticket' ); ?>" class="wk-uvdesk-create-ticket "><i class="fa fa-plus"></i> <?php esc_html_e( 'Create Ticket', 'wk-uvdesk' ); ?></a>
						<input type="hidden" value="<?php echo esc_attr( get_pagenum_link( 99999 ) ); ?>" id="page_link"/>
							<div class="uv-uvdesk-filter-left">
								<div>
									<label id="filter-sel"><?php esc_html_e( 'Sort By - ', 'wk-uvdesk' ); ?><span class="selected-option"><?php esc_html_e( 'Ticket Id', 'wk-uvdesk' ); ?></span></label><span class="uv-uvdesk-down-up-arrow"></span>
									<div class="uv-uvdesk-filter-view">
										<li data-value="t.id" data-order="asc" class="filter-by-id"><?php esc_html_e( 'Ticket Id', 'wk-uvdesk' ); ?></li>
										<li data-value="name" data-order="<?php echo esc_attr( 'asc' ); ?>" class="filter-by-agent"><?php esc_html_e( 'Agent Name', 'wk-uvdesk' ); ?></li>
									</div>
								</div>
							</div>
							<div class="uv-uvdesk-pre-loader">
								<img class="uv-uvdesk-ajax-loader-img" <?php echo wp_kses_post( Includes\WK_UVDESK::wk_uvdesk_convert_attributes_to_html( array() ) ); ?>  alt="<?php esc_attr_e( 'Loading...', 'wk-uvdesk' ); ?>" />
							</div>
					</div>
					<div class="tab-listing" id="uv-uvdesk-tab-id-filter">
						<ul>
							<li data-value="1" class="tab-active"><?php esc_html_e( 'Open', 'wk-uvdesk' ); ?> </li>
							<li name="hi" data-value="2"><?php esc_html_e( 'Pending', 'wk-uvdesk' ); ?></li>
							<li data-value="6"><?php esc_html_e( 'Answered', 'wk-uvdesk' ); ?></li>
							<li data-value="3"><?php esc_html_e( 'Resolved', 'wk-uvdesk' ); ?></li>
						</ul>
						<form id='search-submit'>
							<?php
							$ser_txt = ! empty( filter_input( INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) ? filter_input( INPUT_GET, 'search', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : esc_html__( 'Search', 'wk-uvdesk' );
							?>
							<input class="search-bar" type='text' id="search-key" name='search' value="<?php echo esc_attr( $ser_txt ); ?>" placeholder="<?php esc_attr_e( 'Search', 'wk-uvdesk' ); ?>" />
						</form>
					</div>
					<div class="uv-uvdesk-customer-ticket-section">
						<div class="tabs-table">
						<div class="table-container" id="ticket-table">
							<table class="table">
								<tr>
									<td class="check-col"></td>
									<td class="id-col"><?php esc_html_e( 'Id', 'wk-uvdesk' ); ?></td>
									<td class="reply-col"><?php esc_html_e( 'Reply', 'wk-uvdesk' ); ?></td>
									<td class="date-col"><?php esc_html_e( 'Date', 'wk-uvdesk' ); ?></td>
									<td class="subject-col"><?php esc_html_e( 'Subject', 'wk-uvdesk' ); ?></td>
									<td class="agent-name-col"><?php esc_html_e( 'Agent Name', 'wk-uvdesk' ); ?></td>
								</tr>
								<?php
								$count = 1;
								if ( ! empty( $data_api->tickets ) && isset( $data_api->tickets ) ) {
									foreach ( $data_api->tickets as $ticket_key => $ticket_value ) {
										?>
										<tr data-toggle="tooltip" data-placement="<?php echo esc_attr( 'left' ); ?>" title="" class="Open 1 unread" data-original-title="<?php echo esc_attr( 'Open' ); ?>" >
												<td class="check-col">
													<span class="uv-uvdesk-priority-check" style="<?php echo esc_attr( 'background-color:' . $ticket_value->priority->color ); ?>"></span>
												</td>
												<td class="id-col" >
													<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>">
														<?php echo esc_html( '#' . $ticket_value->id ); ?>
													</a>
												</td>
												<td class="reply-col">
														<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>">
															<span class="badge badge-lg">
															<?php
															echo esc_html( $ticket_value->totalThreads );
															?>
															</span>
														</a>
												</td>
												<td class="date-col">
													<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>">
														<span class="date"><?php echo esc_html( $ticket_value->formatedCreatedAt ); ?></span>
													</a>
												</td>
												<td class="subject-col">
													<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>" class="subject">
														<?php echo esc_attr( $ticket_value->subject ); ?>
													</a>
													<span class="fade-subject"></span>
												</td>
												<td class="agent-name-col">
													<a href="<?php echo esc_url( site_url() . '/uvdesk/customer/ticket/view/' . $ticket_value->incrementId ); ?>">
													<?php
													echo ! empty( $ticket_value->agent->name ) ? esc_attr( $ticket_value->agent->name ) : esc_html__( 'Not Assigned', 'wk-uvdesk' );
													?>

													</a>
												</td>
											</tr>
										<?php
										++$count;
									}
									?>
								</table>
							</div>
						</div>
						<div class="col-sm-12">
							<div class="navigation">
									<?php
									$tot_post  = $data_api->pagination->totalCount;
									$per_page  = $data_api->pagination->numItemsPerPage;
									$last_page = $data_api->pagination->pageCount;

									/**
									 * Pagination function.
									 *
									 * @param int $tot_post Total post.
									 * @param int $per_page Per page.
									 * @param int $last_page Last page.
									 * @param int $paged Paged.
									 *
									 * @return void
									 */
									function uv_pagination( $tot_post, $per_page, $last_page, $paged ) {
										$prev_arrow = esc_html__( 'Next', 'wk-uvdesk' );
										$next_arrow = esc_html__( 'Previous', 'wk-uvdesk' );
										$total      = $tot_post > 0 ? $tot_post / $per_page : $last_page;
										$big        = 9999999999999; // need an unlikely integer.

										if ( $total > 1 ) {
											if ( get_option( 'permalink_structure' ) ) {
												$format = 'page/%#%/';
											} else {
												$format = '&paged=%#%';
											}

											echo wp_kses_post(
												paginate_links(
													array(
														'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
														'format' => $format,
														'current' => max( 1, $paged ),
														'total' => ceil( $total ),
														'mid_size' => 3,
														'type' => 'list',
														'prev_text' => $next_arrow,
														'next_text' => $prev_arrow,
													)
												)
											);
										}
									}

									echo '<nav class="uv-pagination">';

									uv_pagination( $tot_post, $per_page, $last_page, $paged );

									echo '</nav>';
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
										<td class='record-no'><span><?php esc_html_e( 'No Record Found', 'wk-uvdesk' ); ?></span></td>
									</tr>
								</table>
							</div>
						</div>
									<?php
								}
				}
			} else {
				esc_html_e( 'Please Enter a valid Access Token', 'wk-uvdesk' );
			}
		}
	}
}
