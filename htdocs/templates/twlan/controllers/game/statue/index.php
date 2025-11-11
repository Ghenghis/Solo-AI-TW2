<?php 
namespace Twlan;
use Twlan\framework\Time;
?>
<script type="text/javascript">
//<![CDATA[
function confirmEquip(link, question) {
    var callback = function() {
        document.location.replace(link);
    }
    var buttons = [{ text : "Confirm", callback : callback, confirm : true }];
    UI.ConfirmationBox(question, buttons);
    return false;
}

$(document).ready(function() {
    UI.ToolTip($('.tooltip_item'));
});
//]]>
</script>
<?php if($mode == 'inventory') { ?>
<div style="width:840px;float:left;">
	<div style="float:right;width:210px;padding-right:5px;">
		<p><?php l('buildingStatue.inventoryDes'); ?></p>
	</div>
	<div style="float:left;position:relative;z-index:9996;width:605px;padding-left:2px;">
		<div style="width:600px;height:430px;padding:0;margin-right:10px;z-index:9997">
            <?php foreach($knight_items as $itemname => $knight_item) { if(!isset($items_data[$knight_item['unit']])) continue; ?>
			<img src="graphic/inventory/<?php echo $itemname; ?>.png" class="inv_map inv_<?php echo $itemname; ?>" alt=""/>
            <?php } ?>
            
			<img src="graphic/map/empty.png" alt="" title="" class="inv_empty" usemap="#inv" />
			<map id="inv" name="inv">
			<?php foreach($knight_items as $itemname => $knight_item) { if(!isset($items_data[$knight_item['unit']])) continue;?>
				<area shape="poly" id="item_<?php echo $itemname; ?>"
                coords="<?php echo $knight_item['poly']; ?>"
                href="game.php?village=<?php echo $vid; ?>&amp;screen=statue&amp;mode=inventory&amp;action=equip&amp;item=<?php echo $itemname; ?>" 
                class="tooltip_item" 
                title="<?php echo str_replace("'", '&rsquo;', ll("knight.".$itemname)); ?> :: <?php echo str_replace("'", '&rsquo;', $items_des[$itemname]); ?>" 
                onclick="return confirmEquip('game.php?village=<?php echo $vid; ?>&amp;mode=inventory&amp;action=equip&amp;item=<?php echo $itemname; ?>&amp;screen=statue', '<?php l('knight.equipItem', array('item' => $itemname)); ?>?');"
                />
			<?php } ?>
			</map>
			<img src="graphic/inventory/inventory.jpg" alt="" title="" />
		</div>
	</div>
	</div>
<br style="clear:both" />
<table class="vis" style="width: 605px; padding:0;margin:0;">
    <tbody>
        <?php if(count($knight_items) == $items_found) { ?>
            <th><?php l('buildingStatue.allItemsFound'); ?></th>
        <?php } else { ?>
            <th colspan="3"><?php l('buildingStatue.findingProgress'); ?></th>
        <?php } ?>
    <tr>
        <td>
             <div class="progress-bar">
                <span class="label"><?php echo $find_progress; ?>%</span>
                <div style="width:<?php echo $find_progress; ?>%"></div>
            </div>
        </td>
    </tr>
</tbody></table>
    <?php } else { ?>
    
 <?php /* QUEUE BEGIN */ ?>
    <?php if(isset($events) && count($events['events']) > 0) { ?>
    <div class="current_prod_wrapper">
        <div id="replace_<?php echo $building->id;?>">
            <table class="vis">
                <tr>
                    <th width="250"><?php l('game.train.queue.next', array('unit'=>ll('unit'.ucfirst($events['next']['unit']).'.name')));?></th>
                    <th><span class="<?php echo ($events['next']['time'] <= 0) ? 'warn">'.ll('game.overdue') : 'timer">'.Time::date($events['next']['time']);?></span></th>
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
                        <?php $amount_events = count($events['events']); $c = 0;foreach($events['events'] as $event){ ?>
                        <tr class="<?php echo $event['active'] ? 'lit' : 'sortable_row';?>"<?php if(!$event['active']){echo ' id="trainorder_'.$c++.'"';}?>>
                            <td<?php if($event['active']){?> class="lit-item"<?php }?>><?php echo $event['amount'];?> <?php l('unit'.ucfirst($event['unit']).'.'.($event['amount'] == 1 ? 'name' : 'plural'))?></td>
                            <td<?php if($event['active']){?> class="lit-item"<?php }?>><?php if($event['active']){?><span class="<?php echo $event['overdue'] ? 'warn">'.ll('game.overdue') : 'timer">'.Time::date($event['time']);?></span><?php }else{echo Time::date($event['time']);}?></td>
                            <td<?php if($event['active']){?> class="lit-item"<?php }?>><?php echo Time::onTime($event['finish']);?></td>
                            <td<?php if($event['active']){?> class="lit-item"<?php }?>><a class="btn btn-cancel" onclick="return TrainOverview.cancelOrder(<?php echo $event['id_event'];?>)" href="game.php?village=<?php echo $vid;?>&amp;screen=<?php echo $screen;?>&amp;action=cancel&amp;id=<?php echo $event['id_event'];?>"><?php l('game.train.queue.cancel');?></a></td>
                            <td<?php if($event['active']){?> class="lit-item"<?php }?> style="background:none !important;"></td>
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
    <?php } $this->viewPartial('../train/train_js'); ?>
<?php /* QUEUE END */ ?>
    <input type="hidden" id="knight_0" value="1">
	<?php if(!empty($error)) { ?>
		<font class="error"><?php echo $error; ?></font>
	<?php } ?>
	<?php if(!$pala_exists) { ?>
	<form action="game.php?village=<?php echo $vid; ?>&amp;screen=statue&amp;action=train&amp" method="post" onsubmit="this.submit.disabled=true;">
		<table class="vis">
			<tr>
				<th width="150"><?php l('buildingStatue.unit'); ?></th>
				<th colspan="4" width="120"><?php l('buildingStatue.need'); ?></th>
				<th width="130"><?php l('buildingStatue.time'); ?> (hh:mm:ss)</th>
				<th><?php l('buildingStatue.amount'); ?></th>
				<th><?php l('buildingStatue.doRecruit');?></th>
			</tr>

            <?php foreach($units as $unit_dbname => $obj) { $item = $obj->id; ?>
				<tr>
					<td>
                        <a href="javascript:popup('popup_unit.php?unit=<?php echo $unit_dbname; ?>', 520, 520)"> 
                            <img src="graphic/unit/unit_<?php echo $unit_dbname; ?>.png" alt="" /><?php echo $knightname; ?>
                        </a>
                    </td>
                    <?php foreach(array_merge($this->world->getPhysicalResources(), array('population')) as $res) { ?>
					<td>
                        <img src="graphic/<?php echo $res == 'population' ? 'face' : $res; ?>.png" title="<?php l('game.'.$res); ?>" alt="" />
                        <?php echo $obj->getRecruitCost($res); ?>
                    </td>
                    <?php } ?>
					<td><?php echo Time::date($obj->getRecruitCost('time')); ?></td>
                    <?php 
                        $available = $this->village->getOwnArmy()->getUnits();
                        $available = isset($available[$obj->id]) ? $available[$obj->id] : 0;
                        $all_count = $this->village->getAggregatedArmy()->getUnits();
                        $all_count = isset($all_count[$obj->id]) ? $all_count[$obj->id] : 0;
                    ?>
					<td><?php echo $available.'/'.$all_count; ?></td>

                    <?php if(isset($error_recruit)) { ?>
                        <td class="inactive"><?php echo $error_recruit; ?></td>
                    <?php } else { ?>
						<td>
                            <a href="game.php?village=<?php echo $vid; ?>&amp;screen=statue&amp;action=train&amp;"><?php l('buildingStatue.create'); ?></a>
                        </td>
					<?php } ?>
				</tr>
			<?php } ?>
		</table>
		</form>
	<?php } else { ?>
	<?php if($pala_image) { ?>
	<table>
        <tr><td>
        <img src="graphic/inventory/paladin_<?php echo $pala_item; ?>.jpg" alt="" />
        </td>
        <td style="vertical-align: top;">
                <h3><?php l('knight.'.$pala_item); ?></h3>
                <p><?php echo $pala_item_desc; ?></p>
                <br />
                <?php } ?>
                
                <h3 style="margin-top: 4px;"><?php echo $knightname; ?></h3>
                <?php if(!empty($pala_doing)) { ?>
                <table class="vis">
                    <tr>
                        <th><?php echo $pala_doing; ?></th>
                    </tr>
                    <?php if($pala_moveable) { ?>
                    <tr><td><a href="game.php?village=<?php echo $vid; ?>&amp;screen=statue&amp;action=deploy"><?php l('buildingStatue.palaMove'); ?></a></td></tr>
                    <?php } ?>
                    <tr>
                    </tr>
                </table>
                <br />
                <?php } ?>
            <form action="game.php?village=<?php echo $vid; ?>&amp;screen=statue&amp;action=knights_name" method="POST">
            <table class="vis">
                <tr>
                    <td>
                        <?php l('buildingStatue.palaName'); ?>: <input type="text" name="knights_name" value="<?php echo $knightname; ?>" /> 
                        <input type="submit" value="<?php l('buildingStatue.palaDoRename'); ?>" />
                    </td>
                </tr>
            </table>
            </form>
    <?php if($pala_image) { ?>
        </td></tr>
	</table>
    <?php } ?>
<?php } ?>
<?php } ?>