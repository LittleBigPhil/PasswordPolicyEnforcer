<?php

$leetDictionary = array( //key for leet variations of letters
	
	'a' => array('4','@'),
	'b' => array('8'),
	'e' => array('3'),
	'g' => array('9','6'),
	'h' => array('#'),
	'i' => array('1'),
	'l' => array('|'),
	'o' => array('0'),
	's' => array('5','$'),
	't' => array('+','7'),
	'z' => array('2')
	


);

$qwertyKeyboard = array( //values of each keyboard row

	'qwertyRow' => array('q','w','e','r','t','y','u','i','o','p'),
	'asdfRow' => array('a','s','d','f','g','h','j','k','l'),
	'zxcvRow' => array('z','x','c','v','b','n','m'),
	'numberRow' => array('1','2','3','4','5','6','7','8','9'),


);

define('LEETDICTIONARY', $leetDictionary);
define('QWERTYKEYBOARD', $qwertyKeyboard);

function levenshteinVersusFile($password,$threshold,$file){ //Returns true on a match. Threshold is a number between 0 and 1 with 0 always finding a match and 1 never finding a match, so 0 is most strict

	$myfile = fopen($file,'r') or die('Unable to open file!');
	$password = strtolower($password);
	while(!feof($myfile)){
		$test = strtolower(fgets($myfile));
		$percent = 1 - levenshtein($password, $test )/max(strlen($password),strlen($test));
		if($percent  >= $threshold){
			fclose($myfile);
			return true;
		}
	}	
	fclose($myfile);
	return false;
}

function levenshteinVersusString($password,$threshold,$testString){ //Returns true on a match. Threshold is a number between 0 and 1 with 0 always finding a match and 1 never finding a match, so 0 is most strict

	$password = strtolower($password);
	$percent = 1 - levenshtein($password, strtolower($testString) )/max(strlen($password),strlen($testString));
		if($percent  >= $threshold){
			return true;
		}
	return false;
}
	
function leetToNormalText($leetText){
	
	foreach(LEETDICTIONARY as $letter => $leetChar){
		$leetText = str_replace($leetChar,$letter,$leetText);
	}
	
	return $leetText;
	
	
}
//$password should always be in SINGLE QUOTES
function levenshteinTestSuiteVersusFile($password,$threshold,$file,$reverse,$leet){ //Returns array where first value is boolean and second value is description. Threshold is a number between 0 and 1 with 0 always finding a match and 1 never finding a match, so 0 is most strict
	
	if(levenshteinVersusFile($password, $threshold, $file)){
		return array(true,'no changes');
	}
	//vanilla test
	
	if($reverse){
		if(levenshteinVersusFile(strrev($password), $threshold, $file)){ //reversed test
			return array(true,'reverse');
		}
	}
	
	if($leet){
		if(levenshteinVersusFile(leetToNormalText($password), $threshold, $file)){ //reversed test
			return array(true,'leet');
		}
	}
	
	if($reverse && $leet){
		if(levenshteinVersusFile(leetToNormalText(strrev($password)), $threshold, $file)){ //reversed test
			return array(true,'reverse and leet');
		}
	}

	
	return array(false, 'no issues');

}

function levenshteinTestSuiteVersusString($password,$threshold,$string,$reverse,$leet){ //Returns true on a match. Threshold is a number between 0 and 1 with 0 always finding a match and 1 never finding a match, so 0 is most strict
	
	if(levenshteinVersusString($password, $threshold, $string)){
		return array(true,'no changes');
	}
	
	if($reverse){
		if(levenshteinVersusString(strrev($password), $threshold, $string)){ //reversed test
			return array(true,'reverse');
		}
	}
	
	if($leet){
		if(levenshteinVersusString(leetToNormalText($password), $threshold, $string)){ //reversed test
			return array(true,'leet');
		}
	}
	
	if($reverse && $leet){
		if(levenshteinVersusString(leetToNormalText(strrev($password)), $threshold, $string)){ //reversed test
			return array(true,'reverse and leet');
		}
	}

	
	return array(false, 'no issues');

}

function hasConsecutiveCharacters($patternLength, $password){ //Tests if it contains given number of consecutive alphanumerical characters on qwerty keyboard rows
	$password = strtolower($password);
	foreach(QWERTYKEYBOARD as $row){
		for($i = 0; $i < sizeof($row); $i++){
			$pattern = "";
			for($j = 0; $j < sizeof($row)- $i; $j++){
				$pattern .= $row[$i+$j];
				if( strlen($pattern) >= $patternLength && strpos($password, $pattern) !== false){
					return true;
				}

			}

		}
	}
	return false;
	
}

?>