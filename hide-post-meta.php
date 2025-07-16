<?php
/*
Plugin Name: Hide Post Metadata & Customize UI
Plugin URI: https://devdinos.com
Description: A simple plugin to hide post metadata like date, author, categories, and excerpt, with options to customize post UI.
Version: 1.1
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

                <tr valign="top">
                    <th scope="row">Customize Post UI</th>
                    <td>
                        <label for="post_bg_color">Post Background Color:</label><br />
                        <input type="text" name="post_bg_color" id="post_bg_color" value="<?php echo esc_attr( get_option('post_bg_color') ); ?>" class="color-picker" /><br />
                        <label for="post_font_color">Post Font Color:</label><br />
                        <input type="text" name="post_font_color" id="post_font_color" value="<?php echo esc_attr( get_option('post_font_color') ); ?>" class="color-picker" />
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Disable Comments</th>
                    <td>
                        <label for="disable_comments">
                            <input type="checkbox" name="disable_comments" id="disable_comments" value="1" <?php checked( get_option('disable_comments'), 1 ); ?> />
                            Disable Comments on Posts
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
    register_setting('hpmc_options_group', 'hide_author');
    register_setting('hpmc_options_group', 'hide_date');
    register_setting('hpmc_options_group', 'hide_categories');
    register_setting('hpmc_options_group', 'hide_excerpt');
    register_setting('hpmc_options_group', 'post_bg_color');
    register_setting('hpmc_options_group', 'post_font_color');
    register_setting('hpmc_options_group', 'disable_comments');
}
add_action('admin_init', 'hpmc_register_settings');

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
        
        echo '<style>
            .single-post {
                background-color: ' . esc_attr($bg_color) . ';
                color: ' . esc_attr($font_color) . ';
            }
        </style>';
    }
}
add_action('wp_head', 'hpmc_modify_post_ui');

// Disable comments based on settings
function hpmc_disable_comments() {
    if (is_single() && 'post' === get_post_type()) {
        if (get_option('disable_comments') === '1') {
            // Disable comments for posts
            remove_post_type_support('post', 'comments');
            // Optionally, you can also hide the comments section from the frontend:
            echo '<style>.comments-area { display: none; }</style>';
        }
    }
}
add_action('wp', 'hpmc_disable_comments');
