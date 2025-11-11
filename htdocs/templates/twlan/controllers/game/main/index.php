<?php
namespace Twlan;
use Twlan\framework\Text;
$relPath = framework\Router::getRelativePath(); ?>
<script type="text/javascript">
//<![CDATA[
    $(document).ready(function() {
        BuildingMain.upgrade_building_link = '<?php echo $relPath; ?>?village=<?php echo $vid;?>&screen=main&mode=<?php echo $mode;?>&ajaxaction=upgrade_building';
        BuildingMain.downgrade_building_link = '<?php echo $relPath; ?>?village=<?php echo $vid;?>&screen=main&mode=<?php echo $mode;?>&ajaxaction=downgrade_building';
        BuildingMain.confirm_queue = false;
        BuildingMain.mode = <?php echo $_mode;?>;
        BuildingMain.link_cancel = '<?php echo $relPath; ?>?village=<?php echo $vid;?>&screen=main&mode=<?php echo $mode;?>&ajaxaction=cancel_order';
        BuildingMain.link_change_order = '';
        BuildingMain.buildings = <?php echo json_encode($buildingData); ?>;
        $('.inactive img').fadeTo(0, .5);
    });
    $(document).ready(BuildingMain.init);
//]]>
</script>
<table style="width:100%">
    <tr>
        <td>
            <?php require 'queue.php';require $mode.'.php';?>
        </td>
    </tr>
</table>
<form action="game.php?village=<?php echo $vid;?>&amp;screen=main&amp;mode=<?php echo $mode;?>&amp;action=change_name" method="post">
    <table  class="vis" style="margin-left:5px">
        <tr>
            <th colspan="3"><?php echo l('buildingMain.changeName');?></th>
        </tr>
        <tr>
            <td>
                <input type="text" name="name" value="<?php echo Text::formatAll($village->name);?>" maxlength="32" size="32" />
            </td>
            <td>
                <input type="submit" class="btn" value="<?php echo l('buildingMain.change');?>" />
            </td>
        </tr>
    </table>
</form>