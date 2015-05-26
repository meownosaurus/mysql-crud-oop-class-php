<?php
# MySQL Class PHP
# @Package Database PHP
# @since 2.5
# @Version 1.3

class Database {
	/* 
	 * Create variables for credentials to MySQL database
	 * The variables have been declared as private. This
	 * means that they will only be available with the 
	 * Database class
	 */
	private $db_host = "localhost";  // Change as required
	private $db_user = "username";  // Change as required
	private $db_pass = "password";  // Change as required
	private $db_name = "database";	// Change as required
    private $persistent = false;
	
	/*
	 * Extra variables that are required by other function such as boolean con variable
	 */
	var $con = false; // Check to see if the connection is active
	var $result = array(); // Any results from a query will be stored here
    var $myQuery = ""; // used for debugging process with SQL return
    var $numResults = ""; // used for returning the number of rows

    function __construct() {
        /* Connect to the MySQl Server */
        if(!$this->con){
            if ($this->persistent){
                $myconn = @mysql_pconnect($this->db_host, $this->db_user, $this->db_pass); // mysql_pconnect() with variables defined at the start of Database class
            } else {
                $myconn = @mysql_connect($this->db_host, $this->db_user, $this->db_pass); // mysql_connect() with variables defined at the start of Database class
            }
            if($myconn){
                $select_db = @mysql_select_db($this->db_name,$myconn); // Credentials have been pass through mysql_connect() now select the database
                if($select_db){
                    mysql_query("SET character_set_results='utf8'"); // change character set to utf8
                    mysql_query("SET character_set_client='utf8'");
                    mysql_query("SET character_set_connection='utf8'");                    
                    $this->con = true;
                    return true;  // Connection has been made return TRUE
                }else{
                    array_push($this->result, mysql_error());
                    return false;  // Problem selecting database return FALSE
                }  
            }else{
                array_push($this->result, mysql_error());
                return false; // Problem connecting return FALSE
            }
        }else{
            return true; // Connection has already been made return TRUE 
        }
    }

    function __destruct() {
        // If there is a connection to the database
        if($this->con){
            // We have found a connection, try to close it
            if(@mysql_close()){
                // We have successfully closed the connection, set the connection variable to false
                $this->con = false;
                // Return true tjat we have closed the connection
                return true;
            }else{
                // We could not close the connection, return false
                return false;
            }
        }
    }
	
	public function query($sql){
		$query = @mysql_query($sql);
        $this->myQuery = $sql; // Pass back the SQL
		if($query){
			// If the query returns >= 1 assign the number of rows to numResults
			$this->numResults = mysql_num_rows($query);
			// Loop through the query results by the number of rows returned
			for($i = 0; $i < $this->numResults; $i++){
				$r = mysql_fetch_array($query);
               	$key = array_keys($r);
               	for($x = 0; $x < count($key); $x++){
               		// Sanitizes keys so only alphavalues are allowed
                   	if(!is_int($key[$x])){
                   		if(mysql_num_rows($query) >= 1){
                   			$this->result[$i][$key[$x]] = $r[$key[$x]];
						}else{
							$this->result = null;
						}
					}
				}
			}
			return true; // Query was successful
		}else{
			array_push($this->result, mysql_error());
			return false; // No rows where returned
		}
	}
	
	// Function to SELECT from the database
	public function select($table, $rows = '*', $join = null, $where = null, $order = null, $limit = null){
		// Create query from the variables passed to the function
		$q = 'SELECT '.$rows.' FROM '.$table;
		if($join != null){
			$q .= ' JOIN '.$join;
		}
        if($where != null){
        	$q .= ' WHERE '.$where;
		}
        if($order != null){
            $q .= ' ORDER BY '.$order;
		}
        if($limit != null){
            $q .= ' LIMIT '.$limit;
        }
        $this->myQuery = $q; // Pass back the SQL
		// Check to see if the table exists
        if($this->tableExists($table)){
        	// The table exists, run the query
        	$query = @mysql_query($q);
			if($query){
				// If the query returns >= 1 assign the number of rows to numResults
				$this->numResults = mysql_num_rows($query);
				// Loop through the query results by the number of rows returned
				for($i = 0; $i < $this->numResults; $i++){
					$r = mysql_fetch_array($query);
                	$key = array_keys($r);
                	for($x = 0; $x < count($key); $x++){
                		// Sanitizes keys so only alphavalues are allowed
                    	if(!is_int($key[$x])){
                    		if(mysql_num_rows($query) >= 1){
                    			$this->result[$i][$key[$x]] = $r[$key[$x]];
							}else{
								$this->result = null;
							}
						}
					}
				}
				return true; // Query was successful
			}else{
				array_push($this->result, mysql_error());
				return false; // No rows where returned
			}
      	}else{
      		return false; // Table does not exist
    	}
    }
	
	// Function to insert into the database
    public function insert($table, $params=array()){
    	// Check to see if the table exists
    	 if($this->tableExists($table)){
    	 	$sql='INSERT INTO `'.$table.'` (`'.implode('`, `',array_keys($params)).'`) VALUES ("' . implode('", "', $params) . '")';
            $this->myQuery = $sql; // Pass back the SQL
            // Make the query to insert to the database
            if($ins = @mysql_query($sql)){
            	array_push($this->result, mysql_insert_id());
                return true; // The data has been inserted
            }else{
            	array_push($this->result, mysql_error());
                return false; // The data has not been inserted
            }
        }else{
        	return false; // Table does not exist
        }
    }
	
	//Function to delete table or row(s) from database
    public function delete($table, $where = null){
    	// Check to see if table exists
    	 if($this->tableExists($table)){
    	 	// The table exists check to see if we are deleting rows or table
    	 	if($where == null){
                $delete = 'DELETE '.$table; // Create query to delete table
            }else{
                $delete = 'DELETE FROM '.$table.' WHERE '.$where; // Create query to delete rows
            }
            // Submit query to database
            if($del = @mysql_query($delete)){
            	array_push($this->result, mysql_affected_rows());
                $this->myQuery = $delete; // Pass back the SQL
                return true; // The query exectued correctly
            }else{
            	array_push($this->result, mysql_error());
               	return false; // The query did not execute correctly
            }
        }else{
            return false; // The table does not exist
        }
    }
	
	// Function to update row in database
    public function update($table, $params=array(), $where){
    	// Check to see if table exists
    	if($this->tableExists($table)){
    		// Create Array to hold all the columns to update
            $args=array();
			foreach($params as $field=>$value){
				// Seperate each column out with it's corresponding value
				$args[]=$field.'="'.$value.'"';
			}
			// Create the query
			$sql='UPDATE '.$table.' SET '.implode(',',$args).' WHERE '.$where;
			// Make query to database
            $this->myQuery = $sql; // Pass back the SQL
            if($query = @mysql_query($sql)){
            	array_push($this->result, mysql_affected_rows());
            	return true; // Update has been successful
            }else{
            	array_push($this->result, mysql_error());
                return false; // Update has not been successful
            }
        }else{
            return false; // The table does not exist
        }
    }
	
	// Private function to check if table exists for use with queries
	private function tableExists($table){ 
		$tables_db = mysql_list_tables($this->db_name); 
		while (list ($temp) = mysql_fetch_array($tables_db)){
			if ($temp == $table) {
				return true;
			}
		}
		array_push($this->result, $table." does not exist in this database");
		return false;
	}

	// Public function to return the data to the user
    public function getResult(){
        $val = $this->result;
        $this->result = array();
        return $val;
    }

    //Pass the SQL back for debugging
    public function getQuery(){
        $val = $this->myQuery;
        $this->myQuery = array();
        return $val;
    }

    //Pass the number of rows back
    public function numRows(){
        $val = $this->numResults;
        $this->numResults = array();
        return $val;
    }

    // Escape your string
    public function escapeString($data){
		if (function_exists('mysql_real_escape_string') && $myconn) {
			$data = mysql_real_escape_string($data, $myconn);
		} else {
			$data = mysql_escape_string($data);
		}
		return $data;
    }

    // Insert ID
    function insert_id(){
        return mysql_insert_id();
    }

    // Free result
    function free($query) {
        mysql_free_result($query);
    }

    // Close connection
    function close() {
        // If there is a connection to the database
        if($this->con){
            // We have found a connection, try to close it
            if(@mysql_close()){
                // We have successfully closed the connection, set the connection variable to false
                $this->con = false;
                // Return true tjat we have closed the connection
                return true;
            }else{
                // We could not close the connection, return false
                return false;
            }
        }
    }
} 
