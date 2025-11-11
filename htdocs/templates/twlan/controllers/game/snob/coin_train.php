<?php
namespace Twlan;
use Twlan\framework\Text;
use Twlan\framework\Time;
require('coin_queue.php');
?>
<table class="vis">
    <tr>
        <th><?php l('game.unit'); ?></th>
        <th><?php l('buildingMain.costs'); ?></th>
        <th><?php l('game.inVillage'); ?></th>
        <th><?php l('buildingSnob.doCreateHeader'); ?></th>
    </tr>
    
        <?php foreach($units as $unit) { ?>
        <tr>
            <td class="nowrap">
                <a href="#" class="unit_link" onclick="return UnitPopup.open(event, '<?php echo $unit->id; ?>')">
                    <img src="graphic/unit/unit_<?php echo $unit->id; ?>.png" title="<?php echo $unit->getLocalizedId(); ?>" alt="<?php echo $unit->getLocalizedId(); ?>" /> <?php echo $unit->getLocalizedId(); ?>
                </a>
            </td>
            <td class="nowrap">
                <?php foreach($unit->getRecruitCosts() as $name => $value) { ?>
                <span class="icon header <?php echo $name; ?>"> </span><?php echo $name == 'time' ? Time::date($unit->getRecruitTime($unit->getRecruitBuilding(TRUE)->getLevel($village))) : $value; ?>
                <?php } ?>
            </td>

            <?php
            $elem['available'] = $unitAmountData['available'][$unit->id];
            $elem['all_count'] = $unitAmountData['all'][$unit->id];
            ?>
            <td><?php echo $elem['available'].'/'.$elem['all_count']; ?></td>
            
            <?php if(!isset($error_ag)) { ?>
            <td>
                <a href="game.php?village=<?php echo $vid; ?>&amp;action=train&amp;h=XXXX&amp;screen=snob" class="btn btn-recruit"><?php l('buildingSnob.doCreate'); ?></a>
            </td>
            <?php } else { ?>
            <td class="inactive"><?php echo $error_ag; ?></td>
            <?php } ?>
            <input type="hidden" id="snob_0" value="1">
        </tr>
        <?php } ?>
</table>
<br />

<h4><?php l('buildingSnob.amountCreatable'); ?></h4>
<table class="vis">
	<tr>
        <td><?php l('buildingSnob.limit'); ?>:</td>
        <td><?php echo $limit; ?></td></tr>
	<tr>
		<td>- <?php l('buildingSnob.currentAmount'); ?>:</td>
		<td><?php echo $amount_ag; ?></td>
	</tr>
	<tr>
		<td>- <?php l('buildingSnob.currentProdAmount'); ?>:</td>
		<td><?php echo $prod_ag; ?></td>
	</tr>
	<tr>
        <td>- <?php l('buildingSnob.amountEnnobled'); ?>:</td>
        <td><?php echo $snob_villages; ?></td>
    </tr>
	<tr>
        <th><?php l('buildingSnob.amountStillProducable'); ?>:</th>
        <th><?php echo $possible_ag; ?></th>
    </tr>
</table><br />


<script type="text/javascript">
//<![CDATA[
	$(function(){ UI.ToolTip($('.snob_tooltip'));});
//]]>
</script>
<script type="text/javascript">
//<![CDATA[
	$(function(){
		if (location.hash == '#minted') {
			UI.SuccessMessage("<?php l('buildingSnob.coinMinted'); ?>");
			location.hash = '';
		}
	});
//]]>
</script>
<table ><tr><td>
<img src="graphic/gold_big.png" alt="<?php l('buildingSnob.coinsPlural'); ?>" />
</td>
<td><h4><?php l('buildingSnob.coinsPlural'); ?></h4>
<p>
    <?php l('buildingSnob.coinHint'); ?>
</p>
</td></tr></table>

<br/>
<table class="vis">
    <tr>
        <th colspan="2" width="350"><?php l('buildingSnob.coinsPlural'); ?></th>
    </tr>
    <tr>
        <td><?php l('buildingSnob.overall'); ?>:</td>
        <td><?php echo $coins; ?></td>
    </tr>
    <tr><td style="background:none;" ></td></tr>
    <tr>
        <th colspan="2"><?php echo $this->world->units->get('snob')->getLocalizedId(); ?></th>
    </tr>
    <tr>
        <td><?php l('buildingSnob.currentLimit'); ?>:</td>
        <td><?php echo $limit; ?></td>
    </tr>
    <tr>
        <td><?php l('buildingSnob.stillMissing', array('x' => $limit + 1)); ?>:</td>
        <td class="nowrap"><?php echo Text::plural($coins_missing, ll('buildingSnob.coinsSingular'), ll('buildingSnob.coinsPlural')); ?></td>
    </tr>
    <tr>
        <td><?php l('buildingSnob.alreadyCreated', array('x' => $limit + 1)); ?>:</td>
        <td class="nowrap"><?php echo Text::plural($coins_already, ll('buildingSnob.coinsSingular'), ll('buildingSnob.coinsPlural')); ?></td>
    </tr>
</table><br/>

<table class="vis">
    <tr>
        <th><?php l('buildingMain.costs'); ?></th>
        <th><?php l('buildingSnob.actionCreateCoin'); ?></th>
    </tr>
    <tr>
        <td>
            <?php foreach($coins_cost as $name => $value) { ?>
            <span class="icon header <?php echo $name; ?>"></span> <?php echo Text::formatInt($value, '<span class="grey">.</span>'); ?>
            <?php } ?>
        <td>
        
        <?php if(!isset($coin_error)) { ?>
            <a href="game.php?village=<?php echo $vid; ?>&amp;action=coin&amp;screen=snob" class="btn"><?php l('buildingSnob.createCoins'); ?></a>
        <?php } else { ?>
            <span class="inactive"><?php echo $coin_error; ?></span>
        <?php } ?>
        </td>
    </tr>
</table>
<?php require(dirname(__FILE__).'/../train/train_js.php'); ?>