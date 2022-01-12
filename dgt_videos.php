<?php 
  $the_query = new WP_Query( array('posts_per_page'=>10,
  'post_type'=>'dgtvideos',
  'paged' => get_query_var('paged') ? get_query_var('paged') : 1) 
); 
?>


<div class="video-items <?php echo $type; ?>">
  <button aria-label="Close" class="show-videos"><span>Video Gallery</span></button>

  <a href="<?php bloginfo('url'); ?>/videos" class="sidebar-title">View all Videos</a>

  <div class="items">
    <?php while ($the_query -> have_posts()) : $the_query -> the_post(); ?>

    <div class="item">



      <div class="thumb">

        <?php 
  if(!function_exists("get_youtube_id_from_url")) {
  function get_youtube_id_from_url($url)  {
          preg_match('/(http(s|):|)\/\/(www\.|)yout(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $results);    return $results[6]; 
        } 
    } else {
      // it already exists, do something else
    } 
?>

        <img
          src="https://img.youtube.com/vi/<?php echo get_youtube_id_from_url(get_post_meta(get_the_ID(), 'url1', true)); ?>/maxresdefault.jpg"
          alt="<?php echo get_the_title(); ?>">


        <button class="lets-play" data-video="<?php echo get_post_meta(get_the_ID(), 'url1', true); ?>"
          aria-label="Play"><span>Play Video</span></button>

      </div>
      <h3><?php echo get_the_title(); ?></h3>
      <?php the_content(); ?>

    </div>

    <?php endwhile; ?>
  </div>

  <div class="video-pagination">
    <?php $big = 999999999;  
    echo paginate_links( array(
    'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
    'format' => '?paged=%#%',
    'current' => max( 1, get_query_var('paged') ),
    'total' => $the_query->max_num_pages
    ) );

    wp_reset_postdata(); ?>
  </div>

</div>