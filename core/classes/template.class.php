<?php
class Template {
    const Modul = 1;
    const Core = 0;

    private $regex_block = '/{block;(\w+?)}/i';
    private $regex_element = '/{(?:lang;){0}([\w]+)}/i';
    private $regex_split = '/({(?:block;(?:\w+?)|end)})/i';
    private $regex_language = '/{lang;([\w]+)}/i';

    private $loaded_templates = array();

    private $type = Template::Modul;
    private $template_basepath = './';

    public function setType($type){
        if(in_array($type, array(self::Modul,self::Core))){
            $this->type = $type;
        }
        else{
            throw new Exception("Unkown Type", 1);
        }
    }

    public function setBasePath($path){
        if(is_dir($path) && is_readable($path)){
            $this->template_basepath = realpath($path.DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
        }
        else{
            throw new Exception("Unknown Path", 2);
        }
    }


    private function getTemplatePath($template, $namespace){
        if($this->type == self::Modul){
            $path = $this->template_basepath.'modules'.DIRECTORY_SEPARATOR.$namespace.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$template.'.tpl';
        }
        elseif($this->type == self::Core){
            $path = $this->template_basepath.'core'.DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$namespace.DIRECTORY_SEPARATOR.$template.'.tpl';
        }
        else{
            throw new Exception("Unknown Type", 1);
        }
        return $path;
    }

    public function loadTemplate($template, $namespace){
        if(!in_array($template, $this->loaded_templates)){
            $path = $this->getTemplatePath($template, $namespace);
            if(file_exists($path)){
                $this->loaded_templates[$namespace.'_'.$template] = array();
                $this->loaded_templates[$namespace.'_'.$template]['blocks'] = array();
                $content = file_get_contents($path);
                $splits = preg_split($this->regex_split, $content,NULL,PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY | PREG_SPLIT_NO_EMPTY);
                foreach($splits as $block){
                    $block = trim($block);
                    if(empty($block)){
                        continue;
                    }
                    if($this->isBlockStart($block)){
                        $blockname = $this->getBlockName($block);
                    }
                    elseif($this->isBlockEnd($block)){
                    }
                    else{
                        $this->loaded_templates[$namespace.'_'.$template]['blocks'][$blockname] = $block;
                    }
                }
            }
            else{
                throw new Exception("Template (".$path.") does not exist", 3);
            }
        }
    }

    public function fillTemplate($template, $namespace, $block, $values = array()){
        if(!isset($this->loaded_templates[$namespace.'_'.$template])){
            $this->loadTemplate($template, $namespace);
        }

        if(!isset($this->loaded_templates[$namespace.'_'.$template]['blocks'][$block])){
            throw new Exception("Unknown Block name", 4);
        }

        $content = $this->loaded_templates[$namespace.'_'.$template]['blocks'][$block];
        preg_match_all($this->regex_element, $content, $results,PREG_SET_ORDER);
        $key_function = function($object){
            return $object[0];
        };
        $value_function = function($object) use($values){
            if(isset($values[$object[1]])){
                return $values[$object[1]];
            }
            else{
                return '';
            }
        };
        $keys = array_map($key_function, $results);
        $values = array_map($value_function,$results);
        $content_filled = str_replace($keys, $values, $content);
        return $content_filled;
    }

    public function translateTemplate($template, $namespace, $block, $values = array()){
        $content = $this->fillTemplate($template, $namespace, $block, $values);


    }

    private function getBlockName($tag){
       preg_match($this->regex_block, $tag,$erg);
       return $erg[1];
    }

    private function isBlockStart($tag){
        return preg_match($this->regex_block, $tag);
    }

    private function isBlockEnd($tag){
        return $tag == "{end}";
    }
}
?>
