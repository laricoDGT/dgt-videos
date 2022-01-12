<?php

/**
 * Plugin Name: DGT Videos 
 * Description: Video Collection and sidebar items - Use the Shortcode [dgt_videos] for collection and [dgt_videos type="sidebar"] for sidebar 
 * Author: TREMGroup
 * Version: 1.0 
 */

define('DGTIDX_PREFIX', 'dgt');

function dgt_videos() {

  $post_single = 'Video';
  $post_plural = 'Videos';
  
    $labels = array( 
        'name'               => __( $post_plural, DGTIDX_PREFIX ),
        'singular_name'      => __( $post_single, DGTIDX_PREFIX ),
        'add_new'            => __( 'Add New '.$post_single, '${4:Name}', DGTIDX_PREFIX ),
        'add_new_item'       => __( 'Add New '.$post_single, DGTIDX_PREFIX ),
        'edit_item'          => __( 'Edit '.$post_single, DGTIDX_PREFIX ),
        'new_item'           => __( 'New '.$post_single, DGTIDX_PREFIX ),
        'view_item'          => __( 'View '.$post_single, DGTIDX_PREFIX ),
        'search_items'       => __( 'Search '.$post_plural, DGTIDX_PREFIX ),
        'not_found'          => __( sprintf('No %s found', strtolower($post_single)), DGTIDX_PREFIX ),
        'not_found_in_trash' => __( sprintf('No %s found in Trash', strtolower($post_single)), DGTIDX_PREFIX ),
        'parent_item_colon'  => __( 'Parent '.$post_single, DGTIDX_PREFIX ),
        'menu_name'          => __( $post_plural, DGTIDX_PREFIX ),
    );
    $args = array( 
        'labels'              => $labels,
        'hierarchical'        => true,
        'description'         => 'description',
        'taxonomies'          => array( 'video-cat' ),
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-star-filled',
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'publicly_queryable'  => true,
        'exclude_from_search' => false,
        'has_archive'         => true,
        'query_var'           => true,
        'can_export'          => true,
        'rewrite'             => true,
        'capability_type'     => 'post', 
        'supports'            => array( 'title', 'editor', 'thumbnail'),
    );
    // url slug
    register_post_type( 'dgtvideos', $args );
}
add_action( 'init', 'dgt_videos' );

function videos_create_taxonomies() 
{
    register_taxonomy( 'video-cat', array( 'dgtvideos' ), array(
        'hierarchical' => true,
        'label' => 'Video Category',
        'singular_name' => 'Video Category',
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => array( 'slug' => 'video-cat' )
    ));
}
add_action( 'init', 'videos_create_taxonomies', 0 );


function dgtvideos_columns($defaults) {
    $defaults['video-cat'] = 'Video Category';
    return $defaults;
}
function dgtvideos_custom_column($column_name, $post_id) {
    $taxonomy = $column_name;
    $post_type = get_post_type($post_id);
    $terms = get_the_terms($post_id, $taxonomy);

    if ( !empty($terms) ) {
        foreach ( $terms as $term )
            $post_terms[] = "<a href='edit.php?post_type={$post_type}&{$taxonomy}={$term->slug}'> " . esc_html(sanitize_term_field('name', $term->name, $term->term_id, $taxonomy, 'edit')) . "</a>";
        echo join( ', ', $post_terms );
    }
    else echo '<i>Not assigned.</i>';
}
add_filter( 'manage_project_posts_columns', 'dgtvideos_columns' );
add_action( 'manage_project_posts_custom_column', 'dgtvideos_custom_column', 10, 2 );





// Register meta box
function video_register_meta_boxes() { 
	add_meta_box( 'video-box-id', __( 'Video Urls', '' ), 'videos_display_callback', 'dgtvideos', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'video_register_meta_boxes' );


/// Meta box display callback 
function videos_display_callback( $post ) {
	
	$url1 = get_post_meta( $post->ID, 'url1', true ); 
	// $url2 = get_post_meta( $post->ID, 'url2', true );
	 
	wp_nonce_field( 'video_box_nonce', 'meta_box_nonce' );
	
	
	echo '<p><label for="url1_label">YouTube URL</label> <input type="text" name="url1" size="52" id="url1" value="'. $url1 .'" /></p>';
	// echo '<p><label for="url2_label">Vimeo URL </label> <input type="text" name="url2" id="url2" value="'. $url2 .'" /></p>';
}

// Save meta box content 
function video_save_meta_box( $post_id ) {
 
	if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
 
	if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'video_box_nonce' ) ) return;
 
	if( !current_user_can( 'edit_post' ) ) return;
	
	
 
	if( isset( $_POST['url1'] ) )
  update_post_meta( $post_id, 'url1', $_POST['url1'] );
  /*
	if( isset( $_POST['url2'] ) )
  update_post_meta( $post_id, 'url2', $_POST['url2'] );
  */
}
add_action( 'save_post', 'video_save_meta_box' );


function video_css_scripts(){
    wp_enqueue_script('video-script', plugins_url('/js/scripts.min.js', __FILE__), array('jquery'), '', true ); 
    wp_enqueue_style( 'video-stylesheet', plugins_url('/css/style.css', __FILE__), false, '');
}
add_action('wp_enqueue_scripts', "video_css_scripts");


 


// THE SHORTCODE 
add_shortcode('dgt_videos', 'videos_sc');
function videos_sc($atts) {

    extract( shortcode_atts( array(
        'type' => '',
        ), $atts ) );

  ob_start();
  $query_args_list = array(
              'post_type' => 'dgtvideos',
              'posts_per_page' => -1 
          );
  $loop_list = new WP_Query($query_args_list);
  
  include 'dgt_videos.php';
  
  $output = ob_get_clean();  
  return $output;
}
 