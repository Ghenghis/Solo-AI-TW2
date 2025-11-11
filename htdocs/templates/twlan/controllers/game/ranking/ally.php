<?php
use TWLan\Framework\Text;
use TWLan\Controllers\Game\Ranking;
?>
<?php
$allycount = count($allies);
if($offset == 0 && $top) { ?>
<div class="ranking-top3">
    <div class="gold">
        <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $allies[0]->id_ally; ?>&amp;screen=info_ally"><?php echo Text::formatAll($allies[0]->tag); ?></a>
    </div>
    <?php if($allycount > 1) { ?>
    <div class="silver">
        <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $allies[1]->id_ally; ?>&amp;screen=info_ally"><?php echo Text::formatAll($allies[1]->tag); ?></a>
    </div>
    <?php } ?>
    <?php if($allycount > 2) { ?>
    <div class="bronze">
        <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $allies[2]->id_ally; ?>&amp;screen=info_ally"><?php echo Text::formatAll($allies[2]->tag); ?></a>
    </div>
    <?php } ?>
</div>
<?php } ?>

<table id="ally_ranking_table" class="vis" width="100%">
    <tbody>
        <tr>
            <th width="60"><?php echo l("game.rank"); ?></th>
            <th width="60"><?php echo l("game.ally.nameOfTribe"); ?></th>
            <th width="60"><?php echo l("game.totalPoints"); ?></th>
            <th width="100"><?php echo l("game.ally.profile.members"); ?></th>
            <th width="100"><?php echo l("game.ally.profile.pointsPerPlayer"); ?></th>
            <th width="60"><?php echo l("game.villages"); ?></th>
            <th width="100"><?php echo l("game.ranking.pointsPerVillage"); ?></th>
        </tr>
        <?php foreach($allies as $ally) { ?>
        <tr <?php if ((isset($rank) && $ally->cached_rank == $rank) || (isset($name) && $ally->name == $name) || (isset($tag) && $ally->tag == $tag))
                echo 'class="lit"';
        ?>>
            <td class="lit-item"><?php echo $ally->getRank(); ?></td>
            <td class="lit-item nowrap">
                <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $ally->id_ally; ?>&amp;screen=info_ally">
                    <?php echo $ally->tag; ?>
                </a>
            </td>
            <td class="lit-item"><?php echo $ally->getPoints(ll('game.thousandDelimeter')); ?></td>
            <td class="lit-item"><?php echo $ally->countMembers(); ?></td>
            <td class="lit-item"><?php echo $ally->getPointsPerUser(ll('game.thousandDelimeter')); ?></td>
            <td class="lit-item"><?php echo $ally->countVillages(); ?></td>
            <td class="lit-item"><?php echo $ally->getPointsPerVillage(ll('game.thousandDelimeter')); ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
<table class="vis" width="100%">
    <tbody>
        <tr>
            <td style="padding-right:10px">
                <form action="game.php?village=<?php echo $vid; ?>&amp;screen=ranking&amp;mode=ally" method="get">
                    <input type="hidden" name="screen" value="ranking">
                    <input type="hidden" name="mode" value="ally"> <?php echo l("game.rank"); ?>
                    <input name="rank" type="text" value="" size="6">
                    <input class="btn" type="submit" value="OK">
                </form>
            </td>

            <td style="padding-right:3px">
                <form action="game.php?village=<?php echo $vid; ?>&amp;screen=ranking&amp;mode=ally" method="get">
                    <input type="hidden" name="screen" value="ranking">
                    <input type="hidden" name="mode" value="ally"> <?php echo l("game.ally.diplomaty.tag"); ?>
                    <span role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span>
                    <input name="tag" type="text" value="" style="width: 40px" class="autocomplete ui-autocomplete-input" data-type="ally" autocomplete="off">
                    <input class="btn" type="submit" value="OK">
                </form>
            </td>
            <td style="padding-right:3px">
                <form action="game.php?village=<?php echo $vid; ?>&amp;screen=ranking&amp;mode=ally" method="get">
                    <input type="hidden" name="screen" value="ranking">
                    <input type="hidden" name="mode" value="ally"> <?php echo l("game.ally.nameOfTribe"); ?>
                    <input name="name" type="text" value="" size="20">
                    <input class="btn" type="submit" value="OK">
                </form>
            </td>
            </tr>
    </tbody>
</table>
