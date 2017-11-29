<?php
/*
Plugin Name: FilePro - CivicWeb Plugin
Plugin URI: 
Description: Add search/listing funcionality from filepro.civicweb (06/04/2012)
Author: iCompass
Version: 0.5
Author URI:
*/

     $siteurl = get_option('siteurl');
    define('BDFP_FOLDER', dirname(plugin_basename(__FILE__)));
    define('BDFP_URL', $siteurl.'/wp-content/plugins/' . BDFP_FOLDER);
    define('BDFP_FILE_PATH', dirname(__FILE__));
    define('BDFP_DIR_NAME', basename(BDFP_FILE_PATH));
    // this is the table prefix
    global $wpdb;
     $bdfp_options = unserialize(get_option("BDFP_options"));
     wp_enqueue_script('bdfp_filetree', BDFP_URL .'/assets/js/jqueryFileTree.js',array("jquery"));  
     wp_enqueue_style('bdfp_filetree_css', BDFP_URL.'/assets/css/jqueryFileTree.css');
     wp_enqueue_style('bdfp_style', BDFP_URL.'/assets/css/style.css');
     wp_enqueue_style('mystylesheet', BDFP_URL.'/assets/css/style.css');
      
    add_action('admin_menu','bdfp_admin_menu'); 
    function bdfp_admin_menu() { 
        add_menu_page(
            "FilePro",
            "FilePro",
            8,
            __FILE__,
            "bdfp_admin_menu_display"
        ); 
    }

    function bdfp_admin_menu_display() {
     global $bdfp_options;  
     if ( current_user_can('manage_options') ) {   
        include 'admin_page.php';
     }
    }

    //Add ShortCode for "front end listing"  for file tree viewing
    add_shortcode("bdfp_filetree","bdfp_filetree");       
    add_action('wp_ajax_nopriv_bdfp_filetree_ajax', 'bdfp_filetree_ajax'); 
    add_action('wp_ajax_bdfp_filetree', 'bdfp_filetree_ajax');
   
     function bdfp_filetree_ajax() {
        global $bdfp_options;
        // get shortcode option if id is set
        extract( shortcode_atts( array('id' => false ), $atts));
        if(is_numeric($_GET["tree_starting_id"])) $bdfp_options["tree_starting_id"] = $_GET["tree_starting_id"];
        include 'class-filepro.php';
        exit; 
     }
     function bdfp_filetree($atts) {
        global $bdfp_options;   
        global $siteurl;   
        if(is_numeric($atts["id"])) $tree_starting_id = $atts["id"];
        include 'bdfp_filetree.php';    
    }      
// includes [bdfp_filetree] shortcode

    add_shortcode("bdfp_search","bdfp_search");
    function bdfp_search() {
        global $bdfp_options;
        global $bdfp_search_function;
        include 'class-filepro.php';
      include 'search_shortcode.php';  
    }

   function bdfp_show_link() {
        $bdfp_link = "<a href='?s=".$_REQUEST["s"]."&bdfp_search=true'>Search FilePro Database</a>"; 
        print($bdfp_link);
    } 
    function bdfp_show_wp_search_link() {
      $bdfp_link = "<a href='?s=".$_REQUEST["s"]."'>Search WordPress Database</a>"; 
        print($bdfp_link);  
    }  

    // if search is integrated, take it over.
    if($bdfp_options["integrate_filepro_search"] == 1) {
      add_action("wp","trickme");  
    }
    
    // trick wp into thinking there is 1 post even when there isn't, only way to control the page without modifying a theme.
    function trickme() {
        global $wp_query;
        global $no_results_found;
        if(!is_search()) return;
      $actual_posts = $wp_query->post_count;
      if($actual_posts == 0) {
          $wp_query->current_post = '-1';
          $wp_query->current_count = 0;
          $wp_query->found_posts = 1;
          $wp_query->post_count = 1;
          $wp_query->current_comment = -1;
          $wp_query->max_num_pages = 1;
          if(is_null($_REQUEST["bdfp_search"])) {
           add_action('loop_end', "bdfp_no_results_found");    
          }
           
      }
    } 
    
    // bdfp regular search no results found.
    function bdfp_no_results_found() {
        print("No Results Found!");
        get_search_form();
    }
    // bdfp filepro search page no results found/show search form.
    function bdfp_no_results_found_bdfp_search_form() {
        include 'search_form.php';
    }
    // take the loop over to inject content.
    if( ! isset($_REQUEST["bdfp_search"]) && ($bdfp_options["integrate_filepro_search"] == 1) && ($_REQUEST["s"])) {
          add_action('loop_start', "bdfp_show_link");  
    }
    
  if(($bdfp_options["integrate_filepro_search"] == 1) && (isset($_REQUEST["bdfp_search"])) && ($_REQUEST["s"])) {                 
    add_action('loop_start', "bdfp_search_display");            
    //add_action("search_template", "bdfp_search_display");
    function bdfp_search_display( ) { 
        static $count; 
        global $wp_query;
        global $more; 
        global $bdfp_options;
         if(is_null($count)) { $count = 0; }
         if($count == 0) {
            bdfp_show_wp_search_link();
            include 'class-filepro.php';
            print($output["pagination"]);
            print($output["results"]);
            print($output["pagination"]);
            if($output["total"] == 0) {
              bdfp_no_results_found_bdfp_search_form();  
            }
        }      
          $count++;
    }
   
   // search routing.
    if( $_REQUEST["s"] && $_REQUEST["bdfp_search"] ) {
       // $bdfp_link = "<a href='?s=".$_REQUEST["s"]."'>Search Main Site</a>";
       // include 'class-filepro.php';
    }   
    elseif( ! $_REQUEST["bdfp_search"] && $_REQUEST["s"]) {
        $bdfp_link = "<a href='?s=".$_REQUEST["s"]."&bdfp_search=true'>Search FilePro Database</a>"; 
    }
  }
?>
