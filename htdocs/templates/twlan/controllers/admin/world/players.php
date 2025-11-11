<?php
use TWLan\Framework\Text;
?>
<table class="box">
    <tr>
        <td>
            <div class="head">ID</div>
        </td>
        <td>
            <div class="head"><?php l("admin.world.managePlayers.name"); ?></div>
        </td>
        <td>
            <div class="head"><?php l("admin.world.managePlayers.rank"); ?></div>
        </td>
        <td>
            <div class="head"><?php l("admin.world.managePlayers.points"); ?></div>
        </td>
        <td>
            <div class="head"><?php l("admin.world.managePlayers.villages"); ?></div>
        </td>
        <td>
            <div class="head"><?php l("admin.world.managePlayers.ally"); ?></div>
        </td>
        <td>
            <div class="head"><?php l("admin.actions"); ?></div>
        </td>
    </tr>
    <?php foreach($players as $player) { ?>
    <tr>
        <td>
            <?php echo $player->id_user; ?>
        </td>
        <td>
            <strong><?php echo Text::formatAll($player->global->name); ?></strong>
        </td>
        <td>
            <?php echo $player->cached_rank; ?>
        </td>
        <td>
            <?php echo Text::formatInt($player->cached_points, ll('game.thousandDelimeter')); ?>
        </td>
        <td>
            <?php echo Text::formatInt($player->cached_villages, ll('game.thousandDelimeter')); ?>
        </td>
        <td>
            <?php echo Text::formatAll((isset($player->id_ally)) ? $player->ally->tag : "-"); ?>
        </td>
        <td>
            <a href="players/delete?player=<?php echo $player->id_user; ?>"><?php l("admin.delete"); ?></a>
        </td>
    </tr>
    <?php } ?>
</table>
