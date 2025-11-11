<?php 
namespace Twlan;
use Twlan\framework\Time;
if(count($events['events']) > 0){?>
<div class="current_prod_wrapper">
    <div id="replace_<?php echo $building->id;?>">
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
                    <?php $amount_events = count($events['events']); $c = 0;foreach($events['events'] as $event){?>
                    <tr class="<?php echo $event['active'] ? 'lit' : 'sortable_row';?>"<?php if(!$event['active']){echo ' id="trainorder_'.$c++.'"';}?>>
                        <td<?php if($event['active']){?> class="lit-item"<?php }?>><?php echo $event['amount'];?> <?php l('unit'.ucfirst($event['unit']).'.'.($event['amount'] == 1 ? 'name' : 'plural'))?></td>
                        <td<?php if($event['active']){?> class="lit-item"<?php }?>><?php if($event['active']){?><span class="<?php echo $event['overdue'] ? 'warn">'.ll('game.overdue') : 'timer">'.Time::date($event['time']);?></span><?php }else{echo Time::date($event['time']);}?></td>
                        <td<?php if($event['active']){?> class="lit-item"<?php }?>><?php echo Time::onTime($event['finish']);?></td>
                        <td<?php if($event['active']){?> class="lit-item"<?php }?>><a href="game.php?village=<?php echo $vid;?>&amp;screen=<?php echo $screen;?>&amp;action=cancel&amp;id=<?php echo $event['id_event'];?>"><?php l('game.train.queue.cancel');?></a></td>
                        <td<?php if($event['active']){?> class="lit-item"<?php }?> style="background:none !important;">
                        </td>
                    </tr>
                    <?php } ?>
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