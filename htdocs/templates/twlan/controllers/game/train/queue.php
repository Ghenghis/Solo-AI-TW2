<?php 
namespace Twlan;
use Twlan\framework\Time;
if(count($events['events']) > 0){?>
<div class="current_prod_wrapper">
    <div id="replace_<?php echo $building->id;?>">
        <table class="vis">
            <tr>
                <?php if ($events['next']) { ?>
                <th width="250"><?php l('game.train.queue.next', array('unit'=>ll('unit'.ucfirst($events['next']['unit']).'.name')));?></th>
                <th><span class="timer"><?php echo $events['next']['time'] <= 0 ? Time::date(microtime(true) + 1) : Time::date($events['next']['time']); ?></span></th>
                <?php } ?>
            </tr>
        </table>
        <div class="trainqueue_wrap" id="trainqueue_wrap_<?php echo $building->id;?>">
            <table class="vis">
                <tbody id="trainqueue_<?php echo $building->id;?>" class="ui-sortable">
                    <tr>
                        <th width="150"><?php l('game.train.queue.train');?></th>
                        <th width="120"><?php l('game.train.queue.duration');?></th>
                        <th width="150"><?php l('game.train.queue.completion');?></th>
                        <th width="100"><?php l('game.train.queue.cancelTitle');?></th>
                        <th style="background:none !important;"></th>
                    </tr>
                    <?php
                    $amount_events = count($events['events']); 
                    $c = 0;
                    foreach($events['events'] as $event){
                    $classes = array();
                    if ($event['active']) $classes[] = "lit-item";
                    $decommissionClass = $event['decommission'] ? " decommission" : "";
                    $classes = implode(" ", $classes);
                    ?>
                    <tr class="<?php echo $event['active'] ? 'lit' : 'sortable_row';?>" <?php if(!$event['active']){echo ' id="trainorder_'.$c++.'"';}?>>
                        <td class="<?php echo $classes.$decommissionClass; ?>">
                            <?php 
                                echo ($event['decommission']?'-':'').$event['amount'].' '.
                                ll('unit'.ucfirst($event['unit']).'.'.($event['amount'] == 1 ? 'name' : 'plural'))
                            ?>
                        </td>
                        <td class="<?php echo $classes; ?>">
                            <?php if($event['active']){?><span class="<?php echo $event['overdue'] ? 'warn">'.ll('game.overdue') : 'timer">'.Time::date($event['time']);?>
                            </span><?php }else{echo Time::date($event['time']);}?>
                        </td>
                        <td class="<?php echo $classes; ?>"><?php echo Time::onTime($event['finish']);?></td>
                        <td class="<?php echo $classes; ?>"><a class="btn btn-cancel" onclick="return TrainOverview.cancelOrder(<?php echo $event['id_event'];?>)" href="game.php?village=<?php echo $vid;?>&amp;screen=<?php echo $screen;?>&amp;action=cancel&amp;id=<?php echo $event['id_event'];?>"><?php l('game.train.queue.cancel');?></a></td>
                        <td class="<?php echo $classes; ?>" style="background:none !important;">
                            <?php if(!$event['active'] && $events['waiting'] >= 2){?>
                            <div style="width: 11px; height:11px; background-image: url(graphic/sorthandle.png); cursor:pointer" class="bqhandle" title="<?php l('game.train.queue.drag');?>"></div>
                            <?php }?>
                        </td>
                    </tr>
                    <?php }if($amount_events >= 2){?>
                    <tr>
                        <td colspan="3">&nbsp;</td>
                        <td class="lit-item"><a class="btn btn-cancel evt-confirm" data-confirm-msg="<?php l('game.train.queue.cancelSure');?>" href="game.php?village=<?php echo $vid;?>&amp;screen=<?php echo $screen;?>&amp;mode=train&amp;action=cancel_all&amp;building=<?php echo $building->id; ?>"><?php l('game.train.queue.cancelAll');?></a>
                        </td>
                        <th style="background:none !important;"></th>
                    </tr>
                    <?php }?>
                </tbody>
            </table>
        </div>
        <div style="font-size: 7pt;"><?php l('game.train.queue.cancelBack');?></div>
        <br>
        <script type="text/javascript">
            //<![CDATA[
            init_trainqueue('<?php echo $building->id;?>', 'game.php?village=<?php echo $vid;?>&screen=<?php echo $screen;?>&ajaxaction=trainorder_reorder');
            //]]>
        </script>
    </div>
</div>
<?php }?>