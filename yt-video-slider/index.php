<?php

/**
 * Plugin Name: YouTube Video Slider
 * Plugin URI: https://yt-video-slider.com/wordpress-plugins/
 * Description: Simple video slider plugin using YouTube API key.
 * Version: 1.0
 * Requires at least: 5.8
 * Requires PHP: 5.6.20
 * Author: Nikita Savenkov
 * Author URI: https://yt-video-slider.com/
 * License: GPLv2 or later
 * Text Domain: yt-video-slider
 **/
/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2024 Automattic, Inc.
*/
?>

<?php

function video_slider_enqueue_scripts()
{

    wp_enqueue_style('slick-css', plugin_dir_url(__FILE__) . '/assets/lib/slick-1.8.1/slick/slick.css');
    wp_enqueue_style('slick-theme-css', plugin_dir_url(__FILE__) . '/assets/lib/slick-1.8.1/slick/slick-theme.css');
    wp_enqueue_style('magnific-popup-css', plugin_dir_url(__FILE__) . '/assets/lib/Magnific-Popup-1.1.0/dist/magnific-popup.css');
    wp_enqueue_style('video-slider-styles', plugin_dir_url(__FILE__) . '/assets/css/styles.css');

    wp_enqueue_script('jquery');
    wp_enqueue_script('slick-js', plugin_dir_url(__FILE__) . '/assets/lib/slick-1.8.1/slick/slick.min.js', array('jquery'), null, true);
    wp_enqueue_script('magnific-popup-js', plugin_dir_url(__FILE__) . '/assets/lib/Magnific-Popup-1.1.0/dist/jquery.magnific-popup.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'video_slider_enqueue_scripts');


function custom_slider_menu()
{
    add_menu_page(
        'YouTube Slider',
        'YouTube Slider',
        'manage_options',
        'custom-slider',
        'custom_slider_page'
    );
}

add_action('admin_menu', 'custom_slider_menu');


function custom_slider_page()
{
    echo '<div class="wrap">';

    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['slider_id'])) {
        $slider_id = intval($_GET['slider_id']);
        delete_slider($slider_id);
        echo '<div class="updated"><p>Слайдер удален успешно!</p></div>';
    }

    if (isset($_GET['action'])) {
        $action = sanitize_text_field($_GET['action']);
        switch ($action) {
            case 'create':
                create_slider();
                break;
            case 'edit':
                edit_slider();
                break;
            default:

                custom_slider_list_action();
                break;
        }
    } else {
        echo '<h1>Создать слайдер</h1>';

        echo '<form method="post" action="?page=custom-slider&action=create">';
        echo '<label for="slider_name">Название слайдера:</label>';
        echo '<input type="text" name="slider_name" required>';
        echo '<input type="submit" name="create_slider" value="Создать" class="button-primary">';
        echo '</form>';
        custom_slider_list_action();
    }

    echo '</div>';
}

function custom_slider_list_action()
{
    echo '<div class="wrap">';

    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['slider_id'])) {
        $slider_id = intval($_GET['slider_id']);
        delete_slider($slider_id);
        echo '<div class="updated"><p>Слайдер удален успешно!</p></div>';
    }

    echo '<h2>Список слайдеров</h2>';

    $sliders = get_sliders();

    if (!empty($sliders)) {
        echo '<table class="widefat">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>№</th>';
        echo '<th>ID</th>';
        echo '<th>Имя</th>';
        echo '<th>Дата создания</th>';
        echo '<th>Удалить</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        $counter = 1;
        foreach ($sliders as $slider) {
            echo '<tr>';
            echo '<td>' . $counter . '</td>';
            echo '<td>' . $slider->ID . '</td>';
            echo '<td><a href="?page=custom-slider&action=edit&slider_id=' . $slider->ID . '">' . $slider->name . '</a></td>';
            echo '<td>' . $slider->date_created . '</td>';
            echo '<td>';
            echo '<form method="post" action="">';
            echo '<input type="hidden" name="delete_slider_id" value="' . $slider->ID . '">';
            echo '<input type="submit" name="delete_slider" value="Удалить" class="button" onclick="return confirm(\'Вы уверены?\');">';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
            $counter++;
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>Нет созданных слайдеров.</p>';
    }

    echo '</div>';
}

function custom_slider_delete_handler()
{
    if (isset($_POST['delete_slider'])) {
        $slider_id = intval($_POST['delete_slider_id']);
        delete_slider($slider_id);
        echo '<div class="updated"><p>Слайдер удален успешно!</p></div>';
    }
}

add_action('admin_notices', 'custom_slider_delete_handler');

function delete_slider($slider_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_sliders';
    $wpdb->delete($table_name, array('ID' => $slider_id));
}

function edit_slider()
{
    if (isset($_GET['slider_id'])) {
        $slider_id = intval($_GET['slider_id']);
        $slider = get_slider($slider_id);

        if ($slider) {
            echo '<div class="wrap">';
            echo '<h1>Редактировать слайдер</h1>';
            echo '<p>Имя слайдера: ' . esc_html($slider->name) . '</p>';
            // echo '<p>ID слайдера: ' . esc_html($slider_id) . '</p>';
            echo '</div>';
            echo '<a href="' . admin_url('admin.php?page=custom-slider') . '" class="button">Назад</a>';
            video_slider_settings_page($slider_id);
        } else {
            echo '<div class="wrap"><p>Слайдер не найден.</p></div>';
        }
    }
}

function create_slider()
{
    if (isset($_POST['create_slider'])) {
        $slider_name = sanitize_text_field($_POST['slider_name']);

        $slider_id = save_slider($slider_name);

        update_slider_date_created($slider_id);

        echo '<div class="updated"><p>Слайдер создан успешно!</p></div>';
    }
    custom_slider_list_action();
}
function update_slider_date_created($slider_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_sliders';
    $wpdb->update(
        $table_name,
        array('date_created' => current_time('mysql')),
        array('ID' => $slider_id)
    );
}

function custom_slider_admin_notices()
{

    if (isset($_GET['page']) && $_GET['page'] === 'custom-slider' && isset($_GET['action']) && $_GET['action'] === 'create') {
        echo '<div class="wrap">';
        echo '<h1>Создать слайдер</h1>';

        echo '<form method="post" action="?page=custom-slider&action=create">';
        echo '<label for="slider_name">Название слайдера:</label>';
        echo '<input type="text" name="slider_name" required>';
        echo '<input type="submit" name="create_slider" value="Создать" class="button-primary">';
        echo '</form>';

        echo '</div>';
    }
}

add_action('admin_notices', 'custom_slider_admin_notices');

function custom_slider_settings_page()
{
    add_submenu_page(
        'custom-slider',
        'Настройки слайдера',
        'manage_options',
        'custom-slider-settings',
        'custom_slider_settings_page_callback'
    );
}

add_action('admin_menu', 'custom_slider_settings_page');

function custom_slider_settings_page_callback()
{
    echo '<div class="wrap">';

    // Ваш код для страницы настроек слайдера

    echo '</div>';
}
register_activation_hook(__FILE__, 'custom_slider_install');

function custom_slider_install()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_sliders';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
            ID mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (ID)
        ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function get_sliders()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_sliders';

    $query = "SELECT ID, name, date_created FROM $table_name";
    return $wpdb->get_results($query);
}

function get_slider($slider_id)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_sliders';

    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE ID = %d", $slider_id);
    return $wpdb->get_row($query);
}

function save_slider($name)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_sliders';

    $data = array('name' => $name);
    $wpdb->insert($table_name, $data);

    return $wpdb->insert_id;
}

function video_slider_settings_page($slider_id)
{

    if (!current_user_can('manage_options')) {
        return;
    }
    // Подключаем стили и скрипты
    wp_enqueue_style('video-slider-admin-styles', plugin_dir_url(__FILE__) . '/assets/css/wp-admin.css');
    // wp_enqueue_script('video-slider-admin-script', plugin_dir_url(__FILE__) . '/assets/js/admin-script.js', array('jquery'), null, true);
    wp_localize_script('video-slider-admin-script', 'videoSliderAdminParams', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('update-video-list-nonce'),
    ));

    if (isset($_POST['verify_api_key'])) {
        // Fetch the API key from the submitted form data
        $api_key = sanitize_text_field($_POST['youtube_api_key']);
    
        // Verify the API key
        echo verify_api_key($api_key);
    }

    if (isset($_POST['create_feed_file'])) {
        create_feed_file($slider_id);
    }

    if (isset($_POST['submit'])) {

        update_option('youtube_api_key_' . $slider_id, sanitize_text_field($_POST['youtube_api_key']));
        update_option('youtube_channel_id_' . $slider_id, sanitize_text_field($_POST['youtube_channel_id']));
        
        // $selected_videos = sanitize_text_field($_POST['selected_videos']);
        // $file_path = plugin_dir_path(__FILE__) . '/assets/data/saved_slider.json';
        // file_put_contents($file_path, $selected_videos);

        $settings_saved_message = '<div class="updated"><p>Settings saved!</p></div>';
   
    }

    $api_key = get_option('youtube_api_key_' . $slider_id, '');

    $channel_id = get_option('youtube_channel_id_' . $slider_id, '');

    $file_path = plugin_dir_path(__FILE__) . '/assets/data/saved_' . $slider_id . '_feed.json';


    if (file_exists($file_path)) {

        $saved_feed_content = htmlspecialchars(file_get_contents($file_path));
    } else {
        $saved_feed_content = 'No saved feed found.';
    }

    $last_update_time = get_option('last_update_time', '');
    $next_update_time = get_option('next_update_time', '');
?>

    <div class="wrap">
        <!-- <h1>Video Slider Settings</h1> -->
        <?php
        if (isset($settings_saved_message)) {
            echo $settings_saved_message;
        }

        if (isset($verification_message)) {
            echo $verification_message;
        }

        $last_update_time = get_option('last_update_time', '');
        if ($last_update_time) {

            $last_update_time_formatted = date('Y-m-d H:i:s', strtotime($last_update_time));
            echo "<p>Last update time: {$last_update_time_formatted}</p>";
        } else {
            echo "<p>Last update time: Not available</p>";
        }

        $next_update_time = get_option('next_update_time', '');
        if ($next_update_time) {
            $next_update_time_formatted = date('Y-m-d H:i:s', strtotime($next_update_time));
            echo "<p>Next update time: {$next_update_time_formatted}</p>";
        } else {
            echo "<p>Next update time: Not available</p>";
        }
        ?>

        <div class="shortcode">
            <form id="shortcode-form">
                <div class="shortcode-row">
                    <label for="slider_id">Slider ID :</label>
                    <input type="text" name="slider_id" id="slider_id" value="<?php echo esc_attr($slider_id); ?>" readonly>
                </div>

                <div class="shortcode-row">
                    <input type="checkbox" name="arrows" id="arrows" checked>
                    <label for="arrows">: Arrows</label>
                </div>
                <div class="shortcode-row">
                    <input type="checkbox" name="title" id="title" unchecked>
                    <label for="title">: Title</label>
                </div>
                <div class="shortcode-row">
                    <input type="checkbox" name="loop" id="loop" checked>
                    <label for="loop">: Loop</label>
                </div>
                <div class="shortcode-row">
                    <input type="checkbox" name="dots" id="dots" unchecked>
                    <label for="dots">: Dots</label>
                </div>
                <div class="shortcode-row">
                    <input type="checkbox" name="popup" id="popup" checked>
                    <label for="popup">: Popup</label>
                </div>
                <div class="shortcode-row">
                    <label for="autoplay">Autoplay :</label>
                    <input type="text" name="autoplay" id="autoplay" value="5000">
                </div>
                <div class="shortcode-row">
                    <label for="slides_to_show">slidesToShow :</label>
                    <input type="text" name="slides_to_show" id="slides_to_show" value="1">
                </div>
                <div class="shortcode-row">
                    <label for="slides_to_scroll">slidesToScroll :</label>
                    <input type="text" name="slides_to_scroll" id="slides_to_scroll" value="1">
                </div>
           
                <button type="button" class="shortcode-btn" onclick="copyShortcode()">Copy</button>
            </form>
            <p class="shortcode-alert">Shortcode copied</p>
        </div>

        <script>
            function copyShortcode() {
                var shortcode = '[video_slider ';
                var form = document.getElementById('shortcode-form').elements;

                for (var i = 0; i < form.length; i++) {
                    var element = form[i];
                    if (element.type === 'checkbox') {
                        shortcode += element.name + '=' + (element.checked ? 'true' : 'false') + ' ';
                    } else {
                        shortcode += element.name + '=' + element.value + ' ';
                    }
                }

                shortcode += ']';
                shortcode = shortcode.replace(/( = )(?!.*\1)/, '');
                console.log(shortcode);

                // Assuming you have a function to copy text to clipboard
                copyToClipboard(shortcode);

                // Show alert
                var alertElement = document.querySelector('.shortcode-alert');
                alertElement.style.display = 'block';
                setTimeout(function() {
                    alertElement.style.display = 'none';
                }, 2000);
            }

            function copyToClipboard(text) {
                var textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
            }
        </script>

        <form id="yt-vs" method="post" action="">

            <br>
            <div class="setting-option">
                <label for="youtube_api_key">YouTube API Key:</label>
                <input type="text" name="youtube_api_key" value="<?php echo esc_attr($api_key); ?>" />
            </div>

            <div class="setting-option">
                <label for="youtube_channel_id">YouTube Channel ID:</label>
                <input type="text" name="youtube_channel_id" value="<?php echo esc_attr($channel_id); ?>" />
            </div>

            <label for="">[Save Changes] before verify api or create feed file</label>
            <input type="submit" name="submit" class="button-primary" value="Save Changes" />
            <br>
            <div class="setting-btn">
                <input type="submit" name="verify_api_key" class="button-secondary" value="Verify API Key" />
                <input type="submit" name="create_feed_file" class="button-secondary" value="Create Feed File" />
            </div>
        </form>

        <h2>Saved Feed Content</h2>

        <div>

            <div id="saved-feed-content">
                <?php echo isset($file_verification_message) ? $file_verification_message : ''; ?>
                <?php echo isset($api_upload_message) ? $api_upload_message : ''; ?>
            </div>
            <pre><?php if (file_exists($file_path)) {

                        $saved_feed_content = file_get_contents($file_path);
                        $json_data = json_decode($saved_feed_content);

                        if ($json_data && isset($json_data->items)) {

                            $counter = 1;
                            foreach ($json_data->items as $item) {
                                $title = $item->snippet->title;
                                $video_id = isset($item->id->videoId) ? $item->id->videoId : '';

                                if (!empty($video_id)) {
                                    $video_url = "https://www.youtube.com/watch?v={$video_id}";
                                    echo "<div class='video-list'>";
                                    echo "<tr>";
                                    echo "<td><input type='checkbox' class='video-checkbox' data-video-id='{$video_id}' /></td>";
                                    echo "<td>{$counter}</td>";
                                    echo "<td><a href='{$video_url}' target='_blank'>{$title}</a></td>";
                                    echo "</tr>";
                                    echo "</div>";
                                    $counter++;
                                }
                            }
                            echo '</div>';
                        } else {
                            echo '<div class="error"><p>Error decoding JSON from the file.</p></div>';
                        }
                    } else {
                        $saved_feed_content = 'No saved feed found.';
                    } ?></pre>
        </div>
    </div>

<?php
}

add_action('wp_ajax_update_video_list', 'update_video_list_callback');

function update_video_list_callback()
{
    check_ajax_referer('update-video-list-nonce', 'security');

    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied.');
    }

    $video_id = sanitize_text_field($_POST['video_id']);
    $is_checked = filter_var($_POST['is_checked'], FILTER_VALIDATE_BOOLEAN);

    error_log('update_video_list_callback called');

    update_post_meta($video_id, 'video_checked', $is_checked);

    if ($is_checked) {
        wp_send_json_success('Video marked as selected.');
    } else {
        wp_send_json_success('Video marked as unselected.');
    }

    die();
}

function verify_api_key($api_key)
{
    // Your API key verification logic goes here
    // For example, you can use the API key to make a simple request to the YouTube API

    $verification_result = ''; // Initialize the verification result variable

    if (empty($api_key)) {
        $verification_result = '<div class="error"><p>Please enter a valid YouTube API Key.</p></div>';
    } else {
        $url = "https://www.googleapis.com/youtube/v3/channels?part=id&forUsername=GoogleDevelopers&key={$api_key}";

        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $verification_result = '<div class="error"><p>Error checking API Key. ' . esc_html($error_message) . '</p></div>';
        } else {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body);

            if (isset($data->error)) {
                $verification_result = '<div class="error"><p>Invalid API Key. Please enter a valid YouTube API Key.</p></div>';
            } else {
                $verification_result = '<div class="updated"><p>API Key is valid!</p></div>';
            }
        }
    }

    return $verification_result; // Return the verification result
}

function create_feed_file($slider_id)
{
    echo '<div class="updated"><p>Attempting to create feed file...</p></div>';

    $last_update_time = current_time('mysql');
    update_option('last_update_time', $last_update_time);

    $api_key = get_option('youtube_api_key_' . $slider_id, '');
    $channel_id = get_option('youtube_channel_id_' . $slider_id, '');


    if (empty($api_key) || empty($channel_id)) {
        echo '<div class="error"><p>API key or Channel ID is missing. Please provide valid values in the settings.</p></div>';
        return;
    }

    $file_path = plugin_dir_path(__FILE__) . '/assets/data/saved_' . $slider_id . '_feed.json';


    $page_token = '';
    $all_results = array();

    do {
        $rss_feed_url = "https://www.googleapis.com/youtube/v3/search?part=snippet&channelId={$channel_id}&maxResults=50&order=date&type=video&key={$api_key}&pageToken={$page_token}";

        $response = wp_remote_get($rss_feed_url);

        if (is_wp_error($response)) {
            echo '<div class="error"><p>Error fetching feed. Please check the API key and Channel ID.</p></div>';
            return;
        }

        $body = wp_remote_retrieve_body($response);

        $json_data = json_decode($body);

        if ($json_data && isset($json_data->items)) {

            $all_results = array_merge($all_results, $json_data->items);

            $page_token = isset($json_data->nextPageToken) ? $json_data->nextPageToken : '';
        } else {
            echo '<div class="error"><p>Error decoding JSON from the API response.</p></div>';
            return;
        }
    } while ($page_token);

    try {

        $result = file_put_contents($file_path, json_encode(array('items' => $all_results)));

        if ($result === false) {
            throw new Exception('Error writing to the file.');
        }

        echo '<div class="updated"><p>Feed file created successfully!</p></div>';
    } catch (Exception $e) {

        echo '<div class="error"><p>Error creating feed file: ' . esc_html($e->getMessage()) . '</p></div>';
        return;
    }

    $next_update_time = current_time('timestamp') + 24 * 60 * 60;
    update_option('next_update_time', date('Y-m-d H:i:s', $next_update_time));

    echo '<div class="updated"><p>File creation process completed.</p></div>';
}

function schedule_feed_creation_on_activation()
{
    if (!wp_next_scheduled('video_slider_daily_event')) {
        wp_schedule_event(time(), 'daily', 'video_slider_daily_event');
    }
}

register_activation_hook(__FILE__, 'schedule_feed_creation_on_activation');

function unschedule_feed_creation_on_deactivation()
{
    wp_clear_scheduled_hook('video_slider_daily_event');
}

register_deactivation_hook(__FILE__, 'unschedule_feed_creation_on_deactivation');

// function video_slider_daily_event_hook()
// {
//     create_feed_file();
// }

// add_action('video_slider_daily_event', 'video_slider_daily_event_hook');

function video_slider_shortcode($atts)
{
    $atts = shortcode_atts(
        array(
            'slider_id'         => '',
            'loop'              => 'true',
            'arrows'            => 'true',
            'dots'              => 'false',
            'popup'             => 'true',
            'title'             => 'false',
            'autoplay'          => 0,
            'slides_to_show'    => 1,
            'slides_to_scroll'  => 1,
        ),
        $atts,
        'video_slider'
    );

    $slider_id          = sanitize_text_field($atts['slider_id']); 
    $loop_slider        = $atts['loop'] === 'true';
    $show_arrows        = $atts['arrows'] === 'true';
    $show_dots          = $atts['dots'] === 'true';
    $open_popup         = $atts['popup'] === 'true';
    $show_title         = $atts['title'] === 'true';
    $autoplay           = intval($atts['autoplay']);
    $slides_to_show     = intval($atts['slides_to_show']);
    $slides_to_scroll   = intval($atts['slides_to_scroll']);

    $file_path = plugin_dir_path(__FILE__) . '/assets/data/saved_' . $slider_id . '_feed.json';


    if (file_exists($file_path) && filesize($file_path) > 0) {
        $rss_feed_json = file_get_contents($file_path);
        $rss_feed = json_decode($rss_feed_json);

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo '<p>Error decoding JSON from the file.</p>';
            return;
        }
    } else {
        echo '<p>No saved feed found. Please create the feed file in the settings or provide a valid channel_id in the shortcode.</p>';
        return;
    }

    ob_start();
    if ($rss_feed && isset($rss_feed->items) && !empty($rss_feed->items)) {
        // Add the $slider_id to the output HTML
        echo "<div class='video-slider' data-slider-id='" . $slider_id . "' data-loop='" . ($loop_slider ? 'true' : 'false') . "' data-show-arrows='" . ($show_arrows ? 'true' : 'false') . "' data-show-dots='" . ($show_dots ? 'true' : 'false') . "' data-open-popup='" . ($open_popup ? 'true' : 'false') . "' data-show-title='" . ($show_title ? 'true' : 'false') . "' data-autoplay='" . $autoplay . "' data-slides_to_show='" . $slides_to_show . "' data-slides_to_scroll='" . $slides_to_scroll . "'>";

        foreach ($rss_feed->items as $video) {
            $video_id = isset($video->id->videoId) ? $video->id->videoId : '';
            if (empty($video_id)) {
                continue;
            }
            $title      = $video->snippet->title;
            $thumbnail  = $video->snippet->thumbnails->high->url;
            $video_url  = "https://www.youtube.com/watch?v={$video_id}";

            echo "<div class='slide video-popup' data-video-id='{$video_id}'>";

            if ($open_popup) {
                echo "<a class='popup-youtube' href='{$video_url}'>";
                echo "<img src='{$thumbnail}' alt='{$title}' />";
                if ($show_title) {
                    echo "<p>{$title}</p>";
                }
                echo "</a>";
            } else {
                echo "<a href='{$video_url}'>";
                echo "<img src='{$thumbnail}' alt='{$title}' />";
                if ($show_title) {
                    echo "<p>{$title}</p>";
                }
                echo "</a>";
            }

            echo "</div>";
        }
        echo '</div>';
    } else {
        echo '<p>No videos found.</p>';
    }

    return ob_get_clean();
}

add_shortcode('video_slider', 'video_slider_shortcode');


function get_selected_videos()
{
    $selected_videos = array();


    $args = array(
        'post_type' => 'your_video_post_type',
        'meta_key' => 'video_checked',
        'meta_value' => true,
        'fields' => 'ids',
    );

    $selected_videos_ids = get_posts($args);


    foreach ($selected_videos_ids as $id) {
        $selected_videos[] = (string) $id;
    }

    return $selected_videos;
}

function video_slider_scripts_footer()
{
?>
    <script>
        jQuery(document).ready(function($) {

            $('.video-slider').each(function() {
                var loopSlider = $(this).data('loop') === true;
                var showArrows = $(this).data('show-arrows') === true;
                var showDots = $(this).data('show-dots') === true;
                var openInPopup = $(this).data('open-popup') === true;
                var showTitle = $(this).data('show-title') === true;
                var autoplay = parseInt($(this).data('autoplay')) || 0;
                var slidesToShow = parseInt($(this).data('slides_to_show')) || 1;
                var slidesToScroll = parseInt($(this).data('slides_to_scroll')) || 1;

                var isMobile = window.matchMedia("only screen and (max-width: 767px)").matches;
                var showArrows = !isMobile && showArrows;

                $(this).slick({
                    slidesToShow: slidesToShow,
                    slidesToScroll: slidesToScroll,
                    dots: showDots,
                    arrows: showArrows,
                    infinite: loopSlider,
                    adaptiveHeight: true,
                    autoplay: autoplay,
                    autoplaySpeed: autoplay,
                });

            });

            $('.popup-youtube').magnificPopup({
                type: 'iframe',
                mainClass: 'mfp-fade',
                removalDelay: 160,
                preloader: false,
                fixedContentPos: false,
            });
        });
    </script>
<?php
}

add_action('wp_footer', 'video_slider_scripts_footer');
?>