<?php
namespace Twlan;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?php echo (l('index.twlan')).' - '.$title;?></title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="merged/game.css" />
        <script type="text/javascript" src="merged/game.js"></script>
    </head>
    <body id="ds_body" class="header">
        <table class="content-border" style="margin:auto; margin-top: 25px; border-collapse: collapse; width: 80%">
            <tr>
                <td>
                    <?php echo $content;?>
                </td>
            </tr>
        </table>
    </body>
</html>
