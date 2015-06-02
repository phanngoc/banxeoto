<?php
/**
* File: dropshipper-new-order-email.php
* Author: ArticNet LLC.
**/

if(!class_exists('WooCommerce_Dropshippers_Settings'))
{
	class WooCommerce_Dropshippers_Settings
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
			// register actions
            add_action('admin_init', array(&$this, 'admin_init'));
        	add_action('admin_menu', array(&$this, 'add_menu'));
		} // END public function __construct
		
        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
        	// register your plugin's settings
        	register_setting('WooCommerce_Dropshippers', 'woocommerce_dropshippers_options');

        	// add a settings section
        	add_settings_section(
        	    'WooCommerce_Dropshippers-section', 
        	    __('WooCommerce Dropshipper Settings','woocommerce-dropshippers'), 
        	    array(&$this, 'settings_section_WooCommerce_Dropshippers'), 
        	    'WooCommerce_Dropshippers'
        	);
        	
        	// add a setting's fields
            add_settings_field(
                'woocommerce_dropshippers_text_string',
                __('Show full prices to dropshippers','woocommerce-dropshippers'),
                array(&$this, 'settings_field_input_text'),
                'WooCommerce_Dropshippers',
                'WooCommerce_Dropshippers-section'
            );
            // add a setting's fields
            add_settings_field(
                'woocommerce_dropshippers_billing_address',
                __('The billing address that will be shown to dropshippers','woocommerce-dropshippers'),
                array(&$this, 'settings_field_billing_address'),
                'WooCommerce_Dropshippers',
                'WooCommerce_Dropshippers-section'
            );
            // add a setting's fields
            add_settings_field(
                'woocommerce_dropshippers_can_see_email',
                __('Show customer email to dropshippers','woocommerce-dropshippers'),
                array(&$this, 'settings_field_can_see_email'),
                'WooCommerce_Dropshippers',
                'WooCommerce_Dropshippers-section'
            );
            // add a setting's fields
            add_settings_field(
                'woocommerce_dropshippers_can_see_phone',
                __('Show customer phone number to dropshippers','woocommerce-dropshippers'),
                array(&$this, 'settings_field_can_see_phone'),
                'WooCommerce_Dropshippers',
                'WooCommerce_Dropshippers-section'
            );
            // add a setting's fields
            add_settings_field(
                'woocommerce_dropshippers_company_logo',
                __('Shop logo to appear in the Dropshipper packing slip','woocommerce-dropshippers'),
                array(&$this, 'settings_field_company_logo'),
                'WooCommerce_Dropshippers',
                'WooCommerce_Dropshippers-section'
            );
            // add a setting's fields
            add_settings_field(
                'woocommerce_dropshippers_slip_footer',
                __('Footer text to appear in the Dropshipper packing slip','woocommerce-dropshippers'),
                array(&$this, 'settings_field_slip_footer'),
                'WooCommerce_Dropshippers',
                'WooCommerce_Dropshippers-section'
            );
            // add a setting's fields
            add_settings_field(
                'woocommerce_dropshippers_admin_email',
                __("The dropshippers' notifications will be sent to this email address. If left empty, the default Wordpress admin email will be used instead.",'woocommerce-dropshippers'),
                array(&$this, 'settings_field_admin_email'),
                'WooCommerce_Dropshippers',
                'WooCommerce_Dropshippers-section'
            );
            // Possibly do additional admin_init tasks
        } // END public static function activate
        
        public function settings_section_WooCommerce_Dropshippers()
        {
            // Think of this as help text for the section.
            //echo 'These settings do things for the WooCommerce Dropshippers.';
        }
        
        public function settings_field_input_text()
        {
            $options = get_option('woocommerce_dropshippers_options');
            echo '<select id="woocommerce_dropshippers_text_string" name="woocommerce_dropshippers_options[text_string]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['text_string']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
        } // END public function settings_field_input_text($args)
        public function settings_field_billing_address()
        {
            $options = get_option('woocommerce_dropshippers_options');
            echo '<textarea style="width: 300px; height: 150px;" id="woocommerce_dropshippers_billing_address" name="woocommerce_dropshippers_options[billing_address]">' . (isset($options['billing_address'])?$options['billing_address']:'') .'</textarea>'."\n";
        } // END public function settings_field_input_text($args)
        public function settings_field_can_see_email()
        {
            $options = get_option('woocommerce_dropshippers_options');
            echo '<select id="woocommerce_dropshippers_can_see_email" name="woocommerce_dropshippers_options[can_see_email]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['can_see_email']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
        } // END public function settings_field_input_text($args)
        public function settings_field_can_see_phone()
        {
            $options = get_option('woocommerce_dropshippers_options');
            echo '<select id="woocommerce_dropshippers_can_see_phone" name="woocommerce_dropshippers_options[can_see_phone]"><option value="Yes">'. __('Yes','woocommerce-dropshippers') .'</option><option value="No" '. (($options['can_see_phone']=='No')?'selected="selected"':'') . '>'. __('No','woocommerce-dropshippers') .'</option></select>'."\n";
        } // END public function settings_field_input_text($args)
        public function settings_field_company_logo()
        {
            $options = get_option('woocommerce_dropshippers_options');
            echo '<input type="text" id="woocommerce_dropshippers_company_logo" name="woocommerce_dropshippers_options[company_logo]" value="'.(isset($options['company_logo'])?$options['company_logo']:'').'" />'."\n";
            echo '<button id="woocommerce_dropshippers_company_logo_button">'. __('Upload','woocommerce-dropshippers') .'</button>'."\n";
        } // END public function settings_field_input_text($args)
        public function settings_field_slip_footer()
        {
            $options = get_option('woocommerce_dropshippers_options');
            echo '<textarea style="width: 300px; height: 150px;" id="woocommerce_dropshippers_slip_footer" name="woocommerce_dropshippers_options[slip_footer]">' . (isset($options['slip_footer'])?$options['slip_footer']:'') .'</textarea>'."\n";
        } // END public function settings_field_input_text($args)
        public function settings_field_admin_email()
        {
            $options = get_option('woocommerce_dropshippers_options');
            echo '<input type="email" id="woocommerce_dropshippers_admin_email" name="woocommerce_dropshippers_options[admin_email]" value="'.(isset($options['admin_email'])?$options['admin_email']:'').'" />'."\n";
        } // END public function settings_field_input_text($args)

        /**
         * add a menu
         */		
        public function add_menu()
        {
            // Add a page to manage this plugin's settings
        	add_options_page(
        	    __('WooCommerce Dropshipper Settings','woocommerce-dropshippers'),
        	    __('WooCommerce Dropshippers','woocommerce-dropshippers'),
        	    'manage_options',
        	    'WooCommerce_Dropshippers',
        	    array(&$this, 'plugin_settings_page')
        	);
        } // END public function add_menu()
    
        /**
         * Menu Callback
         */		
        public function plugin_settings_page()
        {
        	if(!current_user_can('manage_options')){
        		wp_die(__('You do not have sufficient permissions to access this page.'));
        	}
            ?>
            <div class="wrap">
                <?php screen_icon(); ?>
                <h2><?php echo __('WooCommerce Dropshipper Settings','woocommerce-dropshippers'); ?></h2>
                <form method="post" action="options.php">
                    <?php
                        settings_fields( 'WooCommerce_Dropshippers' );
                        do_settings_sections( 'WooCommerce_Dropshippers' );
                    ?>
                    <?php submit_button(__("Save Settings",'woocommerce-dropshippers')); ?>
                </form>
            </div>
            <?php
        } // END public function plugin_settings_page()
    } // END class WooCommerce_Dropshippers_Settings
} // END if(!class_exists('WooCommerce_Dropshippers_Settings'))