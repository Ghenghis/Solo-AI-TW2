<?php
namespace TWLan;
use TWLan\framework\Router;
use TWLan\framework\Text;
use TWLan\Model\World\Event\Army as ArmyEvent;
use TWLan\ORM\Condition;
if(isset($partial)) { echo $content; exit; }?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?php echo Text::formatAll($village->name);?> (<?php echo $village->x;?>|<?php echo $village->y;?>) - <?php l('index.twlan');?> - <?php echo $this->world->getConfig('world.name');?></title>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="game.css" />
        <script type="text/javascript" src="game.js"></script>
        <script type="text/javascript">
            <?php require('game_init.php'); ?>
        </script>
        <?php foreach($meta['css'] as $value){?>
        <link rel="stylesheet" type="text/css" href="<?php echo $value;?>" />
        <?php }foreach($meta['js'] as $value){?>
        <script type="text/javascript" src="<?php echo $value;?>"></script>
        <?php }?>
    </head>
    <body id="ds_body" class=" scrollableMenu">
        <div class="top_bar">
            <div class="bg_left"></div>
            <div class="bg_right"></div>
        </div>
        <div class="top_shadow"></div>
        <div class="top_background"></div>
        <table id="main_layout" cellspacing="0">
            <tr style="height:48px;">
                <td class="topbar left"></td>
                <td class="topbar center">
                    <div id="topContainer">
                        <table id="topTable" style="text-align:center;" cellspacing="0">
                            <tr>
                                <td style="text-align:center;">
                                    <table class="menu nowrap" style="white-space:nowrap; ">
                                        <tr id="menu_row">
                                            <td class="menu-side"></td>
                                            <td class="menu-item">
                                                <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages"><?php l('game.menu.overview.title');?></a>
                                                <table cellspacing="0" class="menu_column">
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=combined"><?php l('game.menu.overview.combined');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=prod"><?php l('game.menu.overview.prod');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=trader"><?php l('game.menu.overview.trader');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=units"><?php l('game.menu.overview.units');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=commands"><?php l('game.menu.overview.commands');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=incomings"><?php l('game.menu.overview.incomings');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=buildings"><?php l('game.menu.overview.buildings');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=tech"><?php l('game.menu.overview.tech');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=groups"><?php l('game.menu.overview.groups');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bottom">
                                                            <div class="corner"></div>
                                                            <div class="decoration"></div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td class="menu-item">
                                                <a id="menu_map_link" href="game.php?village=<?php echo $vid;?>&amp;screen=map"><?php l('game.menu.map');?></a>
                                            </td>
                                            <td class="menu-item">
                                                <a href="game.php?village=<?php echo $vid; ?>&amp;screen=report">
                                                    <?php $newReports = $gameData['player']['new_report']; $cl = $newReports ? '' : '_faded'; ?>
                                                    <span id="new_report" class="icon header new_report<?php echo $cl; ?>"></span>
                                                    <?php l('game.menu.reports.title'); ?>
                                                    <span id="menu_report_count" class="badge badge-report">
                                                        <?php echo $cl ? '' : '('.$newReports.')'; ?>
                                                    </span>
                                                </a>
                                                <table cellspacing="0" class="menu_column">
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=report&amp;mode=all"><?php l('game.menu.reports.all');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=report&amp;mode=attack"><?php l('game.menu.reports.attack');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=report&amp;mode=defense"><?php l('game.menu.reports.defense');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=report&amp;mode=support"><?php l('game.menu.reports.support');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=report&amp;mode=trade"><?php l('game.menu.reports.trade');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=report&amp;mode=other"><?php l('game.menu.reports.other');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=report&amp;mode=forwarded"><?php l('game.menu.reports.forwarded');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=report&amp;mode=filter"><?php l('game.menu.reports.filter');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=block"><?php l('game.menu.reports.block');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=report&amp;mode=public"><?php l('game.menu.reports.public');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bottom">
                                                            <div class="corner"></div>
                                                            <div class="decoration"></div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td class="menu-item">
                                                <a href="game.php?village=<?php echo $vid;?>&amp;screen=mail">
                                                <?php $newMails = $gameData['player']['new_igm']; if($newMails){?>
                                                <span class="icon header new_mail" title="<?php l('game.menu.mails.iNew');?>"></span>
                                                <?php }l('game.menu.mails.title');?></a>
                                                <table cellspacing="0" class="menu_column">
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=mail&amp;mode=in"><?php l('game.menu.mails.in');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=mail&amp;mode=mass_out"><?php l('game.menu.mails.massOut');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=mail&amp;mode=new"><?php l('game.menu.mails.new');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=block"><?php l('game.menu.mails.block');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=mail&amp;mode=address"><?php l('game.menu.mails.address');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=mail&amp;mode=groups"><?php l('game.menu.mails.groups');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bottom">
                                                            <div class="corner"></div>
                                                            <div class="decoration"></div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td class="menu-item lpad"></td>
                                            <td class="menu-item" id="topdisplay">
                                                <div class="bg">
                                                    <a href="game.php?village=<?php echo $vid;?>&amp;screen=ranking"><?php l('game.menu.ranking.title');?></a>
                                                    <div class="rank">(<span id="rank_rank"><?php echo $gameData['player']['rank_formatted']; ?></span>.|<span id="rank_points"><?php echo $gameData['player']['points_formatted']; ?></span> 
                                                    <?php l('game.menu.ranking.points'); ?>)</div>
                                                    <table cellspacing="0" class="menu_column">
                                                        <tr>
                                                            <td class="menu-column-item">
                                                                <a href="game.php?village=<?php echo $vid;?>&amp;screen=ranking&amp;mode=ally"><?php l('game.menu.ranking.ally');?></a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="menu-column-item">
                                                                <a href="game.php?village=<?php echo $vid;?>&amp;screen=ranking&amp;mode=player"><?php l('game.menu.ranking.player');?></a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="menu-column-item">
                                                                <a href="game.php?village=<?php echo $vid;?>&amp;screen=ranking&amp;mode=con_ally"><?php l('game.menu.ranking.conAlly');?></a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="menu-column-item">
                                                                <a href="game.php?village=<?php echo $vid;?>&amp;screen=ranking&amp;mode=con_player"><?php l('game.menu.ranking.conPlayer');?></a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="menu-column-item">
                                                                <a href="game.php?village=<?php echo $vid;?>&amp;screen=ranking&amp;mode=kill_ally"><?php l('game.menu.ranking.killAlly');?></a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="menu-column-item">
                                                                <a href="game.php?village=<?php echo $vid;?>&amp;screen=ranking&amp;mode=kill_player"><?php l('game.menu.ranking.killPlayer');?></a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="menu-column-item">
                                                                <a href="game.php?village=<?php echo $vid;?>&amp;screen=ranking&amp;mode=wars"><?php l('game.menu.ranking.wars');?></a>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bottom">
                                                                <div class="corner"></div>
                                                                <div class="decoration"></div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </td>
                                            <td class="menu-item">
                                                <?php $ally = $gameData['player']['new_forum_post'];  ?>
                                                <a href="game.php?village=<?php echo $vid;?>&amp;screen=ally"><?php if($ally){?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php }l('game.menu.ally.title');?></a>
                                                <?php if($ally) {?>
                                                <div class="buttonicon">
                                                    <a href="game.php?village=<?php echo $vid;?>&amp;screen=ally&amp;mode=forum" style="display:inline">
                                                        <span class="icon header <?php if(!$ally){?>no_<?php }?>new_post" title="<?php l('game.menu.ally.i_'.($ally ? 'old' : 'none'));?>"></span>
                                                    </a>
                                                </div>
                                                <table cellspacing="0" class="menu_column">
                                                    <?php foreach(Controllers\Game\Ally::getModes($user) as $mode) { ?>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=ally&amp;mode=<?php echo $mode;?>"><?php l('game.ally.navi.'.$mode);?></a>
                                                        </td>
                                                    </tr>
                                                    <?php } ?>
                                                    <tr>
                                                        <td class="bottom">
                                                            <div class="corner"></div>
                                                            <div class="decoration"></div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <?php }?>
                                            </td>
                                            <td class="menu-item rpad"></td>
                                            <td class="menu-item">
                                            <a href="game.php?village=<?php echo $vid; ?>&amp;screen=info_player">
                                                <?php l('game.menu.profile.title'); ?> <span id="menu_counter_profile" class="badge"></span>
                                            </a>
                                            <table cellspacing="0" class="menu_column">
                                                <tbody>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid; ?>&amp;screen=info_player">
                                                            <?php echo Text::formatAll($user->global->name); ?><span class="badge "> </span>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid; ?>&amp;screen=inventory">
                                                            <?php l('game.menu.profile.inventory'); ?><span class="badge badge-inventory"> </span></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid; ?>&amp;mode=awards&amp;screen=info_player">
                                                            <?php l('game.menu.profile.achievements'); ?><span class="badge "> </span></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid; ?>&amp;mode=stats_own&amp;screen=info_player">
                                                            <?php l('game.menu.profile.statistics'); ?><span class="badge "> </span></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid; ?>&amp;screen=buddies">
                                                            <?php l('game.menu.profile.friends'); ?><span class="badge badge-buddy"> </span></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid; ?>&amp;mode=block&amp;screen=info_player">
                                                            <?php l('game.menu.profile.blockList'); ?><span class="badge "> </span></a
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bottom"><div class="corner"></div><div class="decoration"></div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                            <td class="menu-item">
                                                <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings"><?php l('game.menu.settings.title');?></a>
                                                <table cellspacing="0" class="menu_column">
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=profile"><?php l('game.menu.settings.profile');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=settings"><?php l('game.menu.settings.settings');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=change_passwd"><?php l('game.menu.settings.changePasswd');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=delete"><?php l('game.menu.settings.delete');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=notify"><?php l('game.menu.settings.notify');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=quickbar"><?php l('game.menu.settings.quickbar');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=logins"><?php l('game.menu.settings.logins');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=poll"><?php l('game.menu.settings.poll');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=toolbar"><?php l('game.menu.settings.toolbar');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=push"><?php l('game.menu.settings.push');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=block"><?php l('game.menu.settings.block');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="menu-column-item">
                                                            <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=ticket" target="_blank"><?php l('game.menu.settings.ticket');?></a>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="bottom">
                                                            <div class="corner"></div>
                                                            <div class="decoration"></div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                            <td class="menu-side"><img src="graphic/loading.gif" id="loading_content" style="display: none" alt="" class=""></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td class="topbar right"></td>
            </tr>
            <tr class="shadedBG">
                <td class="bg_left" id="SkyScraperAdCellLeft">
                    <div class="bg_left"></div>
                </td>
                <td class="maincell" style="width: 850px;">
                    <br class="newStyleOnly" />
                    <?php if(count($quickbar) > 0){?>
                    <table id="quickbar_outer" align="center" width="100%" cellspacing="0">
                        <tr>
                            <td>
                                <table id="quickbar_inner" style="border-collapse:collapse;" width="100%">
                                    <tr class="topborder">
                                        <td class="left"></td>
                                        <td class="main"></td>
                                        <td class="right"></td>
                                    </tr>
                                    <tr>
                                        <td class="left"></td>
                                        <td class="main">
                                            <ul class="menu quickbar">
                                                <?php foreach($quickbar as $value){if(strlen($value['url']) > 0){?>
                                                <li>
                                                    <span><a href="<?php echo $value['url'];?>"<?php if($value['new_window']){echo ' target="_blank"';}?>><?php if(strlen($value['image']) > 0){?><img src="<?php echo $value['image'];?>" alt="<?php echo $value['name'];?>" /><?php }echo $value['name'];?></a></span>
                                                </li>
                                                <?php }else{?>
                                            </ul>
                                            <ul class="menu nowrap quickbar">
                                                <?php }}?>
                                            </ul>
                                        </td>
                                        <td class="right"></td>
                                    </tr>
                                    <tr class="bottomborder">
                                        <td class="left"></td>
                                        <td class="main"></td>
                                        <td class="right"></td>
                                    </tr>
                                    <tr>
                                        <td class="shadow" colspan="3">
                                            <div class="leftshadow"></div>
                                            <div class="rightshadow"></div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <?php }?>
                    <hr class="oldStyleOnly" />
                    <table id="header_info" align="center" width="100%" cellspacing="0">
                        <colgroup>
                            <col width="1%" />
                            <col width="96%" />
                            <col width="1%" />
                            <col width="1%" />
                            <col width="1%" />
                        </colgroup>
                        <tr>
                            <td class="topAlign">
                                <table class="header-border">
                                    <tr>
                                        <td>
                                            <table class="box menu nowrap">
                                                <tr id="menu_row2">
                                                    <?php if($gameData["player"]["villages"] > 1){ /* TODO: Not implemented village arrows based on group traversal */ if(false){?>
                                                    <td class="box-item icon-box separate arrowCell">
                                                        <span class="groupJump">
                                                            <a class="jump_link" href="game.php?village=j<?php echo $vid; ?>&amp;screen=ranking">
                                                                <img src="graphic/blank-16x22.png?1" title="<?php l('game.title.firstVillage');?>" alt="<?php l('game.title.firstVillage');?>" />
                                                            </a>
                                                        </span>
                                                    </td>
                                                    <?php }
                                                        $tmp = $_GET;
                                                        $tmp['village'] = 'p'.$vid;
                                                    ?>
                                                    <td class="box-item icon-box arrowCell">
                                                        <a id="village_switch_left" class="village_switch_link" href="game.php?<?php echo http_build_query($tmp); ?>" accesskey="a">
                                                            <span class="arrowLeft"></span>
                                                        </a>
                                                    </td>
                                                    <td class="box-item icon-box arrowCell">
                                                        <a id="village_switch_right" class="village_switch_link" href="game.php?<?php 
                                                            $tmp['village'] = 'n'.$vid; 
                                                            echo http_build_query($tmp); 
                                                        ?>" accesskey="d">
                                                            <span class="arrowRight"></span>
                                                        </a>
                                                    </td>
                                                    <?php }?>
                                                    <td style="white-space:nowrap;" id="menu_row2_village" class="box-item icon-box nowrap">
                                                        <a class="nowrap" href="game.php?village=<?php echo $vid;?>&amp;screen=overview"><span class="icon header village"></span><?php echo Text::formatAll($village->name); ?></a>
                                                    </td>
                                                    <td class="box-item">
                                                        <b class="nowrap">(<?php echo $village->x; ?>|<?php echo $village->y; ?>) <?php echo $village->getContinent(); ?></b>
                                                    </td>
                                                    <script type="text/javascript">
                                                    //<![CDATA[
                                                        villageDock.saveLink = 'game.php?village=<?php echo $vid; ?>&ajaxaction=dockVillagelist&screen=overview';
                                                        villageDock.loadLink = '<?php echo Router::getRelativePath(); ?>?village=<?php echo $vid; ?>&mode=overview&ajax=load_group_menu&screen=groups';
                                                        villageDock.docked = 0;

                                                        $(function() {
                                                            if(villageDock.docked) {
                                                                villageDock.open();
                                                            }
                                                        });
                                                    //]]>
                                                    </script>
                                                    <td class="box-item">
                                                        <a href="#" id="open_groups" onclick="return villageDock.open(event);">
                                                            <span class="icon header arr_down"></span>
                                                        </a>
                                                        <a href="#" id="close_groups" onclick="return villageDock.close(event);" style="display:none;">
                                                            <span class="icon header arr_up"></span>
                                                        </a>
                                                        <input type="hidden" id="popup_close" value="<?php l('game.title.close');?>" />
                                                        <input type="hidden" value="game.php?village=<?php echo $vid;?>&amp;screen=groups&amp;mode=overview&amp;ajax=load_villages_from_group" id="show_groups_villages_link" />
                                                        <input type="hidden" value="game.php?screen=ranking" id="village_link" />
                                                        <input type="hidden" value="overview" id="group_popup_mode" />
                                                        <input type="hidden" value="<?php l('game.title.group');?>" id="group_popup_select_title" />
                                                        <input type="hidden" value="<?php l('game.title.village');?>" id="group_popup_villages_select" />
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="newStyleOnly">
                                        <td class="shadow">
                                            <div class="leftshadow"></div>
                                            <div class="rightshadow"></div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td align="right" class="topAlign"></td>
                            <td align="right" class="topAlign">
                                <table align="right" class="header-border menu_block_right">
                                    <tr>
                                        <td>
                                            <table class="box smallPadding" cellspacing="0" style="empty-cells:show;">
                                                <tr style="height:20px;">
                                                    <?php $first = TRUE; foreach($this->world->getPhysicalResources() as $res){?>
                                                    <td class="box-item<?php if($first){$first = FALSE;echo ' icon-box firstcell';}?>">
                                                        <a href="game.php?village=<?php echo $vid;?>&amp;screen=<?php echo $res;?>" title="<?php l('game.'.$res);?>">
                                                            <span class="icon header <?php echo $res;?>"></span>
                                                        </a>
                                                    </td>
                                                    <td class="box-item">
                                                        <span id="<?php echo $res;?>" title="<?php echo $village->getResProduction($res);?>" class="<?php echo ($village->getRes($res) >= $village->getMaxRes($res)) ? 'warn' : ($village->getRes($res) >= $village->getMaxRes($res) * 0.9 ? 'warn_90' : 'res');?>"><?php echo floor($village->getRes($res));?></span>
                                                    </td>
                                                    <?php }?>
                                                    <td class="box-item icon-box">
                                                        <a href="game.php?village=<?php echo $vid;?>&amp;screen=storage" title="<?php l('game.storage');?>">
                                                            <span class="icon header ressources"></span>
                                                        </a>
                                                    </td>
                                                    <td class="box-item"><span id="storage"><?php echo $village->getMaxRes('wood');?></span></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="newStyleOnly">
                                        <td class="shadow">
                                            <div class="leftshadow"></div>
                                            <div class="rightshadow"></div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <td align="right" class="topAlign">
                                <table class="header-border menu_block_right">
                                    <tr>
                                        <td>
                                            <table class="box smallPadding" cellspacing="0">
                                                <tr>
                                                    <td class="box-item icon-box firstcell">
                                                        <a href="game.php?village=<?php echo $vid;?>&amp;screen=farm" title="<?php l('buildingFarm.name');?>">
                                                            <span class="icon header population"></span>
                                                        </a>
                                                    </td>
                                                    <td class="box-item" align="center" style="margin:0;padding:0;">
                                                        <span id="pop_current_label"><?php echo $gameData["village"]["pop"]; ?></span>/<span id="pop_max_label"><?php echo $gameData["village"]["pop_max"];?></span>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="newStyleOnly">
                                        <td class="shadow">
                                            <div class="leftshadow"></div>
                                            <div class="rightshadow"></div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <?php if($this->world->getConfig('world.flagsActive') == 'true'){?>
                            <td align="right" class="topAlign">
                                <table class="header-border menu_block_right" style="border-collapse: collapse;">
                                    <tr>
                                        <td>
                                            <table class="box" cellspacing="0">
                                                <tr>
                                                    <td class="box-item firstcell"><a title="<?php l('game.menu.flags');?>" href="game.php?village=<?php echo $vid;?>&amp;screen=flags"><span class="icon header flags"></span></a></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="newStyleOnly">
                                        <td class="shadow">
                                            <div class="leftshadow"> </div>
                                            <div class="rightshadow"> </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <?php } if($this->world->getConfig('world.knightActive')) {?>
                            <td align="right" class="topAlign">
                                <table class="header-border menu_block_right" style="border-collapse:collapse;">
                                    <tr>
                                        <td>
                                            <table class="box" cellspacing="0">
                                                <tr>
                                                    <td class="box-item firstcell">
                                                        <a title="<?php l('unitKnight.name');?>" href="game.php?village=<?php echo $vid; ?>&amp;screen=statue<?php if ($this->world->getConfig('world.knight_items')) {?>&amp;mode=inventory<?php }?>">
                                                            <span class="icon header knight"></span>
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr class="newStyleOnly">
                                        <td class="shadow">
                                            <div class="leftshadow"></div>
                                            <div class="rightshadow"></div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <?php }
                            $incoming_att = 0;
                            $incoming_sup = 0;
                            $attackType = array_search('attack', ArmyEvent::movementTypes());
                            $supportType = array_search('support', ArmyEvent::movementTypes());
                            foreach ($incomingArmies as $army) {
                                $type = $army->movement_type;
                                if ($type == $attackType)      ++$incoming_att;
                                elseif ($type == $supportType) ++$incoming_sup;
                            }
                           
                            if ($incoming_att || $incoming_sup) { ?>
                            <td class="topAlign">
                                <table class="header-border menu_block_right">
                                    <tr>
                                        <td>
                                            <table class="box smallPadding no-gap" cellspacing="0">
                                                <tr>
                                                    <?php if ($incoming_att) { ?>
                                                    <td height="20" align="center" class="box-item firstcell">
                                                        <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=incomings&amp;subtype=attacks">
                                                            <img src="graphic/unit/att.png" title="<?php l('game.title.attacks');?>" alt="" />
                                                        </a>
                                                    </td>
                                                    <td class="box-item">
                                                        <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=incomings&amp;subtype=attacks">
                                                            (<?php echo $incoming_att; ?>)
                                                        </a>
                                                    </td>
                                                    <?php } if($incoming_sup) { ?>
                                                    <td height="20" align="center" class="box-item separate">
                                                        <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=incomings&amp;subtype=supports">
                                                            <img src="graphic/command/support.png" title="<?php l('game.title.supports');?>" alt="" />
                                                        </a>
                                                    </td>
                                                    <td class="box-item">
                                                        <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=incomings&amp;subtype=supports">
                                                            (<?php echo $incoming_sup; ?>)
                                                        </a>
                                                    </td>
                                                    <?php } ?>
                                                </tr>
                                            </table>

                                        </td>

                                    </tr>
                                    <tr class="newStyleOnly">
                                        <td class="shadow">
                                            <div class="leftshadow"></div>
                                            <div class="rightshadow"></div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <?php } ?>
                        </tr>
                    </table>
                    <table align="center" id="contentContainer" width="100%">
                        <tr>
                            <td>
                                <table class="content-border" width="100%" cellspacing="0">
                                    <tr>
                                        <td id="inner-border">
                                            <table class="main" align="left">
                                                <tr>
                                                    <td id="content_value">
                                                    <?php if(isset($error)){?>
                                                    <div class="error_box">
														<?php echo $error;?>
													</div>
                                                    <?php } ?>
                                                    <?php /* DONT MODIFY THIS NOW !!! */ ?>
                                                    <div id="content_point"></div>
                                                    <?php /*END_DONT_MODIFY*/ ?>
                                                        <?php echo $content;?>
                                                    <?php if(isset($unitPopupData)) $this->viewPartial('unit_popup'); ?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <p class="server_info"><?php l('game.genTime', array('time'=>TWLan::generationTime())); ?>
                        <b>|</b>
                        <?php l('game.servertime');?>
                        <span id="serverTime"><?php echo date(ll('game.time'), time());?></span> - <span id="serverDate"><?php echo date(ll('game.date'));?></span>
                    </p>
                    <p></p>
                </td>
                <td class="bg_right" id="SkyScraperAdCell">
                    <div class="bg_right"></div>
                </td>
            </tr>
            <tr>
                <td class="bg_leftborder"></td>
                <td></td>
                <td class="bg_rightborder"></td>
            </tr>
            <tr class="newStyleOnly">
                <td class="bg_bottomleft">&nbsp;</td>
                <td class="bg_bottomcenter">&nbsp;</td>
                <td class="bg_bottomright">&nbsp;</td>
            </tr>
        </table>
        <div id="footer">
            <div id="footer_logo"></div>
            <div id="linkContainer">
                <div id="footer_left">
                    <a href="http://www.twlan.org/" target="_blank"><?php l('index.twlanLink');?></a>
                    &nbsp;-&nbsp;
                    <a href="game.php?village=<?php echo $vid;?>&amp;screen=settings&amp;mode=ticket" target="_blank"><?php l('game.support');?></a>
                    &nbsp;-&nbsp;
                    <a href="game.php?village=<?php echo $vid;?>&amp;screen=buddies"><?php l('game.buddies');?></a>
                    &nbsp;-&nbsp;
                    <a href="game.php?village=<?php echo $vid;?>&amp;action=logout" target="_top"><?php l('game.menu.logout');?></a>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function() {
                TribalWars.initTab('4b9a3af330');
                Timing.init(<?php echo microtime(true); ?>);
                WorldSwitch.init();
                WorldSwitch.worldsURL = 'game.php?village=<?php echo $vid; ?>&ajax=world_switch&screen=api';
                HotKeys.init();
                //Connection.connect(8081, 'c0efdef86b3f');
                UI.Notification.enabled = true;
            });
        </script>
    </body>
</html>
