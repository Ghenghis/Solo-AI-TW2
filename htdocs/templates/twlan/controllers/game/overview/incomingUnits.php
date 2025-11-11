<?php 
namespace Twlan;
use Twlan\framework\Time;
$armies = $incomingArmies;
if (count($armies) > 0) 
{
?>
<table class="vis" style="width:100%">
    <tr>
        <th width="52%"><?php l('buildingPlace.foreignCommands'); ?> (<span class="command-list-count"><?php echo count($armies); ?></span>)</th>
        <th width="33%"><?php l('buildingPlace.arrival'); ?></th>
        <th width="15%"><?php l('buildingPlace.arrivalIn'); ?></th>
    </tr>
    <?php foreach($armies as $_army) { $_displayDesc = ll('unitMovement.'.$_army->getType(), array(
        'from' => $_army->getFromVillage()->getDisplayName(),
        'to' => $_army->getToVillage()->getDisplayName()), true); ?>
    <tr>
        <td>
            <img src="graphic/command/<?php echo $_army->getType(); ?>.png" alt="" />
            <span class="quickedit-in" data-id="<?php echo $_army->id_event; ?>" data-ignore-icons="1">
                <span class="quickedit-content">
                    <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $_army->id_event; ?>&amp;type=other&amp;screen=info_command">
                        <span class="quickedit-label">
                             <?php echo $_displayDesc; ?>
                        </span>
                    </a>

                    <a class="rename-icon" href="#" title="<?php l('game.ally.forum.admin.rename'); ?>"></a>
                </span>
            </span>
        </td>
        <td>
            <?php echo Time::onTime($_army->event->finish); ?>
        </td>
        <td>
            <span class="timer"><?php echo Time::countDown($_army->event->finish); ?></span>
        </td>
    </tr>
    <?php } ?>
</table>
<script>
    $(function(){
        $('.quickedit-in').QuickEdit( { show_icons: false, url: TribalWars.buildURL('POST', 'info_command', { ajaxaction: 'edit_own_comment', id: '__ID__' } ) } );
        Command.init();
    });
</script>
<?php } ?>
