<?php
namespace Twlan;
?>
<table class="vis modemenu">
	<tbody>
        <?php foreach($_modes as $m) { ?>
        <tr><td <?php if($mode == $m) echo 'class="selected"'; ?> style="min-width: 80px"><a href="game.php?village=<?php echo $vid; ?>&amp;mode=<?php echo $m; ?>&amp;screen=snob"><?php l('buildingSnobNavi.'.$m); ?> </a></td></tr>
        <?php } ?>
    </tbody>
</table>
