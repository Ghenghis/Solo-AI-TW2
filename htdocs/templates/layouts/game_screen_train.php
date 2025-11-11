<?php 
namespace TWLan; ?>                
<h2><?php l('game.train.modeTrain'); ?></h2>
<p><?php l('game.train.desc'); ?></p>

<table class="vis">
    <tbody>
        <tr>
            <td <?php if($mode == 'train') { ?>class="selected"<?php } ?>>
                <a href="game.php?village=<?php echo $vid; ?>&amp;mode=train&amp;screen=train"><?php l('game.train.modeTrain'); ?></a>
            </td>
            <td <?php if($mode == 'decommission') { ?>class="selected"<?php } ?>>
                <a href="game.php?village=<?php echo $vid; ?>&amp;mode=decommission&amp;screen=train"><?php l('game.train.modeDecommission'); ?></a>
            </td>
            <td <?php if($mode == 'mass') { ?>class="selected"<?php } ?>>
                <a href="game.php?village=<?php echo $vid; ?>&amp;mode=mass&amp;screen=train"><?php l('game.train.modeMassrecruitment'); ?></a>
            </td>
            <td <?php if($mode == 'mass_decommission') { ?>class="selected"<?php } ?>>
                <a href="game.php?village=<?php echo $vid; ?>&amp;mode=mass_decommission&amp;screen=train"><?php l('game.train.modeMassdecomission') ?></a>
            </td>
        </tr>
    </tbody>
</table>
<?php echo $content; ?>