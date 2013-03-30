<?php
class Session {
	/**
	 * 
	 * starts session
	 */
	public function __construct(){
		if(!isset($_SESSION)){
			session_start();
		}
	}
	
	public function setUserId($userId){
		$_SESSION['userId'] = $userId;
	}
	
	public function getUserId($userID){
		return $_SESSION['userId'];
	}
	
	public function getUserRights(){
		if(empty($_SESSION['userRights'])){
			return array();
		}
		return $_SESSION['userRights'];
	}
	
	public function setUserRights($userRights){
		$_SESSION['userRights'] = $userRights;
	}
	
	/**
	 * sets the SESSION-Variable $key with given value
	 * @param String $key
	 * @param mixed $value
	 * @throws WebspellException
	 */
	public function set($key, $value = ""){
		if(empty($key)){
			throw new WebspellException("Parameter 'key' has an invalid value.");
		}
		$_SESSION[$key] = $value;
	}
	
	/**
	 * returns the value of SESSION-Variable $key or NULL
	 * @param String $key
	 */
	public function get($key){
		if(empty($_SESSION[$key])){
			return null;
		}
		return $_SESSION[$key];
	}
	
	/**
	 * destroys the session with the given id
	 * @param String $id
	 */
	public function destroy($id = 0){
		if(empty($id)){
			session_destroy();
		}
		else {
			//backup own session
			$tempSession = session_id();
			//change session to given session
			session_id($id);
			//delete session
			session_destroy();
			//return to own session
			session_id($tempSession);
		}
	}
	
}

?>
