<?php
function processResultXML($xmlstring,$emailID){
		
		global $contextData;
		global $respIDs;
		
		
		//Security to prevent scanning local network
		if(function_exists('libxml_disable_entity_loader')){
			libxml_disable_entity_loader(true);
		}
		//Get Errors
		libxml_use_internal_errors(true);

		$doc = simplexml_load_string($xmlstring);
		//$xml = explode("\n", $xmlstring);

		if (!$doc) {
			$errors = libxml_get_errors();
			//returnError("Inside resultsxml ".$errors);
			/*foreach ($errors as $error) {
				echo display_xml_error($error, $xml);
			}*/
			libxml_clear_errors();
			return false;
			}
		

		
		//Parse info into the array
		$assessmentResult = $doc->result->assessment_result;
		$contextData["Quiz Title"]=$assessmentResult["ident_ref"];
		$respIDs["Candidate"]["Response"]="";
		$respIDs["CandidateData2"]["Response"]="";
		$respIDs["CandidateData3"]["Response"]="";
		$respIDs["CandidateData4"]["Response"]="";
		$respIDs["CandidateData5"]["Response"]="";
		
		foreach($assessmentResult->section_result as $sectionResult){
			$ident_ref = $sectionResult["ident_ref"];
			processSectionXML($sectionResult,$emailID);
		}


		
		$theContext = $doc->context;
		foreach($theContext->generic_identifier as $gi){
			$label = $gi->type_label;
			switch ($label){
				case "Source ID":
					$contextData["Source ID"]=$gi->identifier_string;
				break;
				case "System Language":
					$contextData["System Language"]=$gi->identifier_string;
				break;
				case "OS":
					$contextData["OS"]=$gi->identifier_string;
				break;
				case "Screen Res":
					$contextData["Screen Res"]=$gi->identifier_string;
				break;
				case "Version":
					$contextData["Version"]=$gi->identifier_string;
				break;
				case "Client Time":
					$contextData["Client Time"]=$gi->identifier_string;
				break;
				case "Source URL":
					$contextData["Source URL"]=$gi->identifier_string;
				break;
				default:
			}
			
		}
		
		return true;
		
	}
	
	function startsWith($haystack, $needle){
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}
	
	function processSectionXML($xmlNode,$emailID){
		global $contextData;
		$mainSection=false;
		foreach($xmlNode->section_result as $sectionResult){
			processSectionXML($sectionResult,$emailID);
		}
		foreach($xmlNode->item_result as $itemResult){
			$theIdent=$itemResult["ident_ref"];
			if(startsWith($theIdent,"FB:") || $theIdent=="SendResultsPage" || $theIdent=="StartOfFeedbackPage"|| $theIdent=="ReportPage"){
				continue;
			}else{
				if($theIdent=="IntroductionPage" || $theIdent=="EndPage"){
					$mainSection=true;
				}
				processItemXML($itemResult,$emailID);
			}
		}
		if($mainSection){
			$contextData["TotalTime"]=trim($xmlNode->duration,"PS");
		}
	}

	function processSummary($summary){
		$summaryData=array();
		$summaryData["stem"]=$summary->stem;
		foreach($summary->option as $option){
			$summaryData[(string)$option["ident"]]=(string)$option;
		}
		return $summaryData;
	}
	
	function processItemXML($xmlNode,$emailID){
	
		global $respIDs;
		global $contextData;
		global $customScores;
		
		$duration=$xmlNode->duration;
		
		foreach($xmlNode->summary as $summary){
			$ident = (string)$summary["ident"];
			$theSummary = (string)$summary->asXML();

			//Sometimes the order of the type and ident is the wrong way around
			$pattern = '/summary type=\"([^\"]*)\" ident=\"([^\"]*)\"/';
			$replacement = 'summary ident="$2" type="${1}"';
			$theSummary = preg_replace($pattern, $replacement, $theSummary);

			$questionMD5 = md5((string)$theSummary.$emailID);
			
			$respIDs[$ident]=array();
			$respIDs[$ident]["Email"]=$emailID;
			$respIDs[$ident]["MD5hash"]=$questionMD5;
			$respIDs[$ident]["QuestionReference"]=$ident;
			$respIDs[$ident]["Type"]=(string)$summary["type"];
			$respIDs[$ident]["xmlsummary"]=$theSummary;
			$respIDs[$ident]["summarydata"]=processSummary($summary);
						
			
		}
		foreach($xmlNode->response as $response){
			$ident = (string)$response["ident_ref"];
						
			$responseValue="";			
			$useComma=false;
			foreach($response->response_value as $rValue){
				if($useComma){
					$responseValue=$responseValue.",".(string)$rValue;
				}else{
					$responseValue=(string)$rValue;
					$useComma=true;
				}
			}
			
			if(strlen($responseValue)<255) //IF ANSWER IS SHORT
			{
				$respIDs[$ident]["Response"]=$responseValue;
				$respIDs[$ident]["LongResponse"]="";
				//$Anewques[Response]=$Aanswers[$x][2]; //SET TO SAVE IN MAIN RESULT TABLE
			}
			else //IF ANSWER IS ESSAY LENGTH
			{
				$respIDs[$ident]["Response"]="";
				$respIDs[$ident]["LongResponse"]=$responseValue;
			}//END IF
			
			$respIDs[$ident]["QuestionTime"]=trim($duration,"PS");
		}
		
		$outcomes=$xmlNode->outcomes;
		if($outcomes){
			foreach($outcomes->score as $thescoreval){
				$thescore=(string)$thescoreval;
				if($thescore=="NaN"){
					$thescore=0;
				}
				$varname = preg_replace("/[^a-zA-Z0-9\s\p{P}]/", "", $thescoreval["varname"]);
				foreach ($respIDs as $key => $value){
					if((stristr($key,"(".$varname.")"))){
						$respIDs[$key]["Score"]=$thescore;
						continue 2;
					}
				}
				switch ($varname){
						case "overallscore":
							$contextData["overallscore"]=(string)$thescore;
							continue 2;
						case "totalpossiblescore":
							$contextData["totalpossiblescore"]=(string)$thescore;
							continue 2;
						case "percentagescore":
							$contextData["percentagescore"]=(string)$thescore;
							continue 2;
						case "percentagescorestring":
							$contextData["percentagescorestring"]=(string)$thescore;
							continue 2;
						case "percentagepass":
							$contextData["percentagepass"]=(string)$thescore;
							continue 2;
						case "passfail":
							$contextData["passfail"]=(string)$thescore;
							continue 2;
						default:
				}
				if(strtolower($varname)=="score"){
					//skip
				}else{
					if(array_key_exists($varname, $customScores)){
						$customScores[$varname]=(float)$customScores[$varname]+(float)$thescore;
					}else{
						$customScores[$varname]=(float)$thescore;
					}
				}
			}
		}
		
	}
?>