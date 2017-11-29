<?php
// this form has the bdfp_search variable declared in it. included on the filepro search results page when none found.
//
?>
<div class="entry-content" style='width:200px;'>
    <?php _e( 'No results found!' ); ?>
        <form method="get" id="searchform" action="">
        <label for="s" class="assistive-text">Search</label>
        <input type="text" class="field" name="s" id="s" placeholder="Search" />
        <input type="hidden" name="bdfp_search" value="true" />
        <input type="submit" class="submit" name="submit" id="searchsubmit" value="Search" />
    </form>
</div>