<?php

namespace WebSPELL\Core\Classes;

class Session{
	
	/**
	 * 
	 * starts session
	 */
	public function __construct(){
		session_start();
	}
	
	/**
	 * 
	 * destroys session
	 */
	public function destroy(){
		session_destroy();
	}d
	
}

?>