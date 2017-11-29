<?php   

  
?>
     <div id='fileTree'>Loading...</div>
    
         <script language="javascript">
         jQuery(document).ready(function($) {

$('#fileTree').fileTree({ root: '0', script: '<?php print($siteurl)?>/wp-admin/admin-ajax.php?action=bdfp_filetree&tree_starting_id=<?PHP print($tree_starting_id); ?>',expandSpeed: 250, collapseSpeed: 250 },
                     function(file) {
                    window.open(file, 'filepro');
                });  
         
});
         </script>