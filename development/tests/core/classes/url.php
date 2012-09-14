<?php
function arrayRecursiveDiff($aArray1, $aArray2) { 
    $aReturn = array(); 
   
    foreach ($aArray1 as $mKey => $mValue) { 
        if (array_key_exists($mKey, $aArray2)) { 
            if (is_array($mValue)) { 
                $aRecursiveDiff = arrayRecursiveDiff($mValue, $aArray2[$mKey]); 
                if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; } 
            } else { 
                if ($mValue != $aArray2[$mKey]) { 
                    $aReturn[$mKey] = $mValue; 
                } 
            } 
        } else { 
            $aReturn[$mKey] = $mValue; 
        } 
    } 
   
    return $aReturn; 
}

$input = Array(
			Array('module'=>'user'),
			Array('module'=>'user', 'params'=> Array('desc', '5')),
			Array('module'=>'user', 'id'=>'1234', 'title'=>'bluetiger', 'params'=> Array('desc', 'contact')),
			Array('module'=>'user', 'section'=>'edit', 'id'=>'200', 'title'=>'editieren'),
			Array('module'=>'user', 'section'=>'edit'),
			Array('module'=>'user', 'section'=>'edit', 'params'=> Array('desc', '5')),
			Array('module'=>'forum', 'section'=>'board', 'id'=>'31', 'title'=>'allgemeiner support'),
			Array('module'=>'forum', 'section'=>'topic', 'id'=>'1233', 'title'=>'ich brauche hilfe!'),
			Array('module'=>'forum', 'section'=>'topic', 'id'=>'1234', 'title'=>'Dies ist der Topictitel! -#äö+ü', 'params'=> Array('desc', '5'))
		);

$output = Array();
$input_original = Array();

foreach($input as $value) {

	array_push($input_original, Array('module'=>$value['module'], 'section'=>(isset($value['section']) ? $value['section'] : "default"), 'id'=>(isset($value['id']) ? $value['id'] : ""), 'params'=>(isset($value['params']) ? $value['params'] : Array())));
	$c = new Url();
	$url = $c->generateUrl($value);
	echo $url."\n";
	$_SERVER['QUERY_STRING'] = $url;
	$c->parseQueryString();
	array_push($output, Array('module'=>$c->getModule(), 'section'=>$c->getSection(), 'id'=>$c->getId(), 'params'=>$c->getModuleParams()));
	unset($c);

}

$diff = arrayRecursiveDiff($input_original, $output);
if(empty($diff))
	echo "Test erfolgreich";
else
	echo "\nTest fehlgeschlagen:\n".print_r($diff, true);

?>