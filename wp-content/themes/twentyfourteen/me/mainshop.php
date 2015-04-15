<?php
/*
Template Name: My Custom Page
*/


get_header(); ?>
<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri()."/me/css/style_me.css"; ?>"/>
<div id="main-content" class="main-content">

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
                    <?php echo do_shortcode( '[metaslider id=40]' );?>
                    <div class="inner-content">
                        <ul class="products">
                                <?php
                                        $args = array(
                                                'post_type' => 'product',
                                                'posts_per_page' => 12
                                                );
                                        $loop = new WP_Query( $args );
                                        if ( $loop->have_posts() ) {
                                                while ( $loop->have_posts() ) : $loop->the_post();
                                                        woocommerce_get_template_part( 'content', 'product' );
                                                endwhile;
                                        } else {
                                                echo __( 'No products found' );
                                        }
                                        wp_reset_postdata();
                                ?>
                        </ul><!--/.products-->
                    </div><!-- .inner-content -->
		</div><!-- #content -->
                <div class="me-right-sidebar">
                    <div class="inner-right-sidebar">
                      <?php 
                        if(is_active_sidebar('sidebar-2'))
                        {
                            dynamic_sidebar('sidebar-2');
                        }
                      ?> 
                     </div>   
                </div>
	</div><!-- #primary -->
	
</div><!-- #main-content -->

<?php

get_footer();
