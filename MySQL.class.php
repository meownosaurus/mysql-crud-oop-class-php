<?php
# Connect MySQL Database OOP Class
# @package
# @since 2.5
# @version 1.0
# @link http://github.com/meownosaurus/mysql-oop-class-php/

Class MySQL {
	
	# Base variables for credentials to MySQL database
	# The variables have been declared as private. This
	# means that they will only be available with the 
	# Database class
	private $db_host;	// MySQL Hostname
	private $db_user;	// MySQL Username
	private $db_pass;	// MySQL Password
	private $db_name;	// MySQL Database
	
	# Extra variables that are required by other function such as boolean con variable
	var $link = null; // Database Connection Link

	var $error; // Holds the last error
	var $lastQuery; // Holds the last query
	var $result; // Holds the MySQL query result
	var $records; // Holds the total number of records returned
	var $affected; // Holds the total number of records affected
	var $rawResult; // Holds raw 'arrayed' results
	var $valueResult; // Holds an array of the result

	/*Constructor function this will run when we call the class */
	function MySQL($db_host='localhost', $db_user, $db_pass, $db_name, $persistent=false){
		$this->db_host = $db_host;
		$this->db_user = $db_user;
		$this->db_pass = $db_pass;
		$this->db_name = $db_name;
		$this->persistent = $persistent;
		$this->Connect();
	}
	
	# ===================================
	# Private Functions
	# ===================================
	
	// Connects class to database
	// $persistant (boolean) - Use persistant connection?
	private function Connect(){
		$this->CloseConnect();
		if($this->persistent){
			$this->link = mysql_pconnect($this->db_host, $this->db_user, $this->db_pass);
		}else{
			$this->link = mysql_connect($this->db_host, $this->db_user, $this->db_pass);
		}
		
		if(!$this->link){
   		$this->error = 'Could not connect to server: ' . mysql_error($this->link);
			return false;
		}

		/* Select the requested DB */
		if(!$this->UseDB()){
			$this->error = 'Could not connect to database: ' . mysql_error($this->link);
			return false;
		}
		return true;
		
	}
	
	// Closes the connections
	private function CloseConnect(){
		if($this->link){
			mysql_close($this->link);
		}
	}

	// Select database to use
	private function UseDB(){
		if(!mysql_select_db($this->db_name, $this->link)){
			$this->error = 'Cannot select database: ' . mysql_error($this->link);
			return false;
		}else{
			mysql_query("SET character_set_results='utf8'");
			mysql_query("SET character_set_client='utf8'");
			mysql_query("SET character_set_connection='utf8'");
			return true;
		}
	}
	
	
	// Performs a 'mysql_real_escape_string' on the entire array/string
	private function SecureData($data){
		if(is_array($data)){
			foreach($data as $key=>$val){
				if(!is_array($data[$key])){
					$data[$key] = mysql_real_escape_string($data[$key], $this->link);
				}
			}
		}else{
			$data = mysql_real_escape_string($data, $this->link);
		}
		return $data;
	}
	
	# ===================================
	# Public Functions
	# ===================================

	// Executes MySQL query
	public function ExecuteSQL($query){
		$this->lastQuery 	= $query;
		if($this->result = mysql_query($query, $this->link)){
			$this->records = @mysql_num_rows($this->result);
			$this->affected = @mysql_affected_rows($this->link);
			if($this->records > 0){
				$this->ArrayResults();
				return $this->valueResult;
			}else{
				return true;
			}
		}else{
			$this->error = mysql_error($this->link);
			return false;
		}
	}
	
	// Adds a record to the database based on the array key names
	public function Insert($vars, $table, $exclude = ''){
		
		// Catch Exclusions
		if($exclude == ''){
			$exclude = array();
		}
		
		array_push($exclude, 'MAX_FILE_SIZE'); // Automatically exclude this one
		
		// Prepare Variables
		$vars = $this->SecureData($vars);
		
		$query = "INSERT INTO `{$table}` SET ";
		foreach($vars as $key=>$value){
			if(in_array($key, $exclude)){
				continue;
			}
			//$query .= '`' . $key . '` = "' . $value . '", ';
			$query .= "`{$key}` = '{$value}', ";
		}
		
		$query = substr($query, 0, -2);
		
		return $this->ExecuteSQL($query);
	}
	
	// Deletes a record from the database
	public function Delete($table, $where='', $limit='', $like=false){
		$query = "DELETE FROM `{$table}` WHERE ";
		if(is_array($where) && $where != ''){
			// Prepare Variables
			$where = $this->SecureData($where);
			
			foreach($where as $key=>$value){
				if($like){
					//$query .= '`' . $key . '` LIKE "%' . $value . '%" AND ';
					$query .= "`{$key}` LIKE '%{$value}%' AND ";
				}else{
					//$query .= '`' . $key . '` = "' . $value . '" AND ';
					$query .= "`{$key}` = '{$value}' AND ";
				}
			}
			
			$query = substr($query, 0, -5);
		}
		
		if($limit != ''){
			$query .= ' LIMIT ' . $limit;
		}
		
		return $this->ExecuteSQL($query);
	}
	
	
	// Gets a single row from $from where $where is true
	public function Select($from, $where='', $orderBy='', $limit='', $like=false, $operand='AND',$cols='*'){
		// Catch Exceptions
		if(trim($from) == ''){
			return false;
		}
		$query = "SELECT {$cols} FROM `{$from}` WHERE ";
		if(is_array($where) && $where != ''){
			// Prepare Variables
			$where = $this->SecureData($where);
			
			foreach($where as $key=>$value){
				if($like){
					//$query .= '`' . $key . '` LIKE "%' . $value . '%" ' . $operand . ' ';
					$query .= "`{$key}` LIKE '%{$value}%' {$operand} ";
				}else{
					//$query .= '`' . $key . '` = "' . $value . '" ' . $operand . ' ';
					$query .= "`{$key}` = '{$value}' {$operand} ";
				}
			}
			$query = substr($query, 0, -(strlen($operand)+2));
		}else{
			$query = substr($query, 0, -7);
		}
		
		if($orderBy != ''){
			$query .= ' ORDER BY ' . $orderBy;
		}
		
		if($limit != ''){
			$query .= ' LIMIT ' . $limit;
		}
		
		return $this->ExecuteSQL($query);
		
	}
	
	// Updates a record in the database based on WHERE
	public function Update($table, $set, $where, $exclude = ''){
		// Catch Exceptions
		if(trim($table) == '' || !is_array($set) || !is_array($where)){
			return false;
		}
		if($exclude == ''){
			$exclude = array();
		}
		array_push($exclude, 'MAX_FILE_SIZE'); // Automatically exclude this one
		$set 		= $this->SecureData($set);
		$where 	= $this->SecureData($where);
		// SET
		$query = "UPDATE `{$table}` SET ";
		foreach($set as $key=>$value){
			if(in_array($key, $exclude)){
				continue;
			}
			$query .= "`{$key}` = '{$value}', ";
		}
		$query = substr($query, 0, -2);
		// WHERE
		$query .= ' WHERE ';
		foreach($where as $key=>$value){
			$query .= "`{$key}` = '{$value}' AND ";
		}
		$query = substr($query, 0, -5);
		return $this->ExecuteSQL($query);
	}
	
	// 'Arrays' a single result
	public function ArrayResult(){
		$this->valueResult = mysql_fetch_assoc($this->result) or die (mysql_error($this->link));
		return $this->valueResult;
	}

	// 'Arrays' multiple result
	public function ArrayResults(){
		
		if($this->records == 1){
			return $this->ArrayResult();
		}
		
		$this->valueResult = array();
		while ($data = mysql_fetch_assoc($this->result)){
			$this->valueResult[] = $data;
		}
		return $this->valueResult;
	}
	
	// 'Arrays' multiple results with a key
	public function ArrayResultsWithKey($key='id'){
		if(isset($this->valueResult)){
			unset($this->valueResult);
		}
		$this->valueResult = array();
		while($row = mysql_fetch_assoc($this->result)){
			foreach($row as $theKey => $theValue){
				$this->valueResult[$row[$key]][$theKey] = $theValue;
			}
		}
		return $this->valueResult;
	}

	// Returns last insert ID
	public function LastInsertID(){
		return mysql_insert_id();
	}

	// Return number of rows
	public function CountRows($from, $where=''){
		$result = $this->Select($from, $where, '', '', false, 'AND','count(*)');
		return $result["count(*)"];
	}
}
?>