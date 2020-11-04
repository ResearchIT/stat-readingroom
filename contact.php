<?php

// PHPMailer: because stock mail() sucks. -jdwhite
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
include_once($_SERVER['DOCUMENT_ROOT'] . "/_common/PHPMailer-6.1.7/src/Exception.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/_common/PHPMailer-6.1.7/src/PHPMailer.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/_common/PHPMailer-6.1.7/src/SMTP.php");

// Okta auth via OIDC. -jdwhite
include_once($_SERVER['DOCUMENT_ROOT'] . "/_common/auth_oidc.php");
// Authentication check. Authentication will succeed here or die().
auth_oidc($_SERVER['PHP_SELF']);

// This program doesn't care who's authenticated, effectively limiting
// access to all active ISU Okta accounts.

$DEBUG = getenv('DEBUG');

if ($GLOBALS['DEBUG'] == true) {
	print "<BR>DEBUG mode enabled<BR><UL>"
		."<LI>Despite messages to the contrary, no email will be sent</LI>"
		."</UL><BR>\n";
}

print <<<END
<html>
<script type="text/javascript">
		 document.title="People-STAT, ISU";
         showlist('people_home', level=1 )
		 showlist('people_list', level=2 )
</script type="text/javascript">

<h1>Email</h1>
END;

include_once($_SERVER['DOCUMENT_ROOT'] . "/_common/db.php");

$to      = isset($_GET['to']) ? $_GET['to'] : $_POST['to'];
$netid   = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
$BookID  = isset($_GET['BookID']) ? $_GET['BookID'] : $_POST['BookID'];
$StaffID = isset($_GET['StaffID']) ? $_GET['StaffID'] : $_POST['StaffID'];

if(!empty($BookID)) {
	$results = simple_query("SELECT * FROM ReadingRoomBooks WHERE BookID = '".$BookID."'");
	$Book = $results[0];
}

if(!empty($StaffID)) {
	$results = simple_query("SELECT * FROM Statdir WHERE netid = '".$netid."'");
	$faculty = $results[0];
	$to = $faculty['email'].",".$StaffID."@iastate.edu";
}

if(!empty($StaffID)) {
	$results = simple_query("SELECT * FROM Statdir WHERE netid = '$StaffID'");
	$me = $results[0];
	$myname = $me['name'];
	$name = explode(",",$myname);
	$yourname = $name[1]." ".$name[0];
	$youremail = $me['netid']."@iastate.edu";

	$query = "update ReadingRoomBooks set DateContacted = CURDATE(),ContactedID = '".$StaffID."' where BookID = ".$BookID ;
	simple_query($query);

	$yourMessageStrip = $Book['Title'];
}

// Allowed file types. Please remember to keep the format of this array, add the file extensions you want WITHOUT the dot. Please also be aware that certain file types may cause harm to your website and/or server.
$allowtypes=array("sas","jmp","zip", "rar", "txt", "doc", "jpg", "png", "gif","odt","xml");

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

$subjects[1] = "OVERDUE BOOK";
$subjects[2] = "A Message from Your Reading Room";
$subjects[3] = "Please return: ".$Book->Title;
//$subjects[1] = $Book->Title;
//$subjects[2] = "Please return: ".$Book->Title;
$use_subject_drop=true;

// This is an array of the email address for the array above. There must be an email FOR EACH array value specified above. You can have only 1 department if you want.
//YOU MUST HAVE THE SAME AMMOUNT OF $subjects and $emails or this WILL NOT work correctly! The emails also must be in order for what you specify above!
// You can also seperate the emails by a comma to sent 1 department to multiple email addresses.
$emails=array($to, $to, $to);
//$emails=array($to);

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

if($_POST['submit']==true) {
	extract($_POST, EXTR_SKIP);

	if(trim($yourname)=="") {
		$error.="You did not enter your name!<br />";
	}

	if(trim($youremail)=="") {
		$error.="You did not enter your email!<br />";
	} elseif(!preg_match("/^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}\$/i",$youremail)  ) {
		$error.="Invalid email address.<br />";
	}

	if(trim($emailsubject)=="") {
		$emailsubject=$defaultsubject;
	}

	if(trim($yourmessage)=="") {
		$error.="You did not enter a message!<br />";
	}

	$boundary=md5(uniqid(time()));

	//Little bit of security from people forging headers. People are mean sometimes :(

	$email = explode(",",$to);
	$myemail = $email[1];
	$to = $email[0];
	$yourname=clean($yourname);
	$yourmessage=clean($yourmessage);
	$youremail=clean($youremail);

	$mail = new PHPMailer(true);
	try {
		// Server Settings
		//$mail->SMTPDebug	= SMTP::DEBUG_SERVER;
		$mail->Host			= "mailhub.iastate.edu";
		$mail->Port			= 25;
		$mail->SMTPAuth		= false;
		$mail->isSMTP(); // Send using SMTP.

		// Recipients
		$mail->setFrom($youremail, "{$yourname}");
		$mail->addAddress($to);
		$mail->addCC($myemail);

		// Content
		$mail->isHTML(false); // Plain text, please.
		$mail->Subject = $emailsubject;
		$mail->Body = $yourmessage;


		if ($GLOBALS['DEBUG'] == false) {
			$mail->send();
		}

		$log=($DEBUG == true ? "DEBUG mode - mail not " : "Mail ")
			."sent to ".$to;
		error_log($log);
		print $log."<BR>\n";

		$sent_mail=true;
	} catch (Exception $e) {
		$log="Error sending mail to {$to}: {$mail->ErrorInfo}";
		error_log($log);
		exit("<BR>{$log}<BR>Please report this to the website administrator.<BR>");
	} // try/catch

} // $_POST


//================================================================================
//* Start the form layout
//================================================================================
//:- Please know what your doing before editing below. Sorry for the stop and
//start php.. people requested that I use only html for the form.

print <<<END1
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
END1;

if($display_message) {
	print <<<END2
<div align="center" class="error_message"><b><?=$display_message;?></b></div>
<br />
END2;

} // $display_message

if($sent_mail != true) {
	$serverSelf = $_SERVER['PHP_SELF']."?to=".$to;
	print <<<END3
<form method="post" action=" $serverSelf " enctype="multipart/form-data" name="phmailer" onsubmit="return Checkit(this);">
<table align="center" class="table" width="100%">
END3;

	If($allowattach > 0) {
		print <<<END4
		<tr>
			<td width="100%" class="attach_info" colspan="2">
				<b>Valid Attachment Types:</b> $types <br />
				<b>Max size per file:</b> $max_file_size kb.<br />
				<b>Max combined file size:</b> $max_file_totalkb.
			</td>
		</tr>
END4;
	} // $allowattach > 0

	$yourStrip = stripslashes(htmlspecialchars($yourname));
	$yourStripEmail = stripslashes(htmlspecialchars($youremail));
	//$yourStripEmail = $me->netid."@iastate.edu";
	//$youremail = $me->netid."@iastate.edu";
	//print $yourStripEmail."<br>".$yourStrip."<br>";
	//print $youremail."<br>".$yourname."<br>";
	print <<<END5
	<tr>
	  <td>To:</td>
	  <td><strong> {$faculty["name"]} </strong></td>
	</tr>
	<tr>
		<td width="30%" class="table_body">Your Name:</td>
		<td width="70%" class="table_body"><input name="yourname" type="text" size="30" value=" $yourStrip " /><span class="error_message">*</span></td>
	</tr>
	<tr>
		<td width="30%" class="table_body">Your Email:</td>
		<td width="70%" class="table_body"><input name="youremail" type="text" size="30" value=" $yourStripEmail" /><span class="error_message">*</span></td>
	</tr>
	<tr>
		<td width="30%" class="table_body">Subject:</td>
		<td width="70%" class="table_body">

END5;

	if($use_subject_drop AND is_array($subjects)) {
		print <<<END6
					<select name="emailsubject" size="1">
END6;

		foreach($subjects as $s) {
			$sSpecial = htmlspecialchars(stripslashes($s));
			print <<<END66
							<option value="$s"> $sSpecial </option>
END66;
		} //foreach($subjects as $s)

		print <<<END67
					</select>
END67;

	} else {

		print <<<END7
				<input name="emailsubject" type="text" size="30" value="$sSpecial" />
END7;

	} // if($use_subject_drop AND is_array($subjects))
	print "	</td> </tr>";

	for($i=1;$i <= $allowattach; $i++) {
		$yourMessageStrip = stripslashes(htmlspecialchars($yourmessage));
		print <<<END8
	<tr>
		<td width="30%" class="table_body">Attach File:</td>
		<td width="70%" class="table_body"><input name="attachment[]" type="file" size="30" /></td>
	</tr>
END8;
	} // for
	print <<<END88

	<tr>
		<td colspan="2" width="100%" class="table_body">Your Message:<span class="error_message">*</span><br />
				<textarea name="yourmessage" rows="8" cols="60">$yourMessageStrip</textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2" width="100%" class="table_footer">
			<input type="hidden" name="submit" value="true" />
			<input type="hidden" name="id" value="$netid" />
			<input type="submit" value="$submitvalue" /> &nbsp;
		</td>
	</tr>
</table>
</form>

END88;
} else {

print <<<END9
<div align="center" class="thanks_message">$thanksmessage</div>
<br />
<br />

END9;
}
