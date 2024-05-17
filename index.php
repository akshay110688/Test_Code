<?php
// CONTACT US FORM 
$error_msg_fname="";
$fname="";
$phone_number="";
$mail="";
$subject="";
$msg="";
$error_msg_Success="";
if(count($_POST)>0)
{
	  

	// XSS CLEAN function
	
		function xss_clean($data)
		{
		// Fix &entity\n;
		$data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
		$data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
		$data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
		$data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

		// Remove any attribute starting with "on" or xmlns
		$data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

		// Remove javascript: and vbscript: protocols
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
		$data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

		// Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
		$data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

		// Remove namespaced elements (we do not need them)
		$data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

		do
		{
			// Remove really unwanted tags
			$old_data = $data;
			$data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
		}
		while ($old_data !== $data);

		// we are done...
		return $data;
		}
	
	
	//////////////////
	
	function test_input($data) {
	  $data = trim($data);
	  $data = stripslashes($data);
	  $data = htmlspecialchars($data);
	  return $data;
	}
	
	function validate_mobile($mobile)
	{
		return preg_match('/^[6-9]\d{9}$/', $mobile);
	}


	 
	if($_REQUEST['fname']!='')
	{
		$fname = test_input($_POST["fname"]);
		// check if name only contains letters and whitespace
		if (!preg_match("/^[a-zA-Z-' ]*$/",$fname)) {
		  $error_msg_fname.= "<BR> Full Name Only letters and white space allowed";
		}
		$fname = xss_clean($_REQUEST['fname']);
		 
	}
	else
	{
		$error_msg_fname="<BR>Full Name is not found";
		
	}

	if($_REQUEST['phone_number']!='')
	{
		 
		if(strlen($_REQUEST['phone_number'])!=10)
		{
			$error_msg_fname.="<BR>Phone Number is not in proper format";
		}
		
		if(validate_mobile($_REQUEST['phone_number']))
		{ 
		}
		else
		{
			$error_msg_fname.="<BR>Phone Number is not in proper format";
		}
		$phone_number = xss_clean($_REQUEST['phone_number']);
		
	}
	else
	{
		$error_msg_fname.="<BR>Phone Number is not found";
	}	

	if($_REQUEST['mail']!='')
	{
		$mail = xss_clean($_REQUEST['mail']);
		$email = test_input($_REQUEST['mail']);
		// check if e-mail address is well-formed
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		  $error_msg_fname.= "<BR>Invalid Email format";
		}
	}
	else
	{
		$error_msg_fname.="<BR>Mail Address is not found";
	}

	if($_REQUEST['subject']!='')
	{
		$subject = xss_clean($_REQUEST['subject']);
	}
	else
	{
		$error_msg_fname.="<BR>Mail Subject is not found";
	}	
		
	if($_REQUEST['msg']!='')
	{
		$msg = xss_clean($_REQUEST['msg']);
	}
	
	//echo $error_msg_fname;exit;
	if($_REQUEST['fname']!='' && $_REQUEST['phone_number']!='' && $_REQUEST['mail']!='' && $_REQUEST['subject']!='' &&  $error_msg_fname=='')
	{
		$servername = "localhost";
		$username = "root";
		$password = "";
		$dbname = "test2";

		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);

		// Check connection
		if ($conn->connect_error) {
		  die("Connection failed: " . $conn->connect_error);
		}

		// prepare and bind
		$stmt = $conn->prepare("select cust_name from test2.customer where cust_name=? or cust_email=? or cust_mobile=?");
		$stmt->bind_param("ssi", $fname,$mail,$phone_number);

		$stmt->execute();
		($stmt_result = $stmt->get_result()) or trigger_error($stmt->error, E_USER_ERROR);
		//echo $stmt_result->num_rows;exit;
		if($stmt_result->num_rows==0)
		{
				$stmt = $conn->prepare("INSERT INTO test2.customer (`cust_name`, `cust_mobile`, `cust_email`, `cust_subject`, `cust_message`, `entry_on`) VALUES (?, ?, ?, ?, ?, now())");
				$stmt->bind_param("sisss", $fname, $phone_number, $mail, $subject, $msg);
				$stmt->execute();
				$error_msg_Success="<BR><Font color='green'>Custumer Details Saved Successfully</Font>";
				 $fname="";
				 $phone_number="";
				 $mail="";
				 $subject="";
				 $msg="";
		}
		else
		 {
			 $error_msg_Success="<BR><Font color='red'>Custumer Details Already Found</Font>";
		 }
		
		
	}
}
?>

<!DOCTYPE html>
<html>
<body>
<form action='index.php' method='post'>
<table align=center>
<tr>
<td colspan=2 align=center><B>Contact Us Form</B></td>
 
</tr>
<tr>
<td colspan=2 align=center><?php if($error_msg_fname!='') { echo $error_msg_fname;}?></td>
</tr>
<tr>
<td colspan=2 align=center><?php if($error_msg_Success!='') { echo $error_msg_Success;}?></td>
</tr>
<tr>
<td>Full Name : </td>
<td><input type='text' name='fname' pattern="^[a-zA-Z ]+$"  value='<?php echo $fname;?>' maxlength=100></td>
</tr>
<tr>
<td>Phone Number : </td>
<td><input type='text' name='phone_number' pattern="^[0-9 ]+$" value='<?php echo $phone_number;?>' maxlength=10></td>
</tr>
<tr>
<td>Email : </td>
<td><input type='email' name='mail' value='<?php echo $mail;?>'></td>
</tr>
<tr>
<td>Subject : </td>
<td><input type='text' name='subject' pattern="^[a-zA-Z0-9 ]+$" value='<?php echo $subject;?>'></td>
</tr>
<tr>
<td valign='top'>Message : </td>
<td><textarea id="msg" name="msg" rows="10" pattern="^[a-zA-Z0-9 ]+$" value='<?php echo $msg;?>'>
</textarea></td>
</tr>
<tr>
<td colspan=2 align=center><input type='Submit' value='Submit'></B></td>
 
</tr>
</table>
</form>
</body>
</html>
