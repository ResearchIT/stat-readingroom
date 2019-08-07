<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">


<script language="Javascript">

  function inverseOrder() {
     if ('desc' == document.mainform.titleorder.value.toLowerCase() ) {
         document.mainform.titleorder.value = 'ASC'; 
     } else {
         document.mainform.titleorder.value = 'DESC'; 
     }
     document.mainform.isold.value = 'yes'; 
     document.mainform.submit()
  }
  
  function yasumbit() { 
     document.mainform.submit();  
     /* do nothing else now. */  
   }

</script>




 <html>
 <head>
   <title>Reading Room Search and Checkout</title>
 </head>
 <body>

<?php
 include_once($_SERVER['DOCUMENT_ROOT'] . "/_common/db.php");
 include_once($_SERVER['DOCUMENT_ROOT'] . "/_common/class.table.php");
$red   = "<font color=\"#FF0000\">";
$black = "<font color=\"#000000\">";
// The authenticated user id
$user = $_SERVER['REMOTE_USER'];

// Is this an admin user
$adminUser = 0;
if(preg_match("/kathy|cpterson|siyaqing|jqguo|norma|eel|smart|shepard|riker|mtjernag/",$user)) { $adminUser = 1; }

$date = getdate();
$current_date = $date{'month'}." ".$date{'mday'}.", ".$date{'year'};

$opts = initializeParameters();

print("<!--POST(first) & opts");

print_r($_POST);

print_r($opts);

print("-->");


print "<form name='mainform' method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">";



if($adminUser == 1) {
//   adminScreen();
}

// getKeyWords();

if(isset($opts{'clear'})) {
   $opts = NULL;
}

userScreen(&$db,$opts);


// Now for some logic to deal with user input

$sqlorderstr = ' ORDER BY Title '.$opts{'titleorder'}.', Author '.$opts{'authororder'}.' ';


$stype = -1;

if(isset($opts{'all'})) {
   $stype = 0;  // search all 
} 
elseif(isset($opts{'search1'}) ) {
   $stype = 1;  // search  by inputted author and title
} elseif(isset($opts{'search2'}) ) {
   $stype = 2;  // search  by chosen author and title
} elseif(0 == strcmp('yes', $opts{'isold'})) {
     if(isset($opts{'oldall'}))  $stype=3; 
     else if(isset($opts{'oldsearch1'}))  $stype=4; 
     else if(isset($opts{'oldsearch2'}))  $stype=5; 
}


switch($stype) {
   case -1: 
       $books = array();  break;
   case 0:  
   case 3:  
      print("<input type='hidden' name='oldall'>");
      $books =& getBooks(&$db, $sqlorderstr,'','','','','',''); 
      break;
   case 1:  
   case 4:  
      print("<input type='hidden' name='oldsearch1'>"); 
      $books =& getBooks(&$db, $sqlorderstr, $opts{'oldtitle1'},$opts{'oldtitle1_logic'},$opts{'oldtitle2'},$opts{'oldauthor1'},$opts{'oldauthor1_logic'},$opts{'oldauthor2'});  
      break;
   case 2:  
   case 5:  
      print("<input type='hidden' name='oldsearch2'>"); 
      $books =& getBooks(&$db, $sqlorderstr, $opts{'oldtitle3'},$opts{'oldtitle2_logic'},$opts{'oldtitle4'},$opts{'oldauthor3'},$opts{'oldauthor2_logic'},$opts{'oldauthor4'}); 
}


$table = new table();
 $table->SetTableAttributes( array( "width" => "100%", "border" => "1", "align" => "center",
                            "cellpadding" => "1", "fgcolor" => "black" ) );
 $table->SetDefaultCellAttributes( array("bgcolor" => "white", "align" => "left" ) );
 
if(!empty($books)) {


    $tabtitlerow = $table -> AddRow();

    $str1 = "<input type='hidden' name='titleorder' value='".$opts{'titleorder'}."'>"; 
    $str1 .= "<a href='javascript:inverseOrder();'>Title</a>";
    $str2 = "<input type='hidden' name='authororder' value='".$opts{'authorrder'}."'> Author";

    print(keepLastParameters($opts));

    $table -> SetCellcontent( $tabtitlerow, 1, $str1);
    $table -> SetCellcontent( $tabtitlerow, 2, $str2);

   foreach ($books as $book) {
       $row = $table->AddRow();
       $table->SetCellContent( $row, 1, $book->Title);
       $table->SetCellContent( $row, 2, $book->Author);
   }  
   $table->printTable();
}

print "</form>";

print "</body> </html>";




// Function definitions
function userScreen($db,$opts) {

$choices = array("and", "or");

$table = new table();
 $table->SetTableAttributes( array( "width" => "100%", "border" => "0", "align" => "center",
                            "cellpadding" => "1", "fgcolor" => "black" ) );
 $table->SetDefaultCellAttributes( array("bgcolor" => "white", "align" => "left" ) );
 $row = $table->AddRow();
 $table->SetCellContent( $row, 1, " ");
 $table->SetCellContent( $row, 2, " ");
 $table->SetCellContent( $row, 3, " ");
 $table->SetCellContent( $row, 4, " ");
 $table->SetCellContent( $row, 5, " ");
 $table->SetCellContent( $row, 6, " ");
 $table->SetCellContent( $row, 7, " ");

 $row = $table->AddRow();
  $table->SetCellColSpan($row,1,7);
 $content =  "<h1> Search for a book</h1>";
 $table->SetCellContent( $row, 1, $content);
 $table->SetCellAttribute($row,1,"align","center");
// $table->SetCellAttribute($row,1,"colspan","7");
 
 $row = $table->AddRow();
 $table->SetCellColSpan($row,1,3);
 $content =  "<h2>Title Search</h2>";
 $table->SetCellContent( $row, 1, $content);
 $table->SetCellAttribute($row,1,"align","center");
 $content =  "<h2>Author Search</h2>";
 $table->SetCellContent($row,2,$content);
 $table->SetCellAttribute($row,2,"align","center");
 $table->SetCellColSpan($row,2,3);

 $row = $table->AddRow();
 $content = "<input name="."\"title1\" type=\"text\" size=\"20\" value=\"".$opts{'title1'}."\">";
 $table->SetCellContent( $row, 1, $content);

 $content = "<select name=\"title1_logic\">";
 foreach ($choices as $choice) {
    $selected = ($choice == $opts{"title1_logic"}) ? "selected":""; 
    $content.= "<option ".$selected.">".$choice."</option>";
}
 $content.= "</select>";
 $table->SetCellContent( $row, 2, $content);

 $content = "<input name="."\"title2\" type=\"text\" size=\"20\" value=\"".$opts{'title2'}."\">";
 $table->SetCellContent( $row, 3, $content);

 $content = "<input name="."\"search1\" type=\"submit\" onClick=\"javascript:yasubmit(); \" value=\"Search\">";
 $table->SetCellAttribute($row,4,"align","center");
 $table->SetCellContent( $row, 4, $content);

 $content = "<input name="."\"author1\" type=\"text\" size=\"20\" value=\"".$opts{'author1'}."\">";
 $table->SetCellContent( $row, 5, $content);

 $content = "<select name=\"author1_logic\">";
 foreach ($choices as $choice) {
    $selected = ($choice == $opts{"author1_logic"}) ? "selected":""; 
    $content.= "<option ".$selected.">".$choice."</option>";
}
 $content.= "</select>";

 $table->SetCellContent( $row, 6, $content);

 $content = "<input name="."\"author2\" type=\"text\" size=\"20\" value=\"".$opts{'author2'}."\">";
 $table->SetCellContent( $row, 7, $content);

 $row = $table->AddRow();
 $content = createKeyWordList(3,&$db, $opts{"title3"});
 $table->SetCellContent( $row, 1, $content);

 $content = "<select name=\"title2_logic\">";
 foreach ($choices as $choice) {
    $selected = ($choice == $opts{"title2_logic"}) ? "selected":""; 
    $content.= "<option ".$selected.">".$choice."</option>";
}
 $content.= "</select>";
 $table->SetCellContent( $row, 2, $content);

 $content = createKeyWordList(4,&$db, $opts{"title4"});

 $table->SetCellContent( $row, 3, $content);

 $content = "<input name="."\"search2\" type=\"submit\" onClick=\"javascript:yasubmit(); \" value=\"Search\"><br>";
 $table->SetCellAttribute($row,4,"align","center");
 $table->SetCellContent( $row, 4, $content);

 $content = createAuthorList(3,&$db, $opts{"author3"});
 $table->SetCellContent( $row, 5, $content);

 $content = "<select name=\"author2_logic\">";
 foreach ($choices as $choice) {
    $selected = ($choice == $opts{"author2_logic"}) ? "selected":""; 
    $content.= "<option ".$selected.">".$choice."</option>";
}
 $content.= "</select>";
 $table->SetCellContent( $row, 6, $content);

 $content = createAuthorList(4,&$db, $opts{"author4"});
 $table->SetCellContent( $row, 7, $content);

 $row = $table->AddRow();
 $table->SetCellAttribute($row,4,"align","center");
 $content = "<small><input name="."\"clear\" type=\"submit\" value=\"Clear\"><br>";
 $content.= "<input name="."\"all\" type=\"submit\"  onClick=\"javascript:yasubmit(); \"  value=\"List All\"></small>";
 $table->SetCellContent( $row, 1, "&nbsp;");
 $table->SetCellContent( $row, 2, "&nbsp;");
 $table->SetCellContent( $row, 3, "&nbsp;");
 $table->SetCellContent( $row, 4, $content);

 $table->PrintTable();
}



function initializeParameters() {
   foreach ($_POST as $opt_name => $opt_value) {
      $opts{$opt_name} = !isset($_POST[$opt_name])? NULL: stripslashes($_POST[$opt_name]);
      // print $opt_name."|".$opts{$opt_name}."|<br>";
   }
   if ($opts == NULL)  return NULL;
   if (!isset($opts{'isold'}) || (0==strcmp('no', $opts{'isold'})))  {
        foreach ($opts as $key => $value) {
            if (stristr($key, 'old'))  unset($opts{$key});
        }
        foreach ($opts as $key => $value) {
            $opts{'old'.$key} = $value;
        }
        $opts{'titleorder'} = 'ASC';
   }
   return $opts;
}

function keepLastParameters($opts) {
    $str = " ";
    if (NULL == $opts || empty($opts))   return $str;
    $str .= "<input type='hidden' name='isold' value='no'>\n"; 
    foreach ($opts as $key => $value) {
         if (!stristr($key, 'old')) continue;
         if (stristr($key, 'isold')) continue;
         $str .= "<input type='hidden' name='";
         $str .= $key."' value='".$value."' > \n";
    }
     
    return $str;

}

function createAuthorList($which,$db, $original) {

  // Get the author names
   $query = "SELECT * FROM ReadingRoomAuthors order by Author";
   $results   = $db->get_results($query) OR die('Query failed in index.php. Query = '.$query);

   $result =  "<select name=\"author".$which."\">";
   $result.= "<OPTION value=\"None\">&nbsp;</option>";

   foreach ($results as $author) {
       $result.= "<OPTION value='".$author->Author."'";
       if (0 == strcmp($author->Author, $original)) $result .= " selected";
       $result .= ">".$author->Author."(".$author->Quantity.")</option>";
   }
         
   $result.= "</select>";
   return $result;
}


function createKeywordList($which,$db, $original) {

  // Get the author names
   $query = "SELECT * FROM ReadingRoomSearchTerms order by Term";
   $results   = $db->get_results($query) OR die('Query failed in index.php. Query = '.$query);

   $result =  "<select name=\"title".$which."\">";
   $result.= "<OPTION value=\"None\">&nbsp;</option>";

   foreach ($results as $term) {
       $result.= "<OPTION value='".$term->Term."'"; 
       // print_r($term); print($original);
       if (0 == strcmp($term->Term, $original))  $result .= " selected";
       $result .= ">".$term->Term."(".$term->Number.")</option>";
   }
         
   $result.= "</select>";
   return $result;
}

function &getBooks($db,$orderBy,$title1,$tlogic,$title2,$auth1,$alogic,$auth2) {

   if(!empty($title1) && $title1 != "None") { $where_t1 = " Title LIKE '%".$title1."%' "; }

   if(!empty($title2) && $title2 != "None") { $where_t2 = " Title LIKE '%".$title2."%' "; }

   $condition = (!empty($where_t1) && !empty($where_t2)) ? $tlogic:""; 
   $wheret = trim($where_t1." ".$condition." ".$where_t2);

   if(!empty($auth1) && $auth1 != 'None') { $where_a1 = " Author LIKE '%".$auth1."%' "; }

   if(!empty($auth2) && $auth2 != 'None') { $where_a2 = " Author LIKE '%".$auth2."%' "; }

   $condition = (!empty($where_a1)  && !empty($where_a2)) ? $alogic:""; 
   $wherea = trim($where_a1." ".$condition." ".$where_a2);

   if(!empty($wheret)  && !empty($wherea)) {
      $where = "WHERE ".$wherea." OR ".$wheret." ";
   } elseif(!empty($wheret)) {
      $where = "WHERE ".$wheret." ";
   } elseif(!empty($wherea)) {
      $where = "WHERE ".$wherea." ";
   }

   $query = "SELECT * FROM ReadingRoomBooks ".$where." ".$orderBy;

   $results   = $db->get_results($query);
//   $results   = $db->get_results($query) OR die('Query failed in Library.php. Query = '.$query);

   return $results;
}
?>
