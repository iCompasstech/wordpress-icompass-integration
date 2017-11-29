   <?php
       // this file is called when the search shortcode is used.
              ?>           
              
                <?PHP if($_REQUEST["bdfp_search"]) { ?>
                <header class="page-header">
                    <h1 class="page-title"><?php printf( __( 'Search Results for: %s'), '<span>' . $_REQUEST["bdfp_search"] . '</span>' ); ?></h1>
                </header>
                
                <?php 
                }
                
                    ?>
                    <form method="get" id="searchform" action="">
                        <label for="s" class="assistive-text">Search</label>
                        <input type="text" class="field" name="bdfp_search" id="bdfp_search" placeholder="Search FilePro Database" />
                        <input type="submit" class="submit" name="submit" id="searchsubmit" value="Search" />
                    </form>
                <?PHP  
                if($_REQUEST["bdfp_search"]) {
                    // show bdfp search results
                    // to style the pagination and results refer to assets/css/style.css
                   print($output["pagination"]);
                   print($output["results"]);                                 
                   print($output["pagination"]);
                }