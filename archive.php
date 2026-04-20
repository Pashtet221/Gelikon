<?php get_header(); ?>
<main id="primary" class="site-main">
    <div class="gl-container gl-page">
        <header class="gl-archive-head gl-card">
            <h1><?php the_archive_title(); ?></h1>
            <?php the_archive_description('<div class="gl-archive-desc">', '</div>'); ?>
        </header>
        <?php if (have_posts()) : ?>
            <div class="gl-posts-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <article <?php post_class('gl-card gl-post-card'); ?>>
                        <?php if (has_post_thumbnail()) : ?>
                            <a class="gl-post-card__thumb" href="<?php the_permalink(); ?>"><?php the_post_thumbnail('gelikon-card'); ?></a>
                        <?php endif; ?>
                        <div class="gl-post-card__content">
                            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 20)); ?></p>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination(); ?>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
