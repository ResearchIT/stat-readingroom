<div id="doc"> 
<div id="hd">

<div id="page">
<table class="masthead">
<tr>
<td rowspan="2"><a href="/"><img src="http://www.stat.iastate.edu/_images/top-department.jpg" alt="Department of Statistics &amp; Statistical Laboratory" height="109" width="266" /></a></td><td><a href="http://www.iastate.edu/"><img src="http://www.stat.iastate.edu/_images/top-isu.jpg" alt="Iowa State University" height="36" width="342" /></a></td></tr>
<tr><td><img src="http://www.stat.iastate.edu/_images/top-histogram.jpg" alt="" height="73" width="342" /></td></tr>
</table>

<?php 
$path = explode("/", dirname($_SERVER["SCRIPT_NAME"]));
$bar = "/_images/" . $path[1] . ".jpg";

if (file_exists($_SERVER['DOCUMENT_ROOT'] . $bar)) 
	print("<a href='/" . $path[1] . "/'><img src='$bar' width='608' height='36' /></a>");
?>
</div>
</div>
<div id="bd">
