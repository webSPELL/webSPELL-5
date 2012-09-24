<?php

class User {
	
	private $userId;
	private $email;
	private $password;
	private $status;
	private $registered;
	private $lastAction;
	
	public function getUserId() {
		return $this->userId;
	}
	
	public function setUserId($userId) {
		$this->userId = $userId;
	}
	
	public function getEmail() {
		return $this->email;
	}
	
	public function setEmail($email) {
		$this->email = $email;
	}
	
	public function setPassword($password) {
		$this->password = $password;
	}
	
	public function getStatus() {
		return $this->status;
	}
	
	public function setStatus($status) {
		$this->status = $status;
	}
	
	public function getRegistered() {
		return $this->registered;
	}
	
	public function setRegistered($registered) {
		$this->registered = $registered;
	}
	
	public function validatePassword($password) {
		if($this->password === $password) {
			return true;
		}
		return false;
	}
	
}

?>