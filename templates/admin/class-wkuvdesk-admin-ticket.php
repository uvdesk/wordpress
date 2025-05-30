<?php
/**
 * Show all ticket list in both sides.
 *
 * @package UVdesk Free Helpdesk
 */

namespace WKUVDESK\Templates\Admin;

defined( 'ABSPATH' ) || exit(); // Exit if accessed directly.

use WKUVDESK\Helper;

// Check if WP_List_Table class exists.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class WKUVDESK_Admin_Ticket
 * Handles the ticket listing functionality.
 */
class WKUVDESK_Admin_Ticket extends \WP_List_Table {
	/**
	 * Member list data.
	 *
	 * @var array
	 */
	public $data_api_members;

	/**
	 * Default number of items per page.
	 *
	 * @var int
	 */
	private static $option_name = 'uvdesk_tickets_per_page';

	/**
	 * Status options for tickets.
	 *
	 * @var array
	 */
	public $status_options;

	/**
	 * Priority options for tickets.
	 *
	 * @var array
	 */
	public $priority_options;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'wp_ticket',
				'plural'   => 'wp_tickets',
				'ajax'     => true,
				'screen'   => get_current_screen(),
			)
		);

		// Add screen options filter.
		add_action( 'admin_menu', array( $this, 'setup_screen_options' ) );
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );

		// Set parent defaults.
		$this->data_api_members = Helper\WKUVDESK_Api_Handler::wkuvdesk_get_customer_data_api(
			'members.json',
			array(
				'sort'     => 'name',
				'fullList' => 'true',
			)
		);
		$this->init_properties();
		$this->fetch_member_data();
	}

	/**
	 * Set screen options for tickets per page.
	 *
	 * @return void
	 */
	public function setup_screen_options() {
		$args = array(
			'label'   => esc_html__( 'Tickets per page', 'uvdesk' ),
			'default' => 15,
			'option'  => self::$option_name,
		);
		add_screen_option( 'per_page', $args );
	}

	/**
	 * Set screen option for tickets per page.
	 *
	 * @param mixed $status status.
	 * @param mixed $option option.
	 * @param mixed $value value.
	 *
	 * @return mixed
	 */
	public function save_screen_options( $status, $option, $value ) {
		return ( $option === self::$option_name ) ? (int) $value : $status;
	}

	/**
	 * Initialize class properties.
	 *
	 * @return void
	 */
	private function init_properties() {
		$this->status_options = array(
			1 => esc_html__( 'Open', 'uvdesk' ),
			2 => esc_html__( 'Pending', 'uvdesk' ),
			3 => esc_html__( 'Resolved', 'uvdesk' ),
			4 => esc_html__( 'Closed', 'uvdesk' ),
			5 => esc_html__( 'Spam', 'uvdesk' ),
			6 => esc_html__( 'Answered', 'uvdesk' ),
		);

		$this->priority_options = array(
			1 => esc_html__( 'Low', 'uvdesk' ),
			2 => esc_html__( 'Medium', 'uvdesk' ),
			3 => esc_html__( 'High', 'uvdesk' ),
			4 => esc_html__( 'Urgent', 'uvdesk' ),
		);

		$user     = get_current_user_id();
		$per_page = get_user_meta( $user, self::$option_name, true );
		if ( empty( $per_page ) || ! is_numeric( $per_page ) ) {
			$per_page = 15;
		}

		// Set screen options.
		add_screen_option(
			'per_page',
			array(

				'label'   => esc_html__( 'Tickets per page', 'uvdesk' ),
				'default' => 15,

				'option'  => self::$option_name,
			)
		);
	}

	/**
	 * Fetch member data from API.
	 *
	 * @return void
	 */
	private function fetch_member_data() {
		$this->data_api_members = Helper\WKUVDESK_Api_Handler::wkuvdesk_get_customer_data_api(
			'members.json',
			array(
				'sort'     => 'name',
				'fullList' => 'true',
			)
		);
	}

	/**
	 * Prepare items for the table.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$screen_id = 'wk_uvdesk_ticket_screen';
		$columns   = $this->get_columns();
		$hidden    = $this->get_hidden_columns( $screen_id );
		$per_page  = 15;

		$data        = $this->get_table_data();
		$total_items = ! empty( $data->pagination->totalCount ) ? $data->pagination->totalCount : 0;

		$this->_column_headers = array( $columns, $hidden, $this->get_sortable_columns() );
		$this->items           = ! empty( $data->tickets ) ? $data->tickets : array();

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);

		$this->display_search_box();
	}

	/**
	 * Display search box.
	 *
	 * @return void
	 */
	private function display_search_box() {
		$search_value = filter_input( INPUT_GET, 's', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$msg          = filter_input( INPUT_GET, 'msg', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( 'deleted' === $msg ) {
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Ticket(s) deleted successfully.', 'uvdesk' ) . '</p></div>';
		}

		$priority = filter_input( INPUT_POST, 'check-priority', FILTER_SANITIZE_NUMBER_INT );
		$priority = $priority ? $priority : '';
		$agent    = filter_input( INPUT_POST, 'fil-agent', FILTER_SANITIZE_NUMBER_INT );
		$agent    = $agent ? $agent : '';
		?>
		<div class=''>
			<form action="" method="post">
				<div class="wkuvdesk-filter-class">
					<h3><?php esc_html_e( 'Search Filters', 'uvdesk' ); ?></h3>
					<select name="check-status" class="wkuvdesk-ewc-filter-cat">
						<option value=""><?php esc_html_e( 'Filter by Status', 'uvdesk' ); ?></option>
						<?php
						$stat = filter_input( INPUT_POST, 'check-status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
						$stat = $stat ? $stat : 'all';

						$statuses = array(
							1 => esc_html__( 'Open', 'uvdesk' ),
							2 => esc_html__( 'Pending', 'uvdesk' ),
							3 => esc_html__( 'Resolved', 'uvdesk' ),
							4 => esc_html__( 'Closed', 'uvdesk' ),
							5 => esc_html__( 'Spam', 'uvdesk' ),
							6 => esc_html__( 'Answered', 'uvdesk' ),
						);
						foreach ( $statuses as $key => $label ) {
							$selected = ( $stat === $key ) ? 'selected' : '';
							echo '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $label ) . '</option>';
						}
						?>
					</select>
					<select name="check-priority" class="wkuvdesk-ewc-filter-cat">
						<option value=""><?php esc_html_e( 'Filter by Priority', 'uvdesk' ); ?></option>
						<?php
						$priorities = array(
							1 => esc_html__( 'Low', 'uvdesk' ),
							2 => esc_html__( 'Medium', 'uvdesk' ),
							3 => esc_html__( 'High', 'uvdesk' ),
							4 => esc_html__( 'Urgent', 'uvdesk' ),
						);
						foreach ( $priorities as $key => $label ) {
							$selected = ( $priority === $key ) ? 'selected' : '';
							echo '<option value="' . esc_attr( $key ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $label ) . '</option>';
						}
						?>
					</select>
					<?php
					$assign_agent  = "<select class='filter-agent' name='fil-agent'>";
					$assign_agent .= "<option value=''>" . esc_html__( 'Filter Agent', 'uvdesk' ) . '</option>';
					if ( isset( $this->data_api_members ) ) {
						foreach ( $this->data_api_members as $value ) {
							$selected      = ( isset( $agent ) && isset( $value->id ) && $agent === $value->id ) ? 'selected' : '';
							$assign_agent .= sprintf( '<option value="%s" %s>%s</option>', isset( $value->id ) ? esc_attr( $value->id ) : '', isset( $value->id ) ? $selected : '', isset( $value->name ) ? esc_html( $value->name ) : '' );
						}
					}
					$assign_agent .= '</select>';
					echo wp_kses(
						$assign_agent,
						array(
							'select' => array(
								'name'  => true,
								'class' => true,
							),
							'option' => array(
								'value'    => true,
								'selected' => true,
							),
						)
					);
					?>
					<?php wp_nonce_field( 'wk_uvdesk_ticket_filter_action', 'wk_uvdesk_ticket_filter_nonce' ); ?>
					<input type="submit" class="button button-primary" name="filter-submit" value="<?php esc_attr_e( 'Filter', 'uvdesk' ); ?>">
					<!-- Add the Clear Filters button -->
					<?php if ( filter_input( INPUT_GET, 'check-status', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) || filter_input( INPUT_GET, 'check-priority', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) || filter_input( INPUT_GET, 'fil-agent', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) : ?>
					<input type="button" class="button button-secondary" id="clear-filters" value="<?php esc_attr_e( 'Clear Filters', 'uvdesk' ); ?>">
					<?php endif; ?>
				</div>
				<div class="wkuvdsk-list space-between">
					<div class="wkuvdesk-filter-class action-class">
						<h2><?php esc_html_e( 'Bulk Filters', 'uvdesk' ); ?></h2>
						<select name="change-status" class="wkuvdesk-ewc-filter-cat">
							<option value=""><?php esc_html_e( 'Change Status', 'uvdesk' ); ?></option>
							<?php
							foreach ( $statuses as $key => $label ) {
								echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>';
							}
							?>
						</select>
						<select name="change-priority" class="wkuvdesk-ewc-filter-cat">
							<option value=""><?php esc_html_e( 'Change Priority', 'uvdesk' ); ?></option>
							<?php
							foreach ( $priorities as $key => $label ) {
								echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</option>';
							}
							?>
						</select>
						<?php
						$assign_agent  = "<select class='change-agent' name='change-agent'>";
						$assign_agent .= "<option value=''>" . esc_html__( 'Assign Agent', 'uvdesk' ) . '</option>';
						if ( isset( $this->data_api_members ) && is_array( $this->data_api_members ) ) {
							foreach ( $this->data_api_members as $value ) {
								$assign_agent .= '<option value="' . esc_attr( $value->id ) . '">' . esc_html( $value->name ) . '</option>';
							}
						}
						$assign_agent .= '</select>';
						echo wp_kses(
							$assign_agent,
							array(
								'select' => array(
									'name'  => true,
									'class' => true,
								),
								'option' => array( 'value' => true ),
							)
						);
						?>
						<input type="submit" class="button button-primary" name="apply-submit" value="<?php esc_attr_e( 'Apply', 'uvdesk' ); ?>">
					</div>
					<div>
						<p class="search-box">
							<label class="screen-reader-text" for="search-box-id"><?php esc_html_e( 'Search', 'uvdesk' ); ?>:</label>
							<input type="search"
								id="search-box-id"
								name="s"
								value="<?php echo esc_attr( $search_value ); ?>"
								placeholder="<?php esc_attr_e( 'Search by subject...', 'uvdesk' ); ?>"
							/>
							<?php submit_button( esc_html__( 'Search', 'uvdesk' ), '', '', false, array( 'id' => 'search-submit' ) ); ?>
						</p>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Get table data from API.
	 *
	 * @return object
	 */
	private function get_table_data() {
		$params              = $this->get_table_params();
		$uvdesk_access_token = get_option( 'uvdesk_access_token', '' );

		if ( ! $uvdesk_access_token ) {
			return (object) array();
		}

		return Helper\WKUVDESK_Api_Handler::wkuvdesk_get_customer_data_api( 'tickets.json', $params );
	}

	/**
	 * Get parameters for table data.
	 *
	 * @return array
	 */
	private function get_table_params() {
		$params = array(
			'sort'      => 't.id',
			'direction' => 'desc',
			'page'      => $this->get_pagenum(),
			'paged'     => filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ) ? filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT ) : 1,
		);

		// Search parameter.
		$search = filter_input( INPUT_GET, 's', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( $search ) {
			$params['search'] = sanitize_text_field( $search );
		}

		// Status filter.
		$status = filter_input( INPUT_POST, 'check-status', FILTER_SANITIZE_NUMBER_INT );
		if ( $status && 'all' !== $status ) {
			$params['status'] = $status;
		}

		// Priority filter.
		$priority = filter_input( INPUT_POST, 'check-priority', FILTER_SANITIZE_NUMBER_INT );
		if ( $priority ) {
			$params['priority'] = $priority;
		}

		// Agent filter.
		$agent = filter_input( INPUT_POST, 'fil-agent', FILTER_SANITIZE_NUMBER_INT );
		if ( $agent ) {
			$params['agent'] = $agent;
		}

		// Bulk actions processing.
		if ( 'Apply' === filter_input( INPUT_POST, 'apply-submit', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
			$ticket_ids = filter_input( INPUT_POST, 'post', FILTER_SANITIZE_NUMBER_INT, FILTER_REQUIRE_ARRAY );

			if ( $ticket_ids ) {
				// Status change.
				$change_status = filter_input( INPUT_POST, 'change-status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				if ( $change_status ) {
					$json_data = array(
						'ids'      => $ticket_ids,
						'statusId' => $change_status,
					);
					$data      = Helper\WKUVDESK_Api_Handler::wkuvdesk_update_ticket( 'tickets/status.json', $json_data );
					if ( ! empty( $data->success ) ) {
						add_action(
							'admin_notices',
							function () {
								echo '<div class="notice notice-success is-dismissible"><p>' .
								esc_html__( ' Tickets status changed successfully..', 'uvdesk' ) .
								'</p></div>';
							}
						);
					} else {
						add_action(
							'admin_notices',
							function () {
								echo '<div class="notice notice-error is-dismissible"><p>' .
									esc_html__( 'There was an error tickets status.', 'uvdesk' ) .
									'</p></div>';
							}
						);
					}
				}

				// Priority change.
				$change_priority = filter_input( INPUT_POST, 'change-priority', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				if ( $change_priority ) {
					$json_data = array(
						'ids'        => $ticket_ids,
						'priorityId' => $change_priority,
					);
					$data      = Helper\WKUVDESK_Api_Handler::wkuvdesk_update_ticket( 'tickets/priority.json', $json_data );
					if ( ! empty( $data->success ) ) {
						add_action(
							'admin_notices',
							function () {
								echo '<div class="notice notice-success is-dismissible"><p>' .
								esc_html__( ' Tickets priority changed successfully..', 'uvdesk' ) .
								'</p></div>';
							}
						);
					} else {
						add_action(
							'admin_notices',
							function () {
								echo '<div class="notice notice-error is-dismissible"><p>' .
									esc_html__( 'There was an error tickets priority.', 'uvdesk' ) .
									'</p></div>';
							}
						);
					}
				}

				// Agent change.
				$change_agent = filter_input( INPUT_POST, 'change-agent', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
				if ( $change_agent ) {
					$json_data = array(
						'ids'     => $ticket_ids,
						'agentId' => $change_agent,
					);
					$data      = Helper\WKUVDESK_Api_Handler::wkuvdesk_update_ticket( 'tickets/agent.json', $json_data );

					if ( ! empty( $data->success ) ) {
						add_action(
							'admin_notices',
							function () {
								echo '<div class="notice notice-success is-dismissible"><p>' .
								esc_html__( ' Tickets agent changed successfully..', 'uvdesk' ) .
								'</p></div>';
							}
						);
					} else {
						add_action(
							'admin_notices',
							function () {
								echo '<div class="notice notice-error is-dismissible"><p>' .
									esc_html__( 'There was an error tickets agent.', 'uvdesk' ) .
									'</p></div>';
							}
						);
					}
				}
			}
		}

		// Customer filter.
		$customer_id = filter_input( INPUT_GET, 'cid', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( $customer_id && 'customer-tkt' === filter_input( INPUT_GET, 'custmr-action', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) ) {
			$params['customer'] = $customer_id;
		}

		// Status filter.
		$status = filter_input( INPUT_GET, 'check-status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( $status && 'all' !== $status ) {
			$params['status'] = $status;
		}

		// Priority filter.
		$priority = filter_input( INPUT_GET, 'check-priority', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( $priority ) {
			$params['priority'] = $priority;
		}

		// Agent filter.
		$agent = filter_input( INPUT_GET, 'fil-agent', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( $agent ) {
			$params['agent'] = $agent;
		}

		$this->add_filter_params( $params );

		return $params;
	}

	/**
	 * Add filter parameters.
	 *
	 * @param array $params Parameters array.
	 */
	private function add_filter_params( &$params ) {
		$status = filter_input( INPUT_POST, 'check-status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( $status && 'all' !== $status ) {
			$params['status'] = absint( $status );
		}

		$priority = filter_input( INPUT_POST, 'check-priority', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( $priority ) {
			$params['priority'] = absint( $priority );
		}

		$agent = filter_input( INPUT_POST, 'fil-agent', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( $agent ) {
			$params['agent'] = absint( $agent );
		}
	}

	/**
	 * Get columns for the table.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'            => '<input type="checkbox" />',
			'starred'       => esc_html__( 'Starred', 'uvdesk' ),
			'id'            => esc_html__( 'Ticket Id', 'uvdesk' ),
			'timestamp'     => esc_html__( 'Timestamp', 'uvdesk' ),
			'status'        => esc_html__( 'Status', 'uvdesk' ),
			'subject'       => esc_html__( 'Subject', 'uvdesk' ),
			'customer_name' => esc_html__( 'Customer Name', 'uvdesk' ),
			'agent_name'    => esc_html__( 'Agent Name', 'uvdesk' ),
		);
	}

	/**
	 * Show ticket status.
	 *
	 * @param object $item list data.
	 *
	 * @return string
	 */
	public function column_status( $item ) {
		$status = isset( $item->status->name ) ? $item->status->name : '';
		$class  = strtolower( str_replace( ' ', '-', $status ) );
		return sprintf( '<span class="wkuvdesk-status %s">%s</span>', esc_attr( $class ), esc_html( $status ) );
	}

	/**
	 * Show ticket generate time.
	 *
	 * @param object $item list data.
	 *
	 * @return mixed
	 */
	public function column_timestamp( $item ) {
		return isset( $item->timestamp ) ? gmdate( 'm/d/Y H:i:s', $item->timestamp ) : '';
	}

	/**
	 * Show ticket subject.
	 *
	 * @param object $item list data.
	 *
	 * @return object
	 */
	public function column_subject( $item ) {
		return isset( $item->subject ) ? $item->subject : '';
	}

	/**
	 * Show customer name.
	 *
	 * @param object $item list data.
	 *
	 * @return object
	 */
	public function column_customer_name( $item ) {
		return isset( $item->customer->name ) ? $item->customer->name : '';
	}

	/**
	 * Get agent name.
	 *
	 * @param object $item customer ticket.
	 *
	 * @return mixed
	 */
	public function column_agent_name( $item ) {
		$assign_agent  = '<select class="wkuvdesk-agent" data-id="' . esc_attr( isset( $item->id ) ? $item->id : '' ) . '">';
		$assign_agent .= '<option value="">' . esc_html__( 'Add agent', 'uvdesk' ) . '</option>';

		foreach ( $this->data_api_members as $value ) {
			// Check if agent name exists and compare case-insensitively.
			$select = (
			! empty( $item->agent->name ) &&
			strtolower( (string) $item->agent->name ) === strtolower( (string) $value->name )
			) ? 'selected' : '';

			$assign_agent .= '<option value="' . ( isset( $value->id ) ? $value->id : '' ) . '" ' . ( $select ) . '>' .
					( isset( $value->name ) ? $value->name : '' ) . '</option>';
		}

		$assign_agent .= '</select>';

		return $assign_agent;
	}

	/**
	 * Add view and delete label on ticket id column.
	 *
	 * @param object $item list data.
	 *
	 * @return mixed
	 */
	public function column_id( $item ) {
		$actions = array(
			'view'   => sprintf(
				'<a href="%s">' . esc_html__( 'View', 'uvdesk' ) . '</a>',
				esc_url(
					add_query_arg(
						array(
							'page'   => 'wkuvdesk_ticket_system',
							'action' => 'view',
							'post'   => isset( $item->incrementId ) ? $item->incrementId : '',
						),
						admin_url( 'admin.php' )
					)
				)
			),
			'delete' => sprintf(
				'<a href="%s" class="wkuvdesk-delete-tkt-reply" data-id="' . ( isset( $item->id ) ? $item->id : '' ) . '" data-thread-id="' . ( isset( $item->thread_id ) ? $item->thread_id : '' ) . '">' . esc_html__( 'Delete', 'uvdesk' ) . '</a>',
				esc_url(
					add_query_arg(
						array(
							'page'   => 'wkuvdesk_ticket_system',
							'action' => 'delete',
							'post'   => isset( $item->id ) ? $item->id : '',
						),
						admin_url( 'admin.php' )
					)
				)
			),
		);

		return sprintf( '#%1$s %2$s', isset( $item->incrementId ) ? absint( $item->incrementId ) : '', $this->row_actions( $actions ) );
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'id'        => array( 'id', true ),
			'timestamp' => array( 'timestamp', false ),
			'subject'   => array( 'subject', false ),
		);
	}

	/**
	 * Get hidden columns for the screen.
	 *
	 * @param string $screen_id Screen ID.
	 *
	 * @return array
	 */
	public function get_hidden_columns( $screen_id ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return array();
		}

		$hidden = get_user_meta( $user_id, "manage{$screen_id}columnshidden", true );

		return is_array( $hidden ) ? $hidden : array();
	}

	/**
	 * Checkbox column.
	 *
	 * @param object $item Current item.
	 *
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" class="wkuvdesk-check-tkt" id="post_%1$s" name="post[]" value="%2$s" />',
			esc_attr( $item->incrementId ?? '' ),
			esc_attr( $item->id ?? '' )
		);
	}

	/**
	 * Starred column.
	 *
	 * @param object $item Current item.
	 *
	 * @return string
	 */
	public function column_starred( $item ) {
		$is_starred = ! empty( $item->isStarred );
		$select     = $is_starred ? 'stared' : '';
		$star_value = $is_starred ? 1 : 0;

		return sprintf(
			'<div>
				<input type="radio" class="wkuvdesk-opacity">
				<span class="wkuvdesk-priority-check" style="background-color: %1$s"></span>
				<span class="wkuvdesk-starred-ico %2$s" data-id="%3$s" data-star-val="%4$s"></span>
			</div>',
			esc_attr( $item->priority->color ?? '' ),
			esc_attr( $select ),
			esc_attr( $item->id ?? '' ),
			esc_attr( $star_value )
		);
	}

	/**
	 * Render agent selection dropdown.
	 *
	 * @param array  $agents Array of agents.
	 * @param string $selected_agent Selected agent ID.
	 * @param string $name Dropdown name.
	 * @param string $css_class CSS class.
	 * @param string $placeholder Placeholder text.
	 *
	 * @return string
	 */
	public function render_agent_dropdown( $agents, $selected_agent = '', $name = '', $css_class = '', $placeholder = '' ) {
		$html = sprintf(
			'<select name="%s" class="%s">
				<option value="">%s</option>',
			esc_attr( $name ),
			esc_attr( $css_class ),
			esc_html( $placeholder )
		);

		foreach ( $agents as $agent ) {
			$selected = selected( $selected_agent, $agent->id, false );
			$html    .= sprintf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $agent->id ),
				$selected,
				esc_html( $agent->name )
			);
		}

		$html .= '</select>';
		return $html;
	}
}

// Initialize the ticket object.
$ticket_obj = new WKUVDESK_Admin_Ticket();
?>
	<header class="wkuvdesk-header">
		<h1><?php esc_html_e( 'Uvdesk Tickets List', 'uvdesk' ); ?></h1>
		<?php
		$data_api = Helper\WKUVDESK_Api_Handler::wkuvdesk_get_customer_data_api( 'tickets.json', array() );
		if ( ! empty( $data_api->error ) ) {
			$notice = $data_api->error_description ?? $data_api->error;
			echo '<br><h3>' . esc_html( $notice ) . '</h3>';
		}

		$customer_action = filter_input( INPUT_GET, 'custmr-action', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( 'customer-tkt' === $customer_action ) {
			echo '<a href="' . esc_url( admin_url( 'admin.php?page=wkuvdesk_ticket_system' ) ) . '" class="button button-primary wkuvdesk-back-to-list">' . esc_html__( 'All tickets', 'uvdesk' ) . '</a>';
		}
		?>
	</header>
	<div class="wkuvdesk-container">
		<form action="" method="post">
			<div class="wrap">
				<div class="wkuvdesk-pre-loader">
					<img alt="<?php esc_attr_e( 'Loading ...', 'uvdesk' ); ?>" />
				</div>
				<form id="tickets" method="get">
					<?php
					$current_page  = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
					$current_paged = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );
					printf( '<input type="hidden" name="page" value="%s" />', esc_attr( $current_page ) );
					printf( '<input type="hidden" name="paged" value="%d" />', absint( $current_paged ) );
					$ticket_obj->prepare_items();
					$ticket_obj->display();
					?>
				</form>
			</div>
		</form>
	</div>
