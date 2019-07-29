<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
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

print "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">";

if(!isset($_POST['clear'])) {
   $opts = initializeParamaters();
}
if($adminUser == 1) {
//   adminScreen();
}

// getKeyWords();
userScreen(&$db,$opts);

// default is to sort ascending
$sort = "ASC";

// Let's change the order if necessary
if(isset($opts{'title_sort_asc'}) || isset($opts{'author_sort_asc'})) {
   $sort = ' DESC ';
} elseif(isset($opts{'title_sort_desc'}) || isset($opts{'author_sort_desc'})) {
   $sort = ' ASC ';
}
if(isset($opts{'title_sort_asc'}) || isset($opts{'title_sort_desc'})) {
   $order = 'Order by Title'.$sort;
} elseif(isset($opts{'author_sort_desc'}) || isset($opts{'author_sort_asc'})) {
   $order = 'Order by Author'.$sort;
}

// Let's change the html value to the other sort direction
$title_sort  = (isset($opts{'title_sort_asc'})) ? "title_sort_desc":"title_sort_asc";
$author_sort = (isset($opts{'author_sort_asc'})) ? "author_sort_desc":"author_sort_asc";

// remove the hidden variables if we don't need them anymore
if(isset($_POST['search2']) && isset($_POST['search1h'])) { unset($_POST['search1h']); unset($opts{'search1h'}); }
if(isset($_POST['search1']) && isset($_POST['search2h'])) { unset($_POST['search2h']); unset($opts{'search2h'});}

// Now to set the hidden search terms as necessary
if(isset($opts{'search1'}) || isset($opts{'search1h'}))  {
  print("<input type='hidden' name='search1h' value='Search1h'>");
   $books =& getBooks(&$db,$order,$opts{'title1'},$opts{'title1_logic'},$opts{'title2'},$opts{'author1'},$opts{'author1_logic'},$opts{'author2'});
} elseif(isset($opts{'search2'}) || isset($opts{'search2h'})) {
   $books =& getBooks(&$db,$order,$opts{'title3'},$opts{'title2_logic'},$opts{'title4'},$opts{'author3'},$opts{'author2_logic'},$opts{'author4'});
   print("<input type='hidden' name='search2h' value='Search2h'>");
} elseif(isset($opts{'all'})) {
   $books =& getBooks(&$db,' ORDER BY Title ASC ','','','','','','');
} 

$table = new table();
 $table->SetTableAttributes( array( "width" => "100%", "border" => "1", "align" => "center",
                            "cellpadding" => "1", "fgcolor" => "black" ) );
 $table->SetDefaultCellAttributes( array("bgcolor" => "white", "align" => "left" ) );

$row = $table->AddRow();
$table->SetCellContent( $row, 1, "&nbsp;");
 $content = "<center><small><input name='".$title_sort."' type='submit' value='Titles'></center>";
$table->SetCellContent( $row, 2, $content);
 $content = "<center><small><input name='".$author_sort."' type='submit' value='Authors'></center>";
$table->SetCellContent( $row, 3, $content);


if(!empty($books)) {
   foreach ($books as $book) {
     $row = $table->AddRow();
     $bookName = "book_".$book->BookID;
     $content = "<input name='".$bookName."' type='submit' value='Req'>";
     $table->SetCellContent( $row, 1, $content);
     $table->SetCellContent( $row, 2, $book->Title);
     $table->SetCellContent( $row, 3, $book->Author);
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

 $content = "<input name="."\"search1\" type=\"submit\" value=\"Search\">";
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
 $content = createKeyWordList(3,&$db,$opts{"title3"});
 $table->SetCellContent( $row, 1, $content);

 $content = "<select name=\"title2_logic\">";
 foreach ($choices as $choice) {
    $selected = ($choice == $opts{"title2_logic"}) ? "selected":""; 
    $content.= "<option ".$selected.">".$choice."</option>";
}
 $content.= "</select>";
 $table->SetCellContent( $row, 2, $content);

 $content = createKeyWordList(4,&$db,$opts{"title4"});
 $table->SetCellContent( $row, 3, $content);

 $content = "<input name="."\"search2\" type=\"submit\" value=\"Search\"><br>";
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
 $content.= "<input name="."\"all\" type=\"submit\" value=\"List All\"></small>";
 $table->SetCellContent( $row, 1, "&nbsp;");
 $table->SetCellContent( $row, 2, "&nbsp;");
 $table->SetCellContent( $row, 3, "&nbsp;");
 $table->SetCellContent( $row, 4, $content);

 $table->PrintTable();
}

function initializeParamaters() {
   foreach ($_POST as $opt_name => $opt_value) {
      $opts{$opt_name} = !isset($_POST[$opt_name])? NULL: stripslashes($_POST[$opt_name]);
//      print $opt_name."|".$opts{$opt_name}."<br>";
   }
  return $opts;
}

function createAuthorList($which,$db,$original) {

  // Get the author names
   $query = "SELECT * FROM ReadingRoomAuthors order by Author";
   $results   = $db->get_results($query) OR die('Query failed in index.php. Query = '.$query);

   $result =  "<select name=\"author".$which."\">";
   $result.= "<OPTION value=\"None\">&nbsp;</option>";

   foreach ($results as $author) {
      $result.= "<OPTION value='".$author->Author."'";
      $result .= (!strcmp($author->Author, $original)) ? " selected":"";
      if (0 == strcmp($author->Author, $original)) $result .= " selected";
      $result .= ">".$author->Author."(".$author->Quantity.")</option>";

   }
         
   $result.= "</select>";
   return $result;
}


function createKeywordList($which,$db,$original) {

  // Get the author names
   $query = "SELECT * FROM ReadingRoomSearchTerms order by Term";
   $results   = $db->get_results($query) OR die('Query failed in index.php. Query = '.$query);

   $result =  "<select name=\"title".$which."\">";
   $result.= "<OPTION value=\"None\">&nbsp;</option>";

   foreach ($results as $term) {
      $result.= "<OPTION value='".$term->Term."'";
      $result.= (!strcmp($term->Term, $original)) ? " selected":"";
      $result.= ">".$term->Term."(".$term->Number.")</option>";
   }
         
   $result.= "</select>";
   return $result;
}

function &getBooks($db,$orderBy,$title1,$tlogic,$title2,$auth1,$alogic,$auth2) {

   if(!empty($title1) && $title1 != "None") { $where_t1 = " Title LIKE '%".$title1."%' "; }

   if(!empty($title2) && $title2 != "None") { $where_t2 = " Title LIKE '%".$title2."%' "; }

   $condition = (!empty($where_t1) && !empty($where_t2)) ? $tlogic:""; 
   $wheret = trim($where_t1." ".$condition." ".$where_t2);

   if(!empty($auth1) && $auth1 != "None" ) { $where_a1 = " Author LIKE '%".$auth1."%' "; }

   if(!empty($auth2) && $auth2 != "None") { $where_a2 = " Author LIKE '%".$auth2."%' "; }

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

