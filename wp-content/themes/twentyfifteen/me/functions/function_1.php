<?php
function add_output_before_function()
{
    echo "Add output before main";
}
add_action('woocommerce_before_main_content','add_output_before_function');