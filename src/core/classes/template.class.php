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
                $names = array();
                foreach($splits as $block){
                    $block = trim($block);
                    if(empty($block)){
                        continue;
                    }
                    if($this->isBlockStart($block)){
                        $blockname = $this->getBlockName($block);
                        array_push($names, $blockname);
                        $this->loaded_templates[$namespace.'_'.$template]['blocks'][$blockname] = array("content"=>"","blocks"=>array());
                    }
                    elseif($this->isBlockEnd($block)){
                        array_pop($names);
                    }
                    else{
                        $blockname = $names[count($names)-1];
                        $this->loaded_templates[$namespace.'_'.$template]['blocks'][$blockname]["content"] .= $block;
                        if(count($names) != 1){
                            $lastblock = $names[count($names)-2];
                            $this->loaded_templates[$namespace.'_'.$template]['blocks'][$lastblock]["blocks"][] = $blockname;
                            $this->loaded_templates[$namespace.'_'.$template]['blocks'][$lastblock]["content"] .= "{block;".$blockname."}";
                        }
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

        if(!is_array($values)){
            throw new Exception("values needs to be an array",5);
        }

        $content = $this->loaded_templates[$namespace.'_'.$template]['blocks'][$block]["content"];
        $blocks = $this->loaded_templates[$namespace.'_'.$template]['blocks'][$block]["blocks"];
        preg_match_all($this->regex_element, $content, $results,PREG_SET_ORDER);
        if(ArrayHelper::is_assoc($values) == false){
            $content_filled = '';
            foreach ($values as $key => $value_array) {
                $key_function = function($object){
                    return $object[0];
                };
                $value_function = function($object) use($value_array){
                    if(isset($value_array[$object[1]])){
                        return $value_array[$object[1]];
                    }
                    else{
                        return '';
                    }
                };
                $org_values = $value_array;
                $keys = array_map($key_function, $results);
                $values = array_map($value_function,$results);
                $pre_parsed = str_replace($keys, $values, $content);
                $content_filled .=$this->fillSubTemplates($template, $namespace, $block, $org_values, $pre_parsed);
            }
        }
        else{
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
            $org_values = $values;
            $keys = array_map($key_function, $results);
            $values = array_map($value_function,$results);
            $pre_parsed = str_replace($keys, $values, $content);
            $content_filled = $this->fillSubTemplates($template, $namespace, $block, $org_values, $pre_parsed);
        }
        return $content_filled;
    }

    public function translateTemplate($template, $namespace, $block, $values = array()){
        $content = $this->fillTemplate($template, $namespace, $block, $values);
    }

    private function getBlockName($tag){
        preg_match($this->regex_block, $tag,$erg);
        return $erg[1];
    }

    private function fillSubTemplates($template,$namespace, $block, $values, $current){
        $subblocks = $this->loaded_templates[$namespace.'_'.$template]['blocks'][$block]["blocks"];
        if(count($subblocks)){
            foreach($subblocks as $block_extra){
                if(isset($values[$block_extra])){
                    $block_content = $this->fillTemplate($template,$namespace,$block_extra, $values[$block_extra]);
                }
                else{
                    $block_content = "";
                }
                $current = str_replace("{block;".$block_extra."}", $block_content,$current);
            }
        }
        return $current;
    }

    private function isBlockStart($tag){
        return preg_match($this->regex_block, $tag);
    }

    private function isBlockEnd($tag){
        return $tag == "{end}";
    }
}
?>
