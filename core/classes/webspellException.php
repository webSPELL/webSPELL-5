<?php

class WebspellException extends Exception {
	
	private $registry;
	
	/**
	 * 
	 * overrides default constructor for using language system
	 * @param mixed $message
	 * @param int $code [optional]
	 */
	public function __construct($messageId, $code = 0) {
		$this->registry = Registry::getInstance();
		if(is_numeric($messageId)) {
			$message = $this->registry->get('language')->getTranslation($messageId);
		} else {
			$message = $messageId;
		}
		
		parent::__construct($message, $code);
	}
	
	public function __toString() {
		
		$debugLevel = $this->registry->get('debugLevel');
		$pathToLogFile = $this->registry->get('pathToLogFile');

		$logString = "WebspellException:\n".
		"Message: ".$this->getMessage()."\n".
		"Error Code: ".$this->getCode()."\n".
		"File: ".$this->getFile()."\n".
		"Line: ".$this->getLine()."\n".
		"Backtrace: \n";
		foreach($this->getTrace() as $trace) {
			if(isset($trace['file'])) $logString .= "+ File: ".$trace['file']."\n";
			if(isset($trace['line'])) $logString .= "+ Line: ".$trace['line']."\n";
			if(isset($trace['function'], $trace['class'])) $logString .= "+ Function: ".$trace['class'].$trace['type'].$trace['function']." (".var_export($trace['args'], TRUE).")\n";
			else $logString .= "+ Function: ".$trace['function']." (".var_export($trace['args'], TRUE).")\n";
			$logString .= "\n\n";
		}
		
		// 0 = no details, 1 = only logged, 2 = only on website, 3 = website + logfile
		if($debugLevel === 0){
			return $this->getMessage();
		} else {
			if($debugLevel === 1 || $debugLevel === 3) {
				file_put_contents($pathToFile, $logString, FILE_APPEND);
			}
			if($debugLevel === 2 || $debugLevel === 3) {
				return nl2br($logString);
			}
		}
	}
	
}