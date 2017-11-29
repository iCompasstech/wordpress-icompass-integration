<?php

class filepro {
    // API CREDENTIALS & MAIN SETTINGS
    function __construct($options) {
    $options_array = $options; 
    $this->username = $options_array["api_username"];   
    $this->password = $options_array["api_password"];
    $this->civicweb_url = $options_array["civicweb_url"];
    // Where to start the "Tree". Set this to 0 for root, or enter in the folder ID you want 'root' to be.
    $this->tree_starting_id = $options_array["tree_starting_id"];
    $this->search_results_per_page = $options_array["search_results_per_page"];
    /** this variable sets how many page links + or - of the current page is shown. so links in view = 4 would be page 1-5 links.  etc
    *  Or if current page is 5, you'd see pages 1 - 9
    */
    $this->paging_links_in_view = $options_array["paging_links_in_view"];
    // show next/prev links (<< <) and > >>)
    $this->show_next_last = $options_array["show_next_last"];
    
    /** If results or tree not displaying, turn on debug and it will PRINT neat array of any SOAP errors.
    *   It's not printed into a var, so using AJAX it will print into results, check developer console for post returns.
    */
    $this->debug = 0;    
    }
    

    
    // DO NOT CHANGE ANYTHING BELOW THIS UNLESS YOU KNOW WHAT YOU ARE DOING!!    
    function log_on() {  
      $results = array(
        'success' => FALSE,
        'session_id' => '',
      );
      if (strlen($this->civicweb_url) > 0) {
        try { 
          $client = new SoapClient($this->civicweb_url . 'Global/WebServices/Login.asmx?wsdl', array('exceptions' => 1,));        
          $parameters = new stdClass();
          $parameters->userName = $this->username;
          $parameters->password = $this->password;
          $retval = $client->LoginUser($parameters);
          $results['success'] = $retval->LoginUserResult;
          $results['session_id'] = $client->_cookies['CurrentSession'][0];
        }
        catch (SoapFault $E) { 
          $results['success'] = FALSE;
          $results['session_id'] = '';
        }
      }
      
      return $results;
    }

    /**
    * Get Search Result Document IDs.
    * We must first get the IDs before we pass it onto the next function which actually returns the filename and sample html with highlighted words.. 
    * Can't skip this step
    */
    function get_search_results($exact_phrase, $all_words, $at_least_one_word, $proximity_word_1, $proximity_word_2, $not_including_words) {
      $search_result_document_ids = array();
      try { 
        $client = @new SoapClient($this->civicweb_url . 'Global/WebServices/Document.asmx?wsdl', array('exceptions' => 1,));
        $client->__setCookie('CurrentSession', $logon_result['session_id']);
        
        $parameters = new stdClass();            
        $parameters->exactPhrase = $exact_phrase;
        $parameters->allWords = $all_words;
        $parameters->atLeastOneWord = $at_least_one_word;
        $parameters->wordsInProximity1 = $proximity_word_1;
        $parameters->wordsInProximity2 = $proximity_word_2;
        $parameters->wordsNotIncluded = $not_including_words; 
        $retval = $client->GetSearchResults($parameters);        
        if (property_exists($retval->GetSearchResultsResult, 'int')) {  
          $search_result_document_ids = $retval->GetSearchResultsResult->int;
        }
        else {
          $search_result_document_ids = array();
        }
      }
      catch (SoapFault $E) {  
        if($this->debug == 1) {
            print("<pre>");
            print_r($E);
            print("</pre>");
        }
        $search_result_document_ids = array(); 
      }
    return $search_result_document_ids;
    }
    
    /**
    *  This function is called from get_search_results and uses the IDS returned from the first soap call to search those id's for the phrases again to return 
    * the filename and basic html with highlighting.    
    */
    function createList($idList, $exact_phrase, $all_words, $at_least_one_word, $proximity_word_1, $proximity_word_2, $not_including_words) {
      try { 
        $client = @new SoapClient($this->civicweb_url . 'Global/WebServices/Document.asmx?wsdl', array('exceptions' => 1,));
        $client->__setCookie('CurrentSession', $logon_result['session_id']);
        
        $parameters = new stdClass();
        $parameters->searchResultsSubset = $idList;
        $parameters->exactPhrase = $exact_phrase;
        $parameters->allWords = $all_words;
        $parameters->atLeastOneWord = $at_least_one_word;
        $parameters->wordsInProximity1 = $proximity_word_1;
        $parameters->wordsInProximity2 = $proximity_word_2;
        $parameters->wordsNotIncluded = $not_including_words;
            
        $retval = $client->GetSearchResultDetails($parameters);
        if (property_exists($retval->GetSearchResultDetailsResult, 'DocumentSearchResult')) {
          $results = $retval->GetSearchResultDetailsResult->DocumentSearchResult;
        }
        else {
          $results = array();
        }
      }
      // if debug is on, echo in neat format
      catch (SoapFault $E) { 
        if($this->debug == 1) {
            print("<pre>");
            print_r($E);
            print("</pre>");
        }
        $results = array();
      
      }
      return $results;
    }
    // this generates the array for the directory listing for ajax / filetree display page (function fileprotree)
    function directoryListing($id = 0) {
       $logon_result = $this->log_on();
       if($id == 0 && $id != $this->tree_starting_id) $id = $this->tree_starting_id;
      $childList = array();
      if ($logon_result['success']) {
        if (strlen($this->civicweb_url) > 0) {
          //Get results
          try { 
            $client = new SoapClient($this->civicweb_url . 'Global/WebServices/Document.asmx?wsdl', array('exceptions' => 1,));
            $client->__setCookie('CurrentSession', $logon_result['session_id']);
            
            $parameters = new stdClass();
            $parameters->id = $id;
            $parameters->Path = NULL;
            $parameters->includeDocuments = "true";
            $parameters->documentProvider = "iCompass.CivicWeb.Items.DocumentProvider";
            $parameters->controlID = NULL;    
            $retval = $client->GetChildList($parameters);
            $childList = $retval->GetChildListResult;
          }
          catch (SoapFault $E) { 
              if($this->debug == 1) {
                  print("<pre>"); 
                  print_r($E);
                  print("</pre>");
              }
            $childList = array();
          } 
          return $childList;
        }
      }  
    } 
   // this function returns results for TREE viewing
function fileproTree() {
    $dir = str_replace("/","",$_POST["dir"]);
    $var = $this->directoryListing($dir);
    $node = $var->Nodes->DocumentTreeNodeInformation;
    // this if statement fixes problems with the array not returning multi level if theres only 1 result - which would break the next function.
    // it would make it $name instead of $i->name.. essentially
    if(!is_array($node)) {
        $node = array($node);
    }
    // this checks if the node has files/folders in it, if it does check if it is a folder then display accordingly. REL is the folder ID on the api
    if($node[0]->Name != "") {    
        echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
        foreach($node AS $i) {
            if($i->Folder == 1) {
                echo "<li class=\"directory collapsed\"><a href=\"#\" rel=\"/" . $i->ID . "/\">" . htmlentities($i->Name) . "</a></li>";
            } else { 
                echo "<li class=\"file ext_file\"><a href=\"#\" rel=\"".$this->civicweb_url."/Documents/DocumentDisplay.aspx?Id=". $i->ID . "\" target='_BLANK'>" . $i->Name . "</a></li>";  
            }
        } 
        echo "</ul>"; 
    }  else {
        echo "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
        echo "<li class=''><i>Empty</i></li>";
        echo "</ul>";
        
    }
}
// this function handles search functionality and returns results to be displayed
function search($search_query) {
    $all_words = @$_GET["all_words"] or $all_words = "";
    $at_least_one_word = "";
    $proximity_word_1 =  "";
    $proximity_word_2 =  "";   
    $not_including_words = "";
    $exact_phrase = $search_query;
    // this gets the first set of results (ids using the search terms)
    $result_ids = $this->get_search_results($exact_phrase,$all_words,$at_least_one_word,$proximity_word_1,$proximity_word_2,$not_including_words);
    
    // this next small snippet handles returned ID and handles which to send to the next function (this does paging)
    $filepro_search_results_count = count($result_ids); 
    $output["total"] = $filepro_search_results_count;    
      $current_page = @$_GET["results_page"] or $current_page = 1;
      $detailed_result_ids = array();
      for ($index = ($current_page - 1) * $this->search_results_per_page; $index < count($result_ids) && $index < ($current_page * $this->search_results_per_page); $index++) {
        $detailed_result_ids[] = $result_ids[$index];
      }   
      // take IDS sent through paging and grab detailed information
      $results = $this->createList($detailed_result_ids, $exact_phrase, $all_words, $at_least_one_word, $proximity_word_1, $proximity_word_2, $not_including_words);
    

    // Generate paging links! :D
    if ($filepro_search_results_count > 0) {
        $pagination = '<div class="fp_item-list"><ul class="pagination">';
        $first_page = $current_page == 1;
        
        $query_string_original = $_SERVER["QUERY_STRING"]; 
        if(!strpos($query_string_original,"&results_page")) $query_string_original .= "&results_page=1";
        $query_string = str_replace("&results_page=$current_page","&results_page",$query_string_original);
        if($this->show_next_last == 1) {
            $pagination .= '<li class="jump_page">' . (!$first_page ? '<a href="?'.$query_string.'=1" title="First">&lt;&lt;</a>' : '<a href="#">&lt;&lt;</a>') . '</li><li class="jump_page">' . (!$first_page ? '<a href="?'.$query_string.'=' . ($current_page - 1) . '" title="Previous">&lt;</a>' : '<a href="#">&lt;</a>') . '</li>';
        }
        $filepro_search_results_per_page = $this->search_results_per_page;
        $total_pages = ceil($filepro_search_results_count / $this->search_results_per_page);
        
        for ($count = 1; $count <= $total_pages; $count++) {
            if ($count != $current_page && $count >= ($current_page - $this->paging_links_in_view) && $count <= ($current_page + $this->paging_links_in_view)) {
                $pagination .= '<li><a href="?'.$query_string.'=' . $count . '">' . $count . '</a></li>';
            }
            else if ($count == $current_page) {
                $pagination .= '<li class="active">' . $count . '</li>';
            }
        }
        
        $last_page = $current_page == $total_pages;
        if($this->show_next_last == 1) {
            $pagination .= '<li class="jump_page">' . (!$last_page ? '<a href="?'.$query_string.'=' . ($current_page + 1) . '" title="Next">&gt;</a>' : '<a href="#">&gt;</a>') . '</li><li class="jump_page">' . (!$last_page ? '<a href="?'.$query_string.'=' . $total_pages . '" title="Last">&gt;&gt;</a>' : '<a href="#">&gt;&gt;</a>') . '</li>';
        }
        $pagination .= '</ul></div>';
        
    }
    $output["pagination"] = $pagination;
    
    
    // Lets generate the nice HTML 
    $output["results"] .= "<div class='fp_results_container'>";
    foreach ($results AS $i) {
        $file_link = $this->civicweb_url."Documents/DocumentDisplay.aspx?Id=".$i->Id;
        $TitleHtml = $i->TitleHtml;
        $SampleHtml = trim($i->SampleHtml);
        if(strlen($SampleHtml) < 1) { 
            $file_link = $this->civicweb_url."Documents/DocumentList.aspx?ID=".$i->Id;
            $SampleHtml = "<i>Directory Listing</i>"; 
        }
        $PathHtml = str_replace("DocumentList.aspx",$this->civicweb_url . "Documents/DocumentList.aspx",$i->PathHtml);
        $output["results"] .= "<ul>";
        $output["results"] .= "<li class='fp_link'> <a href=$file_link target='_BLANK'>$TitleHtml</a></li>";
        $output["results"] .= "<li class='fp_samplehtml'>$SampleHtml</li>";
        $output["results"] .= "<li class='fp_pathhtml'> $PathHtml</li>";
        $output["results"] .= "</ul>";
    }
    $output["results"] .= "</div>";  
    return $output;
    } 
// end filepro class 
}


/**
* Don't forget styling options for search results are as follows:
*  Pagination:  div[fp_item-list] > ul [pagination]
*  Results: div[fp_results_container] > ul
* 
*/

     $filepro = new filepro($bdfp_options);
    
// basic control function to switch between search/tree listing
if(isset($_POST["dir"])) { 
     $filepro->fileproTree(); 
}

if($_REQUEST["bdfp_search"]) {
    $bdfp_search_query = @$_REQUEST["s"] or $bdfp_search_query = $_REQUEST["bdfp_search"];
    $output = $filepro->search($bdfp_search_query);
} 
   



?>
