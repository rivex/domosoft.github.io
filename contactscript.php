<?php
	// VALUES FROM THE FORM
	$name		= $_POST['name'];
	$email		= $_POST['email'];
	$message	= $_POST['msg'];

	// ERROR & SECURITY CHECKS
	if ( ( !$email ) ||
		 ( strlen($_POST['email']) > 200 ) ||
	     ( !preg_match("#^[A-Za-z0-9](([_\.\-]?[a-zA-Z0-9]+)*)@([A-Za-z0-9]+)(([\.\-]?[a-zA-Z0-9]+)*)\.([A-Za-z]{2,})$#", $email) )
       ) 
	{ 
	?>
		<script language="javascript">
		<!--
			alert('Error: Wrong email.');
			history.back();
		//-->
		</script>
	<?
		exit; 
	} 
	if ( ( !$name ) ||
		 ( strlen($name) > 100 ) ||
		 ( preg_match("/[:=@\<\>]/", $name) ) 
	   )
	{ 
	?>
		<script language="javascript">
		<!--
			alert('Error: Wrong "Your Name".');
			history.back();
		//-->
		</script>
	<?
		exit; 
	} 
	if ( preg_match("#cc:#i", $message, $matches) )
	{ 
	?>
		<script language="javascript">
		<!--
			alert('Error.');
			history.back();
		//-->
		</script>
	<?
		exit; 
	} 
	if ( !$message )
	{
	?>
		<script language="javascript">
		<!--
			alert('Error: Wrong "Message".');
			history.back();
		//-->
		</script>
	<?
		exit; 
	} 
/*	if (eregi("\r",$email) || eregi("\n",$email)){ 
	?>
		<script language="javascript">
		<!--
			alert('Ошибка: Неправильный email адрес.');
			history.back();
		//-->
		</script>
	<?
		exit; 
	} 
*/	if (FALSE) { 
	?>
		<script language="javascript">
		<!--
			alert('Error Sending.');
			history.back();
		//-->
		</script>
	<?
		exit; 
	} 


	// CREATE THE EMAIL
	$headers	= "Content-Type: text/plain; charset=iso-8859-1\n";
	$headers	.= "From: $name <$email>\n";
	$recipient	= "games.domosoft@gmail.com";
	$subject	= "contact from domosoft.biz";
	$message	= wordwrap($message, 1024);

	// SEND THE EMAIL TO YOU
//	mail($recipient, $subject, $message, $headers);

	// REDIRECT TO THE THANKS PAGE
	?>
		<script language="javascript">
		<!--
			alert('The service is temporarily unavailable!\nSend mail to: games.domosoft(at)gmail.com\nThank you!');
//			alert('Your message has been sent. Thanks.');
			document.location='index.html';
		//-->
		</script>
	<?
?>
