<? include("../common/begin.html"); ?>
      
             
          <script type="text/javascript">
         document.title="People-Department of Statistics,ISU";      
         document.getElementById('people_list').style.display=""; 
         </script type="text/javascript">
      



<?php 

// include_once($_SERVER['DOCUMENT_ROOT'] . "/_common/pg_connect.php"); 
include_once($_SERVER['DOCUMENT_ROOT'] . "/_common/db.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/common/citations.php");

 function contact($name, $netid) {
   if (strlen($name) == 0 ) return "";
   return "<a href='/directory/contact.html?id=" . $netid . "'>$name</a>";
 }

?>
<?php
 
 if(! isset($_GET['id']) && !isset($_POST['id'])) {

    if(isset($_GET['sortcond'])) {
       $sortcond = $_GET['sortcond'];
    } elseif (isset($_POST['sortcond'])){
       $sortcond = $_POST['sortcond'];
    } else {
       $sortcond = 'Name';
    }

    if(isset($_GET['asc_or_desc'])) {
       $asc_or_desc = $_GET['asc_or_desc'];
    } elseif (isset($_POST['asc_or_desc'])){
       $asc_or_desc = $_POST['asc_or_desc'];
    } else {
       $asc_or_desc = 'ASC';
    }

    $query = "SELECT Name, Appt, Phone1, Office1, Email, Netid, Phone2, Office2 FROM Statdir WHERE Appt like '%Prof%' OR Appt = 'Instructor' ";
    $query = $query."OR Appt = 'DirectorandChair' ORDER BY $sortcond $asc_or_desc;";
    $result = $db->get_results($query) or die('Query failed');
    
    if(isset($_GET['lastSorted'])) {
       $lastSorted = $_GET['lastSorted'];
    } elseif (isset($_POST['lastSorted'])){
       $lastSorted = $_POST['lastSorted'];
    }
    echo '<style type="text/css">';
    echo 'table.faculty_tab tr td';
    echo '{  white-space: nowrap; }';
    echo '</style>';
    
    echo '<table cellspacing=0 cellpadding=0 style="font-size:80%" class="faculty_tab">';
    echo '<tr >';
    echo '<th align="left"><a href="faculty.php?sortcond=Name&lastSorted=Name&asc_or_desc=';
    echo ($asc_or_desc == 'ASC' && ((isset($lastSorted) && !strcmp($lastSorted,"Name")) || !isset($lastSorted))) ? 'DESC' : 'ASC';
    echo '" style="text-decoration:none"><font size=4 color=#900>Name</font></a></th>';
    
    echo '<th align="left" valign="bottom"><a href="faculty.php?sortcond=Appt&lastSorted=Title&asc_or_desc=';
    echo ($asc_or_desc == 'ASC' && isset($lastSorted) && !strcmp($lastSorted,"Title")) ? 'DESC' : 'ASC';
    echo '" style="text-decoration:none"><font size=4 color=#900>Title</font></a></th>';
    
    echo '<th align="left"><a href="faculty.php?sortcond=Phone1&lastSorted=Phone&asc_or_desc=';
    echo ($asc_or_desc == 'ASC' && isset($lastSorted) && !strcmp($lastSorted,"Phone")) ? 'DESC' : 'ASC';
    echo '" style="text-decoration:none"><font size=4 color=#900>Phone</font></a></th>';
    
    echo '<th align="left"><a href="faculty.php?sortcond=Office1&lastSorted=Office&asc_or_desc=';
    echo ($asc_or_desc == 'ASC' && isset($lastSorted) && !strcmp($lastSorted,"Office")) ? 'DESC' : 'ASC';
    echo '" style="text-decoration:none"><font size=4 color=#900>Office</font></a></th>';
    
    echo '<th align="left"><font size=4 color=#900>Email</font></th>';

    echo '</tr>';
    
    foreach($result as $row) {
    
       $title = $row->Appt;
       
       if(!strcmp($title,"FullProf")) {
          $title = 'Professor';
       } elseif (!strcmp($title,"AssistProf")) {
          $title = 'Assistant Professor';
       } elseif (!strcmp($title,"AssocProf")) {
          $title = 'Associate Professor';
       } elseif (!strcmp($title,"DistProf")) {
          $title = 'Distinguished Professor';
       } elseif (!strcmp($title,"VisitProf")) {
          $title = 'Visiting Professor';
       } elseif (!strcmp($title,"EmerProf")) {
          $title = 'Professor Emeritus';
       } elseif (!strcmp($title,"UnivProf")) {
          $title = 'University Professor';      
       } elseif (!strcmp($title,"DirectorandChair")) {
          $title = 'Professor; Chair';
       }
       $space=" ";
       
       echo '<tr>';
       echo '<td><a href="personal.php?id='.$row->Netid.'">'.$row->Name.'</a> &nbsp </td>';
       echo "<td>$title &nbsp </td>";
       echo strcmp($row->Phone1,"")?"<td>294-":'<td>';
       echo $row->Phone1.'</td>';
       echo "<td>$row->Office1</td>";
       echo '<td align="center">'.contact( "Email",$row->Netid)."</td>";
       echo '</tr>';
       echo '<tr>';
       echo '<td></td><td>';

       if (!strcmp($row->Phone2,"")) {
           echo '<td>';
       } else {

           $ph = $row->Phone2;

           $ph = str_replace('515-294-', 'test', $ph);
           $ph = str_replace('294-', '', $ph);

           echo "<td>294-";
           echo $ph.  ' &nbsp </td>';

       }

       echo '<td>';
       echo strcmp($row->Office2,"")?$row->Office2:"";
       echo '</td><td></td></tr>';
       
    }
    
    echo '</table>';
    
 } else {
 
 if (isset($_GET['id']))  
    $netid = $_GET['id']; 
 else
    $netid = $_POST['id'];
	
// Perform the mysql query
$my_user    = $db->get_row("SELECT * FROM Statdir where Netid = '$netid'");
$semester   = $db->get_var("SELECT Semester FROM SemesterInfo WHERE NOW() >= StartDate and NOW() <= EndDate");  
$my_courses = $db->get_results("SELECT * FROM Courses2 where Netid = '$netid' and semester = '$semester' order by Course");

// Performing postgres query to get faculty info
$query = "SELECT * FROM faculty where id ='$netid'";
$result = pg_query($dbpg,$query) or die('Query failed: ' . pg_last_error());
$pg_user = pg_fetch_object($result);

// Performing postgres query to get faculty publications
$query = "SELECT * FROM publications where id ='$netid' and pub_date IS NOT NULL order by pub_date DESC";
$result = pg_query($dbpg,$query) or die('Query failed: ' . pg_last_error());

// $filename = $_SERVER['DOCUMENT_ROOT']."directory/_images/$netid.jpg";
// if (file_exists($filename) && $_GET['dir'] == '') {
//echo $_SERVER['DOCUMENT_ROOT']."<br>";
echo "<table align='center' width='100%'>";
echo "<tr><td align='left' width = '30%'>";
echo "<H2>".$my_user->name."<br>";
echo $pg_user->title."</H2><br>";
echo "<img src=\"_images/$netid.jpg\"/>";
echo "<H2><a href='$my_user->website'>Personal Homepage</a></H2>";
if(!empty($my_user->calendarURL)) {
   echo "<H2><a href='$my_user->calendarURL'>Schedule</a></H2>";
 }

echo "<H3> Contact Info:</H3>";
echo "$my_user->office1<br>";
echo "515-294-$my_user->phone1<br>";
echo contact( "Email",$netid);

echo "</td><td width='10%'></td>";
echo "<td align='left'>";
echo "<H2><center>".$pg_user->extra_desc."</center></H2><br>";
echo "<H3>Interests</H3><br>";
echo $pg_user->interests."<br><br>";
echo "<H3>Education</H3><br>";
if(!empty($pg_user->phd_institution)) { 
   echo "PhD, $pg_user->phd_institution";

   if(!empty($pg_user->phd_location)) {
     echo ", $pg_user->phd_location";
   } 
   if(!empty($pg_user->phd_year)) {
     echo ", $pg_user->phd_year<br>";
   }
}
if(!empty($pg_user->master_institution)) { 
   echo "MS, $pg_user->master_institution";
   if(!empty($pg_user->master_location)) {
      echo ", $pg_user->master_location";
   }
   if(!empty($pg_user->master_year)) {
     echo ", $pg_user->master_year<br>";
   }
}
if(!empty($pg_user->bachelor_institution)) { 
   echo "BS, $pg_user->bachelor_institution";
   
   if(!empty($pg_user->bachelor_location)) {
     echo ", $pg_user->bachelor_location";
   }
   if(!empty($pg_user->bachelor_year)) {
     echo ", $pg_user->bachelor_year<br>";
   }
}
echo "<br><H3>Current Teaching:</H3><br>";
if(!empty($my_courses)) {
   foreach($my_courses as $course) {
      echo $course->Course .', '. $course->Title."<br>";
      echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$course->Time.", ".$course->Location."<br><br>";
   //	echo "1<br>";
   }
}
echo "</td></tr>";
echo "<tr align='left'><td colspan='3'>";
echo "<br><H3>Recent Publications:</H3><br><ul>";
$num = 0;

if(isset($_GET['allpubs']) || isset($_POST['allpubs'])) {

   $allpubs = 'yes';

}

while($row = pg_fetch_object($result)) {

	$num++;

   if($row->pub_type == "Book") {
      echo "<li>".book_citation($row);
      next;
   }
   if($row->pub_type == "Book-Chapter") {
      echo "<li>".book_chapter_citation($row);
      next;
   }

   echo "<li>".paper_citation($row);
	
   if($num == 5 && !isset($allpubs)) {

      echo '<br/>';
      echo '<a href="faculty.php?id='.$netid.'&allpubs=yes">List All Publications</a>';
      break;
      
   }
}	
echo "</ul></td></tr></table>";
pg_free_result($result);
// Closing connection
pg_close($dbpg);
}//end if(isset()) 
?> 
<?php include("../common/end.html"); ?>


