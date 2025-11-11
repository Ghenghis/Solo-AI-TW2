<?php
namespace Twlan;
use Twlan\framework\Router;
?>
<script type="text/javascript" src="game/building_smith.js"></script>
<?php $relPath = Router::getRelativePath(); ?>
<script type="text/javascript">
  //<![CDATA[
  BuildingSmith.link_research = "<?php echo $relPath; ?>?village=<?php echo $vid; ?>&ajaxaction=research&screen=smith";
  BuildingSmith.link_cancel = "<?php echo $relPath; ?>?village=<?php echo $vid; ?>&ajaxaction=cancel&screen=smith";
  BuildingSmith.link_remove = "<?php echo $relPath; ?>?village=<?php echo $vid; ?>&ajaxaction=remove&screen=smith";
  //]]>
</script>
<script type="text/javascript" src="game/techqueue.js"></script>

<table style="width:100%">
    <tr>
        <td>
            <?php require('queue.php'); ?>
        </td>
    </tr>
</table>

<?php require('main.php'); ?>
<br>