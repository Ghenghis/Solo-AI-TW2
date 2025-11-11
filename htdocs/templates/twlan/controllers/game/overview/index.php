<?php
namespace Twlan;
?>
<table cellspacing="0" cellpadding="0" id="overviewtable" align="center">
    <tr>
        <?php foreach($order as $col => $array){?>
        <td valign="top" id="<?php echo $col;?>" width="620">
            <?php foreach($array as $widget => $attrib){ 
                    ob_start();
                    require($widget.'.php');
                    $widgetContent = ob_get_contents();
                    ob_end_clean();
                    if (!$widgetContent) continue;
                    $state = $attrib['visible'] ? array('img'=>'minus', 'dis'=>'block') : array('img'=>'plus', 'dis'=>'none');?>
            <div id="show_<?php echo $widget;?>" class="vis moveable <?php if(!$attrib['active']){echo 'hidden_';}?>widget"<?php if(!$attrib['active']){echo ' style="'.$hidden.'"';}?>>
                <h4>
                    <?php if($attrib['active']){?>
                    <img style="float: right; cursor: pointer;" onclick="return VillageOverview.toggleWidget('show_<?php echo $widget;?>', this);" src="graphic/<?php echo $state['img'];?>.png" alt="" />
                    <?php }l('game.overview.widgets.'.$widget);?>
                </h4>
                <?php if($attrib['active']){?>
                <div style="display: <?php echo $state['dis'];?>;">
                    <?php echo $widgetContent; ?>
                </div>
                <?php }?>
            </div>
            <?php }?>
        </td>
        <?php }?>
    </tr>
</table>
<table style="width: 100%; text-align: center;">
    <tr>
        <td style="text-align: left;">
            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview&amp;action=reset"><?php l('game.overview.reset');?></a>
        </td>
    </tr>
</table>
<script type="text/javascript">
    //<![CDATA[
    $(function()
    {
        VillageOverview.urls.reorder = 'game.php?village=<?php echo $vid;?>&screen=overview&ajaxaction=reorder';
        VillageOverview.urls.toggle = 'game.php?village=<?php echo $vid;?>&screen=overview&ajaxaction=toggle';
        VillageOverview.urls.show = 'game.php?village=<?php echo $vid;?>&screen=overview&ajaxaction=show';
        VillageOverview.init();
    });
    //]]>
</script>
