<?php
class Autoload{

    public function load($class){
        if(strstr($class, "_")){
            $class = str_replace("_", DIRECTORY_SEPARATOR, $class);
        }

        $class .= '.php';




        return false;
    }

}