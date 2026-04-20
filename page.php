<?php get_header(); ?>
<main id="primary" class="site-main">
    <div class="gl-container gl-page-single">
        <?php while (have_posts()) : the_post(); ?>
            <article <?php post_class('gl-card gl-entry'); ?>>
                <header class="gl-entry__header">
                    <h1><?php the_title(); ?></h1>
                </header>
                <div class="gl-entry__content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
</main>
<?php get_footer(); ?>
