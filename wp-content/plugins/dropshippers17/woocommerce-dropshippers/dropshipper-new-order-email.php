<?php
/**
* File: dropshipper-new-order-email.php
* Author: ArticNet LLC.
**/

	/** SEND EMAIL TO DROPSHIPPERS **/
	function dropshippers_set_html_content_type() {
		return 'text/html';
	}
	add_action('woocommerce_order_status_processing', 'send_email_to_dropshippers');

	function send_email_to_dropshippers($order_id){
		// Dropshippers involved in the order and their shipping information
		$final_dropshippers = array();

		$real_products = array();
		$emails = array();
		$order = new WC_Order($order_id);
		$items = $order->get_items();
		$dropshippers = get_users(array( 'role' => 'dropshipper'));
		$options = get_option('woocommerce_dropshippers_options');
		if( is_array( $dropshippers ) && count( $dropshippers ) > 0 ) {
			foreach ($dropshippers as $dropshipper) {
				$dropshipper_earning = get_user_meta($dropshipper->ID, 'dropshipper_earnings', true);
				if(!$dropshipper_earning){
					$dropshipper_earning = 0;
				}
				$user_login = $dropshipper->user_login;
				foreach ($items as $item) {
					if(get_post_meta( $item["product_id"], 'woo_dropshipper', true) == $user_login){
						if(! isset($real_products[$user_login])){
							$real_products[$user_login] = array();
						}
						array_push($real_products[$user_login], $item);
					}
				}
				// Prepare the email and update earnings if there are products for this dropshipper
				if(isset($real_products[$user_login])){
					$final_dropshippers[$user_login] = 'Not shipped yet';
					ob_start();
					?>
					<div style="background-color: #f5f5f5; width: 100%; -webkit-text-size-adjust: none ; margin: 0; padding: 70px  0  70px  0;">
			        	<table width="100%" cellspacing="0" cellpadding="0" border="0" height="100%">
			        		<tbody><tr><td valign="top" align="center">
							<table width="600" cellspacing="0" cellpadding="0" border="0" style="-webkit-box-shadow: 0  0  0  3px  rgba; box-shadow: 0  0  0  3px  rgba; -webkit-border-radius: 6px ; border-radius: 6px ; background-color: #fdfdfd; border: 1px  solid  #dcdcdc; -webkit-border-radius: 6px ; border-radius: 6px ;" id="template_container"><tbody><tr><td valign="top" align="center">
								<table width="600" cellspacing="0" cellpadding="0" border="0" bgcolor="#557da1" style="background-color: #557da1; color: #ffffff; -webkit-border-top-left-radius: 6px ; -webkit-border-top-right-radius: 6px ; border-top-left-radius: 6px ; border-top-right-radius: 6px ; border-bottom: 0px; font-family: Arial; font-weight: bold; line-height: 100%; vertical-align: middle;" id="template_header"><tbody><tr><td>
									<h1 style="color: #ffffff; margin: 0; padding: 28px  24px; text-shadow: 0  1px  0  #7797b4; display: block; font-family: Arial; font-size: 30px; font-weight: bold; text-align: left; line-height: 150%;"><?php echo __('New customer order','woocommerce-dropshippers'); ?></h1>
								</td></tr></tbody></table></td></tr><tr><td valign="top" align="center">
								<table width="600" cellspacing="0" cellpadding="0" border="0" id="template_body">
									<tbody><tr><td valign="top" style="background-color: #fdfdfd; -webkit-border-radius: 6px ; border-radius: 6px ;">
										<table width="100%" cellspacing="0" cellpadding="20" border="0"><tbody><tr><td valign="top">
										<div style="color: #737373; font-family: Arial; font-size: 14px; line-height: 150%; text-align: left;"><p><?php echo str_replace('%SURNAME%', $order->billing_last_name, str_replace('%NAME%', $order->billing_first_name, __('You have received an order from %NAME% %SURNAME%. Their order is as follows:','woocommerce-dropshippers'))); ?></p>
										<h2 style="color: #505050; display: block; font-family: Arial; font-size: 30px; font-weight: bold; margin-top: 10px; margin-right: 0px; margin-bottom: 10px; margin-left: 0px; text-align: left; line-height: 150%;"><?php echo str_replace('%NUMBER%', $order->get_order_number(), __('From order %NUMBER%','woocommerce-dropshippers')); ?> (<!-- time ignored --><?php echo $order->order_date; ?>)</h2>
										<table cellspacing="0" cellpadding="6" border="1" style="width: 100%; border: 1px  solid  #eee;">
										<thead><tr><th style="text-align: left; border: 1px  solid  #eee;"><?php echo __('Product','woocommerce-dropshippers'); ?></th>
						<th style="text-align: left; border: 1px  solid  #eee;"><?php echo __('Quantity','woocommerce-dropshippers'); ?></th>
						<?php
							if($options['text_string'] == "Yes"){ // Can show prices
								echo '<th style="text-align: left; border: 1px  solid  #eee;">'. __('Price','woocommerce-dropshippers') .'</th>';
							}
						?>
					</tr></thead><tbody>
					<?php
						$drop_subtotal = 0;
						$drop_total_earnings = 0;
						$sudicio = '';
						foreach ($real_products[$user_login] as $item) {
							$my_item_post = get_post($item['product_id']);
							$drop_price = get_post_meta( $item['product_id'], '_dropshipper_price', true );
							if(!$drop_price){ $drop_price = 0;}
							$drop_subtotal += (float) $item['line_subtotal'];
							echo '<tr><td style="text-align: left; vertical-align: middle; border: 1px  solid  #eee; word-wrap: break-word;">'. __($my_item_post->post_title);
							if($item['variation_id'] != 0){
								$drop_price = get_post_meta( $item['variation_id'], '_dropshipper_price', true );
								if(!$drop_price){ $drop_price = 0;}
								$item_meta = new WC_Order_Item_Meta( $item['item_meta'] );
								if ( $item_meta->meta )
									echo '<br/><small>' . nl2br( $item_meta->display( true, true ) ) . '</small>';
							}
							echo '<br><small></small></td>';
							echo '<td style="text-align: left; vertical-align: middle; border: 1px  solid  #eee;">' . $item['qty'] .'</td>';
							$drop_total_earnings += ($drop_price*$item['qty']);
							if($options['text_string'] == "Yes"){ //show prices
								echo '<td style="text-align: left; vertical-align: middle; border: 1px  solid  #eee;"><span class="amount">'. woocommerce_price((float) $item['line_subtotal']).'</span></td></tr>';
							}
						}
						if($drop_total_earnings){
							update_user_meta($dropshipper->ID, 'dropshipper_earnings', ($dropshipper_earning + $drop_total_earnings));
						}
					?>
					</tbody>
					<tfoot>
						<?php
							if($options['text_string'] == "Yes"){ // Can show prices
						?>
							<tr><th style="text-align: left; border: 1px  solid  #eee; border-top-width: 4px;" colspan="2"><?php echo __('Cart Subtotal:','woocommerce-dropshippers'); ?></th>
							<td style="text-align: left; border: 1px  solid  #eee; border-top-width: 4px;"><span class="amount"><?php echo woocommerce_price($drop_subtotal); ?></span></td>
							</tr><tr><th style="text-align: left; border: 1px  solid  #eee;" colspan="2"><?php echo __('Shipping:','woocommerce-dropshippers'); ?></th>
								<td style="text-align: left; border: 1px  solid  #eee;"><?php echo __('Free Shipping','woocommerce-dropshippers'); ?></td>
							</tr><tr><th style="text-align: left; border: 1px  solid  #eee;" colspan="2"><?php echo __('Order Total:','woocommerce-dropshippers'); ?></th>
								<td style="text-align: left; border: 1px  solid  #eee;"><span class="amount"><?php echo woocommerce_price(($drop_subtotal + $order->get_total_shipping())); ?></span></td>
							</tr>
						<?php
							}
						?>
					</tfoot></table><h2 style="color: #505050; display: block; font-family: Arial; font-size: 30px; font-weight: bold; margin-top: 10px; margin-right: 0px; margin-bottom: 10px; margin-left: 0px; text-align: left; line-height: 150%;"><?php echo __('Customer details','woocommerce-dropshippers'); ?></h2>
					<?php
					if($options['can_see_email'] == 'Yes'){ ?>
						<p><strong><?php echo __('Email:','woocommerce-dropshippers'); ?></strong>
						<a onclick="return rcmail.command('compose','<?php echo $order->billing_email; ?>',this)" href="mailto:<?php echo $order->billing_email; ?>"><?php echo $order->billing_email; ?></a></p>
					<?php
					}
					if($options['can_see_phone'] == 'Yes'){ ?>
						<p><strong><?php echo __('Tel:','woocommerce-dropshippers'); ?></strong> <?php echo $order->billing_phone; ?></p>
					<?php
					}
					?>
					<table cellspacing="0" cellpadding="0" border="0" style="width: 100%; vertical-align: top;"><tbody><tr><td width="50%" valign="top">
					<h3 style="color: #505050; display: block; font-family: Arial; font-size: 26px; font-weight: bold; margin-top: 10px; margin-right: 0px; margin-bottom: 10px; margin-left: 0px; text-align: left; line-height: 150%;"><?php echo __('Billing address','woocommerce-dropshippers'); ?></h3><p><?php echo (isset($options['billing_address'])?nl2br($options['billing_address']):''); ?></p>
					</td>
					<td width="50%" valign="top">
						<h3 style="color: #505050; display: block; font-family: Arial; font-size: 26px; font-weight: bold; margin-top: 10px; margin-right: 0px; margin-bottom: 10px; margin-left: 0px; text-align: left; line-height: 150%;"><?php echo __('Shipping address','woocommerce-dropshippers'); ?></h3><p><?php echo $order->get_formatted_shipping_address(); ?></p>

					</td>

					
				</tr></tbody></table></div>
																	</td>
			                                                    </tr></tbody></table></td>
			                                        </tr></tbody></table></td>
			                            </tr><tr><td valign="top" align="center">
			                                    
			                                	<table width="600" cellspacing="0" cellpadding="10" border="0" style="border-top: 0px; -webkit-border-radius: 6px;" id="template_footer"><tbody><tr><td valign="top">
			                                                <table width="100%" cellspacing="0" cellpadding="10" border="0"><tbody><tr><td valign="middle" style="border: 0; color: #99b1c7; font-family: Arial; font-size: 12px; line-height: 125%; text-align: center;" id="credit" colspan="2"><p><?php echo bloginfo('name'); ?></p>
			                                                        </td>
			                                                    </tr></tbody></table></td>
			                                        </tr></tbody></table></td>
			                            </tr></tbody></table></td>
			                </tr></tbody></table></div>
					<?php
					$lolvar = ob_get_clean();
					$headers = __('From:','woocommerce-dropshippers') .' '. get_option('blogname'). ' <'. get_option('admin_email') .'>' . "\r\n";
					add_filter( 'wp_mail_content_type', 'dropshippers_set_html_content_type' );
					wp_mail($dropshipper->user_email, __('New customer order','woocommerce-dropshippers'), $lolvar, $headers);
					// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
					remove_filter( 'wp_mail_content_type', 'dropshippers_set_html_content_type' );
				}
				update_post_meta($order_id, 'dropshippers', $final_dropshippers);
			}
		}
	}
?>