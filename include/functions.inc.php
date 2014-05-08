<?php

/**
 * Project:     Collator: the Question Writer results collator
 * File:        functions.inc.php
 *
 * Copyright 2008 Central Question Ltd.
 * 
 * This application is provided as an extension of the 
 * Question Writer desktop software. Where you hold a valid, paid-for
 * license for the Question Writer desktop software, you are hereby 
 * granted a license to use and modify this application for personal use
 * and use internal to your organization. All other rights are reserved. 
 * 
 * This application is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * @link http://www.questionwriter.com/
 * @copyright 2008 Central Question Ltd.
 * @author Alexander McCabe 
 * @package Collator
 */

	//Function to take an array of name/value parameters and escape the values to return a safe MySQL query
	function generateSafeMySQLQuery($thetablename,$fieldarray){
		
		$query = "INSERT INTO $thetablename SET ";
		foreach ($fieldarray as $item => $value){
			if($item!=""){
				$query .= "$item=\"".mysql_real_escape_string($value)."\", ";
			}
		} // foreach
			
		$query = rtrim($query, ', ');
		return $query;	
	}//End Function 
	
	//Function to return an error to the Question Writer Quiz
	//A 500 internal server error indicates to the quiz that the results have not been properly transmitted
	//It should report this to the user and might then try to resend, or send to the backup server.
	function returnError($errorText, $logerror=true)
	{
if($logerror){
		error_log($errorText);
}
		header('HTTP/1.1 500 Internal Server Error');
		print $errorText;
		exit(0);
	}//End Function
	
	function returnErrorNoLog()
	{
		header('HTTP/1.1 500 Internal Server Error');
		exit(0);
	}//End Function
	
	
	//Function to check for a valid email
	function isValidEmail($emailID) 
	{
		return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $emailID);
	}//End Function
	
	//Function to extract the XML document from the POST stream 
	function getPostedXML() 
	{	
      global $HTTP_RAW_POST_DATA;
	  if($ret = @file_get_contents('php://input')) // Try to get the raw POST data
	  {	
		return $ret;
	  }
	  elseif(!empty($HTTP_RAW_POST_DATA)) // Second Attempt
	  {
		return $HTTP_RAW_POST_DATA;
	  }
	  else //Alternative method of extracting the XML document from the POST data
	  {
		if(empty($_POST)) 
			return '';
		$ret = '';
		foreach($_POST as $k => $v)
		  $ret .= ($k == '<result><assessment_result_ident_ref' ? '<result><assessment_result ident_ref' : $k) . "=$v";
		return $ret;
	  }
	}//END FUNCTION...
	
	function formatQuestionTitle($questionTitle){
		$withoutHash = preg_replace('/Question # /','',$questionTitle);
		$withoutTrailingNumber = preg_replace('/\([0-9]+-[0-9]+\)/','',$withoutHash);
		return $withoutTrailingNumber;
	}
	
	function formatQuizTitle($quizTitle){
		$withoutTrailingNumber = preg_replace('/#[0-9]+-[0-9]+/','',$quizTitle);
		return $withoutTrailingNumber;
	}
	
	function formatseconds ($seconds){
		
		$outstring = "";
		$hours = intval(intval($seconds) / 3600); 
		$outstring.= str_pad($hours, 2, "0", STR_PAD_LEFT).':';
		$minutes = intval(($seconds / 60) % 60); 
		$outstring.= str_pad($minutes, 2, "0", STR_PAD_LEFT).':';
		$seconds = intval($seconds % 60); 
		$outstring.= str_pad($seconds, 2, "0", STR_PAD_LEFT);
		return $outstring;
		
	}
	
	function sendToMailer($emailID, $from, $fromemail,$mailbody,$subject){
		$headers = "";
		$headers.= "From: ".$from." <".$fromemail.">" . "\r\n";
		$headers.= "ReplyTo: ".$from." <".$fromemail.">" . "\r\n";
		$headers.= "MIME-Version: 1.0" . "\r\n";
		//Note: No \r\n on the final header
		$headers.= "Content-Type: text/html; charset=\"utf-8\"";
		//ini_set('sendmail_from', $fromemail);
		
		$mailheader="";
		$mailheader.="<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html;charset=utf-8\">". "\r\n";
		$mailheader.="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">". "\r\n";
		$mailheader.="<html xmlns=\"http://www.w3.org/1999/xhtml\">". "\r\n";
		$mailheader.="<head>". "\r\n";
		$mailheader.="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />". "\r\n";
		$mailheader.="<title>Quiz Results</title>". "\r\n";
		$mailheader.="</head><body>". "\r\n";
		
		$mailfooter.="<br/>You can view your results on-line with the Question Writer Tracker</a>". "\r\n";
		$mailfooter.="<br/>http://www.questionwritertracker.com". "\r\n";
		$mailfooter.="</body></html>". "\r\n";
		
		$mailclient=$mailheader.$mailbody.$mailfooter;	
		mail($emailID, $subject, $mailclient, $headers) or returnError("Error - unable to send email");
		
	}
	
	function getMessage(){
		return "Question Writer HTML5 Beta available now.";
	}

function tokenTruncate($string, $your_desired_width) {
  $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
  $parts_count = count($parts);

  $length = 0;
  $last_part = 0;
  for (; $last_part < $parts_count; ++$last_part) {
    $length += strlen($parts[$last_part]);
    if ($length > $your_desired_width) { break; }
  }

  return implode(array_slice($parts, 0, $last_part));
}
?>