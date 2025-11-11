<?php
namespace Twlan;
use Twlan\framework\Text;
?>
<div class="vis_item" style="text-align: center">
    <?php
    foreach($user->getGroups() as $item) {
        $type = $group == $item->id_group ? 'strong' : 'a';
        $bracket = $type == 'strong' ? array('&gt;', '&lt;') : array('[', ']');
        echo '<'.$type.' class="group_tooltip"';
        if($type == 'a') echo ' href="game.php?village='.$vid.'&amp;mode=coin&amp;group='.$item->id_group.'&amp;screen=snob"';
        echo '>'.$bracket[0].Text::formatAll($item->name).$bracket[1].'</'.$type.'>';
    }
    ?>

    <?php if(isset($group)) { ?>
    <a class="group_tooltip" href="game.php?village=<?php echo $vid; ?>&amp;mode=coin&amp;group=0&amp;screen=snob">[<?php l('game.groups.all'); ?>]</a>
    <?php } else { ?>
    <strong class="group_tooltip">&gt;<?php l('game.groups.all'); ?>&lt;</strong>
    <?php } ?>
</div>

<table class="vis" width="100%">
    <tbody>
        <tr>
            <td align="center" colspan="2">
                <?php for($c = 1; $c <= $pages; ++$c) { $value = ($c - 1) * $pagination; ?>
                    <?php if($value == $from) { ?>
                        <strong>&gt;<?php echo $c; ?>&lt;</strong>
                    <?php } else { ?>
                        <a class="paged-nav-item" href="game.php?village=<?php echo $vid; ?>&amp;mode=coin&amp;from=<?php echo $value; ?>&amp;screen=snob">[<?php echo $c; ?>]</a>
                    <?php }
                }
                if($from >= 0) { ?>
                    <a class="paged-nav-item" href="game.php?village=<?php echo $vid; ?>&amp;mode=coin&amp;from=-1&amp;screen=snob">[<?php l('game.groups.all'); ?>]</a>
                <?php } else { ?>
                    <strong>&gt;<?php l('game.groups.all'); ?>&lt;</strong>
                <?php } ?>
            </td>
        </tr>
    </tbody>
</table>

<table class="vis overview_table" id="coin_overview_table" width="100%">
    <tbody>
        <tr>
            <td colspan="3">
                <input class="mint_multi_button btn" type="button" value="<?php l('buildingSnob.createCoins'); ?>">
                (<span id="selectedBunches_top">0</span> x <img alt="" class="" src="graphic/gold.png" title="">)
            </td>
            <td>
                <select class="coin_amount" name="coin_amount">
                <?php for($c = $gm_single_max; $c >= ($gm_single_max * -1) + ($gm_single_max == 0 ? 0 : 1); --$c) {
                    if($c >= 0) { ?>
                    <option value="<?php echo $c; ?>">
                        <?php echo $c; ?>x
                    </option>
                    <?php } else { ?>
                    <option value="<?php echo $c; ?>">
                        Max <?php echo $c; ?>x
                    </option>
                <?php } } ?>
            </select>
            <input class="btn" id="select_anchor_top" type="button" value="<?php l('game.chose'); ?>"></td>
        </tr>

        <tr>
            <th><?php l('game.title.village'); ?></th>
            <th><?php l('game.resources'); ?></th>
            <th><?php l('buildingStorage.name'); ?></th>
            <th><?php l('buildingSnob.choseAmount'); ?></th>
        </tr>

        <?php foreach($villages as $vill) { ?>
        <tr <?php if($vid == $vill['id']) echo 'class="selected"'; ?> id="village_<?php echo $vill['id']; ?>">
            <td><a href="game.php?village=<?php echo $vill['id']; ?>&amp;screen=snob"><?php echo Text::formatAll($vill['obj']->name); ?></a></td>

            <td class="nowrap resources">
                <?php foreach($vill['res'] as $n => $r) { ?>
                <span class="res <?php echo $n; ?>"><?php echo Text::formatInt(floor($r), '<span class="grey">.</span>'); ?></span>
                <?php } ?>
            </td>

            <td><?php echo $vill['storage']; ?></td>

            <td>
                <?php if($vill['max_gm'] > 0) { ?>
                <select class="select_coins" id="id_<?php echo $vill['id']; ?>" name="id_<?php echo $vill['id']; ?>">
                    <option value="0">- <?php l('game.None'); ?> -</option>

                    <?php $displayAmounts = function($c) use ($gm_price) {
                        $first = TRUE;
                        foreach($this->world->getPhysicalResources() as $res) {
                            if(!isset($gm_price[$res])) continue;
                            if(!$first) echo ', ';
                            echo $gm_price[$res] * $c;
                            $first = false;
                        }
                    }; ?>

                    <?php for($c = 1; $c <= $vill['max_gm']; ++$c) { ?>
                    <option value="<?php echo $c; ?>">
                        <?php echo $c; ?>x (<?php $displayAmounts($c); ?>)
                    </option>
                    <?php } ?>
                </select>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<form action="game.php?village=<?php echo $vid; ?>&amp;action=change_page_size&amp;screen=snob" method="post">
    <table class="vis">
        <tbody>
            <tr>
                <th colspan="2"><?php l('game.overviews.villagespersite'); ?>:</th>
                <td><input name="page_size" style="width: 50px" type="text" value="<?php echo $pagination; ?>"></td>
                <td><input class="btn" type="submit" value="OK"></td>
            </tr>
        </tbody>
    </table>
</form>
<script type="text/javascript">
//<![CDATA[
$(document).ready(function() {
    Snob.Coin.init();
    Snob.Coin.coin_multi_link = "game.php?village=<?php echo $vid; ?>&ajaxaction=coin_multi&screen=snob";
})
//]]>
</script>
