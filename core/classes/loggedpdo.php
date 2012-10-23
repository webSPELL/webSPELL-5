<?php
	class LoggedPDO extends PDO{

		public function __construct($dsn, $username = null, $password = null) {
        	parent::__construct($dsn, $username, $password);
        	$this->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND,"SET NAMES utf8;");
    	}


    	public function query($query, $log = true) {
	        $result = parent::query($query, $log);
	        return $result;
	    }


	    private function log($query, $successful = 1, $error = '', $log = true){
	    	if($log == false){
	    		return;
	    	}
	    	switch($this->logLevel){
	    		case 0:
	    			// Kein Logging;
	    			break;
	    		case 1:
	    			// Log Update, Delete, Insert
	    			$logkeywords = array("INSERT","UPDATE","DELETE");
	    			$log = false;
	    			foreach($logkeywords as $keyword){
	    				if(stripos($query, $keyword) !== false){
	    					$log = true;
	    					break;
	    				}
	    			}
	    			if($log == true){
	    				$this->query("INSERT INTO sql_log (`time`,`user`,`query`,`successful`,`error`) VALUES (NOW(),'','".$query."','".$successful."','".$error."')", false);
	    			}
	    			break;
	    		case 2:
	    			$this->query("INSERT INTO sql_log (`time`,`user`,`query`,`successful`,`error`) VALUES (NOW(),'','".$query."','".$successful."','".$error."')", false);
	    			break;
	    	}
	    }

		public function prepare($sql) {	
			$stmt = parent::prepare($sql, array(
				PDO::ATTR_STATEMENT_CLASS => array('LoggedPDOStatement')
			));
			return $stmt;
		}

	}

	class LoggedPDOStatement extends PDOStatement{
		public function getSingleValue(){
			return $this->fetchColumn(0);
		}
		public function getArray(){
			$this->setFetchMode(PDO::FETCH_NUM);
		    $data = array();
		    while ($row = $this->fetch()) {
		      $data[$row[0]] = $row[1];
		    }
		    return $data;
		}

		private function getFinalQuery($params){

			$query = $this->queryString;
			$keys = array();
			$values = array();
			foreach($params as $key => $value){
				if(is_string($key)){
					$keys[] = "/:".$key."/";
				}
				else{
					$keys[] = "/[?]/";
				}

				if(is_numeric($value)){
					$values[] = $value;
				}
				else{
					$values[] = '"'.$value.'"';
				}
			}

			return preg_replace($keys, $values, $query,1);
		}

		public function execute($input_parameters = null) {
			
		}
	}
?>