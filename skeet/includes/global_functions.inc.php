<?	
	function jamvar($toJam) {
		echo '<pre>';
		print_r($toJam);
		echo '</pre>';
	}
	
	
	function capString($string) {
		$tableName = str_replace("_"," ",$string);
		$tableName = ucwords($string);
		$tableName = str_replace(" ","",$string);
		return $string;	
	}
	
	
	/* 
		$_REQUEST handling functions
		
		These are actually sort of awesome.  In addition to doing all the required isset() 
		checks on $_REQUEST vars, it allows you to pass an array.
		
		Usage examples:
		
		if(requestExists("do_login")) {
			UserFactory::doLogin(getRequestValue("username"),getRequestValue("password"));
		}	
		
		- or - 
		
		if(requestExists(array("search","last_name"))) {
			do some shit
		}
	*/
	
	function requestExists($key) {
		if(is_array($key)) {
			$tempRequest = $_REQUEST;
			foreach($key as $arrayKey) {
				if(isset($tempRequest[$arrayKey]) && $tempRequest[$arrayKey]) {
					$tempRequest = $tempRequest[$arrayKey];
				}
				else {
					return false;
				}
			}
			return true;
		}
		else {
			if(isset($_REQUEST[$key]) && $_REQUEST[$key]) {
				return true;
			}
		}
		return false;
	}
	
	function getRequestValue($key) {
		if(!is_array($key)) {
			if(requestExists($key)) {
				if(is_array($_REQUEST[$key])) {
					return $_REQUEST[$key];	
				}
				else {
					return trim($_REQUEST[$key]);
				}
			}
		}
		else {
			if(requestExists($key)) {
				$tempRequest = $_REQUEST;
				foreach($key as $arrayKey) {
					if(isset($tempRequest[$arrayKey]) && $tempRequest[$arrayKey]) {
						$tempRequest = $tempRequest[$arrayKey];
					}
				}
				return trim($tempRequest);
			}
		}
		
		return '';
	}
	
	/*
	
		Dropdown related functions.
		
		Pretty simple way of generating standardized <select> boxes.  I suppose you could turn these functions
		into objects, DropDown as the base class with things like DayDropDown,MonthDropDown, and YearDropDown as 
		subclasses, but it seems like an unnecessary use of memory to objectify.
		
		Usage examples:
		
		$valuesArray = array(
			"foo" => 1,
			"bar" => 2,
			"poop" => 3
		);
		
		$customStartArray = array("name" => "If you strike me down","value"=>"I shall grow stronger");
		echo generateDropDownFromArray($valuesArray,$customStartArray,"foo_test","2",'onchange="alert(this.value);" class="select_box" id="foo_test"');
		
		That produces this:
		
		<select name="foo_test" onchange="alert(this.value);" class="select_box" id="foo_test">
			<option value="I shall grow stronger">If you strike me down</option>
			<option value="1">foo</option>
			<option value="2" selected>bar</option>
			<option value="3">poop</option>
		</select>
		
	*/
	


	
	function generateDropDownFromArray($dropDownArray=array(),$customStartArray=array(),$name,$selected=NULL,$extras=NULL) {
		$output = '<select name="' . $name . '" ' . $extras . '>' . "\n";
		if(count($customStartArray) == 0) {
			$output .= '<option value="">--- Select One ---</option>' . "\n";
		}
		else {
			$output .= '<option value="' . $customStartArray["value"] . '">' . $customStartArray["name"] . '</option>' . "\n";
		}
		
		foreach($dropDownArray as $name => $value) {
			if($selected == $value) {
				$checked = "selected";
			}
			else {
				$checked = "";
			}
			$output .= '<option value="' . $value . '" ' . $checked . '>' . $name . '</option>' . "\n";
		}
		$output .= '</select>';
		return $output;
	}
	
	function getDaysDropDown($name,$selected=NULL,$extras=NULL) {
		$dataArray = array();
		for($x = 1; $x <= 31; $x++) {
			$dataArray[$x] = $x;
		}
		$customStartArray = array("name"=>"Day","value"=>"");
		return generateDropDownFromArray($dataArray,$customStartArray,$name,$selected,$extras);
	}
	
	function getMonthsDropDown($name,$selected=NULL,$extras=NULL) {
		$dataArray = array();
		for($x = 1; $x <= 12; $x++) {
			$dateValue = date("F (m)",strtotime($x . "/27/2008"));
			$dataArray[$dateValue] = $x;
		}
		$customStartArray = array("name"=>"Month","value"=>"");
		return generateDropDownFromArray($dataArray,$customStartArray,$name,$selected,$extras);
	}
	
	function getYearsDropDown($name,$selected=NULL,$extras=NULL) {
		$dataArray  = array();
		for($x = date("Y"); $x >= 1900; $x--) {
			$dataArray[$x] = $x;
		}
		$customStartArray = array("name"=>"Year","value"=>"");
		return generateDropDownFromArray($dataArray,$customStartArray,$name,$selected,$extras);
		
	}
	
	
	
	/* 
		validation functions 
	*/

	function isGoodEmail($email) {
		$pattern = "/[[:print:]^@^ ]+(\+[[:print:]^@^ ]){0,1}+@+(([[:alnum:]\._\-])+\.){1,5}+[a-zA-Z]{2,6}/";
		if(preg_match($pattern,$email)) {
			return true;
		}
		return false;
	}
	
	function usernameExists($username) {
		$db = DatabaseFactory::getDatabase(UTRIB_DB);
		$sql = "SELECT * FROM user WHERE username = " . $db->quote($username);
		$result = $db->doQuery($sql);
		if($row = $result->getRow()) {
			return true;
		}
		return false;
	}
	
	function isGoodPassword($password) {
		if(strlen($password) < 6) {
			return false;
		}
		return true;
	}
	
	/* 
		random password gen
	*/
	
	function generateRandomPassword($length=8) {
		$charArray = array();
		$charArray[] = "33";
		$charArray[] = "35";
		$charArray[] = "45";
		for($x = 48; $x <= 57; $x++) {
			$charArray[] = $x;
		}

		for($x = 65; $x <= 90; $x++) {
			$charArray[] = $x;
		}
		
		for($x = 97; $x <= 122; $x++) {
			$charArray[] = $x;
		}
		
		$password = "";
		while(strlen($password) < $length) {
			$password .= chr($charArray[mt_rand(0,(count($charArray)-1))]);
		}
		return $password;
	}
	/*
		email generation
	*/	
	
	function getDatatypeFromSQL($sqlDatatype) {
		$sqlDatatype = preg_replace("/[^a-zA-Z]/",'',$sqlDatatype);
		$dataType = DATA_TYPE_STRING;
		switch($sqlDatatype) {
			case "varchar":
				$dataType = DATA_TYPE_STRING;
				break;
			
			case "text":
			case "bigtext":
				$dataType = DATA_TYPE_TEXT;
				break;
					
			case "int":
			case "bigint":
				$dataType = DATA_TYPE_INTEGER;
				break;
				
			case "datetime":
			case "timestamp":
				$dataType = DATA_TYPE_DATETIME;
				break;
				
			case "float":
			case "decimal":
				$dataType = DATA_TYPE_FLOAT;
				break;
				
			case "tinyint":
				$dataType = DATA_TYPE_TINYINT;
				break;
		}
		return $dataType;
	}
?>