<?php
/*
Plugin Name: Recent News Thumbnails
Plugin URI: http://www.peytongregory.com/
Description: Displays two recent blog posts from a spesific category.
Version: 1.0
Author: Peytongregory
Author URI: http://www.peytongregory.com/
License: GPL2
*/


		 
	
class custom_RecentPostsByCategory extends WP_Widget {

    function __construct() {
        $widget_ops = array('classname' => 'widget_recent_entries', 'description' => __( "The most recent posts on your site (by Category)") );
        parent::__construct('recent-posts-custom', __('Custom: Recent Posts'), $widget_ops);
        $this->alt_option_name = 'widget_recent_entries';

        add_action( 'save_post', array(&$this, 'flush_widget_cache') );
        add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
        add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
    }

    function widget($args, $instance) {
        $cache = wp_cache_get('widget_recent_posts', 'widget');

        if ( !is_array($cache) )
            $cache = array();

        if ( ! isset( $args['widget_id'] ) )
            $args['widget_id'] = $this->id;

        if ( isset( $cache[ $args['widget_id'] ] ) ) {
            echo $cache[ $args['widget_id'] ];
            return;
        }

        ob_start();
        extract($args);

        $title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Posts') : $instance['title'], $instance, $this->id_base);
        if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
            $number = 10;

        $r = new WP_Query(array('posts_per_page' => $number, 'no_found_rows' => true, 'post_status' => 'publish', 'ignore_sticky_posts' => true, 'category_name' => $instance['cat']));
        if ($r->have_posts()) :
?>
        <?php echo $before_widget; ?>
        <h2 class="widget-title"><?php if ( $title ) echo $before_title . $title . $after_title; ?></h2>
        <div class="post-container">
        <?php  while ($r->have_posts()) : $r->the_post(); ?>
        
        
       
        <?php 
		$count = 0;
			echo '<div class="' . (++$count%2 ? "odd" : "even") . ' post-'.get_the_ID().' single-post">'; 
		?>
        
        
                <div class="post-title"><a href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a></div>
                <?php the_post_thumbnail('thumbnail', array('class' => 'home-icon-img')); ?>
                <p><?php echo substr(get_the_excerpt(), 0,550); ?></p>
        
		</div>
        <?php endwhile; ?>
        </div><!-- post container --> 
        <?php echo $after_widget; ?>

<?php
        // Reset the global $the_post as this query will have stomped on it
        wp_reset_postdata();

        endif;

        $cache[$args['widget_id']] = ob_get_flush();
        wp_cache_set('widget_recent_posts', $cache, 'widget');
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['cat'] = strip_tags($new_instance['cat']);
        $instance['number'] = (int) $new_instance['number'];
        $this->flush_widget_cache();

        $alloptions = wp_cache_get( 'alloptions', 'options' );
        if ( isset($alloptions['widget_recent_entries']) )
            delete_option('widget_recent_entries');

        return $instance;
    }

    function flush_widget_cache() {
        wp_cache_delete('widget_recent_posts', 'widget');
    }

    function form( $instance ) {
        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
        $cat = isset($instance['cat']) ? esc_attr($instance['cat']) : '';
        $number = isset($instance['number']) ? absint($instance['number']) : 5;
?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

        <p><label for="<?php echo $this->get_field_id('cat'); ?>"><?php _e('Category:'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('cat'); ?>" name="<?php echo $this->get_field_name('cat'); ?>" type="text" value="<?php echo $cat; ?>" /></p>

        <p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:'); ?></label>
        <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
<?php
    }
}

function wpzoom_register_rpa_widget() {
    register_widget('custom_RecentPostsByCategory');
}
add_action('widgets_init', 'wpzoom_register_rpa_widget');
?>
