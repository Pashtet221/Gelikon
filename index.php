<?php get_header(); ?>
<main id="primary" class="site-main">
    <div class="gl-container gl-page">
        <?php if (have_posts()) : ?>
            <?php
            $post_category_tabs = get_categories([
                'hide_empty' => true,
                'orderby'    => 'name',
                'order'      => 'ASC',
            ]);
            ?>
            <?php if (!empty($post_category_tabs)) : ?>
                <div class="gl-post-category-tabs" data-gl-post-category-tabs>
                    <div class="gl-post-category-tabs__list" role="tablist" aria-label="<?php esc_attr_e('Рубрики записей', 'gelikon'); ?>">
                        <button class="gl-post-category-tabs__button is-active" type="button" role="tab" aria-selected="true" data-gl-post-category-tab="all">
                            <?php esc_html_e('Все', 'gelikon'); ?>
                        </button>
                        <?php foreach ($post_category_tabs as $post_category_tab) : ?>
                            <button class="gl-post-category-tabs__button" type="button" role="tab" aria-selected="false" data-gl-post-category-tab="<?php echo esc_attr($post_category_tab->slug); ?>">
                                <?php echo esc_html($post_category_tab->name); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="gl-posts-grid" data-gl-posts-grid>
                <?php while (have_posts()) : the_post(); ?>
                    <?php
                    $post_categories = get_the_category();
                    $post_category_slugs = wp_list_pluck($post_categories, 'slug');
                    ?>
                    <article <?php post_class('gl-card gl-post-card'); ?> data-gl-post-categories="<?php echo esc_attr(implode(' ', $post_category_slugs)); ?>">
                        <?php if (has_post_thumbnail()) : ?>
                            <a class="gl-post-card__thumb" href="<?php the_permalink(); ?>"><?php the_post_thumbnail('gelikon-card'); ?></a>
                        <?php endif; ?>
                        <div class="gl-post-card__content">
                            <?php if (!empty($post_categories)) : ?>
                                <div class="gl-post-card__categories">
                                    <?php gelikon_render_post_category_links($post_categories); ?>
                                </div>
                            <?php endif; ?>
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 24)); ?></p>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <article class="gl-card gl-empty-state"><p><?php esc_html_e('Пока нет записей.', 'gelikon'); ?></p></article>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
