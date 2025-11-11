<?php
use TWLan\Framework\Text;
use TWLan\Controllers\Game\Ranking;
?>
<?php
$playercount = count($players);
if($top && $offset == 0) { ?>
<div class="ranking-top3">
    <div class="gold">
        <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $players[0]->id_user; ?>&amp;screen=info_player"><?php echo Text::formatAll($players[0]->global->name); ?></a>
    </div>
    <?php if($playercount > 1) { ?>
    <div class="silver">
        <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $players[1]->id_user; ?>&amp;screen=info_player"><?php echo Text::formatAll($players[1]->global->name); ?></a>
    </div>
    <?php } ?>
    <?php if($playercount > 2) { ?>
    <div class="bronze">
        <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $players[2]->id_user; ?>&amp;screen=info_player"><?php echo Text::formatAll($players[2]->global->name); ?></a>
    </div>
    <?php } ?>
</div>
<?php } ?>

<table id="player_ranking_table" class="vis" width="100%">
    <tbody>
        <tr>
            <th width="60"><?php l('game.rank'); ?></th>
            <th width="180"><?php l('game.name'); ?></th>
            <th width="100"><?php l('game.ally.tribe'); ?></th>
            <th width="60"><?php l('game.points'); ?></th>
            <th><?php l('game.villages'); ?></th>
            <th><?php l('game.ranking.pointsPerVillage'); ?></th>
        </tr>
        <?php foreach ($players as $player) { ?>
        <tr <?php if ((isset($rank) && $player->cached_rank == $rank) || (isset($name) && $player->global->name == $name))
                echo 'class="lit"';
        ?>>
            <td class="lit-item"><?php echo $player->cached_rank; ?></td>
            <td class="lit-item nowrap">
                <a class="" href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $player->id_user; ?>&amp;screen=info_player">
                    <?php echo Text::formatAll($player->global->name); ?>
                </a>
            </td>
            <td class="lit-item nowrap">
                <?php if(isset($player->id_ally)) { ?>
                    <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $player->id_ally; ?>&amp;screen=info_ally">
                        <?php echo $player->ally->tag; ?>
                    </a>
                <?php } ?>
            </td>
            <td class="lit-item"><?php echo $player->getPoints(ll('game.thousandDelimeter')); ?></td>
            <td class="lit-item"><?php echo $player->cached_villages; ?></td>
            <td class="lit-item"><?php echo $player->getPointsPerVillage(ll('game.thousandDelimeter')); ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<table class="vis" width="100%">
    <tbody>
        <tr>
            <td style="padding-right:10px">
                <form action="game.php?village=<?php echo $vid; ?>&amp;screen=ranking" method="get">
                    <input type="hidden" name="screen" value="ranking">
                    <input type="hidden" name="mode" value="player"> <?php l('game.rank'); ?>:
                    <input name="rank" type="text" value="" size="6">
                    <input class="btn" type="submit" value="OK">
                </form>
            </td>

            <td style="padding-right:10px">
                <form action="game.php?village=<?php echo $vid; ?>&amp;screen=ranking" method="get">
                    <input type="hidden" name="screen" value="ranking">
                    <input type="hidden" name="mode" value="player"> <?php l('game.search'); ?>:
                    <span role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span>
                    <input name="name" type="text" value="" size="20" class="autocomplete ui-autocomplete-input" data-type="player" autocomplete="off">
                    <input class="btn" type="submit" value="OK">
                </form>
            </td>
        </tr>

    </tbody>
</table>
