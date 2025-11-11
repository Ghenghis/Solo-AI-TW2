<?php
use \TWLan\Framework\Text;
?>
<table class="vis modemenu">
    <tbody>
        <?php foreach ($modes as $_mode) { ?>
        <tr>
            <td <?php if ($mode == $_mode) echo 'class="selected"'; ?> style="min-width: 80px">
                <a href="game.php?village=<?php echo $vid; ?>&amp;mode=<?php echo $_mode; ?>&amp;screen=ranking">
                    <?php l('game.menu.ranking.'.Text::underScore2CamelCase($_mode)); ?>
                </a>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>
