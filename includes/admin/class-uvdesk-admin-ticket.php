<?php

if( ! class_exists( 'WP_List_Table' ) ) {

	require_once( ABSPATH.'wp_admin/includes/class-wp-list-table.php' );

}

class Uvdesk_admin_ticket extends WP_List_Table {

	public $data_api_members;

	function __construct() {

		parent::__construct( array(

			'singular'=> 'Wp Ticket',
			'plural' => 'Wp Tickets',
			'ajax'   => true

		) );

		$this->data_api_members = UVDESK_API::get_customer_data_api('members.json',array('sort'=>'name','fullList'=>'true'));

	}

	function prepare_items() {

		$columns = $this->get_columns();

		$hidden = $this->get_hidden_columns();

		$this->process_bulk_action();

		$data = $this->get_table_data();

		$totalitems = $data->pagination->totalCount;

		$user = get_current_user_id();

		$this->_column_headers = array( $columns, $hidden, '' );

		$perpage = 15;

		$this->usort( $data->tickets, 'usort_reorder' );

		$totalpages = ceil( $totalitems / $perpage );

		$currentpage = $this->get_pagenum();

		if ( is_array( $data->tickets ) ) {

			$data = array_slice( $data->tickets, 0, $perpage );

		}

		$this->set_pagination_args( array(

			'total_items' => $totalitems,

			'total_pages' => $totalpages,

			'per_page'    => $perpage,

		) );

		$this->items = $data;

		$this->search_box( __( 'Search' ), 'search-box-id' );

	}

	function get_columns() {

		$column = array(
			'cb'            => '<input type="checkbox" />',
			'starred'       => 'Starred',
			'id'            => __( 'Ticket Id', 'wk_wp_uvdesk' ),
			'timestamp'     => __( 'Timestamp', 'wk_wp_uvdesk' ),
			'subject'       => __( 'Subject', 'wk_wp_uvdesk' ),
			'customer_name' => __( 'Customer Name', 'wk_wp_uvdesk' ),
			'agent_name'    => __( 'Agent Name', 'wk_wp_uvdesk' ),
		);

		return( $column );

	}

	public function get_hidden_columns() {

		return array();

	}

	function usort_reorder( $a, $b ) {

		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : 'id'; // If no sort, default to title.

		$order = ( ! empty( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : 'desc'; // If no order, default to asc.

		$result = strnatcmp( $a[ $orderby ], $b[ $orderby ] ); // Determine sort order.

		return ( 'asc' === $order ) ? $result : -$result; // Send final sort direction to usort.

	}

	function get_table_data() {

	 $arr_sum = array();

	 $arr_sum['sort'] = 't.id';

	 $arr_sum['direction'] = 'desc';

	 if ( ! empty( $_GET['paged'] ) ) {

			$arr_sum['page'] = $_GET['paged'];

		} else{

			$arr_sum['page'] = 1;

		}

		if( !empty( $_POST['s'] ) && $_POST['s'] != '' ) {

			$arr_sum['search'] = $_POST['s'];

		}

		if( !empty( $_POST['check-status'] ) && $_POST['check-status'] !='all' ) {

			$arr_sum['status'] = intval( $_POST['check-status'] );


		}

		if( !empty( $_POST['check-priority'] ) && $_POST['check-priority'] != '' ) {

			$arr_sum['priority'] = intval($_POST['check-priority']);

		}

		if( !empty( $_POST['fil-agent'] ) && $_POST['fil-agent']!='' ) {

			$arr_sum['agent'] = intval($_POST['fil-agent']);

		}

		if( isset( $_POST['apply-submit'] ) &&  $_POST['apply-submit'] == 'Apply' ) {

			if( isset($_POST['post']) ){

				$tkt_ids = $_POST['post'];

			}

			if( !empty( $_POST['change-status'] ) && $_POST['change-status'] !='' && ! empty($tkt_ids)) {

				$cgn_status = intval( $_POST['change-status'] );

				$json_data=array('ids'=>$tkt_ids,'statusId'=>$cgn_status);

				if( $json_data ) {

					$data_customerapi = UVDESK_API::update_ticket('tickets/status.json',$json_data);

				}

			}

			if( !empty( $_POST['change-priority'] ) && $_POST['change-priority'] != '' && ! empty($tkt_ids)) {

				$cgn_priority = intval($_POST['change-priority']);

				$json_data = array('ids'=>$tkt_ids,'priorityId'=>$cgn_priority);

				if( $json_data ) {

					$data_customerapi = UVDESK_API::update_ticket('tickets/priority.json',$json_data);

				}

			}

			if( ! empty( $_POST['change-agent'] ) && $_POST['change-agent']!='' && ! empty($tkt_ids)) {

				$cgn_agent = intval($_POST['change-agent']);

				$json_data=array('ids'=>$tkt_ids,'agentId'=>$cgn_agent);

				if( $json_data ) {

					$data_customerapi = UVDESK_API::update_ticket('tickets/agent.json',$json_data);

				}

			}

		}

		$uvdesk_access_token = get_option( 'uvdesk_access_token' );

		if( isset( $_GET['custmr-action'] ) && ( $_GET['custmr-action'] != '' ) && ( $_GET['custmr-action'] == 'customer-tkt' ) ) {

					$arr_sum['customer'] = $_GET['cid'];

					$data_assign_api = UVDESK_API::get_customer_data_api( 'tickets.json',$arr_sum );

					if( !empty( $data_assign_api ) ) {

						return( $data_assign_api );

					}

		}

		if( !empty( $uvdesk_access_token ) ) {

				$data_api = UVDESK_API::get_customer_data_api( 'tickets.json',$arr_sum );

			}

			return( $data_api );

	 }

	function column_default( $item, $column_name ) {

	 switch( $column_name ) {
		 case 'id':
		 case 'starrred':
		 case 'timestamp':
		 case 'subject':
		 case 'customer_name':
		 case 'agent_name':
			 return $item[ $column_name ];
		 default:
			 return print_r( $item, true ) ;

	 }

 }

 public function column_cb( $item ) {

	 return sprintf( '<input type="checkbox" class="wk-check-tkt" id="post_%s" name="post[]" value="%s" />',$item->incrementId, $item->id );

 }

 public function column_starred( $item ) {

	 if($item->isStarred){
		 $select = 'stared';
		 $str_no = 1;
	 }
	 else{
		 $select = '';
		 $str_no = 0;
	 }
	 return sprintf( '<div><input type="radio" style="opacity: 0;" ><span class="priority-check" style="background-color: '.$item->priority->color.'"></span><span class="wk-starred-ico '.$select.'" data-id="'.$item->id.'" data-star-val="'.$str_no.'"></span></div>' );

 }

 public function column_id( $item ) {

		 $actions = array(

				 'view'    => sprintf( '<a href="?page=uvdesk_ticket_system&action=view&post=%d">View</a>', $item->incrementId ),

				 'delete'  => sprintf( '<a href="?page=uvdesk_ticket_system&action=delete&post=%d">Delete</a>',$item->id ),

		 );
		 return sprintf( '#%1$s %2$s', $item->incrementId, $this->row_actions( $actions ) );

 }

 public function column_timestamp( $item ) {

	 return date( 'm/d/Y H:i:s', $item->timestamp );

 }

 public function column_subject( $item ) {

	 return $item->subject;

 }

 public function column_customer_name( $item ) {

	 return $item->customer->name;

 }

 public function column_agent_name( $item ) {

	 $assign_agent= '<select class="wk-sel-agent" data-id="'.$item->id.'"><option value="">Add agent</option>';

		foreach ( $this->data_api_members as $key => $value ){

		if(isset($item->agent->name)){

			if( $item->agent->name == $value->name ){

				 $select = 'selected';

			 }else{

					$select = '';

			 }
		 }
		 else{

			 $select = '';

		 }

		 $assign_agent.="<option value='".$value->id."' ".$select.">".$value->name."</option>";

	 }

	 $assign_agent.= '</select>';

	 return $assign_agent;

 }

	public function get_bulk_actions() {
			$actions = array(
					'delete'    => 'Delete'

			);
			return $actions;
	}

	function process_bulk_action(){

		if ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] ) {

			if ( isset( $_GET['post'] ) && ( '' !== $_GET['post'] ) ) {

				$ids = $_GET['post'];

				$data_deleted_ticket = UVDESK_API::delete_tag_ticket( 'ticket/'.$ids.'.json' );

			}
		}

		if ( isset( $_POST['action'] ) && $_POST['action'] === 'delete' ) {

			if ( isset( $_POST['post'] ) && ( $_POST['post'] != '' ) ) {

				$ids = $_POST['post'];

				if ( is_array( $ids ) ) {

					$json_id = array( 'ids' => $ids );

					$data_deleted_ticket = UVDESK_API::delete_tag_ticket( 'tickets.json', $json_id );

				} else {

					$data_deleted_ticket = UVDESK_API::delete_tag_ticket( 'ticket/'.$ids.'.json' );

				}

			}

		}

	}
}

$ticket_obj = new Uvdesk_admin_ticket();

?>

<header  style="display:inline-block">

 <h1 style="display:inline-block"> Tickets </h1>
 <?php

 if( isset( $_GET['custmr-action'] ) && ( $_GET['custmr-action'] == 'customer-tkt' ) ){

	 ?>

	 <a href = "<?php echo( admin_url( 'admin.php?page=uvdesk_ticket_system' ) ); ?>" class="button button-primary back-to-list">All tickets </a>

	 <?php
 }
 ?>
</header>
<div>
<div style="display:inline-block;width:12%;vertical-align:top;">
	<?php

	if ( isset( $_POST['check-status'] ) && $_POST['check-status']!= '' ) {

		$stat = intval( $_POST['check-status'] );

	} else {

		$stat = 'all';

	}

	if( isset( $_POST['check-priority'] ) && $_POST['check-priority']!= '' ) {

		$priority = intval( $_POST['check-priority'] );

	}else{

		$priority = '';

	}

	if( isset( $_POST['fil-agent'] ) && $_POST['fil-agent']!= '' ) {

		$agent = intval( $_POST['fil-agent'] );

	}else{

		$agent = '';

	}

	?>


		<form action="" method="POST">
			<div>
			<h2 style="margin-top:50px">Filters</h2>
			<div  class="filter-class" style="display:block;margin:5px 0">

			<select name="check-status" class="ewc-filter-cat">

					<option value=""  ><?php echo __('Filter by Status', 'wk-wp-uvdesk'); ?></option>

					<option value="1" <?php if( $stat == '1'){ echo "selected"; }?> ><?php echo __('Open', 'wk-wp-uvdesk'); ?></option>

					<option value="2" <?php if( $stat == '2'){ echo "selected"; }?> ><?php echo __('Pending', 'wk-wp-uvdesk'); ?></option>

					<option value="6" <?php if( $stat == '6'){ echo "selected"; }?> ><?php echo __('Answered', 'wk-wp-uvdesk'); ?></option>

					<option value="3" <?php if( $stat == '3'){ echo "selected"; }?> ><?php echo __('Resolved', 'wk-wp-uvdesk'); ?></option>

					<option value="4" <?php if( $stat == '4'){ echo "selected"; }?> ><?php echo __('Closed', 'wk-wp-uvdesk'); ?></option>

					<option value="5" <?php if( $stat == '5'){ echo "selected"; }?> ><?php echo __('Spam', 'wk-wp-uvdesk'); ?></option>

			</select>

			<select name="check-priority" class="ewc-filter-cat" >

					<option value="" ><?php echo __('Filter by Priority', 'wk-wp-uvdesk'); ?></option>

					<option value="1" <?php if( $priority == '1'){ echo "selected"; }?> ><?php echo __('Low', 'wk-wp-uvdesk'); ?></option>

					<option value="2" <?php if( $priority == '2'){ echo "selected"; }?> ><?php echo __('Medium', 'wk-wp-uvdesk'); ?></option>

					<option value="3" <?php if( $priority == '3'){ echo "selected"; }?> ><?php echo __('High', 'wk-wp-uvdesk'); ?></option>

					<option value="4" <?php if( $priority == '4'){ echo "selected"; }?> ><?php echo __('Urgent', 'wk-wp-uvdesk'); ?></option>

			</select>

			<?php

				$assign_agent= '<select class="filter-agent" name="fil-agent" ><option value="">Filter Agent</option>';

				 foreach ( $ticket_obj->data_api_members as $key => $value ){

						 if( $agent == $value->id ){

								$select = 'selected';

							}else{

								 $select = '';

							}

					$assign_agent.="<option value='".$value->id."' $select>".$value->name."</option>";

				}

				$assign_agent.= '</select>';

				echo $assign_agent;

				?>

				<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>">

				<input type="submit" class="button button-primary" name="filter-submit" value="Filter">

		</div>

		<div class="action-class"  style="display:block;margin:5px 0;">

			<select name="change-status" class="ewc-filter-cat">

					<option value=""  ><?php echo __('Change Status', 'wk-wp-uvdesk'); ?></option>

					<option value="1"  ><?php echo __('Open', 'wk-wp-uvdesk'); ?></option>

					<option value="2"  ><?php echo __('Pending', 'wk-wp-uvdesk'); ?></option>

					<option value="6" ><?php echo __('Answered', 'wk-wp-uvdesk'); ?></option>

					<option value="3"  ><?php echo __('Resolved', 'wk-wp-uvdesk'); ?></option>

					<option value="4" ><?php echo __('Closed', 'wk-wp-uvdesk'); ?></option>

					<option value="5"  ><?php echo __('Spam', 'wk-wp-uvdesk'); ?></option>

			</select>

			<select name="change-priority" class="ewc-filter-cat" >

					<option value="" ><?php echo __('Change Priority', 'wk-wp-uvdesk'); ?></option>

					<option value="1"  ><?php echo __('Low', 'wk-wp-uvdesk'); ?></option>

					<option value="2"  ><?php echo __('Medium', 'wk-wp-uvdesk'); ?></option>

					<option value="3"  ><?php echo __('High', 'wk-wp-uvdesk'); ?></option>

					<option value="4"  ><?php echo __('Urgent', 'wk-wp-uvdesk'); ?></option>

			</select>


		<?php

			$assign_agent= '<select class="change-agent" name="change-agent" ><option value="">Assign Agent</option>';

			 foreach ( $ticket_obj->data_api_members as $key => $value ){

			 if(isset($item->agent->name)){

				 if( $item->agent->name == $value->name ){

						$select = 'selected';

					}else{

						 $select = '';

					}
				}
				else{

					$select = '';

				}

				$assign_agent.="<option value='".$value->id."' ".$select.">".$value->name."</option>";

			}

			$assign_agent.= '</select>';

			echo $assign_agent;
			?>
			<input type="submit" class="button button-primary" name="apply-submit" value="Apply">

		</div>
  </div>

</div>
<div class="wrap" style="display:inline-block;width:85%;">

<div class="pre-loader">

	<img class="ajax-loader-img" src="<?php echo(admin_url('images/spinner-2x.gif'));?>" alt="">

</div>

	<form id="testimonial" method="get">

				<?php

				$page  = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRIPPED );

				$paged = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_NUMBER_INT );

				printf( '<input type="hidden" name="page" value="%s" />', $page );

				printf( '<input type="hidden" name="paged" value="%d" />', $paged );

					$ticket_obj->prepare_items();

					$ticket_obj->display();
				?>

	</form>

</div>
</div>
