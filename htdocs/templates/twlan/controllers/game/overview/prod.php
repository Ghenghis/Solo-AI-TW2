<?php
namespace Twlan;
use Twlan\framework\Text;
?>
<table width="100%">
    <?php foreach($this->world->getPhysicalResources() as $res){?>
    <tr class="nowrap">
        <td width="70">
            <span class="icon header <?php echo $res;?>"></span>
            <?php l('game.'.$res);?>
        </td>
        <td>
            <?php
            $prod = $village->getResProduction($res);
            if($prod >= $this->world->getConfig('world.prodToSeconds'))
            {
                $timeUnit = 'second';
                $prod /= 3600;
            }
            elseif($prod >= $this->world->getConfig('world.prodToMinutes'))
            {
                $timeUnit = 'minute';
                $prod /= 60;
            }
            else
            {
                $timeUnit = 'hour';
            }
            l('game.overview.prod'.ucfirst($timeUnit), ['x'=>'<strong>'.Text::formatInt(round($prod), ll('game.thousandDelimeter')).'</strong>']);
            ?>
        </td>
    </tr>
    <?php }?>
</table>
