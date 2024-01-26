<?php
/*
Plugin Name: Masonry Post Grid with Gallery View
Description: Enhance your website with a Masonry Post grid featuring a gallery view. [grid] [grid include_categories="category1,category2"]
Version: 1.0
Author: Hassan Naqvi
*/

// Enqueue styles and scripts
function wp_masonry_grid_enqueue_scripts() {
    // Enqueue styles
    wp_enqueue_style('component-style', plugins_url('css/grid_style.css', __FILE__));

    // Enqueue scripts
    wp_enqueue_script('modernizr-script', plugins_url('js/modernizr.custom.js', __FILE__), array(), null, true);
    wp_enqueue_script('imagesloaded-script', plugins_url('js/imagesloaded.pkgd.min.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_script('masonry-script', plugins_url('js/masonry.pkgd.min.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_script('classie-script', plugins_url('js/classie.js', __FILE__), array(), null, true);
    wp_enqueue_script('tilt-script', plugins_url('tilt/tilt.jquery.js', __FILE__), array('jquery'), null, true);
    wp_enqueue_script('cbpGridGallery-script', plugins_url('js/cbpGridGallery.js', __FILE__), array('jquery', 'masonry-script', 'imagesloaded-script', 'classie-script', 'tilt-script'), null, true);

    // Enqueue inline script for initializing the gallery and Tilt.js
    wp_add_inline_script('cbpGridGallery-script', '
        jQuery(document).ready(function($) {
            new CBPGridGallery(document.getElementById(\'grid-gallery\'));
            
            // Initialize Tilt.js on each post figure
            $(".grid-gallery figure").tilt({
                maxTilt:        20,
                perspective:    1000,
                easing:         "cubic-bezier(.03,.98,.52,.99)",
                scale:          1.02,
                speed:          300,
                transition:     true,
                reset:          true,
                glare:          true,
                maxGlare:       0.5
            });
        });
    ', 'after');
}

add_action('wp_enqueue_scripts', 'wp_masonry_grid_enqueue_scripts');

// Function to generate the grid HTML
function generate_grid_html($atts) {
    // Check if we are in the Elementor editor
    if (\Elementor\Plugin::instance()->editor->is_edit_mode()) {
        return ''; // Return an empty string to hide the shortcode in Elementor editor
    }

    // Shortcode attribute for including categories
    $atts = shortcode_atts(
        array(
            'include_categories' => '', // Comma-separated list of category slugs to include
        ),
        $atts,
        'grid'
    );

    // Convert comma-separated category slugs to an array
    $include_categories = explode(',', $atts['include_categories']);

    // Get the latest WordPress posts with category filtering
    $args = array(
        'posts_per_page' => -1,
        'orderby' => 'post_date',
        'order' => 'DESC',
        'category_name' => implode(',', $include_categories), // Include specific categories
    );

    $latest_post = new WP_Query($args);

    // Check if there's a post
    if ($latest_post->have_posts()) :
        ob_start(); // Start output buffering
        ?>
        <div class="container-gl">
            <div id="grid-gallery" class="grid-gallery">
                <section class="grid-wrap">
                    <ul class="grid">
                        <li class="grid-sizer"></li><!-- for Masonry column width -->
                        <?php
                        while ($latest_post->have_posts()) : $latest_post->the_post();
                            // Get unique post ID
                            $post_id = get_the_ID();
                        ?>
                            <!-- Add a unique ID with li class for the post -->
                            <li id="post-<?php echo $post_id; ?>" class="post">
                                <figure>
                                    <?php
                                    // Display post thumbnail if available
                                    if (has_post_thumbnail()) :
                                        the_post_thumbnail('large', array('alt' => get_the_title(), 'class' => 'full'));
                                    endif;
                                    ?>
                                    <figcaption>
                                        <h2 class="grid-title"><?php the_title(); ?></h2>
                                        <p><?php echo wp_trim_words(get_the_excerpt(), 10); ?></p>
                                    </figcaption>
                                </figure>
                            </li>
                        <?php
                        endwhile;
                        wp_reset_postdata(); // Reset the query
                        ?>
                    </ul>
                </section><!-- // grid-wrap -->

                <section class="slideshow">
                    <ul>
                        <?php
                        // Rewind the query to start from the beginning
                        $latest_post->rewind_posts();

                        while ($latest_post->have_posts()) : $latest_post->the_post();
                            // Get unique post ID
                            $post_id = get_the_ID();
                        ?>
                            <!-- Add a unique ID with li class for the post -->
                            <li id="post-<?php echo $post_id; ?>" class="post">
                                <figure>
                                    <figcaption>
                                        <h3 class="slide-title"> <a href="<?php the_permalink(); ?>" class="read-more"><?php the_title(); ?></a></h3>
                                        <p>
                                            <?php
                                            echo wp_trim_words(get_the_excerpt(), 24);
                                            echo ' <a href="' . get_permalink() . '" class="read-more-link"> More</a>';
                                            ?>
                                        </p>
                                    </figcaption>
                                    <?php
                                    // Display post thumbnail if available
                                    if (has_post_thumbnail()) :
                                        the_post_thumbnail('large', array('alt' => get_the_title(), 'class' => 'full'));
                                    endif;
                                    ?>
                                </figure>
                            </li>
                        <?php
                        endwhile;
                        wp_reset_postdata(); // Reset the query
                        ?>
                    </ul>
                    <nav>
                        <span class="icon nav-prev"></span>
                        <span class="icon nav-next"></span>
                        <span class="icon nav-close"></span>
                    </nav>
                    <div class="info-keys icon">Navigate with arrow keys</div>
                </section><!-- // slideshow -->
            </div><!-- // grid-gallery -->
        </div><!-- // container-gl -->
        <?php
        return ob_get_clean();
    endif;

    return ''; // Return an empty string if no posts are found
}

// Shortcode for displaying the grid
function grid_shortcode($atts) {
    return generate_grid_html($atts);
}
add_shortcode('grid', 'grid_shortcode');


// Display the settings page
function masonry_post_grid_settings_page() {
    ?>
    <div class="wrap">
        <h1>Masonry Post Grid With Tilt Effect Settings</h1>
        <p>Welcome to the Masonry Post Grid plugin settings page.</p>
        <h2>How to Use Shortcode</h2>
        <p>To display the Masonry Post Grid on your site, you can use the following shortcode:</p>
        <pre>Use this Code for showing All Posts. [grid]</pre>
        
        <p>You can customize the shortcode attributes based on your requirements.</p>
        <pre>[grid include_categories="category1,category2"]</pre>
        
    </div>
    <?php
}

// Add the settings page to the admin menu
function masonry_post_grid_menu() {
    add_options_page(
        'Masonry Post Grid Settings',
        'Masonry Post Grid',
        'manage_options',
        'masonry-post-grid-settings',
        'masonry_post_grid_settings_page'
    );
}

add_action('admin_menu', 'masonry_post_grid_menu');

// Add shortcode generator to the admin menu (you can implement this part as needed)
function masonry_post_grid_shortcode_generator() {
    // Add your shortcode generator code here
}

add_action('admin_menu', 'masonry_post_grid_shortcode_generator');

// Define the shortcode for Masonry Post Grid
function masonry_post_grid_shortcode($atts) {
    // Shortcode attributes
    $atts = shortcode_atts(
        array(
            'include_categories' => '', // Comma-separated list of category slugs to include
        ),
        $atts,
        'masonry_post_grid'
    );

    // Add your shortcode handling code here, using $atts['include_categories'] to filter categories
    // Generate and return the Masonry Post Grid HTML
}

add_shortcode('masonry_post_grid', 'masonry_post_grid_shortcode');

// Add a settings link on the Plugins page
function masonry_post_grid_settings_link($links) {
    $settings_link = '<a href="options-general.php?page=masonry-post-grid-settings">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'masonry_post_grid_settings_link');
