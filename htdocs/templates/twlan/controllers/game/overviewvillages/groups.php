<?php
namespace Twlan;
use Twlan\framework\Text;
?>
<hr size="3"/>
<div id="group_config" class="group_config" style="margin: 10px 0">
    <form id="add_group_form" action="game.php?village=<?php echo $vid; ?>&amp;ajaxaction=create_group&amp;screen=groups" method="post">
        <input type="hidden" name="mode" value="<?php echo $mode; ?>">
        <?php l('game.groups.create');?>:<input name="group_name" id="add_new_group_name"> <input class="btn" type="submit" value="<?php l('game.ok');?>">
    </form>


    <input type="hidden" value="<?php l('game.groups.noun'); ?>" id="group_config_headline">
    <input type="hidden" value="<?php l('game.ok'); ?>" id="group_submit_text">
    <input type="hidden" value="<?php l('game.groups.rename'); ?>" id="group_title_rename">
    <input type="hidden" value="<?php l('game.groups.delete'); ?>" id="group_title_delete">
    <input type="hidden" value="Are you sure you want to delete group &quot;%1&quot;?" id="group_msg_confirm_delete">
    <input type="hidden" value="game.php?village=<?php echo $vid; ?>&amp;mode=groups&amp;type=static&amp;group=0&amp;partial=1&amp;screen=overview_villages" id="start_edit_group_link">
    <input type="hidden" value="game.php?village=<?php echo $vid; ?>&amp;ajaxaction=delete_group&amp;h=fa5a&amp;screen=groups" id="delete_group_link">
    <input type="hidden" value="game.php?village=<?php echo $vid; ?>&amp;ajaxaction=rename_group&amp;h=fa5a&amp;screen=groups" id="rename_group_link">

    <div id="error_div"></div>
    <div id="group_list">
    </div>

    <script type="text/javascript">
    $(function() {
        VillageGroups.displayGroupInfo(<?php echo json_encode($this->user->getGroupsJSON($vid)); ?>, 'group_list');
    });
    </script>
</div>

<!-- The following line is more important than you can imagine! -->
<input type="hidden" id="group_assign_action" value="game.php?village=<?php echo $vid;?>&amp;screen=groups&amp;ajaxaction=village" />
<form action="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=``bt`a&amp;action=bulk_edit_villages" method="post">
    <table class="vis overview_table" width="100%" id="group_assign_table">
        <tr>
            <th width="280">
                <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=groups&amp;order=village&amp;dir=desc">Dorf</a>
                (<?php echo count($villages);?>)
            </th>
            <th>
                <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=groups&amp;order=count&amp;dir=asc"><?php l('game.overviews.amount');?></a>
            </th>
            <th>
                <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=groups&amp;order=points&amp;dir=asc"><?php l('game.points');?></a>
            </th>
            <th>
                <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=groups&amp;order=pop&amp;dir=asc"><?php l('buildingFarm.name');?></a>
            </th>
            <th>
                <?php l('game.menu.overview.groups');?>
            </th>
            <th width="100">
                <?php l('game.edit');?>
            </th>
        </tr>
        <?php foreach($villages as $village) { $cid = $village->id_village; ?>
        <tr class="row_a">
            <td>
                <span id="label_<?php echo $cid;?>">
                    <a href="game.php?village=<?php echo $cid;?>&amp;screen=overview">
                        <span id="label_text_<?php echo $cid;?>"><?php echo Text::formatAll($village->name); ?></span>
                    </a>
                    <a class="rename-icon" href="javascript:editToggle('label_<?php echo $cid;?>', 'edit_<?php echo $cid;?>')">&nbsp;</a>
                </span>
                <span id="edit_<?php echo $cid;?>" style="display:none">
                    <input id="edit_input_<?php echo $cid;?>" value="<?php echo Text::formatAll($village->name); ?>" onkeydown="if (event.keyCode == 13) { $(this).next().click(); return false; }" />
                    <input type="button" value="OK" onclick="editSubmitNew('label_<?php echo $cid;?>', 'label_text_<?php echo $cid;?>', 'edit_<?php echo $cid;?>', 'edit_input_<?php echo $cid;?>', 'game.php?village=<?php echo $cid;?>&amp;screen=main&amp;ajaxaction=change_name&amp;');" />
                </span>
            </td>
            <?php $groups = $village->getGroups(false); ?>
            <td id="assigned_groups_<?php echo $cid;?>_count"><?php echo count($groups); ?></td>
            <td id="assigned_groups_<?php echo $cid;?>_points">
                <?php echo Text::formatInt($village->getPoints(), ll("game.thousandDelimeter")); ?>
            </td>
            <td id="assigned_groups_<?php echo $cid;?>_pop"><?php echo $village->getPopulation()."/".$village->getMaxRes("population"); ?></td>
            <td id="assigned_groups_<?php echo $cid;?>_names">
                <?php
                $c = 0;
                foreach($groups as $group)
                {
                    if ($c > 0) echo ',';
                    echo Text::formatAll($group->group->name);
                    ++$c;
                }
                $assigned = $c > 0;
                if(!$assigned) { ?>
                    <span class="grey" style="font-style:italic;">
                        <?php l('game.groups.nogroups');?>
                    </span>
                <?php } ?>
            </td>
            <td><?php if($assigned) {?>
                <a onclick="toggle_element('#group_edit_tr_<?php echo $cid;?>');ajaxJSONRequest('groups.php?&amp;ajax=load_groups&amp;mode=village&amp;village_id=<?php echo $cid;?>', 'group_edit_div_<?php echo $cid;?>', true, Callback.handle_village_to_group_from_overview); return false;" href="#">» bearbeiten</a>
            <?php }?>
            </td>
        </tr>
        <tr id="group_edit_tr_<?php echo $cid;?>" class="nohover" style="display: none;">
            <td>
                <div class="group_edit" id="group_edit_div_<?php echo $cid;?>"></div>
            </td>
        </tr>
        <?php }?>
    </table>
</form>
<p>Sortierung: Dorf aufsteigend</p>
<p><small>Klicke auf "Dorf", "Anzahl" oder "Punkte", um die Sortierung zu ändern.</small></p>
