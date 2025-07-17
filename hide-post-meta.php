<?php
/*
Plugin Name: Hide Post Metadata & Customize UI
Plugin URI: https://devdinos.com
Description: A simple plugin to hide post metadata like date, author, categories, and excerpt, with options to customize post UI and more.
Version: 1.3
Author: Shahid Asghar
Author URI: https://devdinos.com
*/

// Add a settings page to the admin dashboard
function hpmc_add_settings_page() {
    add_options_page(
        'Hide Post Metadata Settings', // Page title
        'Hide Post Metadata', // Menu title
        'manage_options', // Capability required
        'hide-post-meta', // Menu slug
        'hpmc_settings_page' // Callback function
    );
}
add_action('admin_menu', 'hpmc_add_settings_page');

// Settings page HTML
function hpmc_settings_page() {
    ?>
    <div class="wrap">
        <h1>Hide Post Metadata, Customize UI & Disable Comments</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('hpmc_options_group');
            do_settings_sections('hpmc_options_group');
            ?>
            
            <table class="form-table">
                <!-- Hide Post Metadata -->
                <tr valign="top">
                    <th scope="row">Hide Post Metadata</th>
                    <td>
                        <label for="hide_author">
                            <input type="checkbox" name="hide_author" id="hide_author" value="1" <?php checked( get_option('hide_author'), 1 ); ?> />
                            Hide Author Name
                        </label><br />
                        <label for="hide_date">
                            <input type="checkbox" name="hide_date" id="hide_date" value="1" <?php checked( get_option('hide_date'), 1 ); ?> />
                            Hide Post Date
                        </label><br />
                        <label for="hide_categories">
                            <input type="checkbox" name="hide_categories" id="hide_categories" value="1" <?php checked( get_option('hide_categories'), 1 ); ?> />
                            Hide Categories
                        </label><br />
                        <label for="hide_excerpt">
                            <input type="checkbox" name="hide_excerpt" id="hide_excerpt" value="1" <?php checked( get_option('hide_excerpt'), 1 ); ?> />
                            Hide Excerpt
                        </label>
                    </td>
                </tr>

                <!-- Other Settings -->
                <!-- Your other settings go here... -->

            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register settings
function hpmc_register_settings() {
    register_setting('hpmc_options_group', 'hide_author');
    register_setting('hpmc_options_group', 'hide_date');
    register_setting('hpmc_options_group', 'hide_categories');
    register_setting('hpmc_options_group', 'hide_excerpt');
    // Register other settings...
}
add_action('admin_init', 'hpmc_register_settings');

// Remove metadata hooks for author, date, categories, and excerpt
function hpmc_remove_post_metadata() {
    if (is_single() && 'post' === get_post_type()) {
        
        // Hide author
        if (get_option('hide_author') === '1') {
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
            add_filter('the_author', '__return_empty_string');
        }

        // Hide date
        if (get_option('hide_date') === '1') {
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
            add_filter('the_date', '__return_empty_string');
        }

        // Hide categories
        if (get_option('hide_categories') === '1') {
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
            add_filter('the_category', '__return_empty_string');
        }

        // Hide excerpt
        if (get_option('hide_excerpt') === '1') {
            remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
            add_filter('the_excerpt', '__return_empty_string');
        }
    }
}
add_action('wp', 'hpmc_remove_post_metadata');

// Modify post content based on settings
function hpmc_modify_post_content($content) {
    if (is_single() && 'post' === get_post_type()) {

        // Check settings and remove metadata if needed
        if (get_option('hide_author') === '1') {
            $content = preg_replace('/<span class="author">.*?<\/span>/', '', $content);
        }
        if (get_option('hide_date') === '1') {
            $content = preg_replace('/<span class="posted-on">.*?<\/span>/', '', $content);
        }
        if (get_option('hide_categories') === '1') {
            $content = preg_replace('/<span class="cat-links">.*?<\/span>/', '', $content);
        }
        if (get_option('hide_excerpt') === '1') {
            $content = preg_replace('/<div class="post-excerpt">.*?<\/div>/', '', $content);
        }
    }

    return $content;
}
add_filter('the_content', 'hpmc_modify_post_content');

// Modify post UI based on settings
function hpmc_modify_post_ui() {
    if (is_single() && 'post' === get_post_type()) {
        $bg_color = get_option('post_bg_color', '#ffffff');
        $font_color = get_option('post_font_color', '#000000');
        $layout = get_option('post_layout', 'full-width');
        $font_size = get_option('post_title_font_size', '36');
        $font_family = get_option('post_title_font_family', 'Arial, sans-serif');
        $custom_font_size = get_option('custom_font_size', '16');
        $custom_margin = get_option('custom_margin', '10');
        $custom_padding = get_option('custom_padding', '20');
        $custom_css = get_option('post_custom_css');
        
        // Post styling
        echo '<style>
            .single-post {
                background-color: ' . esc_attr($bg_color) . ';
                color: ' . esc_attr($font_color) . ';
                width: 100%;
                padding: 20px;
            }
            .single-post.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            .single-post.list { display: block; }

            .single-post .entry-title {
                font-size: ' . esc_attr($font_size) . 'px;
                font-family: ' . esc_attr($font_family) . ';
            }

            .single-post {
                font-size: ' . esc_attr($custom_font_size) . 'px;
                margin: ' . esc_attr($custom_margin) . 'px;
                padding: ' . esc_attr($custom_padding) . 'px;
            }
            ' . esc_html($custom_css) . '
        </style>';

        // Apply the layout class to the post
        if ($layout !== 'full-width') {
            echo "<script>document.querySelector('.single-post').classList.add('$layout');</script>";
        }
    }
}
add_action('wp_head', 'hpmc_modify_post_ui');

// Disable comments based on settings
function hpmc_disable_comments() {
    if (is_single() && 'post' === get_post_type()) {
        if (get_option('disable_comments') === '1') {
            // Disable comments for posts
            remove_post_type_support('post', 'comments');
            
            // Optionally, hide comments section from the frontend with CSS
            echo '<style>
                .comments-area, .comment-respond, #respond { display: none !important; }
            </style>';
            
            // Redirect if someone tries to access the comment section
            if (isset($_GET['comment']) && is_single()) {
                wp_redirect(get_permalink());
                exit;
            }
        }
    }
}
add_action('wp', 'hpmc_disable_comments');

// Add social sharing buttons to post content
function hpmc_add_social_sharing_buttons($content) {
    if (is_single() && 'post' === get_post_type()) {
        $content .= '<div class="social-sharing">
            <a href="https://www.facebook.com/sharer/sharer.php?u=' . get_permalink() . '" target="_blank">Share on Facebook</a> |
            <a href="https://twitter.com/intent/tweet?url=' . get_permalink() . '" target="_blank">Share on Twitter</a>
        </div>';
    }
    return $content;
}
add_filter('the_content', 'hpmc_add_social_sharing_buttons');
