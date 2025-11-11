<?php
namespace Twlan;
use Twlan\framework\Time;
?>
<?php if(count($events['events']) > 0){?>
<div id="current_research">
    <table id="build_queue" class="vis">
        <thead>
            <tr>
                <th width="250"><?php l('buildingSmith.eventResearch'); ?></th>
                <th width="100"><?php l('buildingSmith.eventTime'); ?></th>
                <th width="150"><?php l('buildingSmith.eventFinish'); ?></th>
                <th><?php l('buildingSmith.eventCancel'); ?></th>
                <th style="background:none !important;"></th>
            </tr>
            <tbody>
            <?php $c = 0;foreach($events['events'] as $event){?>
            <tr class="<?php echo $event['active'] ? 'lit nodrag' : 'sortable_row nowrap';?>"<?php if(!$event['active']){echo ' id="buildorder_'.$c++.'"';}?>>
                <td width="250" class="nowrap lit-item">
                    <?php l('unit'.ucfirst($event['technology']).'.name');?> (<?php l('building.level', array('x'=>$event['level'])); ?>)</td>
                <td width="100" class="nowrap lit-item">
                    <?php if($event['overdue']){?>
                    <span class="warn"><?php l('game.overdue'); ?></span>
                    <?php }else{?>
                    <span<?php if($event['active']){echo ' class="timer"';}?>><?php echo Time::date($event['time']); ?></span>
                    <?php }?>
                </td>
                <td class="lit-item" width="170"><?php echo Time::onTime($event['finish']); ?></td>
                <td class="lit-item">
                    <?php if($event['overdue']){?>
                    <span class="inactive"><?php l('buildingMain.tooLate');?></span>
                    <?php }else{?>
                    <a class="btn btn-cancel" href="#" onclick="return BuildingSmith.cancel(<?php echo $event['id_event'];?>);"><?php l('buildingMain.cancel');?></a>
                    <?php }?>
                </td>
                <td class="lit-item" style="background-color: transparent !important;">
                    <?php if(!$event['active'] && $events['waiting'] >= 2){?>
                    <div style="width: 11px; height:11px; background-image: url(graphic/sorthandle.png); cursor:pointer" class="bqhandle" title="<?php l('buildingMain.changeOrder');?>"></div>
                    <?php }?>
                </td>
            </tr>
            <?php } ?>
            </tbody>
            <tbody id="techqueue_smithy">
            </tbody>
    </table>
    <br />
    <script type="text/javascript">
        //<![CDATA[
        init_techqueue('<?php echo $building->id; ?>', 'game.php?village=<?php echo $vid;?>&screen=smith&ajaxaction=buildorder_reorder');
        //]]>
    </script>
</div>
<?php }?>