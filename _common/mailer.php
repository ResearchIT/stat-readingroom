<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>Contact us. Statistics, ISU</title>
	<link href="/_common/screen.css" rel="stylesheet" type="text/css" />
</head>

<?php include($_SERVER['DOCUMENT_ROOT'] . "/_common/header.html"); ?>

<h1>Email</h1>
<?php

include_once($_SERVER['DOCUMENT_ROOT'] . "/_common/db.php");


if (isset($_GET['id']))  
  $netid = $_GET['id']; 
else
  $netid = $_POST['id'];

$faculty = $db->get_row("SELECT * FROM Statdir WHERE netid = '$netid'");
$to = $faculty->email;

// Allowed file types. Please remember to keep the format of this array, add the file extensions you want WITHOUT the dot. Please also be aware that certain file types may cause harm to your website and/or server.
$allowtypes=array("zip", "rar", "txt", "doc", "jpg", "png", "gif","odt","xml");

// What priority should the script send the mail? 1 (Highest), 2 (High), 3 (Normal), 4 (Low), 5 (Lowest).
$priority="3"; 

// Should we allow visitors to attach files? How Many? 0 = Do not allow attachments, 1 = allow only 1 file to be attached, 2 = allow two files etc.
$allowattach="0"; 

// Maximum file size for attachments in KB NOT Bytes for simplicity. MAKE SURE your php.ini can handel it, post_max_size, upload_max_filesize, file_uploads, max_execution_time!
// 2048kb = 2MB,       1024kb = 1MB,     512kb = 1/2MB etc..
$max_file_size="1024";

// Maximum file size for all attachments combined in KB NOT Bytes! MAKE SURE your php.ini can handel it, post_max_size, upload_max_filesize, file_uploads, max_execution_time!
// 2048kb = 2MB,       1024kb = 1MB,     512kb = 1/2MB etc..
$max_file_total="2048";

// Value for the Submit Button
$submitvalue=" Send Email "; 

// Value for the Reset Button
$resetvalue=" Reset Form ";

// Default subject? This will be sent if the user does not type in a subject
$defaultsubject="No Subject"; 

// Because many requested it, this feature will add a drop down box for the user to select a array of subjects that you specify below. 
// True = Use this feature, False = do not use this feature

// This is an array of the email subjects the user can pick from. Make sure you keep the format of this array or you will get errors!
// Look at http://phphq.net/forums/viewtopic.php?p=836 for examples on how to use this feature.

if (isset($_GET['course'])) {
  $course = $_GET['course'];
  $subjects[1] = "$course: Homework question";
  $subjects[2] = "$course: Exam question";
  $subjects[3] = "$course: Other";
  $use_subject_drop=true;  
} else {
  $use_subject_drop=false;
}

// This is an array of the email address for the array above. There must be an email FOR EACH array value specified above. You can have only 1 department if you want.
//YOU MUST HAVE THE SAME AMMOUNT OF $subjects and $emails or this WILL NOT work correctly! The emails also must be in order for what you specify above!
// You can also seperate the emails by a comma to sent 1 department to multiple email addresses.
$emails=array($to, $to, $to);

// This is the message that is sent after the email has been sent. You can use html here.
// If you want to redirect users to another page on your website use this: <script type=\"text/javascript\">window.location=\"http://www.YOUR_URL.com/page.html\";</script>
$thanksmessage="Thank you! Your email has been sent."; 



//Little bit of security from people forging headers. People are mean sometimes :(
function clean($key) {
	$key=str_replace("\r", "", $key);
	$key=str_replace("\n", "", $key);
	$find=array(
		"/bcc\:/i",
		"/Content\-Type\:/i",
		"/Mime\-Type\:/i",
		"/cc\:/i",
		"/to\:/i"
	);
  $key=preg_replace($find,"",$key);
  return $key;
}

// Safe for register_globals=on =)

$error="";
$types="";
$sent_mail=false;


// If they post the form start the mailin'!

If($_POST['submit']==true) {
  extract($_POST, EXTR_SKIP);

	If(trim($yourname)=="") { 
		$error.="You did not enter your name!<br />";
	}
	
	If(trim($youremail)=="") { 
		$error.="You did not enter your email!<br />";
	} Elseif(!eregi("^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}\$",$youremail)) {
		$error.="Invalid email address.<br />";
	}

	If(trim($emailsubject)=="") {
		$emailsubject=$defaultsubject;
	}

	If(trim($yourmessage)=="") { 
		$error.="You did not enter a message!<br />";
	}
	
	
	$boundary=md5(uniqid(time()));
	
	//Little bit of security from people forging headers. People are mean sometimes :(
	
	$yourname=clean($yourname);
	$yourmessage=clean($yourmessage);
	$youremail=clean($youremail);
	
	//Headers
	
	$headers.="From: ".$yourname." <".$youremail.">\n";
	$headers.="Reply-To: ".$yourname." <".$youremail.">\n";
	$headers.="MIME-Version: 1.0\n";
	$headers.="X-Mailer: PHP/".phpversion()."\n";
	$headers.="X-Priority: ".$priority."\n"; 

	//Message
	$message.= $yourmessage;

  //print "<pre>" . $headers . $message . "</pre>";
	// Send the completed message
	
	If(!mail($to,$emailsubject,$message,$headers)) {
		Exit("An error has occured, please report this to the website administrator.\n");
	} Else {
		$sent_mail=true;
	}

} // $_POST

/*
//================================================================================
* Start the form layout
//================================================================================
:- Please know what your doing before editing below. Sorry for the stop and start php.. people requested that I use only html for the form..
*/
?>

<script type="text/javascript">
var error="";
e_regex = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,})+$/;

function Checkit(theform) {
	
	if(theform.yourname.value=="") {
		error+="You did not enter your name\n";
	}
	
	if(theform.youremail.value=="") {
		error+="You did not enter your email\n";
	} else if(!e_regex.test(theform.youremail.value)) {
		error+="Invalid email address\n";
	}
		
	if(theform.yourmessage.value=="") {
		error+="You did not enter your message\n";
	}
	
	if(error) {
		alert('**The form returned the following errors:**\n\n' + error);
		error="";
		return false;
	} else {
		return true;
	}
}
</script>


</head>
<body>
<?If($display_message) {?>

<div align="center" class="error_message"><b><?=$display_message;?></b></div>
<br />

<?}?>

<?If($sent_mail!=true) {?>

<form method="post" action="<?=$_SERVER['PHP_SELF'];?>" enctype="multipart/form-data" name="phmailer" onsubmit="return Checkit(this);">
<table align="center" class="table" width="100%">
	<?If($allowattach > 0) {?>
		<tr>
			<td width="100%" class="attach_info" colspan="2">
				<b>Valid Attachment Types:</b> <?=$types?><br />
				<b>Max size per file:</b> <?=$max_file_size?>kb.<br />
				<b>Max combined file size:</b> <?=$max_file_total?>kb.
			</td>
		</tr>
	<?}?>
	
	<tr>
	  <td>To:</td>
	  <td><strong><?=$faculty->name ?></strong></td>
	</tr>
	<tr>
		<td width="30%" class="table_body">Your Name:</td>
		<td width="70%" class="table_body"><input name="yourname" type="text" size="30" value="<?=stripslashes(htmlspecialchars($yourname));?>" /><span class="error_message">*</span></td>
	</tr>
	<tr>
		<td width="30%" class="table_body">Your Email:</td>
		<td width="70%" class="table_body"><input name="youremail" type="text" size="30" value="<?=stripslashes(htmlspecialchars($youremail));?>" /><span class="error_message">*</span></td>
	</tr>
	<tr>
		<td width="30%" class="table_body">Subject:</td>
		<td width="70%" class="table_body">
		
			<?If($use_subject_drop AND is_array($subjects)) {?>
					<select name="emailsubject" size="1">
						<? foreach($subjects as $s) {?>
							<option value="<?= $s;?>"><?=htmlspecialchars(stripslashes($s));?></option>
						<?}?>
					</select>
				
			<?} Else {?>
				
				<input name="emailsubject" type="text" size="30" value="<?=stripslashes(htmlspecialchars($emailsubject));?>" />
				
			<?}?>
			
		</td>
	</tr>

<?For($i=1;$i <= $allowattach; $i++) {?>
	<tr>
		<td width="30%" class="table_body">Attach File:</td>
		<td width="70%" class="table_body"><input name="attachment[]" type="file" size="30" /></td>
	</tr>
<?}?>

	<tr>
		<td colspan="2" width="100%" class="table_body">Your Message:<span class="error_message">*</span><br />
				<textarea name="yourmessage" rows="8" cols="60"><?=stripslashes(htmlspecialchars($yourmessage));?></textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2" width="100%" class="table_footer">
			<input type="hidden" name="submit" value="true" />
			<input type="hidden" name="id" value="<?echo $netid;?>" />
			<input type="submit" value="<?=$submitvalue;?>" /> &nbsp;
		</td>
	</tr>
</table>
</form>

<?} Else {?>

<div align="center" class="thanks_message"><?=$thanksmessage;?></div>
<br />
<br />

<?}?>

<?php include($_SERVER['DOCUMENT_ROOT'] . "/_common/footer.html"); ?>

</html>
