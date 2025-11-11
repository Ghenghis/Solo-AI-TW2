<?php 
namespace Twlan;
use Twlan\framework\Text;
if(!isset($partial)) { ?>
<script type="text/javascript">
    //<![CDATA[
    if(window.opera) { $("#content_value").css("overflow", "hidden"); }
    // This not bold thing is some kind of hack, because a tooltip contains a title and a body.
    // If they are not splitted like the most tooltips everything is handeld as title and printed bold
    // when using the bodyHandler function the title is removed, but i just return the normal tooltip text
    // the non-bold class is just copyed from previous solutuon
    $(function() { UI.ToolTip( $('.village_note'), { bodyHandler: function() { return this.tooltipText; }, extraClass: "not-bold" } ); } );
    //]]>
</script>
<input type="hidden" id="overview" value="<?php echo $mode;?>" />
<table class="vis modemenu" width="100%" id="overview_menu">
    <tr>
        <?php foreach($modes as $_mode) {?>
        <td style="text-align:center" <?php if($mode == $_mode) echo 'class="selected"';?> width="100">
            <a href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=<?php echo $_mode;?>"><?php l('game.menu.overview.'.$_mode);?></a>
        </td>
        <?php }?>
    </tr>
</table>
<br />
<?php } ?>
<div id="paged_view_content">
    <div class="vis_item" align="center">
        <?php foreach($this->user->getGroups() as $item) { 
                $type = (isset($group) && $group->id_group == $item->id_group) ? 'strong' : 'a'; 
                $bracket = $type == 'strong' ? array('&gt;', '&lt;') : array('[', ']');
            ?>
            <<?php echo $type;?> title="Dörfer: 3" class="group_tooltip" <?php if($type == 'a') {
                ?>href="game.php?village=<?php echo $vid;?>&amp;mode=<?php echo $mode;?>&amp;screen=overview_villages&amp;group=<?php echo $item->id_group; 
                ?>"<?php }?>>
                <?php echo $bracket[0].Text::formatAll($item->name).$bracket[1];?>
            </<?php echo $type;?>>
        <?php } 
        $type = !isset($group) ? 'strong' : 'a'; 
        $bracket = $type == 'strong' ? array('&gt;', '&lt;') : array('[', ']');
        ?>
        <<?php echo $type;?> title="Dörfer: 1791" <?php if($type == 'a') {
            ?>href="game.php?village=<?php echo $vid;?>&amp;mode=<?php echo $mode;?>&amp;screen=overview_villages" <?php }?> class="group_tooltip">
            <?php echo $bracket[0].ll('game.groups.all').$bracket[1];?>
        </<?php echo $type;?>>
    </div>
    <table class="vis" width="100%">
        <tr>
            <td align="center">
            <?php $steps = 3; $pages = max(1, ($items / $perSite));  if($page != 0 && $page-$steps>0) { ?>
                <a class="paged-nav-item" href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=<?php echo $mode;?>&amp;group=0&amp;page=0">
                    [1] 
                </a>
                <span>...</span>
            <?php } ?>
            <?php if($pages>1) for($c = $page - $steps; $c <= $page + $steps && $c < $pages; ++$c) {
                if ($c < 0) continue;
                if ($c != $page) { ?>
                    <a class="paged-nav-item" href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=<?php echo $mode;?>&amp;group=0&amp;page=<?php echo $c; ?>">
                        [<?php echo $c+1; ?>] 
                    </a>
                <?php } else { ?>
                    <strong>&gt;<?php echo $c+1;?>&lt;</strong>
                <?php } ?>
            <?php } ?>
            <?php if ($page != $pages-1 && $pages-1 > $page + $steps) { ?>
                <span>...</span>
                <a class="paged-nav-item" href="game.php?village=<?php echo $vid;?>&amp;screen=overview_villages&amp;mode=<?php echo $mode;?>&amp;group=0&amp;page=<?php echo $pages-1; ?>">
                    [<?php echo $pages; ?>] 
                </a>
            <?php } ?>
            </td>
        </tr>
    </table>
    <?php if(!isset($partial)) { ?>
    <script type="text/javascript">
    $(function(){
        VillageGroups.initOverviews();
    });
    </script>
    <?php } ?>