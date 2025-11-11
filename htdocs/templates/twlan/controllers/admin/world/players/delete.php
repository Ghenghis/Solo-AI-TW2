<?php
use TWLan\Framework\Text;
?>
<form action="" method="POST">
    <table class="box">
        <?php if(isset($error)) { ?>
        <tr>
            <td colspan="100%">
                <?php echo $error; ?>
            </td>
        </tr>
        <?php } else { ?>
        <tr>
            <td colspan="100%">
                <div class="head"><?php l('admin.world.managePlayers.deletePlayer'); ?>: <?php echo Text::formatAll($player->global->name); ?></div>
            </td>
        </tr>
        <tr>
            <td colspan="100%">
                <div class="head"><?php l('admin.world.managePlayers.villageHandleQuestion'); ?></div>
            </td>
        </tr>
        <tr>
            <td><input type="radio" id="input_villagehandle_delete" name="villagehandle" value="delete" checked="checked"></td>
            <td align="left"><label for="input_villagehandle_delete"><?php l('admin.delete'); ?></label></td>
        </tr>
        <tr>
            <td><input type="radio" id="input_villagehandle_move" name="villagehandle" value="move"></td>
            <td align="left"><label for="input_villagehandle_move"><?php l('admin.world.managePlayers.transferToPlayer'); ?>:</label></td>
        </tr>
        <tr>
            <td></td>
            <td align="left"><input type="text" id="input_move_user" name="move_user"></td>
        </tr>
        <tr>
            <td><input type="radio" id="input_villagehandle_barbarian" name="villagehandle" value="barbarian"></td>
            <td align="left"><label for="input_villagehandle_barbarian"><?php l('admin.world.managePlayers.makeBarbarians'); ?></label></td>
        </tr>
        <tr>
            <td colspan="100%">
                <input type="submit" name="delete_player" value="<?php l('admin.world.managePlayers.deletePlayer'); ?>">
            </td>
        </tr>
        <?php } ?>
        <tr>
            <td colspan="100%">
                <a class="button" href="/admin/w/<?php echo $worldId; ?>/players"><?php l('admin.cancel'); ?></a>
            </td>
        </tr>
    </table>
</form>
