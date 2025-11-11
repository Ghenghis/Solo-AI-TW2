<?php 
namespace Twlan;
?>
</div>
<form action="game.php?village=<?php echo $vid; ?>&amp;screen=overview_villages&amp;mode=combined&amp;action=change_page_size0" method="post">
    <table class="vis">
        <tr>
            <th colspan="2">
                <?php l('game.overviews.villagespersite'); ?>
            </th>
            <td>
                <input name="page_size" type="text" size="5" value="5" />
            </td>
            <td>
                <input type="submit" value="<?php l('game.ok'); ?>" />
            </td>
        </tr>
    </table>
</form>
