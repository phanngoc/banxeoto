<?php
/**
 * The template for displaying the footer
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
?>

		</div><!-- #main -->

		<footer id="colophon" class="site-footer" role="contentinfo">

			<?php get_sidebar( 'footer' ); ?>

			<div class="site-info">
				<div class="first-column columns medium-4 large-3 dropclick">
                                    <div class="title">Tư vấn &amp; Hỗ trợ <i class="icon-plus"></i></div>
                                    <div class="contents">
                                       <p>Các chuyên gia của chúng tôi đang ở đây để trả lời câu hỏi của bạn, giúp bạn khắc phục các sự cố và mang đến cho bạn nhiều trải nghiệm mới.</p>
                                       <a class="button secondary flat expand" href="/contact/"> <i class="icon-mail"></i> Liên hệ với chúng tôi </a> 
                                    </div>
                                </div>
                                <div class="second-column columns medium-4 large-3 dropclick">
                                   <div class="title">Chính sách bảo mật <i class="icon-plus"></i></div>
                                   <div class="contents">
                                      <p class="important">Chúng tôi quan tâm đến sự an toàn của bạn</p>
                                      <ul>
                                         <li>128-bit SSL mã hóa</li>
                                         <li>Chương trình xác thực người bán</li>
                                         <li>Chính sách bảo vệ dữ liệu tốt nhất</li>
                                      </ul>
                                   </div>
                                </div>
                                <div class="third-column columns medium-4 large-3 dropclick">
                                   <div class="title">Giới thiệu <i class="icon-plus"></i></div>
                                   <div class="contents">
                                      <ul>
                                         <li class="icon-angle-right"><a href="/about-us/">Về Carmudi Việt Nam</a></li>
                                         <li class="icon-angle-right"><a href="/terms-and-conditions/">Điều khoản</a></li>
                                         <li class="icon-angle-right"><a href="/privacy-policy/">Chính sách bảo mật</a></li>
                                         <li class="icon-angle-right"><a href="/faq/">Câu hỏi thường gặp</a></li>
                                         <li class="icon-angle-right"><a href="/copyrights/">Bản quyền</a></li>
                                         <li class="icon-angle-right"><a href="/index/sitemap/">Sơ đồ trang web</a></li>
                                      </ul>
                                   </div>
                                </div>
			</div><!-- .site-info -->
		</footer><!-- #colophon -->
	</div><!-- #page -->

	<?php wp_footer(); ?>
</body>
</html>