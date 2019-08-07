<?php
date_default_timezone_set("America/Chicago");

//include_once($_SERVER['DOCUMENT_ROOT'] . "/common/begin.html");
//echo $_SERVER['DOCUMENT_ROOT'];
include_once($_SERVER['DOCUMENT_ROOT'] . "/_common/db.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/_common/class.table.php");


$red   = "<font color=\"#FF0000\">";
$black = "<font color=\"#000000\">";

// The authenticated user id
//$user = $_SERVER['REMOTE_USER'];
$user = $_SERVER['uid'];
$checkOutMachines = array("shepard-gx760.stat.iastate.edu",
                          "stat-vista.stat.iastate.edu",
                          "jeanette-gx755.stat.iastate.edu",
                          "ahoek01.student.iastate.edu",
                          "rr.stat.iastate.edu",
                          "falconer.stat.iastate.edu",
                          "mtjernag-gx760.stat.iastate.edu",
                          "riker-5040.stat.iastate.edu",
                          "elandin.stat.iastate.edu",
                          "eel-gx760.stat.iastate.edu",
                          "jeanette-gx755.stat.iastate.edu",
                          "stat-7.stat.iastate.edu");

$query = "select name from Statdir where netid = '".$user."'";
$tmp   = $db->get_row($query);
$userName = $tmp->name;
unset($tmp);

// Is this an admin user?
$adminUser = 0;
if(preg_match("/mbrekke|clabuzze|agadilov|riker|mtjernag|hermanh/",$user)) { $adminUser = 1; }

$date = getdate();
$current_date = $date{'month'}." ".$date{'mday'}.", ".$date{'year'};

if(!isset($_POST['clear'])) {
   $opts = initializeParamaters();
}
if(isset($opts{'REN'})) {
   renewBook($db,$opts{'REN'},$user);
}

if(isset($opts{'email_overdue'})) {
   emailBooksOverdue($db,$user);
}
if(isset($opts{'check_in'}))  { 
   checkINBook($db,$user,$opts{'check_in'}); 
}
if(isset($opts{'CO'})) {
   if($adminUser == 1) {
      if($opts{'ci_user'} == 'Me') { 
         checkOutBook($db,$user,$opts{'CO'});
      } else {
         checkOutBook($db,$opts{'ci_user'},$opts{'CO'});
      }    
   } else {
       checkOutBook($db,$user,$opts{'CO'});
   }
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
 <html>
 <head>
   <title>Reading Room Search and Checkout</title>
   <script type="text/javascript">

   function stopRKey(evt) {
     var evt = (evt) ? evt : ((event) ? event : null);
       var node = (evt.target) ? evt.target : ((evt.srcElement) ? evt.srcElement : null);
         if ((evt.keyCode == 13) && (node.type=="text"))  {return false;}
   }

     document.onkeypress = stopRKey;

  </script> 
 </head>
 <body>
<?php
print "<form method=\"post\" action=\"".$_SERVER['PHP_SELF']."\">";

if(isset($opts{'email_overdue'})) {
   emailBooksOverdue($db,$user);
}
if($adminUser == 1) {
   adminScreen($db,$opts,$user,$userName);
}

if(isset($opts{'REQ'})) {
   emailRequest($db,$user,$opts{'REQ'});
}

// Get all books currently checked out.
$myBooks =& getBooks($db,' ORDER BY Title ASC ','','','','','','',$user);

if(!empty($myBooks)) {
userBooks($myBooks);
}
// getKeyWords();
userScreen($db,$opts);

// Default is to sort ascending.
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

// Let's change the html value to the other sort direction.
$title_sort  = (isset($opts{'title_sort_asc'})) ? "title_sort_desc":"title_sort_asc";
$author_sort = (isset($opts{'author_sort_asc'})) ? "author_sort_desc":"author_sort_asc";

// remove the hidden variables if we don't need them anymore
if(isset($_POST['search2']) && isset($_POST['search1h'])) { unset($_POST['search1h']); unset($opts{'search1h'}); }
if(isset($_POST['search1']) && isset($_POST['search2h'])) { unset($_POST['search2h']); unset($opts{'search2h'});}

if(!isset($order)) $order = "";
// Now to set the hidden search terms as necessary
if(isset($opts{'search1'}) || isset($opts{'search1h'}))  {
  print("<input type='hidden' name='search1h' value='Search1h'>");
   $books =& getBooks($db,$order,$opts{'title1'},$opts{'title1_logic'},$opts{'title2'},$opts{'author1'},$opts{'author1_logic'},$opts{'author2'},'');
} elseif(isset($opts{'search2'}) || isset($opts{'search2h'})) {
   $books =& getBooks($db,$order,$opts{'title3'},$opts{'title2_logic'},$opts{'title4'},$opts{'author3'},$opts{'author2_logic'},$opts{'author4'},'');
   print("<input type='hidden' name='search2h' value='Search2h'>");
} elseif(isset($opts{'all'})) {
   print("<input type='hidden' name='all' value='List All'>");
   $books =& getBooks($db,$order,'','','','','','','');
//   $books =& getBooks($db,' ORDER BY Title ASC ','','','','','','','');
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
$table->SetCellContent( $row, 4, "Location");
$table->SetCellContent( $row, 5, "Type");
$table->SetCellContent( $row, 6, "Thesis");


if(!empty($books)) {
   foreach ($books as $book) {
     if($book->Borrower == $user) { continue; }
     $row = $table->AddRow();
     $bookName = "book_".$book->BookID;
//      $matches = preg_grep("/".gethostbyaddr($_SERVER['REMOTE_ADDR'])."/",$checkOutMachines);
     if(empty($book->Borrower)) {
//     if(empty($book->Borrower) && !empty($matches)) {
        $content = "<input name='".$bookName."' type='submit' value='CO'>";
     } else if(!empty($book->Borrower)) {
        $content = "<input name='".$bookName."' type='submit' value='REQ'>";
     } else { 
        $content = "";
     } 


     $table->SetCellContent( $row, 1, $content);
     if(empty($book->Borrower)) {
        $table->SetCellContent( $row, 2, $book->Title);
     } else {
        $table->SetCellContent( $row, 2, $red.$book->Title."</font>");
     }
     $table->SetCellContent( $row, 3, $book->Author);
     $table->SetCellContent( $row, 4, $book->Location);
     $table->SetCellContent( $row, 5, $book->Type);
     if($book->MS == 1) {
        $table->SetCellContent( $row, 6, "MS");
     } else if($book->PhD == 1) {
        $table->SetCellContent( $row, 6, "PhD");
     } else { 
        $table->SetCellContent( $row, 6, "");
     }   
  }  
  $table->printTable();
}

print "</form>";

print "</body> </html>";

// Function definitions
function adminScreen($db,$opts,$user,$userName) {

   print "<center><h2><strong>Admin Screen</strong> <small> (".$userName.")</small></h2></center>";
   print '<STYLE type="text/css"> P.mypar {text-align: right}</STYLE>';
   print "<P class=\"mypar\"><a href='ReadingRoomBooks.php' target='Books'>Edit Reading Room Books</a><br>";
   print "<a href='ReadingRoomAuthors.php' target='Authors'>Edit Reading Room Authors</a><br>";
   print "<a href='ReadingRoomSearchTerms.php' target='Search'>Edit Reading Room Search Terms</a><br>";
   print "<a href='ReadingRoomHistory.php' target='History'>Edit Reading Room History</a><br></P>";
   booksOut($db); 
   booksOverdue($db,$user); 
//   if(isset($opts{'books_out'})) { booksOut($db); }
//   if(isset($opts{'check_in'}))  { checkINBook($db,$user,$opts{'check_in'}); }
  
   $table = new table();
   $table->SetTableAttributes( array( "width" => "100%", "border" => "0", "align" => "center",
                            "cellpadding" => "1", "fgcolor" => "black" ) );
   $table->SetDefaultCellAttributes( array("bgcolor" => "white", "align" => "left" ) );

//   $row = $table->AddRow();
//   $content = "<input name="."\"books_out\" type=\"submit\" value=\"List Books Out\">";
//   $table->SetCellContent( $row, 1, $content);
   
//   $row = $table->AddRow();
//   $content = "<input name="."\"overdue_books\" type=\"submit\" value=\"List Books Overdue\">";
//   $table->SetCellContent( $row, 1, $content);
   
   $row = $table->AddRow();
   $content = "<input name="."\"email_overdue_books\" type=\"submit\" value=\"Email Scofflaws\">";
   $table->SetCellContent( $row, 1, $content);
  
   $table->printTable();


   print "<br><br>";
   print "Select User to Use for Checkout: ".createUserList($db)."<br>"; 
   print "<h2>User Screen Below</h2>";
   print "===============================================================================================<br><br>";


}
function userScreen($db,$opts) {
// print '<A NAME="purchasing">';
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
 if(!isset($opts{'title1'})) $opts{'title1'} = "";
 $content = "<input name="."\"title1\" type=\"text\" size=\"20\" value=\"".$opts{'title1'}."\">";
 $table->SetCellContent( $row, 1, $content);

 if(!isset($opts{'title1_logic'})) $opts{'title1_logic'} = "";
 $content = "<select name=\"title1_logic\">";
 foreach ($choices as $choice) {
    $selected = ($choice == $opts{"title1_logic"}) ? "selected":""; 
    $content.= "<option ".$selected.">".$choice."</option>";
}
 $content.= "</select>";
 $table->SetCellContent( $row, 2, $content);

 if(!isset($opts{'title2'})) $opts{'title2'} = "";
 $content = "<input name="."\"title2\" type=\"text\" size=\"20\" value=\"".$opts{'title2'}."\">";
 $table->SetCellContent( $row, 3, $content);

 $content = "<input name="."\"search1\" type=\"submit\" value=\"Search\">";
 $table->SetCellAttribute($row,4,"align","center");
 $table->SetCellContent( $row, 4, $content);

 if(!isset($opts{'author1'})) $opts{'author1'} = "";
 $content = "<input name="."\"author1\" type=\"text\" size=\"20\" value=\"".$opts{'author1'}."\">";
 $table->SetCellContent( $row, 5, $content);

 if(!isset($opts{'author1_logic'})) $opts{'author1_logic'} = "";
 $content = "<select name=\"author1_logic\">";
 foreach ($choices as $choice) {
    $selected = ($choice == $opts{"author1_logic"}) ? "selected":""; 
    $content.= "<option ".$selected.">".$choice."</option>";
}
 $content.= "</select>";

 $table->SetCellContent( $row, 6, $content);

 if(!isset($opts{'author2'})) $opts{'author2'} = "";
 $content = "<input name="."\"author2\" type=\"text\" size=\"20\" value=\"".$opts{'author2'}."\">";
 $table->SetCellContent( $row, 7, $content);

 $row = $table->AddRow();
 if(!isset($opts{'title3'})) $opts{'title3'} = "";
 $content = createKeyWordList(3,$db,$opts{"title3"});
 $table->SetCellContent( $row, 1, $content);

 if(!isset($opts{'title2_logic'})) $opts{'title2_logic'} = "";
 $content = "<select name=\"title2_logic\">";
 foreach ($choices as $choice) {
    $selected = ($choice == $opts{"title2_logic"}) ? "selected":""; 
    $content.= "<option ".$selected.">".$choice."</option>";
}
 $content.= "</select>";
 $table->SetCellContent( $row, 2, $content);

 if(!isset($opts{'title4'})) $opts{'title4'} = "";
 $content = createKeyWordList(4,$db,$opts{"title4"});
 $table->SetCellContent( $row, 3, $content);

 $content = "<input name="."\"search2\" type=\"submit\" value=\"Search\"><br>";
 $table->SetCellAttribute($row,4,"align","center");
 $table->SetCellContent( $row, 4, $content);

 if(!isset($opts{'author3'})) $opts{'author3'} = "";
 $content = createAuthorList(3,$db, $opts{"author3"});
 $table->SetCellContent( $row, 5, $content);

 if(!isset($opts{'author2_logic'})) $opts{'author2_logic'} = "";
 $content = "<select name=\"author2_logic\">";
 foreach ($choices as $choice) {
    $selected = ($choice == $opts{"author2_logic"}) ? "selected":""; 
    $content.= "<option ".$selected.">".$choice."</option>";
}
 $content.= "</select>";
 $table->SetCellContent( $row, 6, $content);

 if(!isset($opts{'author4'})) $opts{'author4'} = "";
 $content = createAuthorList(4,$db, $opts{"author4"});
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
   $opts = array();
   foreach ($_POST as $opt_name => $opt_value) {
      $opts{$opt_name} = !isset($_POST[$opt_name])? NULL: stripslashes($_POST[$opt_name]);
      if($opts{$opt_name} == "Renew") {
          $opts{'REN'} = $opt_name;
      }
      if($opts{$opt_name} == "CO") {
          $opts{'CO'} = $opt_name;
      }
      if($opts{$opt_name} == "REQ") {
          $opts{'REQ'} = $opt_name;
      }
      if($opts{$opt_name} == "Check In") {
          $opts{'check_in'} = $opt_name;
      }
      if($opts{$opt_name} == "Email Scofflaws") {
          $opts{'email_overdue'} = $opt_name;
      }
//      print $opt_name."|".$opts{$opt_name}."|<br>";
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

function createUserList($db) {

  // Get the author names
   $query = "select netid,name from Statdir ORDER BY name";
   $names   = $db->get_results($query);

   $result =  "<select name=\"ci_user\">";
   $result.= "<OPTION value=\"Me\">Me</option>";

   foreach ($names as $name) {
      $result.= "<OPTION value='".$name->netid."'>".$name->name."</option>";
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

function userBooks ($myBooks) {

   print "<center><h2>My Checked Out Books</h2></center>";
   $table = new table();
   $table->SetTableAttributes( array( "width" => "100%", "border" => "1", "align" => "center",
                            "cellpadding" => "1", "fgcolor" => "black" ));
   $table->SetDefaultCellAttributes( array("bgcolor" => "white", "align" => "left" ) );

   $row = $table->AddRow();
   $table->SetCellContent($row,1,"<strong>Renew</strong>");
   $table->SetCellContent($row,2,"<strong>Due Date</strong>");
   $table->SetCellContent($row,3,"<strong>Title</strong>");
   $table->SetCellContent($row,4,"<strong>Author</strong>");
   foreach ($myBooks as $book) {
//     print_r($book);
     $row = $table->AddRow();
     $bookName = "bookCI_".$book->BookID;
      $date = explode('-',$book->DateCheckedOut);
//      print($date[0]."|".$date[1]."|".$date[2])."<br>";

      $returnTime = mktime(12,0,0,$date[1],$date[2],$date[0]) + 24*60*60*$book->CheckOutDays;
      $nowTime = date("U");

     if($returnTime < $nowTime) { 
        $content = "<input name='".$bookName."' type='submit' value='Renew'>";
     } else {
        $content = "";
     }
//        $content = "<input name='".$bookName."' type='submit' value='Renew'>";
     $table->SetCellContent( $row, 1, $content);
      $content = date("M-d-Y",mktime(12,0,0,$date[1],$date[2],$date[0]) + 24*60*60*$book->CheckOutDays);
     $table->SetCellContent( $row, 2, $content);
     $table->SetCellContent( $row, 3, $book->Title);
     $table->SetCellContent( $row, 4, $book->Author);
   }  
  $table->printTable();
  print "<br><br>";
}

function emailBooksOverdue ($db,$user) {
// SELECT * FROM `stat`.`ReadingRoomBooks` where DateCheckedOut + CheckOutDays < curdate()
   $query = "SELECT count(*) as count FROM ReadingRoomBooks where DateCheckedOut + INTERVAL CheckOutDays DAY < curdate() AND Borrower is not null"; 
   $count   = $db->get_row($query);
   $query = "SELECT * FROM ReadingRoomBooks where DateCheckedOut + INTERVAL CheckOutDays DAY  < curdate()  AND Borrower is not null "; ;
   $results   = $db->get_results($query);
   $query = "update ReadingRoomBooks set DateContacted = CURDATE(),ContactedID = '".$user."' where DateCheckedOut + INTERVAL CheckOutDays DAY < curdate() AND Borrower is not null"; 
   $db->query($query);
//   $results   = $db->get_results($query) OR die('Query failed in Library.php. Query = '.$query);
     if($count->count > 0) {
       foreach ($results as $book) {
          $email = $user."@iastate.edu";
          $headers  = "MIME-Version: 1.0"."\r\n"."From:".$user."@iastate.edu>"."\r\n";
          $MailBody = '"Please return \''.$book->Title.'\' by \''.$book->Author.' \'because it is overdue"';
          $subject = "Return Overdue Reading Room Book";

           print "Here I am <br>";
           mail($book->Borrower."@iastate.edu",$subject,$MailBody,$headers);
//           print "mail(".$book->Borrower."@iastate.edu".",".$subject.",".$MailBody.",".$headers.");<br>";
//           print "Request for overdue book <strong>'".$book->Title."' </strong> sent<br>";
       }  
     }
 // print "<br><br>";
  header("Location: $_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]"); 
}
function booksOverdue ($db,$user) {
// SELECT * FROM `stat`.`ReadingRoomBooks` where DateCheckedOut + CheckOutDays < curdate()
   $query = "SELECT count(*) as count FROM ReadingRoomBooks where DateCheckedOut + INTERVAL CheckOutDays DAY < curdate()"; 
   $count   = $db->get_row($query);
   $query = "SELECT * FROM ReadingRoomBooks where DateCheckedOut + INTERVAL CheckOutDays DAY < curdate()"; ;
   $results   = $db->get_results($query);
//   $results   = $db->get_results($query) OR die('Query failed in Library.php. Query = '.$query);
   print "<center><h2>Books Overdue (".$count->count.")</h2></center>";
   if($count->count > 0) {
   $table = new table();
   $table->SetTableAttributes( array( "width" => "100%", "border" => "1", "align" => "center",
                            "cellpadding" => "1", "fgcolor" => "black" ));
   $table->SetDefaultCellAttributes( array("bgcolor" => "white", "align" => "left" ) );

   $row = $table->AddRow();
   $table->SetCellContent( $row, 1, "<strong>Borrower</strong>" );
   $table->SetCellContent( $row, 2, "<strong>Check Out Date</strong>");
   $table->SetCellContent( $row, 3, "<strong>Contacted Date</strong>");
   $table->SetCellContent( $row, 4, "<strong>Contacted By</strong>");
   $table->SetCellContent( $row, 5, "<strong>Title</strong>");
   $table->SetCellContent( $row, 6, "<strong>Author</strong>");

     foreach ($results as $book) {
       $query = "select name from Statdir where netid = '".$book->Borrower."'";
       $tmp   = $db->get_row($query);
       $bookName = "bookCI_".$book->BookID;
       $row = $table->AddRow();
       $table->SetCellContent( $row, 1, contact($user,$tmp->name,$book->Borrower,$db, $book->BookID) );
       $table->SetCellContent( $row, 2, $book->DateCheckedOut);
       $table->SetCellContent( $row, 3, $book->DateContacted);
       if(!empty($book->ContactedID)) {
#          $query = "select name from Statdir where netid = '".$book->ContactedID."'";
          $tmp   = $db->get_row($query);
          $ContactedID = $tmp->name;
          $ContactedID = $book->ContactedID;

      } else {
          $ContactedID = "<strong>N</strong>o<strong>O</strong>ne<strong>Y</strong>et";
      }
       $table->SetCellContent( $row, 4, $ContactedID);
       $table->SetCellContent( $row, 5, $book->Title);
       $table->SetCellContent( $row, 6, $book->Author);
     }  
  $table->printTable();
   }
  print "<br><br>";
}
function booksOut ($db) {

   $query = "SELECT count(*) as count FROM ReadingRoomBooks where Borrower IS NOT NULL"; 
   $count   = $db->get_row($query);
   $query = "SELECT * FROM ReadingRoomBooks where Borrower IS NOT NULL ORDER BY Author"; ;
   $results   = $db->get_results($query);
//   $results   = $db->get_results($query) OR die('Query failed in Library.php. Query = '.$query);
   print "<center><h2>Books Out (".$count->count.")</h2></center>";
   if($count->count > 0) {
   $table = new table();
   $table->SetTableAttributes( array( "width" => "100%", "border" => "1", "align" => "center",
                            "cellpadding" => "1", "fgcolor" => "black" ));
   $table->SetDefaultCellAttributes( array("bgcolor" => "white", "align" => "left" ) );

   $row = $table->AddRow();
   $table->SetCellContent( $row, 1, "<strong>Check In</strong>");
   $table->SetCellContent( $row, 2, "<strong>Borrower</strong>" );
   $table->SetCellContent( $row, 3, "<strong>Check Out Date</strong>");
   $table->SetCellContent( $row, 4, "<strong>Title</strong>");
   $table->SetCellContent( $row, 5, "<strong>Author</strong>");
   $table->SetCellContent( $row, 6, "<strong>Renew</strong>");

     foreach ($results as $book) {
       $query = "select name from Statdir where netid = '".$book->Borrower."'";
       $tmp   = $db->get_row($query);
       $bookName = "bookCI_".$book->BookID;
       $row = $table->AddRow();
       $content = "<input name='".$bookName."' type='submit' value='Check In'>";
       $table->SetCellContent( $row, 1, $content);
       $table->SetCellContent( $row, 2, $tmp->name);
       $table->SetCellContent( $row, 3, $book->DateCheckedOut);
       $table->SetCellContent( $row, 4, $book->Title);
       $table->SetCellContent( $row, 5, $book->Author);
       $content = "<input name='".$bookName."' type='submit' value='Renew'>";
       $table->SetCellContent( $row, 6, $content);
     }
  $table->printTable();
   }  
  print "<br><br>";
}
function renewBook($db,$book,$user) {

   $junk = explode('_',$book);
   $book = $junk[1]; 
   $query = "select Borrower,DateCheckedOut from ReadingRoomBooks  where BookID = ".$book;
   $tmp   = $db->get_row($query);
   print $tmp->DateCheckedOut."<br>";
   $query = 'INSERT INTO ReadingRoomHistory (Borrower,DateCheckedOut,DateCheckedIn,BookID,StaffID) VALUES ("'.$tmp->Borrower.'","'.$tmp->DateCheckedOut.'",CURDATE(),"'.$book.'","'.$user.'")';
   $db->query($query)  OR die('Query failed in Library.php. Query = '.$query);
//   $results   = $db->get_results($query) OR die('Query failed in Library.php. Query = '.$query);
   $query = "update ReadingRoomBooks set DateCheckedOut = curdate() where BookID = ".$book;
   $db->query($query)  OR die('Query failed in Library.php. Query = '.$query);
   header("Location: $_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]"); 
} 

function emailRequest($db,$user,$book) {

   $junk = explode('_',$book);
   $book = $junk[1]; 
   $query = "select * from ReadingRoomBooks where BookID = ".$book;
   $checkedOutBook   = $db->get_row($query);

    // Let's get the name of the user who has the book checked out
   $query = "select name from Statdir where netid = '".$user."'";
   $requestorName   = $db->get_row($query);
    
   $email = $user."@iastate.edu";
   $headers  = "MIME-Version: 1.0"."\r\n"."From:".$user."@iastate.edu>"."\r\n";
   $MailBody = $requestorName->name." is requesting you return ".$checkedOutBook->Title;
   $subject = "Return Reading Room Book";

   mail($checkedOutBook->Borrower."@iastate.edu",$subject,$MailBody,$headers);
   print "Request For <strong>".$checkedOutBook->Title." </strong> sent<br>";

   
} 
function checkOutBook($db,$user,$book) {

   $junk = explode('_',$book);
   $book = $junk[1]; 
   $query = "update ReadingRoomBooks set Borrower = '".$user."',DateCheckedOut = CURDATE() where BookID = ".$book;
   
   $db->query($query);

//   print "user = ".$user."<br>";

   
//  header("Location: $_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]"); 
} 

function checkINBook($db,$user,$book) {

   $junk = explode('_',$book);
   $book = $junk[1]; 
//   print $book."<br>";
   $query = "select Borrower,DateCheckedOut from ReadingRoomBooks  where BookID = ".$book;
   $tmp   = $db->get_row($query);
//   print $tmp->DateCheckedOut."<br>";
   $query = 'INSERT INTO ReadingRoomHistory (Borrower,DateCheckedOut,DateCheckedIn,BookID,StaffID) VALUES ("'.$tmp->Borrower.'","'.$tmp->DateCheckedOut.'",CURDATE(),"'.$book.'","'.$user.'")';
    
   $db->query($query);


   $query = "update ReadingRoomBooks set Borrower = NULL, DateCheckedOut = NULL, DateContacted = NULL, ContactedID = NULL  where BookID = ".$book;
   
   $db->query($query);

  header("Location: $_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]"); 
   
} 
function &getBooks($db,$orderBy,$title1,$tlogic,$title2,$auth1,$alogic,$auth2,$borrower) {

   $where_t1 = null; $where_t2 = null;
   if(!empty($title1) && $title1 != "None") { $where_t1 = " Title LIKE '%".$title1."%' "; }

   if(!empty($title2) && $title2 != "None") { $where_t2 = " Title LIKE '%".$title2."%' "; }

   $condition = (!empty($where_t1) && !empty($where_t2)) ? $tlogic:""; 
   $wheret = trim($where_t1." ".$condition." ".$where_t2);

   $where_a1 = null; $where_a2 = null;
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

   if(!empty($borrower)) {
	$where = " WHERE Borrower = '".$borrower."'";
   }
   
   $query = "SELECT * FROM ReadingRoomBooks ".$where." ".$orderBy;

   $results   = $db->get_results($query);
//   $results   = $db->get_results($query) OR die('Query failed in Library.php. Query = '.$query);

   return $results;
}
   function contact($user,$name, $netid, $db, $bookID) { 
      if (strlen($name) == 0 ) return ""; 
//      $query = "update ReadingRoomBooks set DateContacted = CURDATE() where BookID = ".$bookID.";" ;
//      print "<br><br>".$query."<br><br>";
//      $db->query($query);
     return "<a href='contact.php?id=" . $netid . "&BookID=".$bookID."&StaffID=".$user."' target='email'>$name</a>"; }

?>
