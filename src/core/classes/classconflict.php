<?php
class ClassConflict {
    /**
     * Holds all used class names
     * @var array
     */
    var $classNames = array();

    /**
     * Holds all filenames of the classes
     * @var array
     */
    var $classNamesFiles = array();

    /**
     * Reads all files and check for conflicts in classnames
     * @return boolean
     */
    public function __construct() {
        $core_classes = FileSystem::getDirectory('./core/classes/', array(".svn"));
        $modules = FileSystem::getDirectory('./modules/',array(".svn", "templates", "languages"));
        $paths = array_merge($core_classes, $modules);
        foreach ($paths as $path) {
            if(is_dir($path)) {
                continue;
            }
            $classes = $this->getClassesInFile($path);
            $this->classNames = array_merge($this->classNames, $classes['classes']);
            $this->classNamesFiles = array_merge($this->classNamesFiles, $classes['files']);
        }
        $this->classNames = array_values(array_unique($this->classNames));
        $this->classNamesFiles = $this->classNamesFiles;
        return true;
    }


    /**
     * Select all classes from a file
     * @param string $path
     * @return array
     */
    private function getClassesInFile($path) {
        $return = array('classes'=>array());
        $content = file_get_contents($path);
        preg_match_all("/(class ([a-z0-9]*)[a-z0-9 ]{0,}{)/si", $content, $classes, PREG_SET_ORDER);
        foreach ($classes as $class) {
            $name = strtolower($class[2]);
            $return['classes'][] = $name;
            $return['files'][$name] = $path;
        }
        return $return;
    }

    /**
     * Check wheater a class name inside the file is already in use
     * @param string $path
     * @return boolean
     */
    public function checkFile($path) {
        $classes = $this->getClassesInFile($path);
        foreach ($classes['classes'] as $class)
            $this->checkClassName($class, $path);

        return true;
    }

    /**
     * Checks wheather the class name is used by an other class
     * @param string $class
     * @return boolean
     */
    public function checkClassName($class, $org_file = null) {
        $lowerName = strtolower($class);
        if(array_search($lowerName, $this->classNames)) {
            $file = $this->classNamesFiles[$lowerName];
            if(strtolower(basename($org_file)) != strtolower(basename($file)))
                throw new WebspellException("ClassConflict Error: Class name ".$class." already used by ".$file, 1);
        }
        return true;
    }

    /**
     * Checks wheater a class name inside a string is already in use
     * @param string $content
     * @return boolean
     */
    public function checkString($content, $file = null) {
        preg_match_all("/(class ([a-z0-9]*)[a-z0-9 ]{0,}{)/si", $content, $classes, PREG_SET_ORDER);
        foreach ($classes as $class)
            $this->checkClassName($class[2], $file);

        return true;
    }

}
?>
