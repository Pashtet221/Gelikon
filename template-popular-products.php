<?php
/**
 * Template Name: Популярные товары
 * Template Post Type: page
 */

defined('ABSPATH') || exit;

get_header();

$home_page_id = function_exists('gelikon_get_home_page_id') ? gelikon_get_home_page_id() : 0;
$products     = function_exists('gelikon_get_home_popular_products') ? gelikon_get_home_popular_products($home_page_id) : [];
$title        = get_the_title();

if (function_exists('get_field') && $home_page_id) {
	$title = get_field('home_products_title', $home_page_id) ?: $title;
}
?>
<main id="primary" class="site-main gl-popular-products-page">
	<div class="gl-container">
		<header class="gl-popular-products-page__header">
			<h1><?php echo esc_html($title ?: __('Популярные товары', 'gelikon')); ?></h1>
		</header>

		<?php if ($products) : ?>
			<ul class="products columns-4 gl-popular-products-page__grid">
				<?php foreach ($products as $popular_product) :
					$post_object = get_post($popular_product->get_id());
					if (!$post_object) {
						continue;
					}

					$GLOBALS['post']    = $post_object;
					$GLOBALS['product'] = $popular_product;
					setup_postdata($post_object);
					wc_get_template_part('content', 'product');
				endforeach; ?>
			</ul>
			<?php wp_reset_postdata(); ?>
		<?php else : ?>
			<div class="gl-card gl-popular-products-page__empty">
				<p><?php esc_html_e('Популярные товары пока не выбраны.', 'gelikon'); ?></p>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
