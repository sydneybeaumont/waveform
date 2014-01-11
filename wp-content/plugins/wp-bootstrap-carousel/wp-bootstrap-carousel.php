<?php
/*
Plugin Name: WP Bootstrap Carousel
Plugin URI: http://peterherrel.com/wordpress/plugins/wp-bootstrap-carousel
Description: A simple, straightforward implementation of the Twitter Bootstrap Carousel in WordPress. This plugin allows you to display images attached to a post or page as a Bootstrap Carousel using the [carousel] shortcode. As a bonus, this plugin can transform post listings generated by the <a href="http://wordpress.org/extend/plugins/display-posts-shortcode/">Display Posts Shortcode</a> plugin into a Bootstrap Carousel slideshow.
Version: 0.2.1
Author: Peter J. Herrel
Author URI: http://peterherrel.com/
License: GPL3
Text Domain: wp_bootstrap_carousel
Domain Path: /lang
*/

/**
 * @author Peter J. Herrel  (email : peterherrel [at] gmail.com)
 * 
 * @link http://wordpress.org/extend/plugins/wp-bootstrap-carousel/
 * @link https://github.com/diggy/wp-bootstrap-carousel/wiki/
 * @link http://peterherrel.com/wordpress/plugins/wp-bootstrap-carousel
 **/

/**
 * LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as 
 * published by the Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) exit; 
 
if ( ! class_exists( 'WP_Bootstrap_Carousel' ) ) {

/**
 * WP_Bootstrap_Carousel class
 */
class WP_Bootstrap_Carousel
{
    var $version        = '0.2.1';
    var $plugin_dir     = '';
    var $plugin_dir_url = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->plugin_dir       = trailingslashit( dirname( plugin_basename( __FILE__ ) ) );
        $this->plugin_dir_url   = trailingslashit( plugins_url( dirname( plugin_basename( __FILE__ ) ) ) );

        add_action( 'init', array( $this, 'i18n' ), 10 );

        if ( ! is_admin() || defined( 'DOING_AJAX' ) ) :

            add_action( 'wp_enqueue_scripts',   array( $this, 'wp_enqueue_scripts' ),   1 );
            add_action( 'init',                 array( $this, 'init' ),                 10 );
            add_action( 'body_class',           array( $this, 'body_class' ),           10 );

            if( function_exists( 'be_display_posts_shortcode' ) )
                $this->be_display_posts_plugin();

        endif;

        if ( is_admin() ) :

            add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

        endif;

        do_action( 'wp_bootstrap_carousel_loaded' );
    }
    public function i18n()
    {
        load_plugin_textdomain( 'wp_bootstrap_carousel', false, $this->plugin_dir . 'lang/' );
    }
    public function init()
    {
        global $content_width;

        if( defined( 'WP_USE_THEMES' ) && WP_USE_THEMES && ! isset( $content_width ) )
            trigger_error( sprintf( __( 'Your WordPress theme is missing the $content_width feature, needed by the WP Bootstrap Carousel plugin. For more information visit %s', 'wp_bootstrap_carousel' ), esc_url( 'http://codex.wordpress.org/Content_Width' ) ), E_USER_NOTICE );

        add_shortcode( 'carousel', array( $this, 'shortcode' ) );

        do_action( 'wp_bootstrap_carousel_init' );
    }
    public function shortcode( $atts )
    {
        $data       = (array) $this->get_data( $atts );
        $items      = (array) $data['query'];
        $vars       = (array) $data['vars'];
        $parent     = isset( $vars['id'] ) && (int) $vars['id'] ? (int) $vars['id'] : '';
        $width_int  = str_replace( array( '%', 'px' ), '', $vars['width'] );

        global $post;

        if( ! ( $parent || $items ) )
            return apply_filters( 'wp_bootstrap_carousel_no_results', false );

        if( is_feed() )
            return apply_filters( 
                'wp_bootstrap_carousel_feed',
                '<a href="' . get_permalink( $post ) . '#wp-bootstrap-carousel-' . $vars['id'] . '">' . __( 'Click here to view the embedded slideshow.', 'wp_bootstrap_carousel' ) . '</a>',
                $vars
            );

        $this->enqueue( $vars['thickbox'] );

        $carousel = '';

        $carousel .= '<div id="wp-bootstrap-carousel-' . $vars['id'] . '" class="carousel' . ( ( $vars['slide'] ) ? " slide" : "" ) . '" style="width:' . $width_int . 'px;" data-interval="' . $vars['interval'] . '" data-pause="' . $vars['pause'] . '" data-wrap="' . $vars['wrap'] . '">';

        /**
         * INDICATORS
         */
        if( $vars['controls'] )
        {
            $carousel .= '<ol class="carousel-indicators">';
            $o = 0;
            foreach ( $items as $item )
            {
                $carousel .= '<li data-target="#wp-bootstrap-carousel-' . $vars['id'] . '" data-slide-to="' . $o . '" class="' . ( ( $o == 0 ) ? "active" : "" ) . '"></li>';
                $o++;
            }
            $carousel .= '</ol>';
        }

        /**
         * ITEMS
         */
        $carousel .= '<div class="carousel-inner">';

        $i = 0;
        foreach ( $items as $item_id => $item )
        {
            $full   = wp_get_attachment_image_src( $item_id, 'full', false );               // full size
            $thumb  = wp_get_attachment_image_src( $item_id, $vars['image_size'], false );  // thumbnail size
            $link   = ( $vars['file'] ) ? $full[0] : get_attachment_link( $item_id );
            $text   = wpautop( wptexturize( $item->post_content ) );

            $carousel .= '<div id="item-' . $item_id . '" class="' . ( ( $i == 0 ) ? "active" : "" ) . ' item">
                <a class="' . ( ( $vars['file'] && $vars['thickbox'] ) ? "thickbox " : "" ) . '" rel="' . $vars['rel'] . '" href="' . $link . '"><img src="' . $thumb[0] . '" width="' . $vars['width'] . '" alt="' . $item->post_title . '"/></a>';

            $carousel .= '<div class="carousel-caption">';

            $carousel .= '<h3 class="carousel-post-title">' . $item->post_title . '</h3>';

            if( $vars['comments'] && comments_open( $item_id ) ) :

                $comments = ( get_comments_number( $item_id ) != 0 ) ? sprintf( _n( '1 Comment', '%1$s Comments', get_comments_number( $item_id ), 'wp_bootstrap_carousel' ), number_format_i18n( get_comments_number( $item_id ) ) ) : __( '0 Comments', 'wp_bootstrap_carousel' );

                $carousel .= '<p class="carousel-comments-link">
                    <a href="' . get_comments_link( $item_id ) . '" rel="bookmark">' . $comments . '</a>
                </p>';

            endif;

            $carousel .= apply_filters( 'wp_bootstrap_carousel_caption_text', $text, $item_id );

            $carousel .= '</div><!-- .carousel-caption -->';

            $carousel .= '</div><!-- .item -->';

            $i++;
        }

        $carousel .= '</div><!-- .carousel-inner -->';

        /**
         * CONTROLS
         */
        if( $vars['controls'] )
            $carousel .= '<!-- Carousel nav -->
  <a class="carousel-control left" href="#wp-bootstrap-carousel-' . $vars['id'] . '" data-slide="prev"><span class="icon-prev"></span></a>
  <a class="carousel-control right" href="#wp-bootstrap-carousel-' . $vars['id'] . '" data-slide="next"><span class="icon-next"></span></a>';

        $carousel .= '</div><!-- .carousel -->';

        return $carousel;
    }
    public function get_data( $atts )
    {
        global $post, $content_width;

        $id = intval( $post->ID );
        $thumbnail_id = get_post_meta( $id, '_thumbnail_id', true );

        $atts = shortcode_atts( apply_filters( 'wp_bootstrap_carousel_shortcode_atts', array(

            'post_parent'       => $id,
            'post_status'       => 'inherit',
            'post_type'         => 'attachment',
            'post_mime_type'    => 'image',
            'exclude'           => $thumbnail_id,
            'order'             => 'ASC',
            'orderby'           => 'ID',

            'width'             => ( isset( $content_width ) ) ? $content_width : '300',
            'image_size'        => 'large',
            'rel'               => '',
            'file'              => 1,
            'comments'          => 1,
            'slide'             => 1,
            'controls'          => 1,

            'interval'          => 5000,    // (int) The amount of time to delay between automatically cycling an item. If false, carousel will not automatically cycle.
            'pause'             => 'hover', // (string) Pauses the cycling of the carousel on mouseenter and resumes the cycling of the carousel on mouseleave.
            'wrap'              => 1,       // (bool) Whether the carousel should cycle continuously or have hard stops.
            'thickbox'          => 1,
            
        ) ), $atts, 'carousel' );

        // query vars
        $post_parent    = intval( $atts['post_parent'] );
        $post_status    = sanitize_text_field( $atts['post_status'] );
        $post_type      = sanitize_text_field( $atts['post_type'] );
        $post_mime_type = sanitize_text_field( $atts['post_mime_type'] );
        $exclude        = array_map( 'intval', explode( ',', $atts['exclude'] ) );
        $order          = sanitize_key( $atts['order'] );
        $orderby        = sanitize_key( $atts['orderby'] );

        if ( 'RAND' == $order )
            $orderby = 'none';

        // display vars
        $width          = intval( $atts['width'] );
        $image_size     = sanitize_text_field( $atts['image_size'] );
        $rel            = sanitize_text_field( $atts['rel'] );
        $file           = wp_bc_bool( $atts['file'] );
        $comments       = wp_bc_bool( $atts['comments'] );
        $slide          = wp_bc_bool( $atts['slide'] );
        $controls       = wp_bc_bool( $atts['controls'] );

        // js vars
        $interval       = intval( $atts['interval'] );
        $pause          = sanitize_text_field( $atts['pause'] );
        $wrap           = wp_bc_bool( $atts['wrap'] );
        $thickbox       = wp_bc_bool( $atts['thickbox'] );

        // query vars array
        $args = array(
            'post_parent'       => $post_parent,
            'post_status'       => $post_status,
            'post_type'         => $post_type,
            'post_mime_type'    => $post_mime_type,
            'exclude'           => $exclude,
            'order'             => $order,
            'orderby'           => $orderby
        );

        // display vars array
        $vars = array(
            'id'                => $post_parent,
            'width'             => $width,
            'image_size'        => $image_size,
            'rel'               => $rel,
            'file'              => $file,
            'comments'          => $comments,
            'slide'             => $slide,
            'controls'          => $controls,

            'interval'          => $interval,
            'pause'             => $pause,
            'wrap'              => $wrap,
            'thickbox'          => $thickbox,
        );

        // data array
        $data = array( 
            'query' => get_children( $args ),
            'vars' => $vars
        );

        return $data;
    }
    public function enqueue( $thickbox )
    {
        $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        // bootstrap scripts
        wp_enqueue_script( 'wp-bootstrap-carousel',      $this->plugin_dir_url . 'js/carousel' . $min . '.js', array( 'jquery' ), '3.0.0', true );
        wp_enqueue_script( 'wp-bootstrap-carousel-init', $this->plugin_dir_url . 'js/carousel-init.js', array( 'jquery', 'wp-bootstrap-carousel' ), $this->version, true );

        // thickbox styles & script
        if( $thickbox ) :

            wp_enqueue_style( 'thickbox' );
            wp_enqueue_script( 'thickbox' );

        endif;

        // action hook
        do_action( 'wp_bootstrap_carousel_enqueue' );
    }
    public function wp_enqueue_scripts()
    {
        $min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        wp_enqueue_style( 'wp-bootstrap-carousel',       $this->plugin_dir_url . 'css/carousel' . $min . '.css', array(), $this->version, 'screen' );
    }
    public function body_class( $classes )
    {
        $classes[] = 'wpbc-' . sanitize_html_class( strtolower( wp_get_theme()->Name ) );

        return $classes;
    }
    public function be_display_posts_plugin()
    {
        if ( ! class_exists( 'WP_Bootstrap_Carousel_DPS' ) )
            include( 'inc/wp-bootstrap-carousel-dps.php' );

        return new WP_Bootstrap_Carousel_DPS();
    }
    public function plugin_row_meta( $links, $file )
    {
        $plugin = plugin_basename( __FILE__ );

        if ( $plugin === $file )
        {
            $links[] = sprintf( '<a href="%1$s" target="_blank">' . __( 'Wiki', 'wp_bootstrap_carousel' ) . '</a>', esc_url( 'https://github.com/diggy/wp-bootstrap-carousel/wiki' ) );
            $links[] = sprintf( '<a href="%1$s" target="_blank">' . __( 'Donate', 'wp_bootstrap_carousel' ) . '</a>', esc_url( 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=JMEK9E362ALYJ' ) );
        }

        return $links;
    }
} // class

/**
 * Init WP_Bootstrap_Carousel class
 *
 * Initializes the main plugin class
 *
 * @since WP Bootstrap Carousel 0.1
 */

$GLOBALS['wp_bootstrap_carousel'] = new WP_Bootstrap_Carousel();

} // class_exists check

/**
 * Convert string to boolean
 *
 * @since  WP Bootstrap Carousel 0.2.1
 * @param  string $value true/on/yes/1, or false/off/no/0
 * @return bool          true or false
 */
function wp_bc_bool( $value )
{
    return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
}