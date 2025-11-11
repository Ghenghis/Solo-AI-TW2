<?php 
namespace TWLan;
use TWLan\Framework\Router;
?>
<input type="hidden" value="<?php l('game.groups.applyGroups'); ?>" id="group_submit_text" />
<input type="hidden" value="<?php l('game.groups.assignedGroups'); ?>" id="group_headline" />
<input type="hidden" value="<?php echo Router::getRelativePath(array('village')).'screen=groups&ajaxaction=village'; ?>" id="group_assign_action" />
<input type="hidden" value="» <?php l('game.edit'); ?>" id="group_edit_village" />
<input type="hidden" value="game.php?village=<?php echo $vid;?>&amp;screen=groups&amp;mode=village&amp;ajax=load_groups&amp;village_id=<?php echo $vid; ?>" id="group_edit_reload" />
<div id="error_div"></div>
<div id="group_assignment"></div>
<script type="text/javascript">
    //<![CDATA[
    VillageGroups.showGroups(
        <?php echo json_encode($this->village->getGroupsJSON()); ?>,
        'group_assignment', false, function() { 
            /*VillageOverview.refreshAMSettingsWidget()*/ 
        }
    );
    //]]>
</script>