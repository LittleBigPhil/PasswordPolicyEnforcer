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
define('SPECIAL_CHARACTERS', '!@#$%^&*()_+-={}[]|:;,.?<>');
define('NUMBERS','1234567890');
define('ALPHABET_CAPITALIZED' , 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
define('ALPHABET_LOWERCASE' , 'abcdefghijklmnopqrstuvwxyz');
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

function containsConsecutiveCharacters($patternLength, $password){ //Tests if it contains given number of consecutive alphanumerical characters on qwerty keyboard rows
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
	
	foreach(QWERTYKEYBOARD as $row){
		$row = array_reverse($row);
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

function containsCapitalsBesidesFirst($password){ //returns an array where the first value returns true if it contains any capitals and the second value returns true if it contains capitals beside the first
	if (strlen($password) > 0){
		foreach(str_split(ALPHABET_CAPITALIZED) as $letter){
			if ($password[0] === $letter){
				$password = ltrim($password, $letter);
				foreach(str_split(ALPHABET_CAPITALIZED) as $letter){
					if(strpos($password, $letter) !== false){
						return array(true,true);
					}
				}
				return array(true, false);
			}
		}
		
		foreach(str_split(ALPHABET_CAPITALIZED) as $letter){
			if(strpos($password, $letter) !== false){
				return array(true,true);
			}
		}
	}
		
	return array(false,false);
}


function containsFromConstant($password,$constant){ //returns true if password contains at least one character from the constant character list
	foreach(str_split($constant) as $constantCharacter){
		if(strpos($password,$constantCharacter) !== false){
			return true;
		}
	}
	
	return false;
}

function isPassphrase($password){
	$myfile = fopen('words_alpha.txt','r') or die('Unable to open file!');
	$password = strtolower($password);
	while(!feof($myfile)){
		$test = strtolower(trim(fgets($myfile)));
		if(strlen($test) > 3 && strstr($password,$test) !== false){
			fclose($myfile);
			return true;
		}
		
		if(strlen($test) > 3 && strstr($password,strrev($test)) !== false){
			fclose($myfile);
			return true;
		}
	}	
	fclose($myfile);
	return false;
}

function testWithOptions($password, $passwordHistory = array()){ //returns true if password meets all the tests in first value of array, and second value gives description of the issue or a success.
	$configuration = parse_ini_file('policy_config.ini');
	
	if((boolean)$configuration['require_password_length'] && (strlen($password) < (integer)$configuration['password_minimum_length'])){
		return array(false, 'Does not meet minimum length requirement for password of ' . $configuration['password_minimum_length'] . ' characters' );
	}
	
	foreach($passwordHistory as $oldPassword){
		$levenshteinResults = levenshteinTestSuiteVersusString($password,(float)$configuration['threshold'],$oldPassword,true,true);
		if($levenshteinResults[0]){
			return array(false, 'Password is too similar to or is a variation of a previous password');
		}
	}
	
	if(isPassphrase($password)){
		if ((boolean)$configuration['require_passphrase_length']){
			if(strlen($password) < (integer)$configuration['passphrase_minimum_length']){
				return array(false, 'Passphrases containing dictionary words must be at least ' . $configuration['passphrase_minimum_length'] .' characters long');
			}
			
		}

	}
	
	if((boolean)$configuration['require_cap']){
		$levenshteinResults = containsCapitalsBesidesFirst($password);
		if(!$levenshteinResults[1] ){
			if($levenshteinResults[0] === true){
				return array(false, 'The password should contain capital letter besides the first as that is insufficient');
			}
			else{
				return array (false, 'The password should contain at least one capital letter somewhere');
			}
		}
	}
	
	if((boolean)$configuration['require_number']){
		if(!containsFromConstant($password,NUMBERS)){
			return array(false,'Must contain at least 1 number to make it harder to guess the password');
		}
	}
	
	if((boolean)$configuration['require_special']){
		if(!containsFromConstant($password,SPECIAL_CHARACTERS)){
			return array(false,'Must contain at least 1 special character to make it harder to guess the password');
		}
	}
	
	if((boolean)$configuration['exclude_consecutive_characters']){
		if(containsConsecutiveCharacters((integer)$configuration['consecutive_characters'], $password)){
			return array(false, 'Must contain no more than ' .  $configuration['consecutive_characters'] . ' consecutive keyboard characters to make it harder to guess your password' );
		}
	}
	
	if(strlen($password) >= (integer)$configuration['passphrase_minimum_length']){
		return array(true, 'Of sufficient length and passed necessary tests');
	}
	
	
	if ((boolean)$configuration['exclude_dictionary']){ //vs english dictionary
		if((boolean)$configuration['exclude_dictionary_substitutions']){
			$levenshteinResults = levenshteinTestSuiteVersusFile($password, (float)$configuration['threshold'], 'words_alpha.txt',true,true);
			if ($levenshteinResults[0]){
				return array(false, 'Failed the '.$levenshteinResults[1].' test because it is too similar to an english word');
			}
			
		}
		else{
			$levenshteinResults = levenshteinTestSuiteVersusFile($password, (float)$configuration['threshold'], 'words_alpha.txt',false,false);
			if ($levenshteinResults[0]){
				return array(false, 'Failed the '.$levenshteinResults[1].' test because it is too similar to an english word');
			}
		}
	}
	
	if ((boolean)$configuration['exclude_dictionary']){ //vs common passwords
		if((boolean)$configuration['exclude_dictionary_substitutions']){
			$levenshteinResults = levenshteinTestSuiteVersusFile($password, (float)$configuration['threshold'], 'million_common_passwords.txt',true,true);
			if ($levenshteinResults[0]){
				return array(false, 'Failed the '.$levenshteinResults[1].' test because it is too similar to a common password');
			}
			
		}
		else{
			$levenshteinResults = levenshteinTestSuiteVersusFile($password, (float)$configuration['threshold'], 'million_common_passwords.txt',false,false);
			if ($levenshteinResults[0]){
				return array(false, 'Failed the '.$levenshteinResults[1].' test because it is too similar to a common password');
			}
		}
	}
	
	return array(true, 'Password passed all tests');
	
}



?>