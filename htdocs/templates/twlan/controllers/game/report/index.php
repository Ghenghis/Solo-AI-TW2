<?php
namespace Twlan;
?>
<h2><?php l('game.report.title'); ?></h2>

<table class="no_spacing" width="100%">
    <tbody>
    <tr><td valign="top">
            <table class="vis modemenu" width="100">
            <tbody>
            <tr><td <?php if ($mode == 'all') echo 'class="selected"'; ?> style="min-width: 80px"><a href="game.php?village=<?php echo $vid; ?>&amp;mode=all&amp;screen=report">
            <?php l('game.report.navi.all'); ?> </a></td></tr>
            <tr><td <?php if ($mode == 'attack') echo 'class="selected"'; ?> style="min-width: 80px"><a href="game.php?village=<?php echo $vid; ?>&amp;mode=attack&amp;screen=report">
            <?php l('game.report.navi.attack'); ?> </a></td></tr>
            <tr><td <?php if ($mode == 'defense') echo 'class="selected"'; ?> style="min-width: 80px"><a href="game.php?village=<?php echo $vid; ?>&amp;mode=defense&amp;screen=report">
            <?php l('game.report.navi.defense'); ?> </a></td></tr>
            <tr><td <?php if ($mode == 'support') echo 'class="selected"'; ?> style="min-width: 80px"><a href="game.php?village=<?php echo $vid; ?>&amp;mode=support&amp;screen=report">
            <?php l('game.report.navi.support'); ?> </a></td></tr>
            <tr><td <?php if ($mode == 'trade') echo 'class="selected"'; ?> style="min-width: 80px"><a href="game.php?village=<?php echo $vid; ?>&amp;mode=trade&amp;screen=report">
            <?php l('game.report.navi.trade'); ?> </a></td></tr>
            <tr><td <?php if ($mode == 'other') echo 'class="selected"'; ?> style="min-width: 80px"><a href="game.php?village=<?php echo $vid; ?>&amp;mode=other&amp;screen=report">
            <?php l('game.report.navi.other'); ?> </a></td></tr>
            <tr><td <?php if ($mode == 'forwarded') echo 'class="selected"'; ?> style="min-width: 80px"><a href="game.php?village=<?php echo $vid; ?>&amp;mode=forwarded&amp;screen=report">
            <?php l('game.report.navi.forwarded'); ?> </a></td></tr>
            <tr><td <?php if ($mode == 'public') echo 'class="selected"'; ?> style="min-width: 80px"><a href="game.php?village=<?php echo $vid; ?>&amp;mode=public&amp;screen=report">
            <?php l('game.report.navi.public'); ?> </a></td></tr>
            <tr><td <?php if ($mode == 'filter') echo 'class="selected"'; ?> style="min-width: 80px"><a href="game.php?village=<?php echo $vid; ?>&amp;mode=filter&amp;screen=report">
            <?php l('game.report.navi.filter'); ?> </a></td></tr>
            <tr><td <?php if ($mode == 'groups') echo 'class="selected"'; ?> style="min-width: 80px"><a href="game.php?village=<?php echo $vid; ?>&amp;mode=groups&amp;screen=report">
            <?php l('game.report.navi.groups'); ?> </a></td></tr>
            </tbody>
            </table>
    </td>
    <td valign="top" width="100%">
        <?php echo $content; ?>
    </td>
    </tr>
    </tbody>
</table>