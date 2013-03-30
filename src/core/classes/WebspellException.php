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
		
		$debugLevel = DEBUG;
		$pathToLogFile = $this->registry->get('pathToLogFile');

		$logString = "WebspellException:\nMessage: ".$this->getMessage().
		"\nError Code: ".$this->getCode().
		"\nFile: ".$this->getFile().
		"\nLine: ".$this->getLine().
		"\nBacktrace:\n";
		foreach($this->getTrace() as $trace) {

			if(isset($trace['file'])) {
                $logString .= "+ File: ".$trace['file']."\n";
            }
			if(isset($trace['line'])) {
                $logString .= "+ Line: ".$trace['line']."\n";
            }
			if(isset($trace['function'], $trace['class'])) {
                $logString .= "+ Function: ".$trace['class'].$trace['type'].$trace['function']." (".$this->varExport($trace['args'], true).")\n";
            }
			else {
                $logString .= "+ Function: ".$trace['function']." (".$this->varExport($trace['args'], true).")\n";
            }

			$logString .= "\n\n";

		}
		
		// 0 = no details, 1 = only logged, 2 = only on website, 3 = website + logfile
		if($debugLevel === 0) {
			return $this->getMessage();
		} else {
			if($debugLevel === 1 || $debugLevel === 3) {
				file_put_contents($pathToLogFile, $logString, FILE_APPEND);
			}
			if($debugLevel === 2 || $debugLevel === 3) {
				return nl2br($logString);
			}
		}
	}
	
	private function varExport($var, $is_str=false)
	{
		$rtn=preg_replace(array('/Array\s+\(/', '/\[(\d+)\] => (.*)\n/', '/\[([^\d].*)\] => (.*)\n/'), array('array (', '\1 => \'\2\''."\n", '\'\1\' => \'\2\''."\n"), substr(print_r($var, true), 0, -1));
		$rtn=strtr($rtn, array("=> 'array ('"=>'=> array ('));
		$rtn=strtr($rtn, array(")\n\n"=>")\n"));
		$rtn=strtr($rtn, array("'\n"=>"',\n", ")\n"=>"),\n"));
		$rtn=preg_replace(array('/\n +/e'), array('strtr(\'\0\', array(\'    \'=>\'  \'))'), $rtn);
		$rtn=strtr($rtn, array(" Object',"=>" Object'<-"));
		if ($is_str) {
			return $rtn;
		}
		else {
			echo $rtn;
		}
	}

	
}
