<?php
class Upload {
	static $EXCEPTION_1 = "Unknown Upload Field";
	static $EXCEPTION_2 = "Upload folder does not exist";
	
	protected $options = array(
			'upload_path' => null,
			'allow_override' => false);
	public function __construct($options = array()){
		$this->options = array_merge($this->options, $options);
		$this->files = array();
		if(!is_dir($this->options['upload_path'])){
			throw new Exception('UPLOAD',2);
		}
	}	
	
	/**
	 * Parses $_FILES and returns number of uploaded files
	 * @param string $name Upload field name
	 * @throws Exception
	 */
	public function parseUpload($name){
		if(isset($_FILES[$name])){
			if(is_array($_FILES[$name]['name']) == false){
				// Single Upload needs to be in the same form as multiple
				if($_FILES[$name]['error'] != UPLOAD_ERR_NO_FILE){
					foreach($_FILES[$name] as $key => $value){
						$this->files[$name][$key][0] = $value;
					}
				}
			}
			else{
				// Multiple Uploads
				$this->files[$name] = array();
				$num = 0;
				for($i=0;$i<count($_FILES[$name]['name']);$i++){
					foreach($_FILES[$name] as $key => $data){
						if($_FILES[$name]['error'][$i] != UPLOAD_ERR_NO_FILE){
							$this->files[$name][$key][$num] = $_FILES[$name][$key][$i];
							$num++;
						}
					}
				}
			}
			return count($this->files[$name]);
		}
		else{
			throw new Exception("UPLOAD",1);
		}
	}

	/**
	 * Return the names of the uploaded files
	 * @param string $name
	 * @throws Exception
	 */
	public function getFiles($name){
		if(!isset($this->files[$name])){
			throw new Exception('UPLOAD',1);
		}
		
		$names = array();
		foreach($this->files[$name]['name'] as $file){
			$names[] = $file;
		}
		return $file;
	}
	
	/**
	 * Save uploaded files
	 * @param string $name Upload field name
	 * @param string array $filenames New Filenames
	 * @throws Exception
	 */
	public function moveFiles($name, $filenames = array()){
		if(!isset($this->files[$name])){
			throw new Exception('UPLOAD',1);
		}
		
		if(count($this->files[$name]) != count($filenames)){
			throw new Exception('UPLOAD',4);
		}
		
		$errors = array();
		
		for($i=0;$i<count($this->files[$name]);$i++){
			if($this->files[$name]['error'][$i] == UPLOAD_ERR_OK){
				if(file_exists($this->options['upload_path'].DIRECTORY_SEPARATOR.$filenames[$i]) && $this->options['allow_override'] == false){
					$errors[] = array('id'=>$i,'filename'=>$this->files[$name]['name'][$i],'error'=>'filename exists');
				}
				else{
					move_uploaded_file($this->files[$name]['tmp_name'][$i], $this->options['upload_path'].DIRECTORY_SEPARATOR.$filenames[$i]);
				}
			}
			else{
				$errors[] = array('id'=>$i,'filename'=>$this->files[$name]['name'][$i],'error'=>$this->files[$name]['error'][$i]);
			}
		}
		return $errors;
	}	
}