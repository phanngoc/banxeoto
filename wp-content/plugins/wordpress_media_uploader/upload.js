$j = jQuery.noConflict();
var current_input;
jQuery(document).ready(function() {
 
 /* user clicks button on custom field, runs below code that opens new window */
    $j('.ink_image').on('click.me','.upload_image_me',function(e) {
       // e.preventDefault();
          current_input = jQuery(this).prev();
        /* Thickbox function aimed to show the media window. This function accepts three parameters:
         * 
         * Name of the window: "In our case Upload a Image"
         * URL : Executes a WordPress library that handles and validates files.
         * ImageGroup : As we are not going to work with groups of images but just with one that why we set it false.
         */
         tb_show('Upload a Image', 'media-upload.php?referer=media_page&type=image&TB_iframe=true&post_id=0', false);	    
    });


    // window.send_to_editor(html) is how WP would normally handle the received data. It will deliver image data in HTML format, so you can put them wherever you want.
	
    window.send_to_editor = function(html) {
        console.log(html);
        var image_url = jQuery('img', html).attr('src');
        current_input.val(image_url);
        tb_remove(); // calls the tb_remove() of the Thickbox plugin 
        //$j('#submit_button').trigger('click');
    }
    jQuery('.addnew').click(function(e){
        e.preventDefault();
        jQuery("fieldset").last().after('<fieldset><label for="path">Short code</label><input type="text" name="short[]"/><input type="text" name="path[]" class="image_path" value="" ><input type="button"  value="Upload Image" class="upload_image_me"/> Upload your Image from here.</fieldset>');
    });
});