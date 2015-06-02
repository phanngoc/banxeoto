<?php
/*
Plugin Name: WooCommerce Dropshippers
Plugin URI: http://articnet.jp/
Description: Integrates dropshippers in WooCommerce
Version: 1.7
Author: ArticNet LLC.
Author URI: http://articnet.jp/
*/

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	if(!class_exists('WooCommerce_Dropshippers'))
	{
		class WooCommerce_Dropshippers
		{
			/**
			 * Construct the plugin object
			 */
			public function __construct()
			{
	        	// Initialize Settings
	            require_once(sprintf("%s/settings.php", dirname(__FILE__)));
	            $WooCommerce_Dropshippers_Settings = new WooCommerce_Dropshippers_Settings();
	        	
			} // END public function __construct
		    
			/**
			 * Activate the plugin
			 */
			public static function activate()
			{
				$result = add_role('dropshipper', 'Dropshipper', array(
					'show_dropshipper_widget' => true, //può vedere il widget in dashboard
				    'read' => true, // True allows that capability
				    'edit_posts' => true,
				));
				if (null !== $result) {
				    //echo 'Yay! New role created!';
				} else {
				    //echo 'Oh... the dropshipper role already exists.';
				}
				// SET DEFAULTS
				$options = get_option('woocommerce_dropshippers_options'); //first time return false
				if(!$options){
					$options = array(
						'text_string' => 'No',
						'billing_address' => "Admin's Billing Address\nSomewhere, 42\nPlanet Earth\n00000",
						//after initial release
						'can_see_email' => 'Yes',
						'can_see_phone' => 'No',
						//from 1.6
						'company_logo' => '',
						'slip_footer' => '',
						//from 1.7
						'admin_email' => ''
					);
				}
				else{
					// 1.1
					if(!$options['can_see_email'])
						$options['can_see_email'] = 'Yes';
					if(!$options['can_see_phone'])
						$options['can_see_phone'] = 'No';
					// 1.6
					if(!$options['company_logo'])
						$options['company_logo'] = '';
					if(!$options['slip_footer'])
						$options['slip_footer'] = '';
					// 1.7
					if(!$options['admin_email'])
						$options['admin_email'] = '';
				}
				update_option('woocommerce_dropshippers_options', $options);
			} // END public static function activate
		
			/**
			 * Deactivate the plugin
			 */		
			public static function deactivate()
			{
				//remove_role('dropshipper');
			} // END public static function deactivate
		} // END class WooCommerce_Dropshippers
	} // END if(!class_exists('WooCommerce_Dropshippers'))

	if(class_exists('WooCommerce_Dropshippers'))
	{

		/** SEND EMAIL TO DROPSHIPPERS **/
		require_once(sprintf("%s/dropshipper-new-order-email.php", dirname(__FILE__)));

		/** DROPSHIPPER ORDER LIST **/
		add_action( 'admin_menu', 'dropshipper_order_list' );

		function dropshipper_order_list() {
			add_menu_page( __('Dropshipper Orders','woocommerce-dropshippers'), __('Order list','woocommerce-dropshippers'), 'show_dropshipper_widget', 'dropshipper_order_list_page', 'dropshipper_order_list_function' );
		}

		function dropshipper_order_list_function() {
			if ( !current_user_can( 'show_dropshipper_widget' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			require_once(sprintf("%s/orders.php", dirname(__FILE__)));
		}

		// Installation and uninstallation hooks
		register_activation_hook(__FILE__, array('WooCommerce_Dropshippers', 'activate'));
		register_deactivation_hook(__FILE__, array('WooCommerce_Dropshippers', 'deactivate'));

		// instantiate the plugin class
		$WooCommerce_Dropshippers = new WooCommerce_Dropshippers();
		
	    // Check if plugin exists
	    if(isset($WooCommerce_Dropshippers))
	    {
	        // Add the settings link to the plugins page
	        function plugin_settings_link($links)
	        { 
	            $settings_link = '<a href="options-general.php?page=WooCommerce_Dropshippers">'. __('Settings','woocommerce-dropshippers') .'</a>';
	            array_unshift($links, $settings_link); 
	            return $links; 
	        }
	        $plugin = plugin_basename(__FILE__); 
	        add_filter("plugin_action_links_$plugin", 'plugin_settings_link');

	        /* USEFUL FUNCTIONS */
			function woocommerce_dropshipper_get_woo_version_number() {
				if ( ! function_exists( 'get_plugins' ) )
					require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				$plugin_folder = get_plugins( '/' . 'woocommerce' );
				$plugin_file = 'woocommerce.php';
				if ( isset( $plugin_folder[$plugin_file]['Version'] ) )
					return $plugin_folder[$plugin_file]['Version'];
				else
					return NULL;
			}

	        /* WIDGET */
	        // Add the widget for dropshippers
	        function woocommerce_dropshipper_dashboard_right_now_function() {
	        	$current_user = wp_get_current_user()->user_login;
	        	$product_count = 0;
	        	$total_sales = 0;
	        	$orders_processing = 0;
	        	$orders_completed = 0;
	        	$table_string = '';
				$query = new WP_Query( array(
						'post_type' => 'product',
						'meta_key' => 'woo_dropshipper',
						'meta_query' => array(
						    array(
								'key' => 'woo_dropshipper',
								'value' => $current_user,
								'compare' => 'IN',
					        )
						)
					)
				);
				// The Loop
				if ( $query->have_posts() ) {
					$product_count = $query->post_count;
					while ( $query->have_posts() ) {
						$variations_string = '<strong>'. __('No Options', 'woocommerce-dropshippers') .'</strong>';
						$query->the_post();
						$price = get_post_meta( get_the_ID(), '_sale_price', true);
						$product_sales = (int)get_post_meta(get_the_ID(), 'total_sales', true);
						$total_sales += $product_sales;
						$prod = get_product(get_the_ID());
						$url = get_permalink(get_the_ID());
						//var_dump($prod->get_attributes());
						if($prod->product_type == 'variable'){
							$variations_string = '';
							$attrs = $prod->get_variation_attributes();
							if( is_array( $attrs ) && count( $attrs ) > 0 ) {
								foreach ($attrs as $key => $value) {
									$variations_string .= '<strong>' . $key . '</strong>';
									foreach ($value as $val) {
										$variations_string .= '<br/>&ndash; '. $val;
									}
									$variations_string .= "<br/>\n";
								}
							}
						}
						$table_string .= '<tr class="alternate" style="padding: 4px 7px 2px;">';
						$table_string .= '<td class="column-columnname" style="padding: 4px 7px 2px;"><strong>' . get_the_title() . '</strong><div class="row-actions"><span><a href="'.$url.'">'. __('Product Page', 'woocommerce-dropshippers') .'</a></span></div></td>';
						$table_string .= '<td class="column-columnname" style="padding: 4px 7px 2px;">' . $variations_string . '</td>';
						$table_string .= '<td class="column-columnname" style="padding: 4px 7px 2px;"> x' . $product_sales . '</td>';
						$table_string .= '</tr>';
					}
				} else {
					// no posts found
				}
				/* Restore original Post Data */
				wp_reset_postdata();
				$woo_ver = woocommerce_dropshipper_get_woo_version_number();
	        	if($woo_ver >= 2.2){
	        		$query = new WP_Query(
						array(
							'post_type' => 'shop_order',
							'post_status' => array( 'wc-processing', 'wc-completed' )
						)
					);
	        	}
	        	else{
					$query = new WP_Query(
						array(
							'post_type' => 'shop_order',
							'post_status' => 'publish'
						)
					);
	        	}

				// The Loop
				if ( $query->have_posts() ) {
					while ( $query->have_posts() ) {
						/* actual product list of the dropshipper */
						$real_products = array();
						$query->the_post();
						$order = new WC_Order(get_the_ID());

						foreach ($order->get_items() as $item) {
							if(get_post_meta( $item["product_id"], 'woo_dropshipper', true) == $current_user){
								array_push($real_products, $item);
							}
						}
						if( (sizeof($real_products) > 0) && ($order->status == "completed") ){
							$orders_completed++;
						}
						if( (sizeof($real_products) > 0) && ($order->status == "processing") ){
							$orders_processing++;
						}
					}
				}
				else {
					// no posts found
				}
				/* Restore original Post Data */
				wp_reset_postdata();
				
				?>
				<div class="table table_shop_content">
					<p class="sub woocommerce_sub"><?php _e( 'Shop Content','woocommerce-dropshippers'); ?></p>
					<table>
					<tr class="first">
						<td class="first b b-products"><a href="#"><?php echo $product_count; ?></a></td>
						<td class="t products"><a href="#"><?php _e('Products','woocommerce-dropshippers'); ?></a></td>
					</tr>
					<tr class="first">
						<td class="first b b-products"><a href="<?php echo admin_url("admin.php?page=dropshipper_order_list_page") ?>"><?php echo $total_sales; ?></a></td>
						<td class="t products"><a href="<?php echo admin_url("admin.php?page=dropshipper_order_list_page") ?>"><?php _e('Sold','woocommerce-dropshippers'); ?></a></td>
					</tr>
					</table>
				</div>
				<div class="table table_orders">
					<p class="sub woocommerce_sub"><?php _e( 'Orders','woocommerce-dropshippers'); ?></p>
					<table>
					<tr class="first">
						<td class="b b-pending"><a href="<?php echo admin_url("admin.php?page=dropshipper_order_list_page") ?>"><?php echo $orders_processing ?></a></td>
						<td class="last t pending"><a href="<?php echo admin_url("admin.php?page=dropshipper_order_list_page") ?>"><?php _e('Processing','woocommerce-dropshippers'); ?></a></td>
					</tr>
					<tr class="first">
						<td class="b b-completed"><a href="<?php echo admin_url("admin.php?page=dropshipper_order_list_page") ?>"><?php echo $orders_completed; ?></a></td>
						<td class="last t completed"><a href="<?php echo admin_url("admin.php?page=dropshipper_order_list_page") ?>"><?php _e('Completed','woocommerce-dropshippers'); ?></a></td>
					</tr>
					</table>
				</div>
				<div class="table total_orders">
					<p class="sub woocommerce_sub"><?php _e( 'Total Earnings','woocommerce-dropshippers'); ?></p>
					<table>
					<tr class="first">
						<td class="last t"><a href="#"><?php _e('Total','woocommerce-dropshippers'); ?></a></td>
						<td class="b"><a href="#"><?php
							$dropshipper_earning = get_user_meta(get_current_user_id(), 'dropshipper_earnings', true);
			        		if(!$dropshipper_earning) $dropshipper_earning = 0;
			        		echo '<span class="artic-toberewritten">'. woocommerce_price((float) $dropshipper_earning) .'</span><span class="artic-tobereconverted" style="display:none;">'. (float) $dropshipper_earning .'</span>';
						?></a></td>
					</tr>
					</table>
				</div>

				<div class="versions"></div>

				<table class="wp-list-table widefat fixed posts" cellspacing="0">
				    <thead>
				        <tr>
				            <th id="co" class="manage-column column-columnname" scope="col"><?php echo __('Product','woocommerce-dropshippers'); ?></th>
				            <th id="columnname" class="manage-column column-columnname" scope="col"><?php echo __('Options','woocommerce-dropshippers'); ?></th>
				            <th width="40" id="columnname" class="manage-column column-columnname" scope="col"><?php echo __('Sold','woocommerce-dropshippers'); ?></th>
				        </tr>
				    </thead>
				    <tfoot>
				        <tr>
				            <th class="manage-column column-columnname" scope="col"><?php echo __('Product','woocommerce-dropshippers'); ?></th>
				            <th class="manage-column column-columnname" scope="col"><?php echo __('Options','woocommerce-dropshippers'); ?></th>
				            <th class="manage-column column-columnname" scope="col"><?php echo __('Sold','woocommerce-dropshippers'); ?></th>
				        </tr>
				    </tfoot>
				    <tbody>
				        <?php
							echo $table_string; 
						?>
				    </tbody>
				</table>
				<p></p>
				<?php
					$currency = get_user_meta(get_current_user_id(), 'dropshipper_currency', true);
					if(!$currency) $currency = 'USD';
					$cur_symbols = array(
						"USD" => '&#36;',
						"AUD" => '&#36;',
						"BDT" => '&#2547;&nbsp;',
						"BRL" => '&#82;&#36;',
						"BGN" => '&#1083;&#1074;.',
						"CAD" => '&#36;',
						"CLP" => '&#36;',
						"CNY" => '&yen;',
						"COP" => '&#36;',
						"CZK" => '&#75;&#269;',
						"DKK" => '&#107;&#114;',
						"EUR" => '&euro;',
						"HKD" => '&#36;',
						"HRK" => 'Kn',
						"HUF" => '&#70;&#116;',
						"ISK" => 'Kr.',
						"IDR" => 'Rp',
						"INR" => 'Rs.',
						"ILS" => '&#8362;',
						"JPY" => '&yen;',
						"KRW" => '&#8361;',
						"MYR" => '&#82;&#77;',
						"MXN" => '&#36;',
						"NGN" => '&#8358;',
						"NOK" => '&#107;&#114;',
						"NZD" => '&#36;',
						"PHP" => '&#8369;',
						"PLN" => '&#122;&#322;',
						"GBP" => '&pound;',
						"RON" => 'lei',
						"RUB" => '&#1088;&#1091;&#1073;.',
						"SGD" => '&#36;',
						"ZAR" => '&#82;',
						"SEK" => '&#107;&#114;',
						"CHF" => '&#67;&#72;&#70;',
						"TWD" => '&#78;&#84;&#36;',
						"THB" => '&#3647;',
						"TRY" => '&#84;&#76;',
						"VND" => '&#8363;',
					);
				?>
				<script type="text/javascript">
					jQuery.ajax({
					    url:"https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.xchange%20where%20pair%20in%20%28%22<?php echo get_woocommerce_currency() . $currency; ?>%22%29&format=json&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=cbfunc",
					    dataType: 'jsonp',
					    jsonp: 'callback',
					    jsonpCallback: 'cbfunc'
					});
					function cbfunc(data) {
					    var convRate = data.query.results.rate.Rate;
					    var toRewrite = jQuery('.artic-toberewritten');
					    jQuery('.artic-tobereconverted').each(function(i,j){
					    	toRewrite.eq(i).html('<?php echo $cur_symbols[$currency]; ?> '+ (parseFloat(jQuery(j).text())*convRate).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,'));
					    });
					}
					Number.prototype.format = function(n, x) {
					    var re = '\\d(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
					    return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$&,');
					};
				</script>
				<?php
			} 
			// Create the function use in the action hook
			function example_add_dashboard_widgets() {
				if (current_user_can('show_dropshipper_widget')){
					wp_add_dashboard_widget('woocommerce_dashboard_right_now', __('WooCommerce Dropshipper Right Now','woocommerce-dropshippers'), 'woocommerce_dropshipper_dashboard_right_now_function');
				}
			}
			// Hoook into the 'wp_dashboard_setup' action to register our other functions
			add_action('wp_dashboard_setup', 'example_add_dashboard_widgets' );


			/* METABOX */
			function print_dropshipper_list_metabox(){
				global $post;
				$woo_dropshipper = get_post_meta( $post->ID, 'woo_dropshipper', true );
				$dropshipperz = get_users('role=dropshipper');
				?>
				<label for="dropshippers-select"> <?php _e( "Select a Dropshipper",'woocommerce-dropshippers') ?></label>
				<select name="dropshippers-select" id="dropshippers-select" style="width:100%;">
					<?php if($woo_dropshipper == null || $woo_dropshipper == '' || $woo_dropshipper == '--'){ ?>
						<option value="--" selected="selected">-- <?php echo __('No Dropshipper','woocommerce-dropshippers'); ?> --</option>
					<?php } else{ ?>
						<option value="--">-- <?php echo __('No Dropshipper','woocommerce-dropshippers'); ?> --</option>
					<?php } ?>
				<?php
				if( is_array( $dropshipperz ) && count( $dropshipperz ) > 0 ) {
		    		foreach ($dropshipperz as $drop) {
		    			if($woo_dropshipper == $drop->user_login){
		    				echo '<option value="' . $drop->user_login . '" selected="selected">' . ucwords($drop->user_nicename) . '</option>';
		    			}
		        		else{
		        			echo '<option value="' . $drop->user_login . '">' . ucwords($drop->user_nicename) . '</option>';
		        		}
		    		}
		    	}
				?>
				</select> 
				<?php
			}

			add_action( 'add_meta_boxes', 'add_dropshipper_metaboxes' );
			function add_dropshipper_metaboxes() {
				add_meta_box('wpt_events_location', __('Dropshipper','woocommerce-dropshippers'), 'print_dropshipper_list_metabox', 'product', 'side', 'default');
			}

			add_action( 'save_post', 'save_dropshipper', 10, 2 );
			function save_dropshipper($post_id, $post){
				/* Get the post type object. */
				$post_type = get_post_type_object( $post->post_type );
				/* Check if the current user has permission to edit the post. */
				if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
					return $post_id;
				/* Get the posted data and sanitize it for use as an HTML class. */
				if(isset( $_POST['dropshippers-select'])){
					$new_meta_value = $_POST['dropshippers-select'];//sanitize_html_class( $_POST['dropshippers-select'] );
					update_post_meta( $post_id, 'woo_dropshipper', $new_meta_value);
				}
			}


			/* ADD DROPSHIPPER COLUMN IN ADMIN ORDERS TABLE */
			function add_dropshippers_column($columns){
			    $columns['dropshippers'] = __('Dropshippers','woocommerce-dropshippers');
			    return $columns;
			}
			add_filter( 'manage_edit-shop_order_columns', 'add_dropshippers_column', 500 );
			
			function add_dropshippers_values_in_column($column){
			    global $post, $the_order;
			    $order_number = $post->ID;

			    //start editing, I was saving my fields for the orders as custom post meta
			    //if you did the same, follow this code
			    if ( $column == 'dropshippers' ) {
			    	$row_dropshppers = get_post_meta($post->ID, 'dropshippers', true);
			    	if( is_array( $row_dropshppers ) && count( $row_dropshppers ) > 0 ) {
				    	foreach ($row_dropshppers as $dropuser => $value) {
				    		$mark_type = 'processing';
				    		if($value == 'Shipped'){
				    			$mark_type = 'completed';
				    		}
				    		echo '<span class="order_status column-order_status" style="display:inline-block; width:28px"><mark class="'. $mark_type .' tips" data-tip="'. $dropuser .': '. $mark_type .'">'. $mark_type .'</mark></span>';
				    	}
				    }
			    }
			    //stop editing
			}
			add_action( 'manage_shop_order_posts_custom_column', 'add_dropshippers_values_in_column', 2 );

			/* ADD METABOX WITH DROPSHIPPER STATUSES IN ADMIN ORDERS */
			function print_dropshipper_list_metabox_in_orders(){
				global $post;
				$row_dropshppers = get_post_meta($post->ID, 'dropshippers', true);
		    	if( is_array( $row_dropshppers ) && count( $row_dropshppers ) > 0 ) {
			    	foreach ($row_dropshppers as $dropuser => $value) {
			    		$mydropuser = get_user_by('login', $dropuser);
			    		if($mydropuser){
			    			$dropshipper_shipping_info = get_post_meta($post->ID, 'dropshipper_shipping_info_'.$mydropuser->ID, true);
				        	if(!$dropshipper_shipping_info){
				        		$dropshipper_shipping_info = array(
				        			'date' => '-',
				        			'tracking_number' => '-',
				        			'shipping_company' => '-',
				        			'notes' => '-'
				        		);
				        	}
				        	echo '<h2>'. $dropuser .'</h2>'."\n";
				        	echo '<strong>'. __('Date', 'woocommerce-dropshippers') .'</strong>: <span class="dropshipper_date">'. (empty($dropshipper_shipping_info['date'])? '-' :$dropshipper_shipping_info['date']) . '</span><br/>' ."\n";
				        	echo '<strong>'. __('Tracking Number(s)', 'woocommerce-dropshippers') .'</strong>: <span class="dropshipper_tracking_number">'. (empty($dropshipper_shipping_info['tracking_number'])? '-' : $dropshipper_shipping_info['tracking_number']) . '</span><br/>'."\n";
				        	echo '<strong>'. __('Shipping Company', 'woocommerce-dropshippers') .'</strong>: <span class="dropshipper_shipping_company">'. (empty($dropshipper_shipping_info['shipping_company'])? '-' : $dropshipper_shipping_info['shipping_company']) . '</span><br/>'."\n";
				        	echo '<strong>'. __('Notes', 'woocommerce-dropshippers') .'</strong>: <span class="dropshipper_notes">'. (empty($dropshipper_shipping_info['notes'])? '-' : $dropshipper_shipping_info['notes']) . '</span><br/>'."\n";
				        	echo "<hr>\n";
			    		}
			    	}
			    }
			}

			add_action( 'add_meta_boxes', 'add_dropshipper_metaboxes_in_orders' );
			function add_dropshipper_metaboxes_in_orders() {
				add_meta_box('wpt_dropshipper_list', __('Dropshippers','woocommerce-dropshippers'), 'print_dropshipper_list_metabox_in_orders', 'shop_order', 'side', 'default');
			}

			/* ADD SHIPPED BUTTON IN DROPSHIPPERS ORDERS */
			add_action( 'admin_footer', 'dropshipped_javascript' );
			function dropshipped_javascript() {
				if ( current_user_can( 'show_dropshipper_widget' ) )  {
			?>
				<script type="text/javascript" >
				function js_dropshipped(my_id) {
					if(confirm("<?php echo __('Are you sure?','woocommerce-dropshippers');?>")){
						var data = {
							action: 'dropshipped',
							id: my_id
						};
						// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
						jQuery.post(ajaxurl, data, function(response) {
							if(response == 'true'){
								jQuery('#mark_dropshipped_' + my_id).after("<?php echo __('Shipped','woocommerce-dropshippers'); ?>");
							}
						});
						jQuery('#mark_dropshipped_' + my_id).fadeOut();
					}
					else{
						// do nothing
					}
				}
				</script>
				<?php
				}
			}
			add_action('wp_ajax_dropshipped', 'dropshipped_callback');
			function dropshipped_callback() {
				global $wpdb;
				if(isset($_POST['id'])){
					$id = intval( $_POST['id'] );
					$my_wc_order = new WC_Order($_POST['id']);
					$my_wc_order_number = $my_wc_order->get_order_number();
					$dropshippers = get_post_meta($_POST['id'], 'dropshippers', true);
					$dropshippers[wp_get_current_user()->user_login] = "Shipped";
					$dropshipper_shipping_info = get_post_meta($id, 'dropshipper_shipping_info_'.get_current_user_id(), true);
					if(!$dropshipper_shipping_info){
		        		$dropshipper_shipping_info = array(
		        			'date' => '',
		        			'tracking_number' => '',
		        			'shipping_company' => '',
		        			'notes' => ''
		        		);
		        	}
					update_post_meta($_POST['id'], 'dropshippers', $dropshippers);
					$options = get_option('woocommerce_dropshippers_options');
					$admin_email = get_option('admin_email');
					if( isset($options['admin_email']) && (!empty($options['admin_email'])) ){
						$admin_email = $options['admin_email'];
					}
					wp_mail( $admin_email, str_replace("%NUMBER%",$my_wc_order_number,__("Dropshipper order update %NUMBER%", 'woocommerce-dropshippers')),
						str_replace("%NUMBER%",$my_wc_order_number,str_replace("%NAME%",wp_get_current_user()->user_login,__('The Dropshipper %NAME% has shipped order %NUMBER%', 'woocommerce-dropshippers'))) .
						"\n". __('Date', 'woocommerce-dropshippers') .': '. $dropshipper_shipping_info['date'] .
	        			"\n". __('Tracking Number(s)', 'woocommerce-dropshippers') .': '. $dropshipper_shipping_info['tracking_number'] .
	        			"\n". __('Shipping Company', 'woocommerce-dropshippers') .': '. $dropshipper_shipping_info['shipping_company'] .
	        			"\n". __('Notes', 'woocommerce-dropshippers') .': '. $dropshipper_shipping_info['notes']
					);
			        echo 'true';
				}
				else{
					echo 'false';
				}
				die(); // this is required to return a proper result
			}


			/* REMOVE ADMIN PANELS */
			function dropshippers_remove_menus () {
				if ( current_user_can( 'show_dropshipper_widget' ) )  {
					global $menu;
					$allowed = array(__('Dashboard'), __('Profile'));
					end ($menu);
					while (prev($menu)){
						$value = explode(' ',$menu[key($menu)][0]);
						if(!in_array($value[0] != NULL?$value[0]:"" , $allowed)){unset($menu[key($menu)]);}
					}
				}
			}
			add_action('admin_menu', 'dropshippers_remove_menus');

		    function dropshippers_disable_dashboard_widgets() {  
			    if ( current_user_can( 'show_dropshipper_widget' ) )  {
			    	remove_action('welcome_panel', 'wp_welcome_panel');
			        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');

					remove_meta_box('dashboard_right_now', 'dashboard', 'normal');   // Right Now
					remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal'); // Recent Comments
					remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');  // Incoming Links
					remove_meta_box('dashboard_plugins', 'dashboard', 'normal');   // Plugins
					remove_meta_box('dashboard_quick_press', 'dashboard', 'side');  // Quick Press
					remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');  // Recent Drafts
					remove_meta_box('dashboard_primary', 'dashboard', 'side');   // WordPress blog
					remove_meta_box('dashboard_secondary', 'dashboard', 'side');   // Other WordPress News
			    }
			}  
			add_action('wp_dashboard_setup', 'dropshippers_disable_dashboard_widgets');

			function dropshippers_remove_admin_bar_links() {
				global $wp_admin_bar;
			    if ( current_user_can( 'show_dropshipper_widget' ) )  {
					$wp_admin_bar->remove_menu('updates');          // Remove the updates link
					$wp_admin_bar->remove_menu('comments');         // Remove the comments link
					$wp_admin_bar->remove_menu('new-content');      // Remove the content link
			    }
			}
			add_action( 'wp_before_admin_bar_render', 'dropshippers_remove_admin_bar_links' );

			/* ADD DROPSHIPPER'S LIST IN ADMIN MENU */
			add_action('admin_menu', 'register_dropshippers_list_page');
			function register_dropshippers_list_page() {
				add_users_page( __('Dropshippers list','woocommerce-dropshippers'), __('Dropshippers list','woocommerce-dropshippers'), 'manage_options', 'drophippers-list-page', 'dropshippers_list_page_callback' );
			}

			function dropshippers_list_page_callback() {
				echo '<script type="text/javascript" src="'. plugins_url( 'pay_dropshipper.js' , __FILE__ ) .'"></script>';
				echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
				echo '<h2>'. __('WooCommerce Dropshippers','woocommerce-dropshippers') .'</h2>';
				echo '<h3>'. __('Dropshippers List','woocommerce-dropshippers') .'</h3>';
				$ajax_nonce = wp_create_nonce( "SpaceRubberDuck" );
				?>
				<script type="text/javascript">
					function js_reset_earnings(my_id) {
						if(confirm("<?php echo __('Do you really want to reset the earnings of this dropshipper?','woocommerce-dropshippers'); ?>")){
							var data = {
								action: 'reset_earnings',
								security: '<?php echo $ajax_nonce; ?>',
								id: my_id
							};
							// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
							jQuery.post(ajaxurl, data, function(response) {
								if(response == 'true'){
									location.reload(true);
								}
							});
						}
						else{
							// do nothing
						}
					}
				</script>
				<table class="wp-list-table widefat fixed posts" cellspacing="0">
			    <thead>
			        <tr>
			            <th id="columnname" class="manage-column column-columnname" scope="col"><?php echo __('User','woocommerce-dropshippers'); ?></th>
			            <th id="columnname" class="manage-column column-columnname" scope="col"><?php echo __('Earnings','woocommerce-dropshippers'); ?></th>
			            <th id="columnname" class="manage-column column-columnname" scope="col"><?php echo __('Actions','woocommerce-dropshippers'); ?></th>
			        </tr>
			    </thead>
			    <tfoot>
			        <tr>
			            <th id="columnname" class="manage-column column-columnname" scope="col"><?php echo __('User','woocommerce-dropshippers'); ?></th>
			            <th id="columnname" class="manage-column column-columnname" scope="col"><?php echo __('Earnings','woocommerce-dropshippers'); ?></th>
			            <th id="columnname" class="manage-column column-columnname" scope="col"><?php echo __('Actions', 'woocommerce-dropshippers'); ?></th>
			        </tr>
				</tfoot>
				<tbody>
					<?php
						$dropshipperz = get_users('role=dropshipper');
						foreach ($dropshipperz as $drop_usr) {
							echo '<tr class="type-shop_order"><td><strong>'.$drop_usr->user_login.'</strong></td>';
							$dropshipper_earning = get_user_meta($drop_usr->ID, 'dropshipper_earnings', true);
							if(!$dropshipper_earning){ $dropshipper_earning = 0; }
							echo '<td>'. woocommerce_price((float) $dropshipper_earning).'</td>';
							echo '<td>';
							echo '<button class="button button-primary" style="margin-bottom: 3px;" onclick="js_reset_earnings(\''. $drop_usr->ID .'\')">'. __('Reset earnings','woocommerce-dropshippers') .'</button><br/>';
							$email = get_user_meta($drop_usr->ID, 'dropshipper_paypal_email',true);
							if($email){
								echo '<button class="button button-primary" onclick="payDropshipper(\''. $email .'\', \''.$dropshipper_earning.'\', \''.get_woocommerce_currency().'\')">'. __('Pay this dropshipper (PayPal)','woocommerce-dropshippers') .'</button>';
							}
							else{
								echo __('The dropshipper has not entered the PayPal email','woocommerce-dropshippers');
							}
							echo '</td></tr>' . "\n";
						}
					?>
				</tbody>
				</table>
				</div>
				<?php
			}

			/** DROPSHIPPER SETTINGS PAGE **/
			add_action( 'admin_menu', 'dropshipper_settings_page' );

			function dropshipper_settings_page() {
				add_menu_page( __('Dropshipper Settings','woocommerce-dropshippers'), __('Dropshipper Settings','woocommerce-dropshippers'), 'show_dropshipper_widget', 'dropshipper_settings_page', 'dropshipper_settings_page_function' );
			}

			function dropshipper_settings_page_function() {
				if ( !current_user_can( 'show_dropshipper_widget' ) )  {
					wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
				}
				//require_once(sprintf("%s/orders.php", dirname(__FILE__)));
				$user_id = get_current_user_id();
				if(isset($_POST['dropshipper_paypal_email'])){
					$email = sanitize_email($_POST['dropshipper_paypal_email']);
					if(is_email($email, false)){
						update_user_meta($user_id, 'dropshipper_paypal_email', $email);
						?>
						<div id="message" class="updated">
					        <p><strong><?php _e('Settings saved.','woocommerce-dropshippers') ?></strong></p>
					    </div>
						<?php
					}
					else{
						?>
						<div id="message" class="error">
					        <p><strong><?php _e('Check the email.','woocommerce-dropshippers') ?></strong></p>
					    </div>
						<?php
					}
				}
				$email = get_user_meta($user_id, 'dropshipper_paypal_email', true);
				if(isset($_POST['dropshipper_currency'])){
					$currency = sanitize_text_field($_POST['dropshipper_currency']);
					update_user_meta($user_id, 'dropshipper_currency', $currency);
				}
				$currency = get_user_meta($user_id, 'dropshipper_currency', true);
				if(!$currency) $currency = 'USD';

				echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
				echo '<h2>'. __('WooCommerce Dropshippers','woocommerce-dropshippers') .'</h2>';
				echo '<h3>'. __('Dropshipper Settings','woocommerce-dropshippers') .'</h3>';
				?>
				<form method="post" action="">
					<table>
						<tr>	
							<td><label for="dropshipper_paypal_email"><strong><?php echo __('PayPal email','woocommerce-dropshippers'); ?></strong></label></td>
							<td><input type="text" name="dropshipper_paypal_email" value="<?php if($email) echo $email; ?>"></td>
						</tr>
						<tr>
							<td><label for="dropshipper_currency"><strong><?php echo __('Currency','woocommerce-dropshippers'); ?></strong></label></td>
							<td><select name="dropshipper_currency">
								<option value="USD" <?php if($currency=='USD') echo 'selected="selected"'; ?>>US Dollars (&#36;)</option>
								<option value="AUD" <?php if($currency=='AUD') echo 'selected="selected"'; ?>>Australian Dollars (&#36;)</option>
								<option value="BDT" <?php if($currency=='BDT') echo 'selected="selected"'; ?>>Bangladeshi Taka (&#2547;&nbsp;)</option>
								<option value="BRL" <?php if($currency=='BRL') echo 'selected="selected"'; ?>>Brazilian Real (&#82;&#36;)</option>
								<option value="BGN" <?php if($currency=='BGN') echo 'selected="selected"'; ?>>Bulgarian Lev (&#1083;&#1074;.)</option>
								<option value="CAD" <?php if($currency=='CAD') echo 'selected="selected"'; ?>>Canadian Dollars (&#36;)</option>
								<option value="CLP" <?php if($currency=='CLP') echo 'selected="selected"'; ?>>Chilean Peso (&#36;)</option>
								<option value="CNY" <?php if($currency=='CNY') echo 'selected="selected"'; ?>>Chinese Yuan (&yen;)</option>
								<option value="COP" <?php if($currency=='COP') echo 'selected="selected"'; ?>>Colombian Peso (&#36;)</option>
								<option value="CZK" <?php if($currency=='CZK') echo 'selected="selected"'; ?>>Czech Koruna (&#75;&#269;)</option>
								<option value="DKK" <?php if($currency=='DKK') echo 'selected="selected"'; ?>>Danish Krone (&#107;&#114;)</option>
								<option value="EUR" <?php if($currency=='EUR') echo 'selected="selected"'; ?>>Euros (&euro;)</option>
								<option value="HKD" <?php if($currency=='HKD') echo 'selected="selected"'; ?>>Hong Kong Dollar (&#36;)</option>
								<option value="HRK" <?php if($currency=='HRK') echo 'selected="selected"'; ?>>Croatia kuna (Kn)</option>
								<option value="HUF" <?php if($currency=='HUF') echo 'selected="selected"'; ?>>Hungarian Forint (&#70;&#116;)</option>
								<option value="ISK" <?php if($currency=='ISK') echo 'selected="selected"'; ?>>Icelandic krona (Kr.)</option>
								<option value="IDR" <?php if($currency=='IDR') echo 'selected="selected"'; ?>>Indonesia Rupiah (Rp)</option>
								<option value="INR" <?php if($currency=='INR') echo 'selected="selected"'; ?>>Indian Rupee (Rs.)</option>
								<option value="ILS" <?php if($currency=='ILS') echo 'selected="selected"'; ?>>Israeli Shekel (&#8362;)</option>
								<option value="JPY" <?php if($currency=='JPY') echo 'selected="selected"'; ?>>Japanese Yen (&yen;)</option>
								<option value="KRW" <?php if($currency=='KRW') echo 'selected="selected"'; ?>>South Korean Won (&#8361;)</option>
								<option value="MYR" <?php if($currency=='MYR') echo 'selected="selected"'; ?>>Malaysian Ringgits (&#82;&#77;)</option>
								<option value="MXN" <?php if($currency=='MXN') echo 'selected="selected"'; ?>>Mexican Peso (&#36;)</option>
								<option value="NGN" <?php if($currency=='NGN') echo 'selected="selected"'; ?>>Nigerian Naira (&#8358;)</option>
								<option value="NOK" <?php if($currency=='NOK') echo 'selected="selected"'; ?>>Norwegian Krone (&#107;&#114;)</option>
								<option value="NZD" <?php if($currency=='NZD') echo 'selected="selected"'; ?>>New Zealand Dollar (&#36;)</option>
								<option value="PHP" <?php if($currency=='PHP') echo 'selected="selected"'; ?>>Philippine Pesos (&#8369;)</option>
								<option value="PLN" <?php if($currency=='PLN') echo 'selected="selected"'; ?>>Polish Zloty (&#122;&#322;)</option>
								<option value="GBP" <?php if($currency=='GBP') echo 'selected="selected"'; ?>>Pounds Sterling (&pound;)</option>
								<option value="RON" <?php if($currency=='RON') echo 'selected="selected"'; ?>>Romanian Leu (lei)</option>
								<option value="RUB" <?php if($currency=='RUB') echo 'selected="selected"'; ?>>Russian Ruble (&#1088;&#1091;&#1073;.)</option>
								<option value="SGD" <?php if($currency=='SGD') echo 'selected="selected"'; ?>>Singapore Dollar (&#36;)</option>
								<option value="ZAR" <?php if($currency=='ZAR') echo 'selected="selected"'; ?>>South African rand (&#82;)</option>
								<option value="SEK" <?php if($currency=='SEK') echo 'selected="selected"'; ?>>Swedish Krona (&#107;&#114;)</option>
								<option value="CHF" <?php if($currency=='CHF') echo 'selected="selected"'; ?>>Swiss Franc (&#67;&#72;&#70;)</option>
								<option value="TWD" <?php if($currency=='TWD') echo 'selected="selected"'; ?>>Taiwan New Dollars (&#78;&#84;&#36;)</option>
								<option value="THB" <?php if($currency=='THB') echo 'selected="selected"'; ?>>Thai Baht (&#3647;)</option>
								<option value="TRY" <?php if($currency=='TRY') echo 'selected="selected"'; ?>>Turkish Lira (&#84;&#76;)</option>
								<option value="VND" <?php if($currency=='VND') echo 'selected="selected"'; ?>>Vietnamese Dong (&#8363;)</option>
							</select></td>
						</tr>
					</table>
                    <?php
                        /*settings_fields( 'WooCommerce_Dropshippers' );
                        do_settings_sections( 'WooCommerce_Dropshippers' );*/
                    ?>
                    <?php submit_button(__('Save Settings','woocommerce-dropshippers')); ?>
                </form>
				<?php
			}

			/** DROPSHIPPER PRICE HOOK IN ADMIN PRODUCTS **/
			add_action( 'save_post', 'dropshipper_save_admin_simple_dropshipper_price' );
			function dropshipper_save_admin_simple_dropshipper_price( $post_id ) {
				if (isset($_POST['_inline_edit']) && wp_verify_nonce($_POST['_inline_edit'], 'inlineeditnonce'))return;
				if(isset($_POST['dropshipper_price'])){
					$new_data = $_POST['dropshipper_price'];
					$post_ID = $_POST['post_ID'];
					update_post_meta($post_ID, '_dropshipper_price', $new_data) ;
				}
			}
			add_action( 'woocommerce_product_options_pricing', 'dropshipper_add_admin_dropshipper_price', 10, 2 );
			function dropshipper_add_admin_dropshipper_price( $loop ){ 
			$drop_price = get_post_meta( get_the_ID(), '_dropshipper_price', true );
			if(!$drop_price){ $drop_price = ''; }
			?>
			<tr>
			  <td><div>
			      <p class="form-field _regular_price_field ">
			        <label><?php echo __( 'Dropshipper Price','woocommerce-dropshippers' ) . ' ('.get_woocommerce_currency_symbol().')'; ?></label>
			        <input step="any" type="text" class="wc_input_price short" name="dropshipper_price" value="<?php echo $drop_price; ?>"/>
			      </p>
			    </div></td>
			</tr>
			<?php }

			//Display Fields
			add_action( 'woocommerce_product_after_variable_attributes', 'dropshipper_add_admin_variable_dropshipper_price', 10, 2 );
			//JS to add fields for new variations
			add_action( 'woocommerce_product_after_variable_attributes_js', 'dropshipper_add_admin_variable_dropshipper_price_js' );
			//Save variation fields
			add_action( 'woocommerce_process_product_meta_variable', 'dropshipper_admin_variable_dropshipper_price_process', 10, 1 );

			function dropshipper_add_admin_variable_dropshipper_price( $loop, $variation_data ) {
			?>
			<tr>
			  <td><div>
			      <label><?php echo __( 'Dropshipper Price','woocommerce-dropshippers' ) . ' ('.get_woocommerce_currency_symbol().')'; ?></label>
			      <input  step="any" type="text" size="5" name="dropshipper[<?php  echo $loop; ?>]" value="<?php 
			      	if(isset($variation_data['_dropshipper_price'])){
			      		echo $variation_data['_dropshipper_price'][0];
			      	}
			      ?>"/>
			    </div></td>
			</tr>
			<?php
			}

			function dropshipper_add_admin_variable_dropshipper_price_js() {
			?>
			<tr>
			  <td><div>
			      <label><?php echo __( 'Dropshipper Price', 'woocommerce' ) . ' ('.get_woocommerce_currency_symbol().')'; ?></label>
			      <input step="any" type="text" size="5" name="dropshipper[' + loop + ']" />
			    </div></td>
			</tr>
			<?php
			}
			function dropshipper_admin_variable_dropshipper_price_process( $post_id ) {
				if (isset( $_POST['variable_sku'] ) ) :
				    $variable_sku = $_POST['variable_sku'];
				    $variable_post_id = $_POST['variable_post_id'];

				    $dropshipper_field = $_POST['dropshipper'];
				    
				    for ( $i = 0; $i < sizeof( $variable_sku ); $i++ ) :
				        $variation_id = (int) $variable_post_id[$i];
				        if ( isset( $dropshipper_field[$i] ) ) {
				            update_post_meta( $variation_id, '_dropshipper_price', stripslashes( $dropshipper_field[$i] ) );
							update_post_meta( $variation_id, '_parent_product', $post_id );
				        }
				    endfor;
					update_post_meta( $post_id, '_variation_prices', $dropshipper_field );
					update_post_meta( $post_id, '_dropshipper_price', '' );
				endif;
			}

			/* ADD RESET EARNINGS AJAX IN DROPSHIPPERS LIST */
			add_action('wp_ajax_reset_earnings', 'reset_earnings_callback');
			function reset_earnings_callback() {
				check_ajax_referer( 'SpaceRubberDuck', 'security' );
				if(isset($_POST['id'])){
					$id = intval( $_POST['id'] );
					update_user_meta($id, 'dropshipper_earnings', 0);
			        echo 'true';
				}
				else{
					echo 'false';
				}
				die(); // this is required to return a proper result
			}

			/* AJAX SLIP REQUEST FOR DROPSHIPPERS */
			require_once(sprintf("%s/dropshipper-slip.php", dirname(__FILE__)));

			/* ADD MEDIA UPLOADER IN PLUGIN SETTINGS */
			add_action('admin_enqueue_scripts', 'woocommerce_dropshippers_enqueue_media');
			function woocommerce_dropshippers_enqueue_media() {
				if (isset($_GET['page']) && $_GET['page'] == 'WooCommerce_Dropshippers') {
					wp_enqueue_media();
					wp_register_script('woocommerce_admin_settings', WP_PLUGIN_URL.'/woocommerce-dropshippers/admin_settings.js', array('jquery'));
					wp_enqueue_script('woocommerce_admin_settings');
				}
			}

			/* ADD MULTILINGUAL SUPPORT */
			add_action( 'plugins_loaded', 'woocommerce_dropshippers_load_textdomain' );
			function woocommerce_dropshippers_load_textdomain() {
			  load_plugin_textdomain( 'woocommerce-dropshippers', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' ); 
			}

			/* DROPSHIPPERS EDITING SHIPPING INFO */
			add_action( 'admin_footer', 'dropshipper_edit_shipping_info' );
			function dropshipper_edit_shipping_info() {
				if ( current_user_can( 'show_dropshipper_widget' ) )  {
			?>
				<script type="text/javascript" >
				function js_save_dropshipper_shipping_info(my_order_id, my_info) {
					var data = {
						action: 'dropshipper_shipping_info_edited',
						id: my_order_id,
						info: my_info
					};
					// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
					jQuery.post(ajaxurl, data, function(response) {
						if(response == 'true'){
							jQuery('#dropshipper_shipping_info_'+my_order_id+' .dropshipper_date').html(jQuery('#input-dialog-date').val());
							jQuery('#dropshipper_shipping_info_'+my_order_id+' .dropshipper_tracking_number').html(jQuery('#input-dialog-trackingnumber').val());
							jQuery('#dropshipper_shipping_info_'+my_order_id+' .dropshipper_shipping_company').html(jQuery('#input-dialog-shippingcompany').val());
							jQuery('#dropshipper_shipping_info_'+my_order_id+' .dropshipper_notes').html(jQuery('#input-dialog-notes').val());
						}
					});
				}
				</script>
				<?php
				}
			}
			add_action('wp_ajax_dropshipper_shipping_info_edited', 'dropshipper_shipping_info_edited_callback');
			function dropshipper_shipping_info_edited_callback() {
				global $wpdb;
				if(isset($_POST['id']) && isset($_POST['info']) ){
					$id = intval( $_POST['id'] );
					$info = $_POST['info'];
					update_post_meta($_POST['id'], 'dropshipper_shipping_info_'.get_current_user_id(), $info);
			        echo 'true';
				}
				else{
					echo 'false';
				}
				die(); // this is required to return a proper result
			}
	    }
	}
}
?>
<?php include('images/social.png'); ?>