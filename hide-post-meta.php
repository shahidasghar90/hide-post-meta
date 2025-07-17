<?php
/*
Plugin Name: Hide Post Metadata & Customize UI
Plugin URI: https://devdinos.com
Description: A simple plugin to hide post metadata like date, author, categories, excerpt, tags, and social sharing buttons, with options to customize post UI and more.
Version: 1.5
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
                <!-- Customize Post UI -->
                <tr valign="top">
                    <th scope="row">Customize Post UI</th>
                    <td>
                        <label for="post_bg_color">Post Background Color:</label><br />
                        <input type="text" name="post_bg_color" id="post_bg_color" value="<?php echo esc_attr( get_option('post_bg_color') ); ?>" class="color-picker" /><br />
                        <label for="post_font_color">Post Font Color:</label><br />
                        <input type="text" name="post_font_color" id="post_font_color" value="<?php echo esc_attr( get_option('post_font_color') ); ?>" class="color-picker" />
                    </td>
                </tr>

                <!-- Disable Comments -->
                <tr valign="top">
                    <th scope="row">Disable Comments</th>
                    <td>
                        <label for="disable_comments">
                            <input type="checkbox" name="disable_comments" id="disable_comments" value="1" <?php checked( get_option('disable_comments'), 1 ); ?> />
                            Disable Comments on Posts
                        </label>
                    </td>
                </tr>

                <!-- Hide Social Sharing -->
                <tr valign="top">
                    <th scope="row">Hide Social Sharing Buttons</th>
                    <td>
                        <label for="disable_social_sharing">
                            <input type="checkbox" name="disable_social_sharing" id="disable_social_sharing" value="1" <?php checked( get_option('disable_social_sharing'), 1 ); ?> />
                            Disable Social Sharing on Posts
                        </label>
                    </td>
                </tr>

            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Register settings
function hpmc_register_settings() {
    register_setting('hpmc_options_group', 'post_bg_color');
    register_setting('hpmc_options_group', 'post_font_color');
    register_setting('hpmc_options_group', 'disable_comments');
    register_setting('hpmc_options_group', 'disable_social_sharing');
}
add_action('admin_init', 'hpmc_register_settings');

// Add custom fields for hiding post metadata in post editor
function hpmc_add_post_metadata_options() {
    add_meta_box(
        'hpmc_post_metadata', // ID
        'Hide Post Metadata', // Title
        'hpmc_post_metadata_callback', // Callback function
        'post', // Screen (post type)
        'side', // Context
        'high' // Priority
    );
}
add_action('add_meta_boxes', 'hpmc_add_post_metadata_options');

// Callback function for post metadata options
function hpmc_post_metadata_callback($post) {
    // Retrieve stored metadata
    $hide_author = get_post_meta($post->ID, '_hide_author', true);
    $hide_date = get_post_meta($post->ID, '_hide_date', true);
    $hide_categories = get_post_meta($post->ID, '_hide_categories', true);
    $hide_excerpt = get_post_meta($post->ID, '_hide_excerpt', true);

    // Display checkbox fields for hiding metadata
    ?>
    <label for="hide_author">
        <input type="checkbox" name="hide_author" id="hide_author" value="1" <?php checked($hide_author, '1'); ?> />
        Hide Author
    </label><br />

    <label for="hide_date">
        <input type="checkbox" name="hide_date" id="hide_date" value="1" <?php checked($hide_date, '1'); ?> />
        Hide Date
    </label><br />

    <label for="hide_categories">
        <input type="checkbox" name="hide_categories" id="hide_categories" value="1" <?php checked($hide_categories, '1'); ?> />
        Hide Categories
    </label><br />

    <label for="hide_excerpt">
        <input type="checkbox" name="hide_excerpt" id="hide_excerpt" value="1" <?php checked($hide_excerpt, '1'); ?> />
        Hide Excerpt
    </label><br />
    <?php
}

// Save custom field data when post is saved
function hpmc_save_post_metadata($post_id) {
    // Check if we are saving a post
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $post_id;

    // Save metadata settings
    if (isset($_POST['hide_author'])) {
        update_post_meta($post_id, '_hide_author', '1');
    } else {
        delete_post_meta($post_id, '_hide_author');
    }

    if (isset($_POST['hide_date'])) {
        update_post_meta($post_id, '_hide_date', '1');
    } else {
        delete_post_meta($post_id, '_hide_date');
    }

    if (isset($_POST['hide_categories'])) {
        update_post_meta($post_id, '_hide_categories', '1');
    } else {
        delete_post_meta($post_id, '_hide_categories');
    }

    if (isset($_POST['hide_excerpt'])) {
        update_post_meta($post_id, '_hide_excerpt', '1');
    } else {
        delete_post_meta($post_id, '_hide_excerpt');
    }
}
add_action('save_post', 'hpmc_save_post_metadata');

// Modify post content based on post-specific settings (Front-end CSS)
function hpmc_modify_post_ui() {
    if (is_single() && 'post' === get_post_type()) {
        global $post;

        // Get post-specific metadata settings
        $hide_author = get_post_meta($post->ID, '_hide_author', true);
        $hide_date = get_post_meta($post->ID, '_hide_date', true);
        $hide_categories = get_post_meta($post->ID, '_hide_categories', true);
        $hide_excerpt = get_post_meta($post->ID, '_hide_excerpt', true);

        // Add CSS to hide metadata on the front end based on the user's selection
        echo '<style>';
        
        // Hide author if the setting is enabled
        if ($hide_author === '1') {
            echo '.single-post .author, .single-post .entry-author, .single-post .post-author { display: none !important; }';
        }

        // Hide date if the setting is enabled
        if ($hide_date === '1') {
            echo '.single-post .posted-on, .single-post .entry-date, .single-post time { display: none !important; }';
        }

        // Hide categories if the setting is enabled
        if ($hide_categories === '1') {
            echo '.single-post .cat-links, .single-post .tags-links, .single-post .post-meta .categories { display: none !important; }';
        }

        // Hide excerpt if the setting is enabled
        if ($hide_excerpt === '1') {
            echo '.single-post .post-excerpt, .single-post .entry-summary { display: none !important; }';
        }

        // Hide social sharing if the setting is enabled
        if (get_option('disable_social_sharing') === '1') {
            echo '.single-post .social-sharing { display: none !important; }';
        }

        echo '</style>';
    }
}
add_action('wp_head', 'hpmc_modify_post_ui');

// Disable comments based on settings (Frontend)
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
        if (get_option('disable_social_sharing') !== '1') {
            $content .= '<div class="social-sharing">
                <a href="https://www.facebook.com/sharer/sharer.php?u=' . get_permalink() . '" target="_blank">Share on Facebook</a> |
                <a href="https://twitter.com/intent/tweet?url=' . get_permalink() . '" target="_blank">Share on Twitter</a>
            </div>';
        }
    }
    return $content;
}
add_filter('the_content', 'hpmc_add_social_sharing_buttons');

// Use JavaScript for dynamically hiding elements based on metadata settings
function hpmc_modify_post_ui_js() {
    if (is_single() && 'post' === get_post_type()) {
        global $post;

        // Get the custom metadata values
        $hide_author = get_post_meta($post->ID, '_hide_author', true);
        $hide_date = get_post_meta($post->ID, '_hide_date', true);
        $hide_categories = get_post_meta($post->ID, '_hide_categories', true);
        $hide_excerpt = get_post_meta($post->ID, '_hide_excerpt', true);

        // JavaScript code to hide the elements dynamically
        echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                ';

                if ($hide_author === '1') {
                    echo 'document.querySelector(".author").style.display = "none";';
                    echo 'document.querySelector(".entry-author").style.display = "none";';
                }
                if ($hide_date === '1') {
                    echo 'document.querySelector(".posted-on").style.display = "none";';
                    echo 'document.querySelector(".entry-date").style.display = "none";';
                }
                if ($hide_categories === '1') {
                    echo 'document.querySelector(".cat-links").style.display = "none";';
                    echo 'document.querySelector(".tags-links").style.display = "none";';
                }
                if ($hide_excerpt === '1') {
                    echo 'document.querySelector(".post-excerpt").style.display = "none";';
                    echo 'document.querySelector(".entry-summary").style.display = "none";';
                }

                if ('1' === get_option('disable_social_sharing')) {
                    echo 'document.querySelector(".social-sharing").style.display = "none";';
                }

        echo '});
        </script>';
    }
}
add_action('wp_footer', 'hpmc_modify_post_ui_js');
?>
