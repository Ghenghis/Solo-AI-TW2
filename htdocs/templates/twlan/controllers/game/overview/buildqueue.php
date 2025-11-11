<?php
namespace Twlan;
use Twlan\framework\Time;
?>
<table width="100%" id="overview_buildqueue" class="vis">
    <?php foreach($events as $event){ $building = $this->world->buildings->get($event['building']); ?>
    <tr class="queueRow" style="height:50px">
        <td width="40px" align="center">
            <img width="40" src="graphic/big_buildings/<?php echo $building->getImage($event['level'] == 0 ? $village->getBuilding($event['building']) : $event['level']);?>" 
            alt="<?php echo $building->getLocalizedId(); ?>" />
        </td>
        <td>
            <?php echo $building->getLocalizedId(); ?><br/>
            <span class="<?php echo ($event['time'] <= 0) ? 'warn ' : (($event['active']) ? 'timer ' : '');?>small">
            <?php echo $event['time'] <= 0 ? l('game.overdue') : Time::date($event['time']);?></span>
        </td>
        <td align="center">
            <?php if(!$event['overdue']){?>
            <a class="cancel-icon solo evt-confirm" data-confirm-msg="<?php l('game.overview.cancelBuild'); ?>" 
            href="game.php?village=<?php echo $vid;?>&amp;screen=overview&amp;action=cancelBuild&amp;id=<?php echo $event['id_event'];?>"></a>
            <?php } ?>
            &nbsp;&nbsp;&nbsp;
        </td>
    </tr>
    <?php }?>
</table>