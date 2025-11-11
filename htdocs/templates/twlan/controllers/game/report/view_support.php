<?php
namespace Twlan;
use Twlan\framework\Text;
use Twlan\framework\Time;
?>
<table class="vis" width="470">
    <tbody><tr>
        <td class="nopad">
        <table align="center" class="vis" width="100%" style="margin-top: -2px;">
    <tbody><tr>
        <td align="center" width="20%">
            <a href="game.php?village=<?php echo $vid; ?>&amp;type=all&amp;mode=forward&amp;id=<?php echo $report->id_report; ?>&amp;screen=report">
            Weiterleiten</a>        
        </td>
        <td align="center" width="20%">
            <a href="game.php?village=<?php echo $vid; ?>&amp;mode=all&amp;action=del_one&amp;id=<?php echo $report->id_report; ?>&amp;screen=report">
            Löschen</a>
        </td>
                
        <td align="center" width="20%">
            <a href="game.php?village=<?php echo $vid; ?>&amp;mode=all&amp;view=<?php echo $report->id_report; ?>&amp;screen=report" id="report-next" class="">
                <img src="graphic/arrow_up.png" style="vertical-align: -2px" alt="" class="">
            </a>
        </td>
        <td align="center" width="20%">
       </td>
    </tr>
    <tr class="move_list" style="display:none">
        <td align="center" colspan="4">
            <form action="" method="POST">
                <select name="group_id">
                </select>
                <input class="btn" type="submit" value="Move">
            </form>
        </td>
    </tr>
</tbody>
</table>
<table class="vis">
                <?php
                $fromUser = $report->fromVillage->owner;
                $fromVillage = $report->fromVillage;
                $toVillage = $report->toVillage;
                $toUser = $report->toVillage->owner;
                ?>
                <tbody><tr>
                        <th width="140"><?php l('game.report.subject'); ?></th>
                        <th width="400">
                                            
                        <span data-id="">
                            <span class="quickedit-content">
                                <span class="quickedit-label">
                                     <?php l('game.report.'.$report->type(), array(
                                        'fromUser' => Text::formatAll($fromUser->global->name), 
                                        'fromVillage' => Text::formatAll($fromVillage->name), 
                                        'toVillage' => $toVillage->getDisplayName()
                                    ), true); 
                                    ?>
                                </span>
                            </span>
                        </span>
                    </th>
                </tr>
                <tr>
                    <td>
                        <?php l('game.report.time'); ?>
                    </td>
                    <td>
                        <?php echo Time::onTime($report->data['time']); ?>                
                    </td>
                </tr>
                                <tr>
<td colspan="2" valign="top" height="160" style="border: solid 1px black; padding: 4px;">
    <div class="report_image image_support">
    <div class="report_transparent_overlay">
        <table width="100%" style="border-spacing: 0 !important;">
            <tbody><tr>
                <th class="overlay-item" width="60"><?php l('game.report.from'); ?>:</th>
                <th class="overlay-item">
                    <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $fromUser->id_user; ?>&amp;screen=info_player">
                    <?php echo Text::formatAll($fromUser->global->name); ?></a></th>
                </th>
            </tr>
            <tr>
                <td class="overlay-item"><?php l('game.report.origin'); ?>:</td>
                <td class="overlay-item">
                    <span class="village_anchor contexted" data-player="<?php echo $fromUser->id_user; ?>" 
                        data-id="<?php echo $fromVillage->id_village; ?>">
                        <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $fromVillage->id_village; ?>&amp;screen=info_village">
                        <?php echo $fromVillage->getDisplayName(); ?></a>
                        <a class="ctx" href="#"></a>
                    </span>
                </td></tr>
            <tr>
                <th class="overlay-item"><?php l('game.report.to'); ?>:</th>
                <?php if($toUser) { ?>
                <th class="overlay-item">
                    <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $toUser->id_user; ?>&amp;screen=info_player">
                    <?php echo Text::formatAll($toUser->global->name); ?></a></th>
                </th> 
                <?php } ?>
            </tr>
            <tr>
                <td class="overlay-item"><?php l('game.report.target'); ?>:</td>
                <td class="overlay-item">
                    <span class="village_anchor contexted" data-player="<?php echo $toUser->id_user; ?>" data-id="<?php echo $toVillage->id_village; ?>">
                        <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $toVillage->id_village; ?>&amp;screen=info_village">
                        <?php echo $toVillage->getDisplayName(); ?></a>
                        <a class="ctx" href="#"></a>
                    </span>
                </td>
            </tr>
        </tbody>
        </table>
    </div>
    </div>

<h4><?php l('game.report.units'); ?>:</h4>
<table class="vis">
<tbody>
    <tr>
        <?php foreach($this->world->units->getAll() as $_unit) { ?>
        <th width="35">
            <img src="graphic/unit/unit_<?php echo $_unit->id; ?>.png" title="<?php echo $_unit->getLocalizedId(); ?>" alt="" 
            class="<?php if (!$report->data['supporter'][$_unit->id]) echo 'faded'; ?>">
        </th>
        <?php } ?>
    </tr>
    <tr>
        <?php foreach($this->world->units->getAll() as $_unit) { ?>
        <td class="unit-item <?php if (!$report->data['supporter'][$_unit->id]) echo 'hidden'; ?>">
            <?php echo $report->data['supporter'][$_unit->id]; ?>
        </td>
        <?php } ?>
    </tr>
</tbody></table>
                    </td>
                </tr>
            </tbody>
</table>
    </td>
    </tr>
</tbody>
</table>

<script>
    $(function(){
        $('.quickedit').QuickEdit( { url: TribalWars.buildURL('POST', 'report', { ajaxaction: 'edit_subject', report_id: '__ID__' } ) } );
    });
</script>