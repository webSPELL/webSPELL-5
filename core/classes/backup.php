<?php
class Backup{
		/**
		 * The MySQLi Connection
		 * @var object
		 */
		private $mysqli = false;
		
		/**
		 * The MySQL Backup
		 * @var string
		 */
		private $_backup_mysql = '';
		
		/**
		 * The Filename of the mysql backup
		 * @var string
		 */
		private $mysqlBackupFile = 'mysql_backup.sql';
		
		/**
		 * The name of the database
		 * @var string
		 */
		private $database = '';
		
		/**
		 * Add DROP Table instruction to mysql querys
		 * @var boolean
		 */
		private $dropTable = true;
		
		/**
		 * Holds an array with insert keys
		 * @var array
		 */
		private $insertKeys = array();
		
		/**
		 * The Seperator between statements
		 * @var string
		 */
		private $seperator = "\n";
		
		/**
		 * Type of the Backup, 0 = Backup in folder, 1 = Zip + Compression
		 * @var integer
		 */
		private $backupType = Backup::COMPRESSED;
		
		/**
		 * The ZipArchive Handle
		 * @var resource
		 */
		private $backupHandle = false;
		
		/**
		 * Name of the backup file
		 * @var string
		 */
		private $backupFileName = '';
		
		/**
		 * Allowed Extensions for the compressed backup file
		 * @var array
		 */
		private $allowedBackupExtensions = array('zip');
		
		/**
		 * Backup subfolder pattern
		 * Used by Date function
		 * @var string
		 */
		private $backupPathSubFolderPattern = "Y/m/";
		
		/**
		 * The finished backup path
		 * @var string
		 */
		private $backupPathUse;
		/**
		 * 
		 * Save Backup as Compressed File
		 * @var int 
		 */
		const COMPRESSED = 1;
		/**
		 * 
		 * Save Backup as a copy into folder
		 * @var int
		 */
		const COPY = 0;
		/**
		 * Class constructor method
		 * @param object $mysqli [optional]
		 * @return boolean;
		 */
		public function __construct(){
			$this->mysqli = Registry::getInstance()->get('db');
			$this->database = $this->mysqli->querySingleValue("SELECT DATABASE()");
			$this->setBackupPath($this->mysqli->querySingleValue("SELECT `value` FROM ".DB_PREFIX."system_vars WHERE `field`='backup_path'"));
			$this->setBackupSavePath($this->mysqli->querySingleValue("SELECT `value` FROM ".DB_PREFIX."system_vars WHERE `field`='backup_save_path'"));
			$this->setBackupFileName(date("d-M-Y").".zip");
			return true;
		}
		
		/**
		 * Set the status of DropTable, 1 = insert drop table, 0 = don't insert drop table
		 * @param boolean $new
		 */
		public function setDropTable($new){
			$this->dropTable = $new;
		}
		
		/**
		 * Set the seperator of mysql statements
		 * @param string $string
		 */
		public function setSeperator($string){
			$this->seperator = $string;
		}
		
		/**
		 * Set the type of Backup
		 * @see $backupType
		 * @param integer $type
		 */
		public function setBackupType($type){
			$this->backupType = $type;
		}
		
		/**
		 * Set the save path of the backup 
		 * @param string $path
		 * @return 
		 */
		public function setBackupSavePath($path){
			$path = FileSystem::Folder($path);
			if(is_dir($path) && is_writable($path)){
				$this->backupSavePath = $path;
			}
			else{
				throw new WebspellException("BACKUP ERROR: Save Path (".$path.") is not writeable");
			}
		}
				
		/**
		 * Set the path who will be backuped
		 * @param string $path
		 * @return 
		 */
		public function setBackupPath($path){
			$path = FileSystem::Folder($path);
			if(is_dir($path) && is_writable($path)){
				$this->backupPath = $path;
			}
			else{
				throw new WebspellException("BACKUP ERROR: Backup Path (".$path.") is not writeable");
			}
		}
		
		/**
		 * 
		 * Set the name for the backup
		 * @param string $name Backup-Filename
		 * @throws WebspellException
		 */
		public function setBackupFileName($name){
			$ext = FileSystem::getExtension($name);
			if(in_array($ext,$this->allowedBackupExtensions)){
				$this->backupFileName = $name;
			}
			else{
				throw new WebspellException("BACKUP ERROR: Backup File name (".$name.") has to end with ".implode(",",$this->allowedBackupExtensions));
			}
		}
		
		/**
		 * Set the subfolder pattern
		 * @param string $pattern
		 * @return 
		 */
		public function setSubFolderPattern($pattern){
			$this->backupPathSubFolderPattern = FileSystem::Folder($pattern);
		}
		
		/**
		 * Create a small header info
		 * @return boolean
		 */
		public function getMySQLHead(){
			if($this->mysqli){
				$this->_backup_mysql .= "-- Host: ".$_SERVER['SERVER_NAME']."\n"; //$this->mysqli->host_info
				$this->_backup_mysql .= "-- Date: ".date("d. M Y")." ".date("H:i")."\n";
				$this->_backup_mysql .= "-- Server Version: ".$this->mysqli->server_info."\n";
				$this->_backup_mysql .= "-- PHP-Version: ".phpversion()."\n";
				$this->_backup_mysql .= "-- Database: `".$this->database."`\n";
				return true;
			}
			else{
				throw new Exception("BACKUP ERROR: No MySQL Connection");
			}
		}
		
		/**
		 * Get the backup of the database
		 * @param string $filename The filename of the MySQL Backup
		 * @param boolean $data [optional] Also backup data
		 * @return boolean
		 */
		public function saveMySQLDatabase($filename, $data = true){
			if($this->backupHandle == false){
				$this->openBackup();
			}
			$this->getMySQLHead();
			$tables = $this->mysqli->queryResults("SHOW TABLE STATUS FROM `".$this->database."`");
			foreach($tables as $table){
				$this->createTable($table[0]);
				if($data){
					$this->createTableData($table[0]);
				}
			}
			$this->mysqlBackupFile = $filename;
			return $this->addFromString($filename, $this->_backup_mysql);
		}
		
		/**
		 * Create the mysql query for the table structure
		 * @param string $table The table to be backed up
		 * @return 
		 */
		public function createTable($table){
			$this->_backup_mysql .= "\n-- Table structure for table `".$table."`\n";
			if($this->dropTable){
				$this->_backup_mysql .= "DROP TABLE IF EXISTS `".$table."`;".$this->seperator;
			}
			$create = $this->mysqli->queryResults("SHOW CREATE TABLE `".$table."`");
			if(is_array($create)){
				$this->_backup_mysql .= $create[0][1];
			}
			else{
				throw new Exception("BACKUP ERROR: Can't use SHOW to get table definition. Contact your hoster");
			}
		}
		
		/**
		 * Create the mysql query for the table data
		 * @param string $table The table to be backed up
		 * @return boolean
		 */
		public function createTableData($table){
			$this->_backup_mysql .= "\n-- Data for table `".$table."`\n";
			$this->_backup_mysql .= "INSERT INTO `".$table."` VALUES ".$this->seperator;
			$fields = $this->mysqli->queryResults("SELECT * FROM `".$table."`");
			$rows = $this->mysqli->affected_rows;
			for($i = 0; $i < $rows; $i++){
				$data = $fields[$i];
				$this->_backup_mysql .= "(";
				$number = count($data)/2;
				for($x =0; $x<$number; $x++){
					if(is_numeric($data[$x])){
						$this->_backup_mysql .= $data[$x];
					}
					else{
						$this->_backup_mysql .= "'".$data[$x]."'";
					}
					if($x < $number-1){
						$this->_backup_mysql .= ", ";
					}
				}
				$this->_backup_mysql .= ")";
				$seperator = ($i < $rows-1)
									 ? ",".$this->seperator
									 : $this->seperator;
				$this->_backup_mysql .= $seperator;
			}
			return true;
		}
		
		/**
		 * Create a full Backup of the page
		 * @return boolean
		 */
		public function getFullBackup(){
			$this->openBackup();
			$this->saveFileSystem();
			$this->saveMySQLDatabase('mysql_backup.sql');
			return $this->saveBackup();
		}
		
		/**
		 * Copy filesystem to the backup
		 * @return boolean
		 */
		public function saveFileSystem(){
			if($this->backupHandle == false){
				$this->openBackup();
			}
			foreach(FileSystem::getDirectory($this->backupPath,array(".svn","backup")) as $path){
				$this->addPath($path);
			}
			return true;
		}
		
		/**
		 * Open the ZipArchive and create the folder
		 * @return boolean
		 */
		public function openBackup(){
			$this->backupPathUse = $this->backupSavePath.date($this->backupPathSubFolderPattern);
			if(!is_dir($this->backupPathUse)){
				mkdir($this->backupPathUse,0777,true);
			}
			
			if($this->backupType == Backup::COMPRESSED){
				$this->backupHandle = new ZipArchive();
				$open = $this->backupHandle->open($this->backupPathUse.$this->backupFileName,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
				if($open === true){
					return true;
				}
				else{
					throw new Exception("BACKUP ERROR: Can't create backup file");
				}
			}
		}
		
		/**
		 * Get the Path (+Filename) of the Backup
		 * @return string
		 */
		public function getBackupPath(){
			if($this->backupType == Backup::COMPRESSED){
				if(function_exists("bzcompress")){
					return $this->backupPathUse.$this->backupFileName.".bz2";
				}
				elseif(function_exists("gzencode")){
					return $this->backupPathUse.$this->backupFileName.".gz";
				}
				else{
					return $this->backupPathUse.$this->backupFileName;
				}
			}
			else{
				return $this->backupPathUse;
			}
		}
		
		/**
		 * Save the backup file and try to compress is with Bzip2 or gzip
		 * @return boolean
		 */
		public function saveBackup(){
			if($this->backupType == Backup::COMPRESSED){
				if($this->backupHandle->close()){
					$source = file_get_contents($this->backupPathUse.$this->backupFileName);
					if(function_exists("bzcompress")){
						$string = bzcompress($source);
						unlink($this->backupPathUse.$this->backupFileName);
						return (file_put_contents($this->backupPathUse.$this->backupFileName.".bz2",$string) !== false);
					}
					elseif(function_exists("gzencode")){
						$string = gzencode($source);
						unlink($this->backupPathUse.$this->backupFileName);
						return (file_put_contents($this->backupPathUse.$this->backupFileName.".gz",$string) !== false);
					}
					return true;
				}
				else{
					return false;
				}
			}
			return true;
		}
		
		/**
		 * Add a path (folder or file) to the backup
		 * It will be copied to the folder, or inserted into the zip file
		 * @param string $path The path of a folder/file that will be added
		 * @return boolean
		 */
		public function addPath($path){
			$path_add = str_replace("./", "", $path);
			$path_add = str_replace($this->backupPath, "", $path_add);
			if($this->backupType == Backup::COMPRESSED){
				if(is_dir($path)){
					return $this->backupHandle->addEmptyDir($path_add);
				}
				else{
					
					return $this->backupHandle->addFile($path,$path_add);
				}
			}
			else{
				if(is_dir($path)){
					if(!is_dir($this->backupPathUse.$path_add)){
						return mkdir($this->backupPathUse.$path_add,0777,true);
					}
				}
				else{
					$folder = $this->backupPathUse.$path_add;
					$new_folder = substr($folder,0,strrpos($folder,"/")+1);
					if(!is_dir($new_folder)){
						mkdir($new_folder,0777,true);
					}
					return copy($path,$this->backupPathUse.$path_add);
				}
			}
		}
		
		/**
		 * Add a file from a string to the backup
		 * @param string $filename The filename 
		 * @param string $content The content of the file
		 * @return boolean
		 */
		public function addFromString($filename,$content){
			if($this->backupType == Backup::COMPRESSED){
				return $this->backupHandle->addFromString($filename, $content);
			}
			else{
				return (file_put_contents($this->backupPathUse.$filename, $content) !== false);
			}
		}
}
?>