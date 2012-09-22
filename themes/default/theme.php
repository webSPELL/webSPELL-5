<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title><?php echo $theme['core']['pagetitle'];?></title>
        <?php echo $theme['core']['scripts']; ?>
        <?php echo $theme['core']['styles']; ?>
    </head>
    <body>
        <div style="float:left; width:100px; background-color:#ff0000;"><?php echo $theme['box1'];?></div>
        <?php echo $theme['content'];?>
    </body>
</html>