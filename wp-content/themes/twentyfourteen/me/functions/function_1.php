<?php
function me_add_to_cart()
{
   
}
add_action("woocommerce_after_shop_loop_item","me_add_to_cart");
// Creating the widget 
class wpb_widget extends WP_Widget {

    function __construct() {
        parent::__construct(
        // Base ID of your widget
        'me_wp_show_nha_san_xuat', 
        // Widget name will appear in UI
        __('Show nha san xuat', 'me_wp_show'), 
        // Widget description
        array( 'description' => __( 'Show nha san xuat', 'me_wp_show' ), ) 
        );
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $title ) )
         echo $args['before_title'] . $title . $args['after_title'];
         //echo "So luong nha san xuat:".$instance['count'];
        //list terms in a given taxonomy using wp_list_categories  (also useful as a widget)
  
        $show_count = 0; // 1 for yes, 0 for no
        $pad_counts = 0; // 1 for yes, 0 for no
        $hierarchical = 1; // 1 for yes, 0 for no
        $taxonomy = 'loai-xe';
        $title = '';

        $args_cate = array(
          'show_count' => $show_count,
          'pad_counts' => $pad_counts,
          'hierarchical' => $hierarchical,
          'taxonomy' => $taxonomy,
          'title_li' => $title
        );
        ?>
        <ul>
        <?php
        wp_list_categories($args_cate);
        ?>
        </ul>
        <?php
        echo $args['after_widget'];
    }
		
// Widget Backend 
    public function form( $instance ) {
        if ( isset( $instance[ 'count' ] ) ) {
           $count = $instance[ 'count' ];
        }
        else {
           $count = 6;
        }
        if ( isset( $instance[ 'title' ] ) ) {
           $title = $instance[ 'title' ];
        }
        else {
           $title = "Widget title";
        }
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Tieu de:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'So Luong:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo esc_attr( $count ); ?>" />
        </p>
        <?php 
    }
	
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['count'] = ( ! empty( $new_instance['count'] ) ) ? strip_tags( $new_instance['count'] ) : 5;
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : "Widget title";
        return $instance;
    }
} // Class wpb_widget ends here

// Creating the widget 
class WidgetManufacturer extends WP_Widget {

    function __construct() {
        parent::__construct(
        // Base ID of your widget
        'me_wp_show_nha_san_xuat', 
        // Widget name will appear in UI
        __('Show nha san xuat', 'me_wp_show'), 
        // Widget description
        array( 'description' => __( 'Show nha san xuat', 'me_wp_show' ), ) 
        );
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $title ) )
         echo $args['before_title'] . $title . $args['after_title'];
         //echo "So luong nha san xuat:".$instance['count'];
        //list terms in a given taxonomy using wp_list_categories  (also useful as a widget)
  
        $show_count = 0; // 1 for yes, 0 for no
        $pad_counts = 0; // 1 for yes, 0 for no
        $hierarchical = 1; // 1 for yes, 0 for no
        $taxonomy = 'nha-san-xuat';
        $title = '';

        $args_cate = array(
          'show_count' => $show_count,
          'pad_counts' => $pad_counts,
          'hierarchical' => $hierarchical,
          'taxonomy' => $taxonomy,
          'title_li' => $title
        );
        ?>
        <ul>
        <?php
        wp_list_categories($args_cate);
        ?>
        </ul>
        <?php
        echo $args['after_widget'];
    }
		
// Widget Backend 
    public function form( $instance ) {
        if ( isset( $instance[ 'count' ] ) ) {
           $count = $instance[ 'count' ];
        }
        else {
           $count = 6;
        }
        if ( isset( $instance[ 'title' ] ) ) {
           $title = $instance[ 'title' ];
        }
        else {
           $title = "Widget title";
        }
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Tieu de:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'So Luong:' ); ?></label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" type="text" value="<?php echo esc_attr( $count ); ?>" />
        </p>
        <?php 
    }
	
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['count'] = ( ! empty( $new_instance['count'] ) ) ? strip_tags( $new_instance['count'] ) : 5;
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : "Widget title";
        return $instance;
    }
} // Class wpb_widget ends here


// Register and load the widget
function wpb_load_widget() {
	register_widget( 'wpb_widget' );
        register_widget( 'WidgetManufacturer' );
}
add_action( 'widgets_init', 'wpb_load_widget' );

function me_create_main_menu()
{
    ?>
     <div class="nav-menu">
         
     </div>
    <?php
}
function me_get_search_form()
{
    ?>
        <form role="search" method="get" class="search-form" action="http://localhost/wptest/">			
            <input type="search" class="search-field" placeholder="Search …" value="" name="s" title="Search for:">			
            <input type="submit" class="search-submit" value="">
	</form>
    <?php
}
function me_widget_add() {

	register_sidebar( array(
		'name'          => __( 'Near Footer Area', 'twentyfourteen' ),
		'id'            => 'sidebar-near-footer',
		'description'   => __( 'Appears in the near footer section of the site.', 'twentyfourteen' ),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h1 class="widget-title">',
		'after_title'   => '</h1>',
	) );
}
add_action( 'widgets_init', 'me_widget_add' );

add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

function add_resource_single_product()
{
    if(is_product())
    {
        wp_enqueue_style( 'single-product', get_template_directory_uri() . '/me/css/single-product.css');
    }
    wp_enqueue_style( 'global-product', get_template_directory_uri() . '/me/css/globalme.css');
    if(is_cart())
    {
        wp_enqueue_style( 'cart', get_template_directory_uri() . '/me/css/cart.css');
    }
    if(is_checkout())
    {
        wp_enqueue_style( 'checkout', get_template_directory_uri() . '/me/css/checkout.css');
    }
    if(is_page_template('me/mainshop.php')|| is_tax('nha-san-xuat') || is_tax('loai-xe') )
    {
        wp_enqueue_style( 'mainshop', get_template_directory_uri() . '/me/css/style_me.css');
    }
}
add_action('wp_enqueue_scripts','add_resource_single_product');

function custom_override_checkout_fields( $fields ) {
     unset($fields['billing']['billing_country']);
     unset($fields['billing']['billing_company']);
     unset($fields['billing']['billing_address_2']);
     unset($fields['billing']['billing_state']);
     unset($fields['billing']['billing_postcode']);
     unset($fields['shipping']);
     return $fields;
}
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );
?>
