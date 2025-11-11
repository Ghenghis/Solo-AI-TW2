<?php
namespace Twlan;
$village = $this->village;

?>
<script type="text/javascript" src="unit_popup.js"></script>
<script type="text/javascript">
//<![CDATA[
    $(function() {
        UnitPopup.unit_data = <?php echo json_encode($unitPopupData); ?>;
        UnitPopup.init();
    });
//]]>
</script>

<div class="popup_helper">
    <div id="inline_popup" style="width:400px;">
        <div id="inline_popup_menu">
            <a href="javascript:inlinePopupClose()"><?php l('game.title.close'); ?></a>
        </div>
        <div id="inline_popup_main" style="height: auto;">
            <div id="inline_popup_content"></div>
        </div>
    </div>
</div>

<div id="unit_popup_template" style="display: none">
    <div class="inner-border main content-border" style="border: none; font-weight: normal">
        <table style="float: left;width:380px">
            <tr>
                <td>
                    <h2 class="unit_name"></h2>
                    <p class="unit_desc"></p>
                </td>
            </tr>
            <tr>
                <td>
                    <table style="border: 1px solid #DED3B9;" class="vis" width="100%">
                        <tr>
                            <th width="180"><?php l('game.costs'); ?></th>
                            <th><?php l('game.population'); ?></th>
                            <th><?php l('game.speed'); ?></th>
                            <th><?php l('game.booty'); ?></th>
                        </tr>
                        <tr class="center">
                            <td>
                                <?php foreach($this->world->getPhysicalResources() as $res) { ?>
                                <nobr><span class="icon header <?php echo $res; ?>"> </span><span class="unit_<?php echo $res; ?>"></span></nobr> 
                                <?php } ?>
                            </td>
                            <td><span class="icon header population"> </span><span class="unit_pop"></span></td>
                            <td id="unit_speed"></td>
                            <td class="unit_carry"></td>
                        </tr>
                    </table>
                    <br />


                    <table class="vis has_levels_only" style="border: 1px solid #DED3B9;text-align:center" class="vis"  width="100%">
                        <tr><th colspan="3"><?php l('game.unit.combatValues'); ?></th></tr>
                        <tr>
                            <td align="left"><?php l('game.unit.attackStrength'); ?></td>
                            <td width="20px"><img src="graphic/unit/att.png?1" alt="Angriffsstärke" /></td>
                            <td><span class="unit_attack"></span></td>
                        </tr>
                        <tr>
                            <td align="left"><?php l('game.unit.defenseGeneral'); ?></td>
                            <td><img src="graphic/unit/def.png?1" alt="<?php l('game.unit.defenseGeneral'); ?>" /></td>
                            <td><span class="unit_defense"></span></td>
                        </tr>
                        <tr>
                            <td align="left"><?php l('game.unit.defenseCav'); ?></td>
                            <td><img src="graphic/unit/def_cav.png?1" alt="<?php l('game.unit.defenseCav'); ?>" /></td><td><span class="unit_defense_cavalry"></span></td>
                        </tr>
                        <tr>
                            <td align="left"><?php l('game.unit.defenseArcher'); ?></td>
                            <td><img src="graphic/unit/def_archer.png?1" alt="<?php l('game.unit.defenseArcher'); ?>" /></td>
                            <td><span class="unit_defense_archer"></span></td>
                        </tr>
                    </table>
                    <br />

                    <div class="show_if_has_reqs">
                        <table class="vis" width="100%">
                            <tr><th id="reqs_count" colspan="1"><?php l('game.unit.requirements'); ?></th></tr>
                            <tr id="reqs"></tr>
                        </table>
                        <br />
                    </div>

                    <table class="unit_tech vis unit_tech_levels" width="100%">
                        <tr style="text-align:center">
                            <th><?php l('game.unit.techLevel'); ?></th>
                            <th width="350"><?php l('game.unit.researchCosts'); ?></th>
                            <th width="30" style="text-align:center"><img src="graphic/unit/att.png?1" alt="<?php l('game.unit.attackStrength'); ?>" /></th>
                            <th width="30" style="text-align:center"><img src="graphic/unit/def.png?1" alt="<?php l('game.unit.defenseGeneral'); ?>" /></th>
                            <th width="30" style="text-align:center"><img src="graphic/unit/def_cav.png?1" alt="<?php l('game.unit.defense_sav'); ?>" /></th>
                            <th width="30" style="text-align:center"><img src="graphic/unit/def_archer.png?1" alt="<?php l('game.unit.defenseArcher'); ?>" /></th>
                        </tr>
                        <tr id="unit_tech_prototype" style="display: none;text-align:center">
                            <td class="tech_level"></td>
                            <td>
                                <span class="grey tech_researched"><?php l('game.unit.alreadyResearched'); ?></span>
                                <span class="tech_res_list">
                                    <?php foreach($this->world->getPhysicalResources() as $res) { ?>
                                    <span class="icon header <?php echo $res; ?>"></span>
                                    <span class="tech_<?php echo $res; ?>"></span>
                                    <?php } ?>
                                </span>
                            </td>
                            <td class="tech_att"></td>
                            <td class="tech_def"></td>
                            <td class="tech_def_cav"></td>
                            <td class="tech_def_archer"></td>
                        </tr>
                    </table>
                    <table class="vis unit_tech unit_tech_cost"  width="100%">
                        <tr><th><?php l('game.unit.researchCosts'); ?></th></tr>
                        <tr>
                            <td>
                                <?php foreach($this->world->getPhysicalResources() as $res) { ?>
                                <span class="icon header <?php echo $res; ?>"></span>
                                <span class="tech_cost_<?php echo $res; ?>"></span>
                                <?php } ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <img style="margin-top: 60px" id="unit_image" src="" alt="" />
    </div>
</div>