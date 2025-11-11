<?php
namespace Twlan;
?>
<h2><?php echo $tribe->name; ?></h2>
<table class="vis modemenu"><tbody><tr>
    <?php foreach($modes as $sv => $sm) { ?>
    <td width="100" <?php if($mode == $sm) echo 'class="selected"'; ?> >
        <a href="game.php?village=<?php echo $vid; ?>&amp;screen=ally&amp;mode=<?php echo $sm; ?>"><?php l('game.ally.navi.'.$sm); ?></a>
    </td>
    <?php } ?>
</tr></tbody></table>
<br>