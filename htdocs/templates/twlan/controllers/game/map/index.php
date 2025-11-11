<?php
namespace Twlan;
?>
<script type="text/javascript">
//<![CDATA[
    /** General purpose map script variables **/
var mobile_on_normal = false;
var mobile = false;
    TWMap.premium = true;
    TWMap.mobile = false;
    TWMap.morale = true;
    TWMap.night = <?php echo TWLan::isNight($this->world) ? 'true' : 'false';?>;
    TWMap.light = false;
    //Needed to display day borders if user activated classic graphics
    TWMap.classic_gfx = false;
    TWMap.scrollBound = [0, 0, 999, 999];
    TWMap.tileSize = [53, 38];
    TWMap.screenKey = '70e7';
    TWMap.topoKey = 833245638;
    TWMap.con.CON_COUNT = 10;
    TWMap.con.SEC_COUNT = 20;
    TWMap.con.SUB_COUNT = 5;
    TWMap.image_base = 'graphic/';
    TWMap.graphics = 'graphic/<?php echo $map;?>/';
    TWMap.currentVillage = <?php echo $vid;?>;
    TWMap.cachePopupContents = true;

    /** Context menu **/
    TWMap.urls.ctx = {};
    TWMap.urls.ctx.mp_overview = 'game.php?village=__village__&screen=overview';
	TWMap.urls.ctx.mp_info = 'game.php?village=<?php echo $vid;?>&screen=info_village&id=__village__';
	TWMap.urls.ctx.mp_fav = 'game.php?village=<?php echo $vid;?>&screen=info_village&id=__village__&ajaxaction=add_target&json=1';
	TWMap.urls.ctx.mp_unfav = 'game.php?village=<?php echo $vid;?>&screen=info_village&id=__village__&ajaxaction=del_target&json=1';
	TWMap.urls.ctx.mp_lock = 'game.php?village=<?php echo $vid;?>&screen=info_village&id=__village__&ajaxaction=toggle_reserve_village&json=1';
	TWMap.urls.ctx.mp_res = 'game.php?village=<?php echo $vid;?>&screen=market&mode=send&target=__village__';
	TWMap.urls.ctx.mp_att = 'game.php?village=<?php echo $vid;?>&screen=place&target=__village__';
	TWMap.urls.ctx.mp_recruit = 'game.php?village=__village__&screen=train&mode=mass';
	TWMap.urls.ctx.mp_profile = 'game.php?village=<?php echo $vid;?>&screen=info_player&id=__owner__';
	TWMap.urls.ctx.mp_msg = 'game.php?village=<?php echo $vid;?>&screen=mail&mode=new&player=__owner__';
    TWMap.urls.ctx.mp_unlock = TWMap.urls.ctx.mp_lock;
    TWMap.context.enabled = true;
    TWMap.context._showPremium = true;
    TWMap.centerList.enabled = true;

    /** Other URLs **/
    TWMap.urls.villageInfo = 'game.php?village=<?php echo $vid;?>&screen=info_village&id=__village__';
	TWMap.urls.villagePopup = 'game.php?village=__village__&screen=overview&json=1&source=<?php echo $vid;?>';
	TWMap.urls.sizeSave = 'game.php?village=<?php echo $vid;?>&screen=settings&ajaxaction=set_map_size';
	TWMap.urls.changeShowBelief = 'game.php?village=<?php echo $vid;?>&screen=settings&ajaxaction=change_topo_show_belief';
	TWMap.urls.changeShowPolitical = 'game.php?village=<?php echo $vid;?>&screen=settings&ajaxaction=change_topo_show_political';
	TWMap.urls.changeUseContext = 'game.php?village=<?php echo $vid;?>&screen=settings&ajaxaction=change_use_contextmenu';
	TWMap.urls.savePopup = 'game.php?village=<?php echo $vid;?>&screen=map&ajax=save_map_popup';
	TWMap.urls.centerCoords = 'game.php?village=<?php echo $vid;?>&screen=map&mode=centerlist'
	TWMap.urls.centerSave = 'game.php?village=<?php echo $vid;?>&screen=map&mode=centerlist&ajaxaction=save_center';

    /** Attacked villages **/

    /** Village colors **/
    TWMap.colors['this'] = [255, 255, 255];
    TWMap.colors['player'] = [240, 200, 0];
    TWMap.colors['friend'] = [69, 255, 146];
    TWMap.colors['ally'] = [0, 0, 244];
    TWMap.colors['partner'] = [0, 160, 244];
    TWMap.colors['nap'] = [128, 0, 128];
    TWMap.colors['enemy'] = [244, 0, 0];
    TWMap.colors['other'] = [130, 60, 10];
    TWMap.colors['sleep'] = [0, 0, 0];
    TWMap.colors['grey'] = [150, 150, 150];
    TWMap.colors['highlight_village'] = [255, 0, 255];
    TWMap.colors['highlight_player'] = [239, 165, 239];


    TWMap.secrets = {};
    TWMap.inline_send.enabled = 1;
    TWMap.ignore_villages = [];

    /** Sector prefetch */
    TWMap.sectorPrefech = <?php echo $prefetchedSectors; ?>;
//]]>
</script>
<h2>
    Kontinent <span id="continent_id">37</span>
</h2>
<table cellspacing="0" cellpadding="0">
    <tbody>
        <tr>
            <td valign="top" class="map_big visible" id="map_big">
                <div style="" class="popup_style ui-draggable" id="worldmap">
                    <form method="post" action="" name="worldmap">
                        <!--  WORLDMAP HEAD -->
                        <div id="worldmap_header">
                            <div class="close popup_menu">
                                <a onclick="Worldmap.toggle(); return false;" href="javascript:void(0);">close</a>
                            </div>
                            <fieldset id="worldmap_settings">
                                <input type="checkbox" onclick="Worldmap.reload();" checked="checked" id="worldmap_barbarian_toggle" name="worldmap_barbarian_toggle">
                                <label for="worldmap_barbarian_toggle">Barbarians</label>
                                <input type="checkbox" onclick="Worldmap.reload();" checked="checked" id="worldmap_ally_toggle" name="worldmap_ally_toggle">
                                <label for="worldmap_ally_toggle">Your tribe</label>
                                <input type="checkbox" onclick="Worldmap.reload();" checked="checked" id="worldmap_partner_toggle" name="worldmap_partner_toggle">
                                <label for="worldmap_partner_toggle">Allies</label>
                                <input type="checkbox" onclick="Worldmap.reload();" checked="checked" id="worldmap_nap_toggle" name="worldmap_nap_toggle">
                                <label for="worldmap_nap_toggle">Non-Aggression-Pact (NAP)</label>
                                <input type="checkbox" onclick="Worldmap.reload();" checked="checked" id="worldmap_enemy_toggle" name="worldmap_enemy_toggle">
                                <label for="worldmap_enemy_toggle">Enemies</label>
                            </fieldset>
                            <input type="hidden" value="300" name="min_x">
                            <input type="hidden" value="300" name="min_y">
                        </div>
                        <img style="display: none" alt="Loading..." id="worldmap-throbber" src="graphic/throbber.gif">
                        <div id="worldmap_body">
                            <div id="worldmap_image">
                                <input type="image" src="graphic/transparent.png">
                            </div>
                        </div>
                        <div id="worldmap_footer">
                            <table style="text-align: left; display: inline;">
                                <tbody>
                                    <tr>
                                        <th><?php l('game.villages'); ?></th>
                                        <th><?php l('game.map.barbarians'); ?></th>
                                        <th>%</th>
                                        <th><?php l('game.map.yourAlly'); ?></th>
                                        <th>%</th>
                                        <th><?php l('game.map.own'); ?></th>
                                        <th>%</th>
                                    </tr>
                                    <tr>
                                        <td><?php echo $worldinfo["villages"]; ?></td>
                                        <td><?php echo $worldinfo["barbarians"]; ?></td>
                                        <td><?php echo $worldinfo["barbarians_percentage"]; ?></td>
                                        <td><?php echo $worldinfo["tribe_villages"]; ?></td>
                                        <td><?php echo $worldinfo["tribe_percentage"]; ?></td>
                                        <td><?php echo $worldinfo["own_villages"]; ?></td>
                                        <td><?php echo $worldinfo["own_percentage"]; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                <script type="text/javascript">
                //<![CDATA[
                $(document).ready(function() {
                    Worldmap.init(0);
                    });
                //]]>
                </script>
                <div id="map_whole" class="containerBorder narrow">
                    <table cellspacing="0" cellpadding="0" class="map_container">
                        <tbody>
                            <tr>
                                <td></td>
                                <td align="center" style="padding-left: 26px;" class="map_navigation" onclick="TWMap.scrollBlock(0, -1); return false;">
                                <img style="z-index: 1; position: relative;" alt="map/map_n.png" src="graphic/map/map_n.png"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td align="center" style="padding-bottom: 22px;" class="map_navigation" onclick="TWMap.scrollBlock(-1, 0); return false;">
                                <img style="z-index: 1; position: relative;" alt="map/map_w.png" src="graphic/map/map_w.png"></td>
                                <td style="padding: 0">
                                    <div style="position: relative;" id="map_wrap">
                                        <div id="map_coord_y_wrap" style="height:325px;">
                                            <div id="map_coord_y" style="position:absolute; left:0px; top:0px; height:38000px; overflow: visible;"></div>
                                        </div>
                                        <div id="map_coord_x_wrap" style="width:477px; ">
                                            <div id="map_coord_x" style="position:absolute; left:0px; top:0px; width:53000px; overflow: visible;"></div>
                                        </div>
                                        <img alt="" onclick="TWMap.goFullscreen()" id="fullscreen" src="graphic/fullscreen.png" style="display: inline;">
                                        <a href="game.php?screen=map" title="Send resources" id="mp_res" class="mp" style="opacity: 0; display: none;"></a>
                                        <a href="game.php?screen=map" title="Send troops" id="mp_att" class="mp" style="opacity: 0; display: none;"></a>
                                        <a href="game.php?screen=map" title="Make a noble claim for the village" id="mp_lock" class="mp" style="opacity: 0; display: none;"></a>
                                        <a href="game.php?screen=map" title="Delete noble claim" id="mp_unlock" class="mp" style="opacity: 0; display: none;"></a>
                                        <a href="game.php?screen=map" title="Add to favorites" id="mp_fav" class="mp" style="opacity: 0; display: none;"></a>
                                        <a href="game.php?screen=map" title="Delete from favorites" id="mp_unfav" class="mp" style="opacity: 0; display: none;"></a>
                                        <a href="game.php?screen=map" title="Write message" id="mp_msg" class="mp" style="opacity: 0; display: none;"></a>
                                        <a href="game.php?screen=map" title="Show player profile" id="mp_profile" class="mp" style="opacity: 0; display: none;"></a>
                                        <a href="game.php?screen=map" title="Village overview" id="mp_overview" class="mp" style="opacity: 0; display: none;"></a>
                                        <a href="game.php?screen=map" title="Mass recruitment" id="mp_recruit" class="mp" style="opacity: 0; display: none;"></a>
                                        <a href="game.php?screen=map" title="Show in new tab" id="mp_tab" class="mp" style="opacity: 0; display: none;"></a>
                                        <a href="game.php?screen=map" title="Village information" id="mp_info" class="mp" style="opacity: 0; display: none;"></a>
                                        <a style="width: 477px; height: 342px; overflow: hidden; position: relative; background-image: url('graphic/<?php echo $map;?>/gras1.png');" href="#" id="map" class="ui-resizable">
                                            <div style="position: absolute; top: 0px; left: 0px; width: 100%; height: 100%; background-color: black; z-index: 20; opacity: 0; display: none;" id="map_blend"></div>
                                            <div style="position: absolute; left: -954px; top: 5714px; z-index: 1; overflow: visible;" id="map_container"></div>
                                            <div style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%; z-index: 12; background-image: url('graphic/<?php echo $map;?>/empty.png'); cursor: move; -moz-user-select: none;" id="map_mover"></div>
                                            <div style="height: 100%; width: 100%; z-index: 10; background-image: url('graphic/<?php echo $map;?>/empty.png'); position: absolute; left: 0px; top: 0px; display: none;" id="warplanner_selection"></div>
                                            <!--<div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 14;"></div>-->
                                        </a>
                                    </div>
                                </td>
                                <td align="center" style="padding-bottom: 22px;" class="map_navigation" onclick="TWMap.scrollBlock(1, 0); return false;">
                                    <img style="z-index: 1; position: relative;" alt="map/map_e.png" src="graphic/map/map_e.png">
                                </td>
                            </tr>
                            <tr>
                                <td></td>
                                <td align="center" style="padding-left: 26px;" class="map_navigation" onclick="TWMap.scrollBlock(0, 1); return false;">
                                    <img style="z-index: 1; position: relative;" alt="map/map_s.png" src="graphic/map/map_s.png">
                                </td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <br />
                <div style="float: left; margin-bottom: 15px;" class="containerBorder">
                    <table style="border: solid 1px #8c5f0d; background-color: #f4e4bc; margin-left: 0px; border-collapse: separate; text-align: left;">
                        <tbody>
                            <tr class="nowrap">
                                <td valign="top" class="small">Standard:</td>
                                <td style="width: 15px; min-width: 15px; padding: 0px; background-color: rgb(255, 255, 255)"></td>
                                <td style="white-space: normal" class="small">Selected</td>
                                <td style="width: 15px; min-width: 15px; padding: 0px; background-color: rgb(240, 200, 0)"></td>
                                <td style="white-space: normal" class="small">Your villages</td>
                                <td style="width: 15px; min-width: 15px; padding: 0px; background-color: rgb(0, 0, 244)"></td>
                                <td style="white-space: normal" class="small">Your tribe</td>
                                <td style="width: 15px; min-width: 15px; padding: 0px; background-color: rgb(150, 150, 150)"></td>
                                <td style="white-space: normal" class="small">Abandoned villages</td>
                                <td style="width: 15px; min-width: 15px; padding: 0px; background-color: rgb(130, 60, 10)"></td>
                                <td style="white-space: normal" class="small">Miscellaneous</td>
                            </tr>
                            <tr class="nowrap">
                                <td valign="top" class="small">Tribe:</td>
                                <td style="width: 15px; min-width: 15px; padding: 0px; background-color: rgb(0, 160, 244)"></td>
                                <td style="white-space: normal" class="small">Allies</td>
                                <td style="width: 15px; min-width: 15px; padding: 0px; background-color: rgb(128, 0, 128)"></td>
                                <td style="white-space: normal" class="small">Non-Aggression-Pact (NAP)</td>
                                <td style="width: 15px; min-width: 15px; padding: 0px; background-color: rgb(244, 0, 0)"></td>
                                <td style="white-space: normal" class="small">Enemies</td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <br />
                <div style="width: 100%; text-align: left; clear: both;">
                    <a href="javascript:void(0);" onclick="$('#village_colors').toggle();">» Manage groups</a>
                </div>
                <br />
                <div style="float: left; clear: both; display: none;" class="containerBorder" id="village_colors">
                    <table style="background-color: #f4e4bc; border: solid 1px #8c5f0d;">
                        <tbody>
                            <tr>
                                <td valign="top">
                                    <h5>Your villages</h5>
                                </td>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                                <td valign="top">
                                    <h5>Other villages</h5>
                                    <form action="game.php?village=<?php echo $vid; ?>&amp;screen=map&amp;type=for&amp;action=activate_group" method="post">
                                        <table id="for_groups" class="vis">
                                            <tbody>
                                                <tr style="display: none" id="new_group">
                                                    <td colspan="5"><input type="text" onkeydown="if (event.keyCode == 13) $('#for_new_group').click();" name="new_group_name">
                                                        <input type="submit" value="OK" id="for_new_group" name="for_new_group">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </form>
                                    <br />
                                    <a onclick="javascript:ColorGroups.add_for_village();return false" href="#">» Create new group</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </td>
            <td valign="top" class="map_topo" id="map_topo">
                <div id="minimap_whole" class="containerBorder">
                    <table cellspacing="1" cellpadding="0" class="map_container minimap_container">
                        <tbody>
                            <tr>
                                <td align="center"><img src="graphic/map/map_nw.png" style="z-index: 1; position: relative;" onclick="TWMap.scrollBlock(-1, -1); return false;" class="dir_arrow" alt="North west"></td>
                                <td align="center"><img src="graphic/map/map_n.png" style="z-index: 1; position: relative;" onclick="TWMap.scrollBlock(0, -1); return false;" class="dir_arrow" alt="North"></td>
                                <td align="center"><img src="graphic/map/map_ne.png" style="z-index: 1; position: relative;" onclick="TWMap.scrollBlock(1, -1); return false;" class="dir_arrow" alt="North east"></td>
                            </tr>
                            <tr>
                                <td align="center"><img src="graphic/map/map_w.png" style="z-index: 1; position: relative;" onclick="TWMap.scrollBlock(-1, 0); return false;" class="dir_arrow" alt="West"></td>
                                <td id="minimap_cont">
                                    <div style="overflow: hidden; position: relative; padding: 0px; width: 250px; height: 250px" id="minimap" class="ui-resizable">
                                        <div style="border: 1px solid white; position: absolute; z-index: 10; width: 45px; height: 45px; left: 100px; top: 100px;" id="minimap_viewport"></div>
                                        <div style="position: absolute; left: -2490px; top: -2635px; z-index: 1; overflow: visible;" id="minimap_container">
                                            <div style="width: 250px; height: 250px; position: absolute; left: 2500px; top: 2500px;">
                                                <img style="position: absolute; z-index: 1; left: 0px; top: 0px;" src="page.php?page=topo_image&amp;player_id=<?php echo $user->id_user;?>&amp;village_id=<?php echo $vid;?>&amp;x=500&amp;y=500&amp;church=0&amp;political=0&amp;war=0">
                                            </div>
                                            <div style="width: 250px; height: 250px; position: absolute; left: 2500px; top: 2750px;">
                                                <img style="position: absolute; z-index: 1; left: 0px; top: 0px;" src="page.php?page=topo_image&amp;player_id=<?php echo $user->id_user;?>&amp;village_id=<?php echo $vid;?>&amp;x=500&amp;y=550&amp;church=0&amp;political=0&amp;war=0">
                                            </div>
                                            <div style="width: 250px; height: 250px; position: absolute; left: 2250px; top: 2500px;">
                                                <img style="position: absolute; z-index: 1; left: 0px; top: 0px;" src="page.php?page=topo_image&amp;player_id=<?php echo $user->id_user;?>&amp;village_id=<?php echo $vid;?>&amp;x=450&amp;y=500&amp;church=0&amp;political=0&amp;war=0">
                                            </div>
                                            <div style="width: 250px; height: 250px; position: absolute; left: 2250px; top: 2750px;">
                                                <img style="position: absolute; z-index: 1; left: 0px; top: 0px;" src="page.php?page=topo_image&amp;player_id=<?php echo $user->id_user;?>&amp;village_id=<?php echo $vid;?>&amp;x=450&amp;y=550&amp;church=0&amp;political=0&amp;war=0">
                                            </div>
                                        </div>
                                        <div id="minimap_mover" style="position: absolute; left: 0px; top: 0px; width: 100%; height: 100%; z-index: 12; background-image: url('graphic/<?php echo $map;?>/empty.png'); cursor: move; -moz-user-select: none;"></div>
                                        <!--<div class="ui-resizable-handle ui-resizable-se ui-icon ui-icon-gripsmall-diagonal-se" style="z-index: 14;"></div>-->
                                    </div>
                                </td>
                                <td align="center">
                                    <img src="graphic/map/map_e.png" style="z-index: 1; position: relative;" onclick="TWMap.scrollBlock(1, 0); return false;" class="dir_arrow" alt="East">
                                </td>
                            </tr>
                            <tr>
                                <td align="center">
                                    <img src="graphic/map/map_sw.png" style="z-index: 1; position: relative;" onclick="TWMap.scrollBlock(-1, 1); return false;" class="dir_arrow" alt="South west">
                                </td>
                                <td align="center">
                                    <img src="graphic/map/map_s.png" style="z-index: 1; position: relative;" onclick="TWMap.scrollBlock(0, 1); return false;" class="dir_arrow" alt="South">
                                </td>
                                <td align="center">
                                    <img src="graphic/map/map_se.png" style="z-index: 1; position: relative;" onclick="TWMap.scrollBlock(1, 1); return false;" class="dir_arrow" alt="South east">
                                    </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div id="map_config">
                    <div style="margin-top: 10px; margin-bottom: 10px;">
                        <a onclick="Worldmap.toggle()" href="javascript:void(0);">» Show World Map</a><br>
                    </div>
                    <table width="100%" style="border-spacing: 0px; border-collapse: collapse;" class="vis">
                        <tbody>
                            <tr>
                                <th colspan="3">Display options</th>
                            </tr>

                            <tr id="pmap_options" style="display: none;">
                                <td style="padding-left: 8px;" colspan="3"><label><input
                                        type="radio" checked="checked" id="pmap_filter1" value="1"
                                        onclick="TWMap.politicalMap.toggle(false);" name="pmap_filter">
                                        Show all </label><br> <label><input type="radio"
                                        id="pmap_filter2" value="2"
                                        onclick="TWMap.politicalMap.toggle(false);" name="pmap_filter">
                                        Show all tribes </label><br> <label><input type="radio"
                                        id="pmap_filter3" value="3"
                                        onclick="TWMap.politicalMap.toggle(false);" name="pmap_filter">
                                        Only show own tribe </label><br> <label><input type="radio"
                                        id="pmap_filter4" value="4"
                                        onclick="TWMap.politicalMap.toggle(false);" name="pmap_filter">
                                        Show owned villages </label><br> <br> <label><input
                                        type="checkbox" checked="checked" id="pmap_show_topo"
                                        onclick="TWMap.politicalMap.toggle(false);"> Display on
                                        minimap </label><br> <label><input type="checkbox"
                                        checked="checked" id="pmap_show_map"
                                        onclick="TWMap.politicalMap.toggle(false);"> Display on map </label>
                                </td>
                            </tr>


                            <tr>
                                <td><input type="checkbox" checked="checked" id="classiclink"
                                    onclick="TWMap.context.toggleUse();" name="usecontext"></td>
                                <td><label for="classiclink"> Activate context menu </label></td>
                                <td></td>
                            </tr>

                            <tr>
                                <td><input type="checkbox" id="show_popup" checked="checked"></td>
                                <td><label for="show_popup">Show popup</label></td>
                                <td width="18" style="display: none;"><img src="graphic/icons/slide_down.png"
                                    class="popup_options_toggler"></td><!-- Not implemented-->
                            </tr>
                            <tr id="popup_options" style="display: none;">
                                <td style="padding-left: 8px" colspan="3">
                                    <form id="form_map_popup">
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td><input type="checkbox" name="map_popup_attack"
                                                        id="map_popup_attack"></td>
                                                    <td><label for="map_popup_attack">Show last attack</label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="map_popup_moral"
                                                        id="map_popup_moral"></td>
                                                    <td><label for="map_popup_moral">Show morale</label></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="map_popup_res"
                                                        id="map_popup_res"></td>
                                                    <td><label for="map_popup_res">Show resources</label></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="map_popup_pop"
                                                        id="map_popup_pop"></td>
                                                    <td><label for="map_popup_pop">Show population</label></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="map_popup_trader"
                                                        id="map_popup_trader"></td>
                                                    <td><label for="map_popup_trader">Show merchants</label></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="map_popup_reservation"
                                                        id="map_popup_reservation"></td>
                                                    <td><label for="map_popup_reservation">Show reservations</label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox"
                                                        onclick="$('#map_popup_units_home').attr('disabled', this.checked ? '' : 'disabled').attr('checked', '')"
                                                        name="map_popup_units" id="map_popup_units"></td>
                                                    <td><label for="map_popup_units">Show troops</label></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" disabled="disabled"
                                                        name="map_popup_units_home" id="map_popup_units_home"></td>
                                                    <td><label for="map_popup_units_home">Show local troops</label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="map_popup_units_times"
                                                        id="map_popup_units_times"></td>
                                                    <td><label for="map_popup_units_times">Show walking
                                                            duration</label></td>
                                                </tr>
                                                <tr>
                                                    <td><input type="checkbox" name="map_popup_notes"
                                                        id="map_popup_notes"></td>
                                                    <td><label for="map_popup_notes">Show Village Notebook</label>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </form>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div> <br>



                <form method="post" action="">
                    <table width="100%"
                        style="border-spacing: 0px; border-collapse: collapse;"
                        class="vis">
                        <tbody>
                            <tr>
                                <th colspan="3">Center map</th>
                            </tr>
                            <tr>
                                <td class="nowrap">x:&nbsp;<input type="text"
                                    onkeyup="xProcess('mapx', 'mapy')" size="5" value="<?php echo $this->village->x; ?>"
                                    class="centercoord" id="mapx" name="x"> y:&nbsp;<input
                                    type="text" size="5" value="<?php echo $this->village->y; ?>" class="centercoord"
                                    id="mapy" name="y">
                                </td>
                                <td><input type="submit" value="OK"
                                    onclick="return TWMap.focusSubmit();"></td>
                                <td width="18" style="display: none;"><img class="map-slider centercoords_toggler"
                                    src="graphic/icons/slide_down.png"></td><!-- Not implemented -->
                            </tr>
                            <tr id="centercoords" style="display: none;">
                            </tr>
                        </tbody>
                    </table>
                </form> <br>
                <table width="100%" class="vis">
                    <tbody>
                        <tr>
                            <th colspan="2">Change map size</th>
                        </tr>
                        <tr>
                            <td><table cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td width="80">Map:</td>
                                            <td><select
                                                onchange="TWMap.resize(parseInt($('#map_chooser_select').val()), true)"
                                                id="map_chooser_select">
                                                    <option style="display: none;" value="9x9"
                                                        id="current-map-size">9x9</option>
                                                    <option value="4">4x4</option>
                                                    <option value="5">5x5</option>
                                                    <option value="7">7x7</option>
                                                    <option selected="selected" value="9">9x9</option>
                                                    <option value="11">11x11</option>
                                                    <option value="13">13x13</option>
                                                    <option value="15">15x15</option>
                                                    <option value="20">20x20</option>
                                                    <option value="30">30x30</option>
                                            </select></td>
                                            <td valign="middle"><img width="13" height="13"
                                                src="graphic/questionmark.png" class="tooltip" alt=""></td>
                                        </tr>
                                    </tbody>
                                </table> <input type="hidden" id="change_map_size_link"
                                value="game.php?village=<?php echo $vid; ?>&amp;screen=settings&amp;ajaxaction=set_map_size">
                            </td>
                        </tr>
                        <tr>
                            <td><table cellspacing="0">
                                    <tbody>
                                        <tr>
                                            <td width="80">Minimap:</td>
                                            <td colspan="2"><select
                                                onchange="TWMap.resizeMinimap(parseInt($('#minimap_chooser_select').val()), true)"
                                                id="minimap_chooser_select">
                                                    <option style="display: none;" value="50x50"
                                                        id="current-minimap-size">50x50</option>
                                                    <option value="20">20x20</option>
                                                    <option value="30">30x30</option>
                                                    <option value="40">40x40</option>
                                                    <option selected="selected" value="50">50x50</option>
                                                    <option value="60">60x60</option>
                                                    <option value="70">70x70</option>
                                                    <option value="80">80x80</option>
                                                    <option value="90">90x90</option>
                                                    <option value="100">100x100</option>
                                                    <option value="110">110x110</option>
                                                    <option value="120">120x120</option>
                                            </select></td>
                                        </tr>
                                    </tbody>
                                </table> <input type="hidden" id="change_map_size_link"
                                value="game.php?village=<?php echo $vid; ?>&amp;screen=settings&amp;ajaxaction=set_map_size">
                            </td>
                        </tr>
                    </tbody>
                </table>




            </td>
        </tr>
    </tbody>
</table>
<!-- Translations -->
<input id="newbieProt"
    value="Das Ziel steht noch unter Anfangsschutz. Du darfst erst %s angreifen."
    type="hidden" />
<input id="barbarianVillage" value="Barbarendorf" type="hidden" />
<input id="pointFormat" value="%s (%s Punkte)" type="hidden" />
<input id="villageFormat" value="%name% (%x%|%y%) K%con%" type="hidden" />
<input id="villageNotes" value="Notizen" type="hidden" />
<input id="villageFavoriteAdded"
    value="Dorf wurde zu den Favoriten hinzugefügt." type="hidden" />
<input id="villageFavoriteRemoved"
    value="Dorf wurde aus den Favoriten entfernt." type="hidden" />
<input id="changesSaved" value="Änderungen wurden gespeichert."
    type="hidden" />
<input id="confirmCenterDelete"
    value="Eintrag '%name%' wirklich löschen?" type="hidden" />
<input id="troopsSent" value="Truppen wurden entsandt." type="hidden" />
<script type="text/javascript">
//<![CDATA[

$(document).ready(function() {

    MapCanvas.churchData = [[530,459,6]];
	MapCanvas.init();

    TWMap.autoPixelSize = $(window).width() - 100;
    TWMap.autoSize = Math.ceil(TWMap.autoPixelSize / TWMap.tileSize[0]);

    TWMap.size = [7, 7];

    TWMap.popup.extraInfo = false;

    TWMap.church.displayed = false;

    TWMap.init();
    TWMap.focus(<?php echo $village->x; ?>, <?php echo $village->y; ?>);

    TWMap.context.init();
    /* Resize stuff :P */
    TWMap.minimap.createResizer([20, 20], [120,120], 5);
    TWMap.map.createResizer([4,4], [30,30]);

    // Allow resize of map when iPhone/Android phone is flipped.

    if(mobile) {
        var resizeTimer = null;
        var flippingSupported = "onorientationchange" in window,
            flipEvent = flippingSupported ? "orientationchange" : "resize";

        window.addEventListener(flipEvent, function() {
            var autoSelected = (parseInt($('#map_chooser_select').val()) == 0);
            if(autoSelected) {
                if (resizeTimer === null) {
                    resizeTimer = setTimeout(function() {
                        TWMap.resize(0, false);
                        resizeTimer = null;
                    }, 500);
                }
            }
        }, false);
    }
    UI.ToolTip($('.tooltip'));
});
//]]>
</script>

<script type="text/html" id="tpl_popup">
    <table id="info_content" class="vis" style="background-color: #e5d7b2; width:auto">
<% if (bonus) { %>
    <tr id="info_bonus_image_row" >
        <td id="info_bonus_image" rowspan="14"><img src="<%= bonus.img %>" /></td>
    </tr>
<% } /* end bonus */ %>

    <tr>
        <th colspan="2"><%== '%name% (%x%|%y%) K%continent%' %></th>
    </tr>


<% if (bonus) { %>
    <tr id="info_bonus_text_row">
        <td colspan="2"><strong id="info_bonus_text"><%= bonus.text %></strong></td>
    </tr>
<% } /* end bonus */ %>

    <tr id="info_points_row">
        <td width="100px">Punkte:</td>
        <td id="info_points"><%= points %></td>
    </tr>

<% if (owner) { %>
    <tr id="info_owner_row">
        <td>Besitzer:</td>
        <td><%== '%name% (%points% Punkte)', owner %></td>
    </tr>
<% } else { %>
    <tr id="info_left_row">
        <td colspan="2">verlassen</td>
    </tr>
<% } /* end owner */ %>

<% if (ally) { %>
    <tr id="info_ally_row">
        <td>Stamm:</td>
        <td><%== '%name% (%points% Punkte)', ally %></td>
    </tr>
<% } /* end ally */ %>
<% if (extra && extra.reservation && $('#map_popup_reservation').is(":checked")) { %>
    <tr><td>Reserviert von:</td><td id="info_reserved_by"><%= extra.reservation.name %> [<%= extra.reservation.ally%>]</td></tr>
    <tr><td>Reservierung läuft ab:</td><td id="info_reserved_till"><%= extra.reservation.expires_at %></td></tr>
<% } %>
<% if (extra && extra.attack && $('#map_popup_attack').is(":checked")) { %>
    <tr>
        <td>Letzter Angriff:</td>
        <td id="info_last_attack">
            <img src="graphic/<%= TWMap.popup.attackDots[extra.attack.dot]%>?1" alt="" />
            <% if (extra.attack.dot != 4) { %>
                <img src="graphic/<%= TWMap.popup.attackMaxLoot[extra.attack.max_loot]%>?1" alt="" />
            <% } %>

            <%= extra.attack.time %>
        </td>
    </tr>
<% } %>
<% if (extra && extra.morale && $('#map_popup_moral').is(":checked") && TWMap.morale) { %>
    <tr id="info_moral_row">
        <td>Moral:</td>
        <td id="info_moral"><%= Math.round(100 * extra.morale) %>%</td>
    </tr>
<% } %>
<% if (extra && extra.groups && extra.groups.length) { %>
    <tr id="info_village_groups_row">
        <td>Gruppen:</td>
        <td id="info_village_groups"><%= extra.groups.join(', ') %></td>
    </tr>
<% } %>

<% if (owner && owner.newbie_time) { %>
    <tr id="info_newbie_protect_row">
        <td colspan="2"><%== 'Das Ziel steht noch unter Anfangsschutz. Du darfst erst %newbie_time% angreifen.', owner %></td>
    </tr>
<% } /* end newbie */ %>
<% if (extra && extra.resources && $('#map_popup_res').is(":checked")) { %>
    <tr>
        <td colspan="2">
            <table cellpadding="3" class="nowrap">
                <tr>
                    <% if (extra.resources.wood) { %>
                        <td><img src="graphic/holz.png" alt="" /> <%= extra.resources.wood %></td>
                    <% } %>
                    <% if (extra.resources.stone) { %>
                        <td><img src="graphic/lehm.png" alt="" /> <%= extra.resources.stone %></td>
                    <% } %>
                    <% if (extra.resources.iron) { %>
                        <td><img src="graphic/eisen.png" alt="" /> <%= extra.resources.iron %></td>
                    <% } %>
                    <% if (extra.resources.max) { %>
                        <td><img src="graphic/res.png" alt="" /> <%= extra.resources.max %></td>
                    <% } %>
                </tr>
            </table>
        </td>
    </tr>
<% } %>
<%
  var showPopulation = extra && extra.population && $('#map_popup_pop').is(":checked");
  var showTrader = extra && extra.trader && $('#map_popup_trader').is(":checked");
%>

<% if (showPopulation || showTrader) { %>
    <tr>
        <% if (showPopulation && showTrader) { %>
        <td>
        <% } else { %>
        <td colspan="2">
        <% } %>

        <% if (showPopulation) { %>
            <img src="graphic/face.png" alt="" /> <%= extra.population.current %>/<%= extra.population.max %>
        <% } %>

        <% if (showPopulation && showTrader) { %> </td><td> <% } %>

        <% if (showTrader) { %>
            <img src="graphic/overview/trader.png" alt="" /> <%= extra.trader.current %>/<%= extra.trader.total %>
        <% } %>
        </td>
    </tr>
<% } %>
<%
  var bg_colors = ['F8F4E8', 'DED3B9'];
  if (units.length > 0) {
%>
    <tr>
        <td colspan="2">
            <table style="border:1px solid #DED3B9" width="100%" cellpadding="0" cellspacing="0">
                <tr class="center">
                    <% for (var i = 0; i < units.length; i++) { %>
                    <td style="padding:2px;background-color:#<%= bg_colors[i%2] %>">
                        <img src="graphic/<%= units[i].image %>?1" alt="" />
                    </td>
                    <% } %>
                </tr>

                <% if (units_display.count) { %>
                <tr class="center">
                    <% for (var i = 0; i < units.length; i++) { %>
                    <td style="padding:2px;background-color:#<%= bg_colors[i%2] %>">
                        <%= units[i].count %>
                    </td>
                    <% } %>
                </tr>
                <% } /* end unit count */ %>
                <% if (units_display.time) { %>
                <tr class="center">
                    <% for (var i = 0; i < units.length; i++) { %>
                    <td style="padding:2px;background-color:#<%= bg_colors[i%2] %>">
                        <%= units[i].time %>
                    </td>
                    <% } %>
                </tr>
                <% } /* end unit times */ %>

            </table>
        </td>
    </tr>
<%
  } /* end units */
%>
<% if (extra && extra.notes && $('#map_popup_notes').is(':checked')) { %>
    <tr>
        <td colspan="2"><hr /><u>Notizen:</u><%= extra.notes %></td>
    </tr>
<% } /* end notes */ %>
<% if (extra === false) { %>
    <tr>
        <td colspan="2"><table><tr><td><img src="graphic/throbber.gif" alt="" /></td><td>Lade Informationen ...</td></tr></table></td>
    </tr>
<% } %>
</table>
</script>
<div id="map_popup" class="nowrap"
    style="position: absolute; top: 0px; left: 0px; min-width: 150px; display: none; z-index: 19; direction: ltr;">
</div>
