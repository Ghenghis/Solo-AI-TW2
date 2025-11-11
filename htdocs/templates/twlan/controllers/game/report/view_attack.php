<?php
namespace Twlan;
use Twlan\framework\Text;
use Twlan\framework\Time;
$fromVillage = $report->fromVillage;
$fromUser = isset($fromVillage->owner) ? $fromVillage->owner : null;
$toVillage = $report->toVillage;
$toUser = isset($toVillage->owner) ? $toVillage->owner : null;
?>
<table class="vis" width="470">
    <tbody>
        <tr>
            <td class="nopad">
                <table align="center" class="vis" width="100%" style="margin-top: -2px;">
                    <tbody>
                        <tr>
                            <td align="center" width="20%">
                                <a href="game.php?village=<?php echo $vid; ?>&amp;type=<?php echo $mode; ?>&amp;mode=forward&amp;id=<?php echo $report->id_report; ?>&amp;screen=report">
                                Weiterleiten</a>
                            </td>
                            <td align="center" width="20%">
                                <a href="#" onclick="$('.move_list').toggle();return false;">Verschieben</a>
                            </td>
                            <td align="center" width="20%">
                                <a href="game.php?village=<?php echo $vid; ?>&amp;mode=<?php echo $mode; ?>&amp;action=del_one&amp;id=<?php echo $report->id_report; ?>&amp;screen=report">
                                Löschen</a>
                            </td>
                            <td align="center" width="20%">
                                <a href="#" onclick="$('#report_export_code').toggle();return false;">
                                <span class="">Exportieren</span>
                                </a>
                            </td>
                            <td align="center" width="20%">
                                <a href="game.php?village=<?php echo $vid; ?>&amp;mode=all&amp;view=<?php echo $report->id_report; ?>&amp;screen=report"
                                id="report-next" class="">
                                <img src="graphic/arrow_up.png" style="vertical-align: -2px" alt="" class="">
                                </a>
                            </td>
                            <td align="center" width="20%">
                            </td>
                        </tr>
                        <tr class="move_list" style="display:none">
                            <td align="center" colspan="4">
                                <form action="game.php?village=<?php echo $vid; ?>&amp;action=move&amp;group_id=0&amp;report_id=<?php echo $report->id_report; ?>&amp;type=all&amp;screen=report" method="POST">
                                    <select name="group_id">
                                        <option value="127">Archiv</option>
                                    </select>
                                    <input class="btn" type="submit" value="Verschieben">
                                </form>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table class="vis">
                    <tbody>
                        <tr>
                            <th width="140"><?php l('game.report.subject'); ?></th>
                            <th width="400">
                                <img src="graphic/dots/<?php echo $report->data['color']; ?>.png" class="">
                                <span class="quickedit" data-id="43768">
                                <span class="quickedit-content">
                                <span class="quickedit-label">
                                    <?php l('game.report.'.$report->type(), array(
                                        'fromUser' => Text::formatAll($fromUser->global->name),
                                        'fromVillage' => Text::formatAll($fromVillage->name),
                                        'toVillage' => $toVillage->getDisplayName()
                                    ), true);
                                    ?>
                                </span>
                                <a class="rename-icon" href="#" title="<?php l('game.report.rename'); ?>"></a>
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
                                <h3><?php l('game.report.won', array('player' =>
                                    $report->data['winner'] == 'attacker' ? Text::formatAll($fromUser->global->name)
                                        : ($report->data['winner'] == 'defender' ?
                                        ll('game.report.defender') : 'Unknown')
                                    ));
                                ?></h3>
                                <?php
                                // Determine battle result image based on outcome
                                $imageClass = 'image_attack_won';
                                if (isset($report->result)) {
                                    if ($report->result == 'won') $imageClass = 'image_attack_won';
                                    else if ($report->result == 'lost') $imageClass = 'image_attack_lost';
                                    else if ($report->result == 'draw') $imageClass = 'image_attack_draw';
                                }
                                ?>
                                <div class="report_image <?php echo $imageClass; ?>">
                                    <div class="report_transparent_overlay">
                                        <h4><?php l('game.report.luck'); ?></h4>
                                        <table id="attack_luck">
                                            <tbody>
                                                <tr>
                                                    <?php $_l = $report->data['luck']; ?>
                                                    <td class="nobg"><img src="graphic/rabe<?php echo $_l < 0 ? '' : '_grau'; ?>.png" alt="<?php l('game.report.badluck'); ?>" class=""></td>
                                                    <td class="nobg">
                                                        <table class="luck" cellspacing="0" cellpadding="0">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="luck-item nobg" width="0" height="12">
                                                                    <?php
                                                                        // Handle 0.0 correctly
                                                                        $_p = 0;
                                                                        $_g = 0;
                                                                        if ($_l > 0) $_g = 2 * $_l;
                                                                        if ($_l < 0) $_p = -2 * $_l;
                                                                    ?>
                                                                    <td class="luck-item nobg" width="<?php echo 50 - $_p; ?>">
                                                                    <?php if($_p != 0) { ?>
                                                                    <td class="luck-item nobg" style="background-image:url(graphic/balken_pech.png);"
                                                                    width="<?php echo $_p; ?>"></td>
                                                                    <?php } ?>
                                                                    <td class="luck-item nobg" width="1" style="background-color: black"></td>
                                                                    <?php if($_g != 0) { ?>
                                                                        <td class="luck-item nobg" style="background-image:url(graphic/balken_glueck.png);"
                                                                        width="<?php echo $_g; ?>" height="12"></td>
                                                                    <?php } ?>
                                                                    <td class="luck-item nobg" width="<?php echo 50 - $_g; ?>">
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                    <td class="nobg"><img src="graphic/klee<?php echo $_l > 0 ? '' : '_grau'; ?>.png" alt="<?php l('game.report.normalLuck'); ?>" class=""></td>
                                                    <td class="nobg"><b><?php echo $report->data['luck']; ?>%</b></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <table id="attack_info_att" width="100%" style="border: 1px solid #DED3B9">
                                    <tbody>
                                        <tr>
                                            <th style="width:20%"><?php l('game.report.attacker'); ?>:</th>
                                            <th><a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $fromUser->id_user; ?>&amp;screen=info_player">
                                            <?php echo Text::formatAll($fromUser->global->name); ?></a></th>
                                        </tr>
                                        <tr>
                                            <td><?php l('game.report.origin'); ?>:</td>
                                            <td><span class="village_anchor contexted" data-player="<?php echo $fromUser->id_user; ?>" data-id="<?php echo $fromVillage->id_village; ?>">
                                            <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $fromVillage->id_village;
                                            ?>&amp;screen=info_village"><?php echo $fromVillage->getDisplayName(); ?></a>
                                            <a class="ctx" href="#"></a></span></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" style="padding:0px">
                                                <table id="attack_info_att_units" class="vis" style="border-collapse:collapse">
                                                    <tbody>
                                                        <tr class="center">
                                                            <td></td>
                                                            <?php foreach ($units as $unit) { ?>
                                                            <td width="35"><a class="unit_link" href="#"
                                                            onclick="return UnitPopup.open(event, '<?php echo $unit->id;
                                                            ?>')"><img src="graphic/unit/unit_<?php echo $unit->id; ?>.png"
                                                            title="<?php echo $unit->getLocalizedId(); ?>" alt=""
                                                            <?php if (!isset($report->data['attacker']['before'][$unit->id]) || !$report->data['attacker']['before'][$unit->id]) 
                                                                echo 'class="faded"';
                                                            ?>
                                                            >
                                                            </a></td>
                                                            <?php } ?>
                                                        </tr>
                                                        <tr>
                                                            <td width="20%"><?php l('game.report.amount'); ?>:</td>
                                                            <?php foreach ($units as $unit) {
                                                                $am = 0;
                                                                if (isset($report->data['attacker']['before'][$unit->id]))
                                                                {
                                                                    $am = $report->data['attacker']['before'][$unit->id];
                                                                }
                                                                echo '<td style="text-align:center" class="unit-item';
                                                                if ($am == 0) echo ' hidden';
                                                                echo '">'.$am.'</td>';
                                                            } ?>
                                                        </tr>
                                                        <tr>
                                                            <td align="left" width="20%"><?php l('game.report.losses'); ?>:</td>
                                                            <?php foreach ($units as $unit) {
                                                                $am = 0;
                                                                if (isset($report->data['attacker']['before'][$unit->id]))
                                                                {
                                                                    $am = $report->data['attacker']['before'][$unit->id] -
                                                                        $report->data['attacker']['after'][$unit->id];
                                                                }
                                                                echo '<td style="text-align:center" class="unit-item';
                                                                if ($am == 0) echo ' hidden';
                                                                echo '">'.$am.'</td>';
                                                            } ?>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <br>

                                <table id="attack_info_def" width="100%" style="border: 1px solid #DED3B9">
                                    <tbody>
                                        <tr>
                                            <th style="width:20%"><?php l('game.report.defender'); ?>:</th>
                                            <th>
                                                <?php if($toUser) { ?>
                                                    <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $toUser->id_user; ?>&amp;screen=info_player">
                                                        <?php echo Text::formatAll($toUser->global->name); ?>
                                                    </a>
                                                <?php } else { echo '---'; } ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <td><?php l('game.report.target'); ?>:</td>
                                            <td><span class="village_anchor contexted" data-player="<?php echo $toUser->id_user; ?>" data-id="<?php echo $toVillage->id_village; ?>">
                                            <a href="game.php?village=<?php echo $vid; ?>&amp;id=<?php echo $toVillage->id_village;
                                            ?>&amp;screen=info_village"><?php echo $toVillage->getDisplayName(); ?></a>
                                            <a class="ctx" href="#"></a></span></td>
                                        </tr>
                                        <?php if(!$report->showDefenderTroops()) { ?>
                                            <tr><td colspan="2">
                                                <?php l('game.report.noDefInfo'); ?>
                                            </td></tr>

                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="2" style="padding:0px">
                                                    <table id="attack_info_def_units" class="vis" style="border-collapse:collapse">
                                                        <tbody>
                                                            <tr class="center">
                                                                <td></td>
                                                                <?php foreach ($units as $unit) { ?>
                                                                <td width="35"><a class="unit_link" href="#"
                                                                onclick="return UnitPopup.open(event, '<?php echo $unit->id;
                                                                ?>')"><img src="graphic/unit/unit_<?php echo $unit->id; ?>.png"
                                                                title="<?php echo $unit->getLocalizedId(); ?>" alt=""
                                                                <?php if (!isset($report->data['defender']['before'][$unit->id]) || !$report->data['defender']['before'][$unit->id]) 
                                                                    echo 'class="faded"';
                                                                ?>
                                                                >
                                                                </a></td>
                                                                <?php } ?>
                                                            </tr>
                                                            <tr>
                                                                <td width="20%"><?php l('game.report.amount'); ?>:</td>
                                                                <?php foreach ($units as $unit) {
                                                                    $am = 0;
                                                                    if (isset($report->data['defender']['before'][$unit->id]))
                                                                    {
                                                                        $am = $report->data['defender']['before'][$unit->id];
                                                                    }
                                                                    
                                                                    echo '<td style="text-align:center" class="unit-item';
                                                                    if ($am == 0) echo ' hidden';
                                                                    echo '">'.$am.'</td>';
                                                                } ?>
                                                            </tr>
                                                            <tr>
                                                                <td align="left" width="20%"><?php l('game.report.losses'); ?>:</td>
                                                                <?php foreach ($units as $unit) {
                                                                    $am = 0;
                                                                    if (isset($report->data['defender']['before'][$unit->id]))
                                                                    {
                                                                        $am = $report->data['defender']['before'][$unit->id] -
                                                                            $report->data['defender']['after'][$unit->id];
                                                                    }
                                                                    echo '<td style="text-align:center" class="unit-item';
                                                                    if ($am == 0) echo ' hidden';
                                                                    echo '">'.$am.'</td>';
                                                                } ?>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        <?php } //End of /defenderTroops ?> 
                                    </tbody>
                                </table>
                                <br>
                                <table id="attack_results" width="100%" style="border: 1px solid #DED3B9">
                                    <tbody>
                                        <?php if (isset($report->data['loot'])) { ?>
                                        <tr>
                                            <th><?php l('game.booty'); ?>:</th>
                                            <td width="250">
                                                <?php if(isset($report->data["loot"]["res"])) foreach($this->world->getPhysicalResources() as $res) { ?>
                                                <span class="nowrap"><span class="icon header <?php echo $res; ?>">
                                                </span><?php echo $report->data['loot']['res'][$res]; ?></span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <?php if (count($report->data["loot"])) { echo $report->data['loot']['used'].'/'.$report->data['loot']['max']; } else {
                                                echo "0/0"; }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php } ?>
                                        <?php if (isset($report->data['loyalty'])) { ?>
                                        <tr>
                                            <th><?php l('game.report.loyalty'); ?>:</th>
                                            <td colspan="2"><?php l('game.report.loyaltyChange', array(
                                                'from' => ceil($report->data['loyalty'][0]),
                                                'to' => ceil($report->data['loyalty'][1]))); ?></td>
                                        </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                                <br>
                                <a href="game.php?village=<?php echo $vid; ?>&amp;mode=sim&amp;report_id=<?php echo $report->id_report;
                                ?>&amp;screen=place">» Truppen in Simulator einfügen</a>
                                <br>
                                <a style="display:none;" href="game.php?village=<?php echo $vid; ?>&amp;mode=sim&amp;only_survive=&amp;report_id=<?php echo $report->id_report;
                                ?>&amp;screen=place">» Überlebende Truppen in Simulator einfügen</a>
                                <hr>
                                <a style="display: none;" href="game.php?target=<?php echo $toVillage->id_village; ?>&amp;village=<?php echo $vid; ?>&amp;screen=place">
                                » Das Dorf angreifen</a>
                                <br>
                                <a style="display: none;" href="game.php?try=confirm&amp;type=same&amp;report_id=<?php echo $report->id_report;
                                ?>&amp;village=<?php echo $fromVillage->id_village; ?>&amp;screen=place">
                                » Mit gleichen Truppe noch einmal angreifen</a>
                                <br>
                                <a style="display: none;" href="game.php?try=confirm&amp;type=all&amp;report_id=<?php echo $report->id_report; ?>
                                &amp;village=<?php echo $fromVillage->id_village; ?>&amp;screen=place">
                                » Mit allen Truppe noch einmal angreifen</a>
                                <hr>
                                <a style="display: none;" href="/game.php?village=<?php echo $vid; ?>
                                &amp;mode=publish&amp;report_id=<?php echo $report->id_report; ?>&amp;screen=report">
                                » Den Bericht veröffentlichen</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
<textarea cols="55" rows="7" onclick="this.focus();this.select()" readonly="readonly" id="report_export_code" style="display:none">
<?php
// Generate export code for forum posting
$exportData = base64_encode(json_encode([
    'attacker' => $fromUser ? $fromUser->username : 'Unknown',
    'defender' => $toUser ? $toUser->username : 'Unknown',
    'result' => $report->result ?? 'unknown',
    'time' => $report->time ?? time()
]));
echo "[spoiler][report_export]{$exportData}[/report_export][/spoiler]";
?>
</textarea>
<script>
    $(function(){
        $('.quickedit').QuickEdit( { url: TribalWars.buildURL('POST', 'report', { ajaxaction: 'edit_subject', report_id: '__ID__' } ) } );
    });
</script>
