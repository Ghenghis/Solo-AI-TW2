<?php namespace Twlan; ?>
<table width="100%">
    <tr>
        <td>
            <img src="graphic/big_buildings/<?php echo $building->getImage($village->getBuilding($building->id));?>" alt="<?php echo $building->getLocalizedId(); ?>" />
        </td>
        <td width="100%">
            <h2>
                <?php echo $building->getLocalizedId(); ?>
                (<?php $lvl = $village->getBuilding($building->id); echo $lvl > 0 ? ll('building.level', array('x'=>$lvl)) : ll('building.noLevel');?>)
            </h2>
            <?php echo $building->getLocalizedDescription(); ?>
        </td>
    </tr>
</table>
<?php if($lvl > 0){?>
<br />
<table cellpadding="0" cellspacing="0" width="100%">
    <?php if(count($modes) >= 2){?>
    <tr>
        <td valign="top">
            <table class="vis modemenu">
                <tr>
                    <?php foreach($modes as $m){?>
                    <td <?php if($m == $mode){echo 'class="selected" ';}?>width="100">
                        <a href="game.php?village=<?php echo $vid;?>&amp;screen=<?php echo $building->id;?>&amp;mode=<?php echo $m;?>"><?php l($modeLangPath.'.mode'.ucfirst($m));?> </a>
                    </td>
                    <?php }?>
               </tr>
            </table>
        </td>
    </tr>
    <?php } ?>
</table>
<?php echo $content;}?>