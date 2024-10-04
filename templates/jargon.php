<?php
/*
Template Name: Jargon Pages
File Info /wp-content/plugins/jargon-jugg/templates/jargon.php
*/
?>


get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header>

            <div class="entry-content">
                <?php
                // Output the content of the page
                the_content();
                ?>
            </div>
        </article>
    </main>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
