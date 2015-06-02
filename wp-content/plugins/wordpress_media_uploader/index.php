<?php

/*
  Plugin Name: WordPress Media Uploader
  Plugin URI: http://inkthemes.com/
  Description: This plugin is basically focused on uploading images with WordPress Media Uploader. This plugin will guide you on how to integrate WordPress Media Uploader in option panel whether it is of theme or plugin.
  Version: 1.0.0
  Author: InkThemes
  Author URI: http://www.inkthemes.com/
*/

/**
 * We enclose all our functions in a class.
 * Main Class - WPMU stands for WordPress Media Uploader.
 */
if(!function_exists('_log')){
  function _log( $message ) {
    if( WP_DEBUG === true ){
      if( is_array( $message ) || is_object( $message ) ){
        error_log( print_r( $message, true ) );
      } else {
        error_log( $message );
      }
    }
  }
}

Class WPMU {
    /* --------------------------------------------*
     * Attributes
     * -------------------------------------------- */

    /** Refers to a single instance of this class. */
    private static $instance = null;

    /* Saved options */
    public $options;

    /* --------------------------------------------*
     * Constructor
     * -------------------------------------------- */

    /**
     * Creates or returns an instance of this class.
     *
     * @return  WPMU_Theme_Options A single instance of this class.
     */
    public static function get_instance() {

        if (null == self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

// end get_instance;

    /**
     * Initialize the plugin by setting localization, filters, and administration functions.
     */
    private function __construct() {
        // Add the page to the admin menu.
        add_action('admin_menu', array(&$this, 'ink_menu_page'));

        // Register javascript.
        add_action('admin_enqueue_scripts', array(&$this, 'enqueue_admin_js'));
        
        // Add function on admin initalization.
        add_action('admin_init', array(&$this, 'ink_options_setup'));
        
        // Call Function to store value into database.
        add_action('init', array(&$this, 'store_in_database'));
        
        // Call Function to delete image.
        add_action('init', array(&$this, 'delete_image'));
        
        // Add CSS rule.
        add_action('admin_enqueue_scripts', array(&$this, 'add_stylesheet'));
    }

    /* --------------------------------------------*
     * Functions
     * -------------------------------------------- */

    /**
     * Function will add option page under Appearance Menu.
     */
    public function ink_menu_page() {
        add_theme_page('media_uploader', 'Media Uploader', 'edit_theme_options', 'media_page', array($this, 'media_uploader'));
    }

    
    //Function that will display the options page.
     
    public function media_uploader() {
        global $wpdb;
        $img_path = unserialize(get_option($this->key_img));
        $shortcodes = unserialize(get_option($this->key_short));
        ?>  
        
	<form class="ink_image" method="post" action="#">
            <h2> <b>Image setting</b> </h2>
            <fieldset>
                <label for="path">Short code</label>
                <input type="text" name="short[]"/>
                <input type="text" name="path[]" class="image_path">
                <input type="button"  value="Upload Image" class="upload_image_me"/> Upload your Image from here.
            </fieldset>
            <button class="addnew">Add new</button>
            <div id="show_upload_preview">  
                <?php  
                   if(! empty($img_path)&&  is_array($img_path)){
                       foreach ($img_path as $key => $value) {
                           ?>
                            <div class="cup">
                                <img src="<?php echo $value ;?>">
                                <p>Shortcode:<?php echo $shortcodes[$key];?></p>
                                <input type="submit"  name="remove" value="Remove Image<?php echo $key;?>" class="button-secondary" id="remove_image"/>
                            </div>
                   <?php  
                       } 
                   }
                ?>
            </div>      
            <style type="text/css">
                .cup{
                    display : block;
                    width : 300px;
                    height : 300px;
                    float : left;
                }
                .cup img{
                    display: block;
                    width : 200px;
                    height : 200px;
                    margin : 0px auto;
                }
            </style>
            <input type="submit" name="submit" class="save_path button-primary" id="submit_button" value="Save Setting">
           
        </form>
        <?php
    }

//Call three JavaScript library (jquery, media-upload and thickbox) and one CSS for thickbox in the admin head.

    public function enqueue_admin_js() {
        wp_enqueue_script('media-upload'); //Provides all the functions needed to upload, validate and give format to files.
        wp_enqueue_script('thickbox');   //Responsible for managing the modal window.
        wp_enqueue_style('thickbox');    //Provides the styles needed for this window.
        wp_enqueue_script('script', plugins_url('upload.js', __FILE__), array('jquery'), '', true); //It will initialize the parameters needed to show the window properly.
    }
    
    //Function that will add stylesheet file.
    public function add_stylesheet(){
      wp_enqueue_style( 'stylesheet', plugins_url( 'stylesheet.css', __FILE__ ));    
    }
    
// Here it check the pages that we are working on are the ones used by the Media Uploader.    
  public function ink_options_setup() {
    global $pagenow;
    if ('media-upload.php' == $pagenow || 'async-upload.php' == $pagenow) {
       // Now we will replace the 'Insert into Post Button inside Thickbox' 
        add_filter('gettext', array($this, 'replace_window_text'), 1, 2);
        // gettext filter and every sentence.
    }
}

/*
 * Referer parameter in our script file is for to know from which page we are launching the Media Uploader as we want to change the text "Insert into Post". 
 */
function replace_window_text($translated_text, $text) {
    if ('Insert into Post' == $text) {
        $referer = strpos(wp_get_referer(), 'media_page');
        if ($referer != '') {
            return __('Upload Image', 'ink');
        }
    }
    return $translated_text;
}
private $key_img = 'ink_image';
private $key_short = 'ink_shortcode';
// The Function store image path in option table.
public function store_in_database(){
    
    if(isset($_POST['submit'])){
        $image_path = array_diff($_POST['path'],array(''));
        $short_code = array_diff($_POST['short'],array(''));
     
        if(!empty($image_path)&& !empty($short_code))
        {
            _log("co vao");
            $this->store_me($image_path,$this->key_img);
            $this->store_me($short_code,$this->key_short);
            
        }
    }
    $short_code = unserialize(get_option($this->key_short));
    foreach ($short_code as $key => $value) {
                add_shortcode('imgtag', array(&$this,'add_short'));
    }
}
public function add_short( $atts ) {
	$tag = $atts['tag'];
        $data_path = unserialize(get_option($this->key_short));
        $img_path = unserialize(get_option($this->key_img));
        if(is_array($data_path)&&is_array($img_path))
        {
            foreach ($data_path as $key => $value) {
               if($value==$tag)
               {
                  echo '<img src="'.$img_path[$key].'" />';
               }
            }
        }    
}

public function store_me($data,$option)
{
        $data_path = unserialize(get_option($option));
        if(!$data_path) $data_path = array();

        if(is_array($data))
        {
            foreach ($data as $key => $value) {
                array_push($data_path,$value);
            }
        }
        update_option($option,  serialize($data_path));
}
// Below Function will delete image.
function delete_image() {
    if(isset($_POST['remove'])){
        _log($_POST['remove']);
        $post = $_POST['remove'];
        $id = substr($post,strlen($post)-1, 1);
        _log($id);
        $data_path = unserialize(get_option($this->key_short));
        $img_path = unserialize(get_option($this->key_img));
        
        global $wpdb;
    
        $img_src = $img_path[(int)$id];
        // We need to get the images meta ID.
        $query = "SELECT ID FROM wp_posts where guid = '" . esc_url($img_src) . "' AND post_type = 'attachment'";
        $results = $wpdb->get_results($query);

        // And delete it
        foreach ( $results as $row ) {
            wp_delete_attachment( $row->ID ); //delete the image and also delete the attachment from the Media Library.
        }
        if(is_array($data_path)&&is_array($img_path))
        {
           array_splice($data_path, (int)$id, 1);
           array_splice($img_path, (int)$id, 1);
           update_option($this->key_img, serialize($img_path));
           update_option($this->key_short, serialize($data_path));
        }
    }  
}

}
// End class

WPMU::get_instance();
?>