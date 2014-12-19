<?php
require('common_constant.php');
/*
 * Class	  : Database Record
 * Develop by : Bell Lazy'Life
 */
class DBRecord {
	protected $database;
	protected $data			= null;
	protected $dataType		= null;
	protected $key			= null;
	protected $tableInfo	= null;

	protected $tableName = '';
    protected $keyField = '';
	

	protected $insertSuccess = false;

    public function __construct($key, $values) {
		$this->tableInfo = getTableInfo($this->tableName);
		$this->keyFieldName = $this->tableInfo['keyFieldName'];
		$this->key = $key;
    	$this->dbConnect();

		if($key != null) {
			if(!is_array($key)) {
				// Fetch data with specified key
				$this->fetchData($key);
			} else if($values != null && is_array($values)) {
				// Insert new record with specified field
				switch($this->tableInfo['keyFieldType']) {
					case 1:
						$this->insertSpecifiedField(null, $key, $values);
						break;
					case 2:
						$this->insertSpecifiedField($this->genKeyCharRunning(), $key, $values);
						break;
					case 3:
						$this->insertSpecifiedField($this->genKeyCharRunningWithYear(), $key, $values);
						break;
				}
			} else {
				// Insert new record all field
				switch($this->tableInfo['keyFieldType']) {
					case 1:
						$this->insertAllField(null, $key);
						break;
					case 2:
						$this->insertAllField($this->genKeyCharRunning(), $key);
						break;
					case 3:
						$this->insertAllField($this->genKeyCharRunningWithYear(), $key);
						break;
				}
			}
		}
    }

	public function dbConnect() {
		$this->database = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);
		if($this->database) {
			if(mysql_select_db(DB_NAME, $this->database)) {
				// Initial
				mysql_query("SET NAMES UTF8", $this->database);
				mysql_query("set character_set_results='utf8'", $this->database);
				mysql_query("set character_set_client='utf8'", $this->database);
				mysql_query("set character_set_connection='utf8'", $this->database);
			} else {
				throw new Exception('Cannot select database');
			}
		} else {
			throw new Exception('Cannot connect host');
		}
	}

	public function dbClose() {
		return mysql_close($this->database);
	}

	public function insertSpecifiedField($pk, $fieldNameList, $values) {
		if(count($fieldNameList) == count($values)) {
			$values = $this->wrapSingleQuote($values);
			$sql = "INSERT INTO $this->tableName ";
			$sql .= $this->keyIsChar() ? "($this->keyFieldName," : '(';
			$sql .=	implode(',', $fieldNameList) . ') VALUES ';
			$sql .= $this->keyIsChar() ? '('.$this->wrapSingleQuote($pk).',' : '(';
			$sql .= implode(',', $values) . ')';
			$this->insertSuccess = mysql_query($sql, $this->database);
			if($this->insertSuccess) {
				$this->key = $pk;
				$this->fetchData($pk);
			}
		} else {
			throw new Exception('Number of parameter in field name and values not equal');
		}
	}

	public function insertAllField($pk, $values) {
		global $table;
		$allFieldList = $this->tableInfo['fieldNameList'];
		if(count($values) == count($allFieldList)-1) {
			$fieldNameList = array();
			foreach($allFieldList as $fieldName => $val) {
				if($fieldName != $this->keyFieldName) {
					array_push($fieldNameList, $fieldName);
				}
			}
			$values = $this->wrapSingleQuote($values);
			$sql = "INSERT INTO $this->tableName ";
			$sql .= $this->keyIsChar() ? "($this->keyFieldName," : '(';
			$sql .=	implode(',', $fieldNameList) . ') VALUES ';
			$sql .= $this->keyIsChar() ? '('.$this->wrapSingleQuote($pk).',' : '(';
			$sql .= implode(',', $values) . ')';
			$this->insertSuccess =  mysql_query($sql, $this->database);
			if($this->insertSuccess) {
				$this->key = $pk;
				$this->fetchData($pk);
			}
		} else {
			throw new Exception('Invalid number of values');
		}
	}

	public function fetchData($key) {
		$key = $this->keyIsChar() ? wrapSingleQuote($key) : $key;
		$sql = "SELECT * FROM $this->tableName WHERE $this->keyFieldName = $key";
		$result = mysql_query($sql, $this->database);
		if(mysql_num_rows($result) > 0) {
			$record = mysql_fetch_assoc($result);
			$offset = 0;
			foreach ($record as $fieldName => $value) {
				if($fieldName != $this->keyFieldName) {
					$this->data[$fieldName] 	= $value;
					$this->dataType[$fieldName] = mysql_field_type($result, $offset);
				}
				$offset++;
			}
		} else {
			$this->data = null;
		}
	}

	public function setFieldValue($fieldName, $value) {
		$this->data[$fieldName] = $value;
	}

	public function getFieldValue($fieldName) {
		return $this->data[$fieldName];
	}

	public function getFieldType($fieldName) {
		return $this->dataType[$fieldName];
	}

	public function delete() {
		if($this->key != null) {
			$key = $this->keyIsChar() ? wrapSingleQuote($this->key) : $this->key;
			$sql = "DELETE FROM $this->tableName WHERE $this->keyFieldName = $key";
			return mysql_query($sql, $this->database);
		} else {
			return false;
		}
	}

	public function commit() {
		if(isset($this->data)) {
			 $fieldList = array();
			 foreach($this->data as $fieldName => $fieldValue) {
			 	if($fieldValue == null) {
			 		$fieldValue = "NULL";
			 	} else if($fieldValue != 'NULL') {
			 		$fieldValue = (gettype($fieldValue) == 'string' ? "'$fieldValue'" : $fieldValue);
			 	}
			 	array_push($fieldList, "$fieldName = $fieldValue");
			 }
			 $key = $this->keyIsChar() ? wrapSingleQuote($this->key) : $this->key;
			 $sql = "UPDATE {$this->tableName} SET " . implode(',', $fieldList)
				  . " WHERE {$this->keyFieldName} = {$key}";
             return mysql_query($sql, $this->database);
		} else {
			return flase;
		}
	}

	public function genKeyCharRunning() {
		$keyLength	= $this->tableInfo['keyLength'];
		$keyChar	= $this->tableInfo['keyChar'];
		$keyCharLen	= strlen($keyChar);
		$newkey		= $keyChar;

		$sql		= "SELECT MAX(SUBSTR($this->keyFieldName, ".($keyCharLen+1).')) lastNo '
					. "FROM $this->tableName";
		$result		= mysql_query($sql, $this->database);
		$record		= mysql_fetch_assoc($result);
		$lastNo		= $record['lastNo'];
		if($lastNo != '') {
			// Gen next key
			$nextNo = (int)$lastNo+1;
			$zeroNum = $keyLength-$keyCharLen-(int)strlen($nextNo);
			for($i=1; $i<=$zeroNum; $i++) {
				$newkey .= '0';
			}
			$newkey .= $nextNo;
		} else {
			// Gen first key
			$zeroNum   = $keyLength-$keyCharLen-1;
			for($i=1; $i<=$zeroNum; $i++) {
				$newkey .= '0';
			}
			$newkey .= '1';
		}

		return $newkey;
	}

	public function genKeyCharRunningWithYear() {
		date_default_timezone_set('Asia/Bangkok');
		$keyLength	= $this->tableInfo['keyLength'];
		$keyChar	= $this->tableInfo['keyChar'];
		$keyCharLen	= strlen($keyChar);
		$keyNumLen	= $keyLength - $keyCharLen - 3; // 3 is length of year example /57, /58...
		$minYear	= '/'.substr((int)date('Y') + 543, 2);
		$newkey		= $keyChar;

		$sql		= "SELECT MAX(SUBSTR($this->keyFieldName, ".($keyCharLen+1).", $keyNumLen)) lastNo "
					. "FROM $this->tableName WHERE $this->keyFieldName LIKE '%$minYear'";
		$result		= mysql_query($sql, $this->database);
		$record		= mysql_fetch_assoc($result);
		$lastNo		= $record['lastNo'];
		if($lastNo != '') {
			// Gen next key
			$nextNo = (int)$lastNo+1;
			$zeroNum = $keyLength-$keyCharLen-(int)strlen($nextNo)-3; // 3 is length of year example /57, /58...
			for($i=1; $i<=$zeroNum; $i++) {
				$newkey .= '0';
			}
			$newkey .= $nextNo;
		} else {
			// Gen first key
			$zeroNum   = $keyLength-$keyCharLen-1-3; // 3 is length of year example /57, /58...
			for($i=1; $i<=$zeroNum; $i++) {
				$newkey .= '0';
			}
			$newkey .= '1';
		}

        $newkey .= $minYear;
		return $newkey;
	}

	public function wrapSingleQuote($data) {
		if(is_array($data)) {
			foreach ($data as $key => $value) {
				if($value == null) {
			 		$data[$key] = "NULL";
			 	} else if($value != 'NULL') {
			 		$data[$key] = (gettype($value) == 'string' ? "'$value'" : $value);
			 	}
			}
			return $data;
		} else {
			return "'$data'";
		}
	}

	public function keyIsChar() {
		global $table;
		if($this->tableInfo['keyFieldType'] == 1) {
			return false;
		} else {
			return true;
		}
	}

	public function insertSuccess() {
		return $this->insertSuccess;
	}

	public function getKey() {
		return $this->key;
	}
}

/**
 * Class: TableSpa 
 */
class TableSpa extends DBRecord {
	public function __construct($tableName, $key, $values = null) {
		$this->tableName = $tableName;
		parent::__construct($key, $values);
	}
}

/**
 * Class: Title 
 */
class Titles extends DBRecord {
	public function __construct($key, $values = null) {
		$this->tableName = 'titles';
		parent::__construct($key, $values);
	}
}

/**
 * Class: Position 
 */
class Positions extends DBRecord {
	public function __construct($key, $values = null) {
		$this->tableName = 'positions';
		parent::__construct($key, $values);
	}
}
?>