<?php
namespace Twlan;
use Twlan\framework\Time;
?>
<?php if(count($events) > 0){?>
<div id="buildqueue_wrap">
    <table id="build_queue" class="vis">
        <tbody id="buildqueue">
            <tr>
                <th width="250"><?php echo l('buildingMain.eventBuilding');?></th>
                <th width="100"><?php echo l('buildingMain.eventTime');?></th>
                <th width="150"><?php echo l('buildingMain.eventFinish');?></th>
                <th><?php echo l('buildingMain.eventCancel');?></th>
                <th style="background:none !important;"></th>
            </tr>
            <?php $c = 0;foreach($events as $event){ ?>
            <tr class="<?php echo $event['active'] ? 'lit nodrag' : 'sortable_row nowrap';?>"<?php if(!$event['active']){echo ' id="buildorder_'.$c++.'"';}?>>
                <td width="250" class="nowrap lit-item">
                    <?php $building = $this->world->buildings->get($event['building']); ?>
                    <img src="graphic/buildings/mid/<?php 
                        echo $building->getImage($event['level'] ?: $building->getLevel($village)); ?>" 
                        title="" alt="" class="bmain_list_img">

                    <?php echo $building->getLocalizedId(); ?> 
                    <br> 
                    <?php 
                        echo $event['level'] ? ll('building.level', array('x'=>$event['level'])) : ll('buildingMain.levelDestroy');
                    ?>
                </td>
                <td width="100" class="nowrap lit-item">
                    <span<?php if($event['active']){echo ' class="timer"';}?>><?php echo Time::date($event['time']); ?></span>
                </td>
                <td class="lit-item" width="170"><?php echo Time::onTime($event['finish']); ?></td>
                <td class="lit-item">
                    <?php if($event['overdue']){?>
                    <span class="inactive"><?php echo l('buildingMain.tooLate');?></span>
                    <?php }else{?>
                    <a class="btn btn-cancel" href="game.php?village=<?php echo $vid;?>&amp;screen=main&amp;mode=build&amp;action=cancel&amp;id=<?php echo $event['id_event'];?>" onclick="return BuildingMain.cancel(<?php echo $event['id_event'];?>);"><?php echo l('buildingMain.cancel');?></a>
                    <?php }?>
                </td>
                <td class="lit-item" style="background-color: transparent !important;">
                    <?php if(!$event['active'] && $waiting_events >= 2){?>
                    <div style="width: 11px; height:11px; background-image: url(graphic/sorthandle.png); cursor:pointer" class="bqhandle" title="<?php echo l('buildingMain.changeOrder');?>"></div>
                    <?php }?>
                </td>
            </tr>
            <?php if ($event['active']) { ?>
            <tr class="lit">
                <td colspan="5" style="padding: 0">
                    <?php $leftTime = $event['finish'] - $event['start']; ?>
                    <div class="order-progress" data-progress="{&quot;progress&quot;:[],&quot;slot_time&quot;:<?php echo $leftTime; 
                        ?>,&quot;slot_elapsed&quot;:<?php echo -1*Time::countDown($event['start'], false); ?>,&quot;percentage_complete&quot;:&quot;0&quot;}">
                    </div>
                </td>
            </tr>
            <?php } ?>
            <?php }if($queueFactor > 1){?>
            <tr class="nodrag">
                <td colspan="4">
                    <?php echo l('buildingMain.queueInfo', array('x'=>'<b>'.round(($queueFactor - 1) * 100).'%</b>'));?><br />
                    <small><?php echo l('buildingMain.queueText');?></small>
               </td>
            </tr>
            <?php }?>
        </tbody>
    </table>
    <br />
    <script type="text/javascript">
        //<![CDATA[
        BuildingMain.init_buildqueue('game.php?village=<?php echo $vid;?>&screen=main&mode=<?php echo $mode;?>&ajaxaction=buildorder_reorder');
        //]]>
    </script>
</div>
<?php }?>