<?php
/*
  Plugin Name: Most Read in XX Days
  Plugin URI: http://www.studio404.it/2006/plugin-wordpress-articoli-piu-letti.html
  Description: Returns a list of the most read posts in last XX days. Supports Widgets and configuration in the Settings Page. Reloaded posts are not counted :-)
  Version: 2.3.2
  Author: Claudio Simeone - Studio404.it
  Author URI: http://www.studio404.it

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
  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

  --------------------------------------------
  INSTALL

  1) Drop this file in your 'wp-content/plugins' folder and activate it.
  2) (optional) If automatic creation fails, create a table in your WP Database:

  CREATE TABLE `wp_most_read_hits` (
  `ID` int(5) unsigned NOT NULL auto_increment,
  `post_ID` int(5) NOT NULL default '0',
  `hits` int(10) NOT NULL default '0',
  PRIMARY KEY  (`ID`)
  );

  3) SHOW IN THE SIDEBAR
  If you don't want to use widgets, add this line
  in your 'sidebar.php' template file:

  <?php ST4_most_read(30, 5, 'yes', 'yes', 'post'); ?>
  30 is the number of days you want to show, 0 to remove the date limit
  5 is the number of the posts
  'yes' if you want to show the hits after the post title, 'no' if you don't
  'yes' if you want to show the featured image preview before the post title, 'no' if you don't
  'page' if you want to show Pages' hits, 'post' if you want to show Posts' hits

  4) SHOW IN THE SINGLE POST

  If you want to show the number of hits of a single post
  add this anywhere in your 'single.php'

  <?php ST4_single_hits(); ?>

  5) SHOW IN THE SINGLE PAGE

  If you want to show the number of hits of a page
  add this anywhere in your 'page.php'

  <?php ST4_single_hits(); ?>

  6) SHOW IN THE HOME PAGE (IN THE LOOP)
  If you want to show the number of hits of every post
  in the home page (or archives)
  add this in the Loop

  <?php ST4_hits(); ?>

  7) Now you can use ST4_get_post_hits() to get the number of hits of every post
  --------------------------------------------
 */

$wpdb->posthits = $wpdb->prefix . 'most_read_hits';

register_activation_hook(__FILE__, 'ST4_most_read_install' );
add_action('template_redirect', 'ST4_most_read_update_post');
add_action('admin_menu', 'ST4_most_read_menu');
add_action('plugins_loaded', 'ST4_most_read_init');
add_action('widgets_init', 'ST4_most_read_register_widget');
add_filter('plugin_action_links', 'ST4_most_read_action_links', 10, 2);
add_action('after_setup_theme', 'ST4_most_read_setup');
add_action('wp_head', 'ST4_most_read_head');
add_filter('the_content', 'ST4_most_read_the_content');
add_filter('manage_posts_columns', 'ST4_most_read_columns_head');
add_action('manage_posts_custom_column', 'ST4_most_read_columns_content', 10, 2);
add_filter('manage_pages_columns', 'ST4_most_read_columns_head');
add_action('manage_pages_custom_column', 'ST4_most_read_columns_content', 10, 2);
add_filter('manage_edit-post_sortable_columns', 'ST4_most_read_sortable_column');
add_filter('posts_join', 'ST4_most_read_join');
add_filter('posts_orderby', 'ST4_most_read_orderby');

function ST4_most_read_init() {
  load_plugin_textdomain( 'most_read', false, dirname( plugin_basename( __FILE__ ) ) . '/most-read-plugin/' );
}

function ST4_most_read_default_css(){
    $most_read_default_css = "
img.most_read_preview{margin-right:5px;}
div.most_read_hits{font-style:italic;}
div.most_read_hits .most_read_hits_label{color:#F00;}
div.most_read_hits .most_read_hits_number{font-weight:bold;}
";
    return $most_read_default_css;
}

function ST4_most_read_head(){
    $options = ST4_most_read_get_options();
    if ($options['image_css_usecustom'] == 1) {
        echo "<style type=\"text/css\">\n" . stripslashes($options['image_css']). "\n</style>\n";
    }
}

function ST4_most_read_the_content($content){
    global $post;
    $options = ST4_most_read_get_options();
    $post_hits_html = '<div id="most_read_hits_'.$post->ID.'" class="most_read_hits"><span class="most_read_hits_label">'.__('Hits','most_read').':</span> <span class="most_read_hits_number">' . ST4_get_post_hits($post->ID) . '</span></div>';
    if ($options['filter_content'] == 'ac') {
        return $content . $post_hits_html;
    } elseif($options['filter_content'] == 'bc'){
        return $post_hits_html . $content;
    } else {
        return $content;    
    }
}

class MostReadWidget extends WP_Widget {

    function MostReadWidget() {
        parent::__construct(false, __('Most Read Posts', 'most_read'));
    }

    function widget($args, $instance) {
        $title = empty($instance['title']) ? 'Most Read Posts' : $instance['title'];
        $quanti = empty($instance['quanti']) ? '0' : $instance['quanti'];
        $limit = empty($instance['limit']) ? '5' : $instance['limit'];
        $wshits = empty($instance['wshits']) ? 'n' : $instance['wshits'];
        $image_preview = empty($instance['image_preview']) ? 'n' : $instance['image_preview'];
        $mr_post_type = empty($instance['mr_post_type']) ? 'post' : $instance['mr_post_type'];
        echo $args['before_widget'];
        echo $args['before_title'] . apply_filters( 'widget_title', $title ) . $args['after_title'];
        ST4_mostread($quanti, $limit, $wshits, $image_preview, $mr_post_type);
        echo $args['after_widget'];
    }

    function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title']  = (!empty($new_instance['title']) ) ? strip_tags($new_instance['title']) : __('Most Read Posts', 'most_read');
        $instance['quanti'] = (!empty($new_instance['quanti']) ) ? strip_tags($new_instance['quanti']) : 0;
        $instance['limit']  = (!empty($new_instance['limit']) ) ? strip_tags($new_instance['limit']) : 5;
        $instance['wshits']  = (!empty($new_instance['wshits']) ) ? $new_instance['wshits'] : 'n';
        $instance['image_preview']  = (!empty($new_instance['image_preview']) ) ? $new_instance['image_preview'] : 'n';
        $instance['mr_post_type']  = (!empty($new_instance['mr_post_type']) ) ? $new_instance['mr_post_type'] : 'post';
        return $instance;
    }

    function form($instance) {
        $title = (isset($instance['title'])) ? htmlspecialchars($instance['title'], ENT_QUOTES) : __('Most Read Posts', 'most_read');
        $quanti = (isset($instance['quanti'])) ? htmlspecialchars($instance['quanti'], ENT_QUOTES) : 0;
        $limit = (isset($instance['limit'])) ? htmlspecialchars($instance['limit'], ENT_QUOTES) : 5;
        $wshits = (isset($instance['wshits'])) ? htmlspecialchars($instance['wshits'], ENT_QUOTES) : 'n';
        $image_preview = (isset($instance['image_preview'])) ? htmlspecialchars($instance['image_preview'], ENT_QUOTES) : 'n';
        $mr_post_type = (isset($instance['mr_post_type'])) ? htmlspecialchars($instance['mr_post_type'], ENT_QUOTES) : 'post';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title') ?></label>
            <input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" 
                   value="<?php echo esc_attr($title); ?>" />
        </p>
        
        <p><?php echo $post_type; ?>
            <label for="<?php echo $this->get_field_id('mr_post_type'); ?>"><?php _e('Show', 'most_read'); ?>:</label>            
            <input type="radio" id="<?php echo $this->get_field_id('mr_post_type'); ?>-post"
                   name="<?php echo $this->get_field_name('mr_post_type'); ?>" 
                   value="post" <?php if ($mr_post_type == 'post') echo 'checked'; ?>
                   /> Posts
            <input type="radio" id="<?php echo $this->get_field_id('mr_post_type'); ?>-page"
                   name="<?php echo $this->get_field_name('mr_post_type'); ?>" 
                   value="page" <?php if ($mr_post_type == 'page') echo 'checked'; ?>
                   /> Pages </p>
        
        <p>
            <label for="<?php echo $this->get_field_id('quanti'); ?>"><?php _e('Show most read posts in', 'most_read'); ?></label>
            <input style="width:50px;" type="text" id="<?php echo $this->get_field_id('quanti'); ?>" name="<?php echo $this->get_field_name('quanti'); ?>" value="<?php echo esc_attr($quanti); ?>" /> <?php _e('days', 'most_read'); ?>
            <br />
            <small><?php _e('Set to <b>0</b> to remove the date limit', 'most_read'); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>"><?php _e('Show', 'most_read'); ?></label>
            <input style="width:50px;" type="text" id="<?php echo $this->get_field_id('limit'); ?>" name="<?php echo $this->get_field_name('limit'); ?>" value="<?php echo esc_attr($limit); ?>" />
                <?php _e('items in the Sidebar', 'most_read'); ?>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wshits'); ?>"><?php _e('Show Hits', 'most_read'); ?></label>
            <input id="<?php echo $this->get_field_id('wshits'); ?>" 
                   name="<?php echo $this->get_field_name('wshits'); ?>" 
                   type="checkbox" value="yes" <?php if ($wshits == 'yes') echo ' checked'; ?>/>
            <br />
            <small><?php _e('Check this if you want to show hits after the Title', 'most_read'); ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('image_preview'); ?>"><?php _e('Show Featured Image', 'most_read'); ?></label>
            <input id="<?php echo $this->get_field_id('image_preview'); ?>" name="<?php echo $this->get_field_name('image_preview'); ?>" type="checkbox" value="yes" <?php if ($image_preview == 'yes') echo ' checked'; ?>/>
            <br />
            <small>
                <?php _e('Check this if you want to show the featured image before the Title', 'most_read'); ?>.<br />
                <?php printf( __( 'Image dimensions can be set here <a href="%s">here</a>.', 'most_read' ) , admin_url('options-general.php?page=ST4_most_read.php') ); ?><br />
            </small>
        </p>
<?php
    }
}

function ST4_most_read_register_widget() {
    register_widget('MostReadWidget');
}

function ST4_single_hits() {
    global $posts;
    $actual_hits = ST4_get_post_hits($posts[0]->ID);
    echo $actual_hits;
}

function ST4_hits() {
    global $post;
    $actual_hits = ST4_get_post_hits($post->ID);
    echo $actual_hits;
}

function ST4_get_post_hits($post_ID) {
    global $wpdb;
    $post_hits = $wpdb->get_var("SELECT hits FROM $wpdb->posthits WHERE post_ID='$post_ID'");
    return (int) $post_hits;
}

function ST4_most_read_update_post() {
    if (!is_admin()) {
        if ( (is_single()) OR (is_page()) ) {
            global $wpdb, $post;
            $p_ID = (int) $post->ID;
            $mread_options = ST4_most_read_get_options();
            $cookie_label = 'ST4_read_post_' . $p_ID . '_ID';
            if (empty($_COOKIE[$cookie_label])) {
                $actual_hits = $wpdb->get_var("SELECT hits FROM $wpdb->posthits WHERE post_ID = '$p_ID'");
                if (!$actual_hits) {
                    $wpdb->query("INSERT INTO $wpdb->posthits (post_ID, hits) VALUES ('$p_ID', 1)");
                    $n_hits = 1;
                } else {
                    $n_hits = $actual_hits + 1;
                    $wpdb->query("UPDATE $wpdb->posthits SET hits = '$n_hits' WHERE post_ID = '$p_ID'");
                }
                setcookie($cookie_label, 1, time() + $mread_options['read_seconds'], COOKIEPATH, COOKIE_DOMAIN);
            }
        }
    }
}

function ST4_mostread($last_days, $how_many = '5', $show_shits = 'yes', $image_preview = 'no', $post_type = 'post') {
    global $wpdb;

    if ($last_days > 0)
        $sql_filter_date = " AND DATE_SUB(CURDATE(),INTERVAL $last_days DAY) < p.post_date";
    else
        $sql_filter_date = " ";

    $sql = "SELECT p.post_title, p.post_date, st.post_ID, st.hits as cnt
      FROM $wpdb->posthits st, $wpdb->posts p
      WHERE p.ID = st.post_ID AND p.post_status = 'publish' AND p.post_type = '$post_type'
      $sql_filter_date
      GROUP BY st.post_ID ORDER BY cnt DESC LIMIT 0, $how_many";
    $output = $wpdb->get_results($sql);

    if ($output) {
        $mread_opts = ST4_most_read_get_options();
        echo '<ul>';
        foreach ($output as $line) {
            $img_html = '';
            if ($image_preview == 'yes'){
                $post_thumbnail_id = get_post_thumbnail_id( $line->post_ID );
                if ($post_thumbnail_id){
                    $img_src = wp_get_attachment_image_src( $post_thumbnail_id, 'st4-mostread-preview');
                    $img_html = '<img class="most_read_preview" width="'.$mread_opts['image_width'].'" height="'.$mread_opts['image_height'].'" src="'.$img_src[0].'" border="0" />';
                }
            }
            $ttviews = ' (' . $line->cnt . ')';
            $t = esc_attr($line->post_title);
            echo "<li><a title='" . $t . $ttviews . "' href='" . get_permalink($line->post_ID) . "'>" .$img_html . $line->post_title . "</a> ";
            if ($show_shits == 'yes') {
                echo $ttviews;
            }
            echo "</li>";
        }
        echo '</ul>';
    } else {
        echo '<ul><li>' . __('No results available', 'most_read') . '</li></ul>';
    }
}

function ST4_most_read_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . "most_read_hits";
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") !== $table_name) {
        $sql = "CREATE TABLE " . $table_name . " (
	      ID int(5) unsigned NOT NULL auto_increment,
	      post_ID int(5) NOT NULL default '0',
	      hits int(10) NOT NULL default '0',
	      PRIMARY KEY  (ID)
	);";
        require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
        dbDelta($sql);
    }
    update_option('MostReadOptions', array(
        'read_seconds' => '172800',
        'image_width' => '25',
        'image_height' => '25',
        'image_css' => ST4_most_read_default_css(),
        'image_css_usecustom' => '1',
        'filter_content' => 'nc'
    ));
}

function ST4_most_read_strtime($s) {
    $d = intval($s / 86400);
    $s -= $d * 86400;
    $h = intval($s / 3600);
    $s -= $h * 3600;
    $m = intval($s / 60);
    $s -= $m * 60;
    if ($d)
        $str = $d;
    if ($h)
        $str .= $h;
    if ($m)
        $str .= $m;
    if ($s)
        $str .= $s;
    return $str;
}

function ST4_most_read_get_options() {
    $mread_options = get_option('MostReadOptions');
    $options_image_width = (int) $mread_options['image_width'];
    $options_image_height = (int) $mread_options['image_height'];
    $mread_options_ok['read_seconds'] = $mread_options['read_seconds'];
    $mread_options_ok['image_width'] = ($mread_options['image_width'] < 1) ? 25 : $options_image_width;
    $mread_options_ok['image_height'] = ($mread_options['image_height'] < 1) ? 25 : $options_image_height;
    $mread_options_ok['image_css'] = (empty($mread_options['image_css'])) ? ST4_most_read_default_css() : $mread_options['image_css'];
    $mread_options_ok['image_css_usecustom'] = $mread_options['image_css_usecustom'];
    $mread_options_ok['filter_content'] = (empty($mread_options['filter_content'])) ? 'nc' : $mread_options['filter_content'];
    return $mread_options_ok;
}

function ST4_most_read_options_validate($input) {
    $options['read_seconds'] = trim($input['read_seconds']) * 86400;
    $options_image_width = (int) $input['image_width'];
    $options_image_height = (int) $input['image_height'];
    $options['image_width'] = ($options_image_width < 1) ? 25 : $options_image_width;
    $options['image_height'] = ($options_image_height < 1) ? 25 : $options_image_height;
    $options['image_css'] = ($input['image_css'] !== '') ? stripslashes($input['image_css']) : ST4_most_read_default_css();
    $options['image_css_usecustom'] = $input['image_css_usecustom'];
    $options['filter_content'] = $input['filter_content'];
    return $options;
}

function ST4_most_read_options_page() {
    ?>
        <div class="wrap">
            <h2><?php _e('Most Read Posts Settings', 'most_read'); ?></h2>
            <form action="options.php" method="post">
                <?php settings_fields('MostReadOptions'); ?>
                <?php do_settings_sections('register_st4_most_read_opts'); ?>
                <?php do_settings_sections('register_st4_most_read_opts_image'); ?>           
                <?php do_settings_sections('register_st4_most_read_opts_text_filters'); ?>                     
                <?php do_settings_sections('register_st4_most_read_opts_css'); ?>                
                <?php submit_button(); ?>
            </form>
        </div>
    <?php
}

function ST4_most_read_options_page_text() {
    echo '<p>'. __('To avoid multiple counts of the same post by the same user, the plugin uses a cookie.', 'most_read') . '</p>';
}

function ST4_most_read_options_page_text_image() {
    echo '<p>' . __('Set a width and an height for the featured image preview in Widgets.', 'most_read') . '<br />';
    _e('If you already have one or more featured images you should recreate the thumbnails.', 'most_read') . ' ';
    printf( __( 'To do this you can use one of <a href="%s">these Plugins</a>.', 'most_read' ) , admin_url('plugin-install.php?tab=search&s=recreate+thumbnails') );
    echo '</p>';
}

function ST4_most_read_options_page_text_css() {
    echo '<p>' . __('You can style image previews in the widgets by adding a <code>.most_read_preview</code> class in your CSS.', 'most_read') . '<br />';
    echo __('You can style automatic posts\' counts by adding a <code>.most_read_hits</code> class in your CSS.', 'most_read') . '</p>';
}

function ST4_most_read_options_page_text_filters() {
    echo '<p>' . __('If you don\'t want to add plugin tags manually in your template, you can show hits automatically before or after Post Content.', 'most_read') . '</p>';
}

function ST4_most_read_options_page_string() {
    $options = ST4_most_read_get_options();
    echo '<input style="width:50px;" id="st4_read_seconds" name="MostReadOptions[read_seconds]" size="40" type="text" value="'.ST4_most_read_strtime($options['read_seconds']).'" /> ' . __('days', 'most_read');
}

function ST4_most_read_options_page_width() {
    $options = ST4_most_read_get_options();
    echo '<input style="width:50px;" id="st4_image_width" name="MostReadOptions[image_width]" size="40" type="text" value="'.$options['image_width'].'" /> pixel</p>';
}

function ST4_most_read_options_page_height() {
    $options = ST4_most_read_get_options();
    echo '<input style="width:50px;" id="st4_image_height" name="MostReadOptions[image_height]" size="40" type="text" value="'.$options['image_height'].'" /> pixel';
}

function ST4_most_read_options_page_css() {
    $options = ST4_most_read_get_options();
    echo '<p><input id="st4_image_css_usecustom" name="MostReadOptions[image_css_usecustom]" type="checkbox" value="1"';
    if ($options['image_css_usecustom'] == 1) echo ' checked ';
    echo '/> '. __('Use the following CSS code', 'most_read') . '</p>';
    echo '<p><textarea style="width:80%" rows="6" id="st4_image_css" name="MostReadOptions[image_css]">'.$options['image_css'].'</textarea></p>';
}

function ST4_most_read_options_text_filters_content() {
    $options = ST4_most_read_get_options();    
    echo '<p><select id="st4_filter_content" name="MostReadOptions[filter_content]">';
    echo '<option value="nc"';
    if ($options['filter_content'] == 'nc') echo ' selected ';
    echo '>' . __('Don\'t show, I do this manually', 'most_read') . '</option>';
    
    echo '<option value="bc"';
    if ($options['filter_content'] == 'bc') echo ' selected ';
    echo '>' . __('Before Post Content', 'most_read') . '</option>';
    
    echo '<option value="ac"';
    if ($options['image_css_filter_content'] == 'ac') echo ' selected ';
    echo '>' . __('After Post Content', 'most_read') . '</option>';    
    echo '</select></p>';
}

function ST4_most_read_register_settings() {
    register_setting('MostReadOptions', 'MostReadOptions', 'ST4_most_read_options_validate');

    add_settings_section('ST4_most_read_options_page_main', __('Cookie Expiration', 'most_read'), 'ST4_most_read_options_page_text', 'register_st4_most_read_opts');
    add_settings_field('ST4_most_read_options_page_read_seconds', __('The cookie expires in', 'most_read'), 'ST4_most_read_options_page_string', 'register_st4_most_read_opts', 'ST4_most_read_options_page_main');

    add_settings_section('ST4_most_read_options_page_main', __('Image Preview Size', 'most_read'), 'ST4_most_read_options_page_text_image', 'register_st4_most_read_opts_image');
    add_settings_field('ST4_most_read_options_page_image_width', __('Width', 'most_read'), 'ST4_most_read_options_page_width', 'register_st4_most_read_opts_image', 'ST4_most_read_options_page_main');
    add_settings_field('ST4_most_read_options_page_image_height', __('Height', 'most_read'), 'ST4_most_read_options_page_height', 'register_st4_most_read_opts_image', 'ST4_most_read_options_page_main');

    add_settings_section('ST4_most_read_options_page_main', __('CSS Settings', 'most_read'), 'ST4_most_read_options_page_text_css', 'register_st4_most_read_opts_css');
    add_settings_field('ST4_most_read_options_page_read_css', __('Custom CSS style', 'most_read'), 'ST4_most_read_options_page_css', 'register_st4_most_read_opts_css', 'ST4_most_read_options_page_main');

    add_settings_section('ST4_most_read_options_page_main', __('Show Hits Automatically', 'most_read'), 'ST4_most_read_options_page_text_filters', 'register_st4_most_read_opts_text_filters');
    add_settings_field('ST4_most_read_options_page_read_filter_content', __('Show Hits', 'most_read'), 'ST4_most_read_options_text_filters_content', 'register_st4_most_read_opts_text_filters', 'ST4_most_read_options_page_main');

}

function ST4_most_read_menu() {
    add_options_page(__('Most Read Posts', 'most_read'), __('Most Read Posts', 'most_read'), 'administrator', basename(__FILE__), 'ST4_most_read_options_page');
    add_action( 'admin_init', 'ST4_most_read_register_settings' );
}

function ST4_most_read_action_links($links, $file) {
    if ($file == plugin_basename(dirname(__FILE__) . '/ST4_most_read.php')) {
        $links[] = '<a href="' . admin_url('options-general.php?page=ST4_most_read.php') . '">' . __('Settings', 'most_read') . '</a>';
        $links[] = '<a href="' . admin_url('widgets.php') . '">' . __('Widget', 'most_read') . '</a>';
    }
    return $links;
}

if (!function_exists('ST4_most_read_setup')) :
    function ST4_most_read_setup() {
        if (!current_theme_supports('post-thumbnails')) {
           $options = ST4_most_read_get_options();
           add_theme_support('post-thumbnails');
           add_image_size( 'st4-mostread-preview', $options['image_width'], $options['image_height'], true );
        }
    }
endif;

function ST4_most_read_columns_head($defaults) {
    $defaults['st4_post_hits'] = __('Hits', 'most_read');
    return $defaults;
}

function ST4_most_read_columns_content($column_name, $post_ID) {
    if ($column_name == 'st4_post_hits') {
        echo ST4_get_post_hits($post_ID);
    }
}

function ST4_most_read_sortable_column($columns) {
    $columns['st4_post_hits'] = 'st4_post_hits';
    return $columns;
}

function ST4_most_read_join($join) {
    global $wp_query, $wpdb;
    if ((!empty($wp_query->query_vars['order'])) AND (!empty($wp_query->query_vars['orderby'])) AND ($wp_query->query_vars['orderby'] == 'st4_post_hits')) {
        $join .= "LEFT JOIN $wpdb->posthits ON $wpdb->posts.ID = $wpdb->posthits.post_id ";
    }
    return $join;
}

function ST4_most_read_orderby($orderby) {
    global $wp_query, $wpdb;
    if ((!empty($wp_query->query_vars['order'])) AND (!empty($wp_query->query_vars['orderby'])) AND ($wp_query->query_vars['orderby'] == 'st4_post_hits')) {
        $orderby = "$wpdb->posthits.hits " . $wp_query->query_vars['order'];
    }
    return $orderby;
}

