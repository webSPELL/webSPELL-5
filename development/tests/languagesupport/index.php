<?php
$rounds = 10000;

echo "<h1>Vergleich gettext/array, $rounds Durchläufe</h1>";

/* gettext */

$sum = 0.0;
$max = 0.0;
$min = 1.0;

ob_start();

for($i=0;$i<$rounds;$i++) {

    $time_start = microtime(true);

    // Sprache auf Deutsch setzen
    putenv('LC_ALL=de_DE');
    setlocale(LC_ALL, 'de_DE');

    // Angeben des Pfads der Übersetzungstabellen
    bindtextdomain("forum", "./languages/");
    textdomain("forum");

    echo _("back to buddy-list");
    echo "<br />";

    $time_end = microtime(true);
    $time = $time_end - $time_start;
    $sum += $time;
    if($time > $max)
        $max = $time;
    if($time < $min)
        $min = $time;
}

ob_end_clean();

printf("Gettext: durchschnittlich %f Sekunden (%f s / %f s)<br />", $sum/$i, $min, $max);

/* Array */

$sum = 0.0;
$max = 0.0;
$min = 1.0;

ob_start();

for($i=0;$i<$rounds;$i++) {

    $time_start = microtime(true);

    include("languages/de_DE/forum.php");

    echo $language_array['back_buddy'];
    echo "<br />";

    $time_end = microtime(true);
    $time = $time_end - $time_start;
    $sum += $time;
    if($time > $max)
        $max = $time;
    if($time < $min)
        $min = $time;
}

ob_end_clean();

printf("Array: durchschnittlich %f Sekunden (%f s / %f s)", $sum/$i, $min, $max);
?>
