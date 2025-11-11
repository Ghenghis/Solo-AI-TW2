<?php //TODO: implement some of this options ?>
var image_base = "graphic";
var server_utc_diff = <?php echo date('Z'); ?>;
var mobile = false;
var mobile_on_normal = false;
var mobiledevice = false;
var game_data = <?php echo json_encode($gameData); ?>;
VillageContext._urls.overview = 'game.php?village=__village__&screen=overview';
VillageContext._urls.info = 'game.php?village=<?php echo $vid; ?>&id=__village__&screen=info_village';
VillageContext._urls.fav = 'game.php?village=<?php echo $vid; ?>&id=__village__&ajaxaction=add_target&json=1&screen=info_village';
VillageContext._urls.unfav = 'game.php?village=<?php echo $vid; ?>&id=__village__&ajaxaction=del_target&json=1&screen=info_village';
VillageContext._urls.claim = 'game.php?village=<?php echo $vid; ?>&id=__village__&ajaxaction=toggle_reserve_village&json=1&screen=info_village';
VillageContext._urls.market = 'game.php?village=<?php echo $vid; ?>&mode=send&target=__village__&screen=market';
VillageContext._urls.place = 'game.php?village=<?php echo $vid; ?>&target=__village__&screen=place';
VillageContext._urls.recruit = 'game.php?village=__village__&screen=train';
VillageContext._urls.map = 'game.php?village=<?php echo $vid; ?>&id=__village__&screen=map';
VillageContext._urls.unclaim = VillageContext._urls.claim;
VillageContext.claim_enabled = false;
VillageContext.igm_enabled = true;
VillageContext.send_troops_enabled = true;
VillageContext.send_attack_disabled = false;
       

$(document).ready( function() {
    UI.ToolTip( $( '.group_tooltip' ), { delay: 1000 } );
    VillageContext.init();
});
