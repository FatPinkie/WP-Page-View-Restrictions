<?php

/**
 * Plugin Name:       WP Page View Restrictions
 * Version:           1.0.0
 * Author:            Denis Hudinčec
 */
 
 
 
register_activation_hook(__FILE__, 'add_metafield');

function add_metafield(){
    //Add meta field to be used by the plugin
    $args = array(
        'post_type' => 'page', 
        'posts_per_page'   => -1 
    );
    $posts = get_posts($args);
    foreach ( $posts as $post ) {
        add_post_meta( $post->ID, 'restrict', 'Non Restricted');
    }
}
 
 
 
 function restr_options_page()
 //Adding settings menu in admin page
{
    add_submenu_page(
        'options-general.php',
        'WP Page View Restrictions',
        'WP Page View Restrictions',
        'manage_options',
        'restr',
        'restr_options_page_html'
    );
}
add_action('admin_menu', 'restr_options_page');


function restr_options_page_html() {
    // check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    
    
    <div class="wrap">
        <?php //display pages list in admin menu
        $posts = get_pages() ?>
        <table id="postTable" class="wp-list-table widefat fixed striped table-view-list pages">
            <thead>
              <tr>
               <th>Page Title</th>
               <th>Author</th>
               <th>Page Status</th>
               <th>Restrictions</th>
             </tr>
            </thead>
            <tbody>
                <?php foreach($posts as $post){?>
            <tr>
                <td><?php echo $post->post_title?></td>
                <td><?php echo $post->post_author?></td>
                <td><?php echo $post->post_status?></td>
                <td><div id="<?php $post->ID ?>">
                
                <?php  
                  $link = admin_url('admin-ajax.php?action=do_change&post_id='.$post->ID.'');
                    echo '<a class="change" " data-post_id="' . $post->ID . '" href="' . $link . '">' . $post->restrict . '</a>';
                    ?>
                    </div>
                </td>
            </tr>
            <?php } ?>
            </tbody>
    
    </div>
    
  
    
    <?php
}
 

//AJAX function
add_action("wp_ajax_do_change", "do_change");
add_action("wp_ajax_nopriv_do_change", "my_must_login");

function do_change() {

   $restrict = get_post_meta($_REQUEST["post_id"], "restrict", true);
    if($restrict === "Restricted") {
      $new_restrict="Non Restricted";
   }
   else {
       $new_restrict="Restricted";
   }

    update_post_meta($_REQUEST["post_id"], "restrict", $new_restrict);
    
    $result['post'] = $_REQUEST["post_id"];
    $result['restrict'] =  $new_restrict;
    $result = json_encode($result);
    
    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      $result = json_encode($result);
      echo $result;
   }
   else {
      header("Location: ".$_SERVER["HTTP_REFERER"]);
   }
    
   die();
}

//Script registration
add_action( 'init', 'my_script_enqueuer' );
function my_script_enqueuer() {
   wp_register_script( "script", WP_PLUGIN_URL.'/wp-page-view-restrictions/js/script.js', array('jquery') );
   wp_localize_script( 'script', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        

   wp_enqueue_script( 'jquery' );
   wp_enqueue_script( 'script' );

}


//Restrict non logged users to pages
add_action('template_redirect','my_non_logged_redirect');
function my_non_logged_redirect()
{

     if ((get_post_meta(get_the_ID(), 'restrict', true)==="Restricted") && is_user_logged_in() )
    {
        wp_redirect( home_url() );
        die();
    }
}   

