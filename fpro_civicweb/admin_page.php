<?php 
$options_array = $bdfp_options;
    if($_REQUEST["update_settings"]) {
        $options_array["api_username"]              = $_POST["api_username"];
        $options_array["api_password"]              = $_POST["api_password"];
        $options_array["civicweb_url"]              = $_POST["civicweb_url"];
        $options_array["tree_starting_id"]          = $_POST["tree_starting_id"];
        $options_array["paging_links_in_view"]      = $_POST["paging_links_in_view"];
        $options_array["search_results_per_page"]   = $_POST["search_results_per_page"];
        $options_array["show_next_last"]            = @$_POST["show_next_last"] ? '1' : '0';
        $options_array["integrate_filepro_search"]  = @$_POST["integrate_filepro_search"] ? '1' : '0';
        $update_options = serialize($options_array);
        update_option("BDFP_options",$update_options);
    }
      
    $api_username = $options_array["api_username"];
    $api_password = $options_array["api_password"];
    $api_civicweb_url = $options_array["civicweb_url"];
    // Where to start the "Tree". Set this to 0 for root, or enter in the folder ID you want 'root' to be.
    $tree_starting_id = @$options_array["tree_starting_id"] or $tree_starting_id = 0;
    $search_results_per_page = @$options_array["search_results_per_page"] or $search_results_per_page = 5;
    /** this variable sets how many page links + or - of the current page is shown. so links in view = 4 would be page 1-5 links.  etc
    *  Or if current page is 5, you'd see pages 1 - 9
    */
    $paging_links_in_view = @$options_array["paging_links_in_view"] or $paging_links_in_view = 4;
    // show next/prev links (<< <) and > >>)
    $show_next_last = $options_array["show_next_last"] ? '1' : '0';
    $integrate_filepro_search = $options_array["integrate_filepro_search"] ? '1' : '0';
?>

<form method="post">
<h1>CivicWeb/FilePro Settings</h1>
<div class="bdfp_admin_settings">
    <ul>
        <li>CivicWeb URL</li>
        <li><input type="text" name="civicweb_url" value="<?php print($api_civicweb_url);?>"></li>
    </ul>
    <ul>
        <li>API Username</li>
        <li><input type="text" name="api_username" value="<?php print($api_username);?>"></li>
    </ul>
    <ul>
        <li>API Password</li>
        <li><input type="text" name="api_password" value="<?php print($api_password);?>"></li>
    </ul>
    <ul>
        <li>Tree Starting ID</li>
        <li><input type="text" name="tree_starting_id" value="<?php print($tree_starting_id);?>"></li>
    </ul>
    <ul>
        <li>Search Results Per Page</li>
        <li><input type="text" name="search_results_per_page" value="<?php print($search_results_per_page);?>"></li>
    </ul>
    <ul>
        <li>Paging Links In View</li>
        <li><input type="text" name="paging_links_in_view" value="<?php print($paging_links_in_view);?>"></li>
    </ul>
    <ul>
        <li>Show Next/Last Links</li>
        <li><input type="checkbox" name="show_next_last" <?php if($show_next_last == 1) echo "checked"; ?>></li>
    </ul>
    <ul>
        <li>Integrate FilePro Searching**</li>
        <li><input type="checkbox" name="integrate_filepro_search" <?php if($integrate_filepro_search == 1) echo "checked"; ?>></li>
    </ul>
    <ul>
        <li><input type="submit" name="update_settings" value="Update Settings"></li>
    </ul>

</div>

</form>
<div style='width:85%;'>
    <ul>
     <li>* Note: To use the filetree on your pages-the short code is : <span style='color:#b70000;'>[bdfp_filetree] </span> To specificy a specific starting ID which is different
     than your default id used above specify it like so: <span style='color:#b70000;'>[bdfp_filetree <b>id="90"</b>] </span></li> 
     <li>** <b>IMPORTANT:</b> Use of this feature may break other search plugins/integrations! 
    You are able to include the search functionality on a custom page aswell by using the short code <span style='color:#b70000;'>[bdfp_search]</span> </li>
     </ul>
</div>
