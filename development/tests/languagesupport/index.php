<?php
$outputs = 10000;
$modules = 100;

echo "<h1>Vergleich gettext/array, $outputs Durchläufe</h1>";

/* gettext */

$sum = 0.0;
$max = 0.0;
$min = 1.0;

ob_start();

$gesamt_start = microtime(true);

// Sprache auf Deutsch setzen
putenv('LC_ALL=de_DE');
setlocale(LC_ALL, 'de_DE');

for($n=0;$n<$modules;$n++) {

    $time_start = microtime(true);

    // Angeben des Pfads der Übersetzungstabellen
    bindtextdomain("forum", "./languages/");
    textdomain("forum");

    for($i=0;$i<$outputs;$i++)
        echo _("back to buddy-list");

    $time_end = microtime(true);
    $time = $time_end - $time_start;
    $sum += $time;
    if($time > $max)
        $max = $time;
    if($time < $min)
        $min = $time;

}

$gesamt_end = microtime(true);
$gesamt = $gesamt_end - $gesamt_start;

ob_end_clean();

printf("Gettext: %f gesamt, durchschnittlich %f Sekunden/Modul (%f s / %f s)<br />", $gesamt, $sum/$i, $min, $max);

/* Array */

$sum = 0.0;
$max = 0.0;
$min = 1.0;

ob_start();

$gesamt_start = microtime(true);

for($n=0;$n<$modules;$n++) {

    $time_start = microtime(true);

    include("languages/de_DE/forum.php");


    for($i=0;$i<$outputs;$i++)
        echo $language_array['back_buddy'];

    $time_end = microtime(true);
    $time = $time_end - $time_start;
    $sum += $time;
    if($time > $max)
        $max = $time;
    if($time < $min)
        $min = $time;

}

$gesamt_end = microtime(true);
$gesamt = $gesamt_end - $gesamt_start;

ob_end_clean();

printf("Array: %f gesamt, durchschnittlich %f Sekunden/Modul (%f s / %f s)", $gesamt, $sum/$i, $min, $max);
?>
