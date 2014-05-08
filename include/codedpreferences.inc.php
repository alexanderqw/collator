<?php
/**
 * Project:     Collator: the Question Writer results collator
 * File:        codedpreferences.inc.php
 *
 * Copyright 2012 Question Writer Corporation
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
 * @copyright 2012 Question Writer Corporation
 * @author Alexander McCabe 
 * @package Collator
 */

function getEmailFromUserID($userid,$dbhost,$dbusername,$dbuserpass,$db_name){
	return false;
}

function storeResultsInDatabase($emailID,$dbhost,$dbusername,$dbuserpass,$db_name){
	return true;
}//End Function

function sendResultsByEmail($emailID,$dbhost,$dbusername,$dbuserpass,$db_name){
	return false;
}//End Function  

function logXML(){
	return false;
}

function sendEmail($emailID, $subject, $mailbody, $from, $fromemail,$dbhost,$dbusername,$dbuserpass,$db_name){
	sendToMailer($emailID, $from, $fromemail,$mailbody,$subject);
}

?>