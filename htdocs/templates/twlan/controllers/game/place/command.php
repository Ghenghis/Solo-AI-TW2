<?php
namespace Twlan;
?>
<h3><?php l('buildingPlace.command'); ?></h3>
<script type="text/javascript" src="js/game/VillageTarget.js"></script>
<script type="text/javascript">
$(function() {
    //TroopTemplates.current = [];
    $(".evt-select-template").change(function() { /*TroopTemplates.useTemplate(this);*/ });
    $(document).ready(function() {
        var selectAllState = false;
        $("#selectAllUnits").click(function(event) {
            selectAllState = !selectAllState;
            selectAllUnits(selectAllState);
            TroopTemplates.resetSelect($('.evt-select-template'));
            event.preventDefault();
        });
        TargetField.init();

        <?php if (isset($target_village)) { ?>TargetField.setVillageByData(<?php echo json_encode($target_village); ?>); <?php } ?>
    });
});
</script>
<link rel="stylesheet" type="text/css" href="css/game/village_target.css" />
<form id="units_form" name="units" action="game.php?village=<?php echo $vid; ?>&amp;try=confirm&amp;screen=place" method="post" class="float_left">
    <input type="hidden" name="1d6571b9ece0685b692178" value="8e73f3161d6571">
    <input type="hidden" id="template_id" name="template_id" value="">
    <table>
        <tbody>
            <tr style="display:none">
                <td>
                </td>
            </tr>
            <tr>
                <?php $army = $village->getOwnArmy(); ?>
                <?php foreach($unitsColumns as $unitColumn) { ?>
                <td valign="top">
                    <table class="vis" width="100%">
                        <tbody>
                            <tr><th><!-- Unit Type in original Tribalwars --></th></tr>
                            <?php foreach($unitColumn as $_unit) { $_max = $army->getUnits(); $_max=$_max[$_unit->id]; ?>
                            <tr>
                                <td class="nowrap"><a href="#" class="unit_link" onclick="return UnitPopup.open(event, '<?php echo $_unit->id; ?>')">
                                    <img src="graphic/unit/unit_<?php echo $_unit->id; ?>.png" title="<?php echo $_unit->getLocalizedId(); ?>" alt="" class=""></a> 
                                    <input id="unit_input_<?php echo $_unit->id; ?>" name="<?php echo $_unit->id; ?>" type="text" style="width: 40px" tabindex="1" 
                                    value="<?php echo isset($_POST[$_unit->id])?$_POST[$_unit->id]:''; ?>" class="unitsInput"> <a href="javascript:insertUnit($('#unit_input_<?php echo $_unit->id; ?>'), <?php echo $_max; ?>)">(<?php echo $_max; ?>)</a>
                                </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
               </td>
               <?php } ?>
            </tr>
        </tbody>
    </table>

    
    <input type="text" name="x" id="inputx" value="" style="display: none">
    <input type="text" name="y" id="inputy" value="" style="display: none">

    <br>

            <div class="target-select clearfix vis float_left">
            <h4>Ziel:</h4>

            <table class="vis" style="width: 100%">
                <tbody><tr>
                    <td>
                        <div class="target-types">
                            <label><input type="radio" name="target_type" value="coord" checked="checked"> Koordinaten</label>
                            <label><input type="radio" name="target_type" value="village_name"> Dorfname</label>
                            <label><input type="radio" name="target_type" value="player_name"> Spielername</label>
                        </div>

                        <div id="place_target" class="target-input float_left">
                            <span role="status" aria-live="polite" class="ui-helper-hidden-accessible"></span><input type="text" name="input" class="target-input-field target-input-autocomplete ui-autocomplete-input" data-type="player" value="" autocomplete="off" tabindex="14" placeholder="123|456">
                        </div>
                        <a href="#" class="target-quickbutton target-last-attacked"></a>

                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="target-select-links">
                            <a href="#" onclick="TargetField.loadTargetsPopup(event, 'game.php?village=<?php echo $vid; ?>&amp;ajax=bookmark&amp;building=place&amp;prefix=&amp;screen=targets');">» Favoriten</a>
                            <a href="#" onclick="TargetField.loadTargetsPopup(event, 'game.php?village=<?php echo $vid; ?>&amp;ajax=recent&amp;building=place&amp;prefix=&amp;screen=targets');">» Verlauf</a>
                            <span></span>
                            <span></span>
                        </div>
                    </td>
                </tr>
            </tbody></table>
        </div>

        <div class="target-select clearfix vis float_left">
            <h4>Befehl:</h4>

            <table class="vis" style="width: 100%">
                <tbody><tr>
                    <td>
                        <input id="target_attack" tabindex="15" class="attack btn btn-attack btn-target-action" name="attack" type="submit" value="Angreifen">
                        <input id="target_support" tabindex="16" class="support btn btn-support btn-target-action" name="support" type="submit" value="Unterstützen">
                    </td>
                </tr>
            </tbody></table>
        </div>
    </form>
    <div class="vis float_left" style="margin: 4px 0 0 10px; min-width: 125px;">
    <h4><a href="/game.php?village=<?php echo $vid; ?>&amp;mode=templates&amp;screen=place">Truppe-Vorlage</a></h4>
    <table class="vis" style="width: 100%">
        <tbody><tr class="row_b">
            <td><a id="selectAllUnits" href="#">Alle Truppen</a></td>
        </tr>
            </tbody></table>
</div>
<div class="popup_helper">
    <div id="inline_popup" class="hidden" >
        <div id="inline_popup_menu">
            <span id="inline_popup_title"></span>
            <a id="inline_popup_close" href="javascript:inlinePopupClose()">X</a>
        </div>
        <div id="inline_popup_main" style="height: auto;">
            <h3>Ziu</h3>
            <div>
                <div id="inline_popup_content" style="height: 340px; overflow: auto;" >
                    <img src="graphic/throbber.gif" alt="Ladt" />
                </div>
            </div>
        </div>
    </div>
</div>
<h3 style="clear: both;"><?php l('buildingPlace.troopMovements'); ?></h3>
<?php $this->viewPartial('../overview/outgoingUnits'); ?>
<?php $this->viewPartial('../overview/incomingUnits'); ?>
<script>
    $(function(){
        $('.quickedit-out').QuickEdit( { show_icons: false, url: TribalWars.buildURL('POST', 'info_command', { ajaxaction: 'edit_own_comment', id: '__ID__' } ) } );
    });
</script>