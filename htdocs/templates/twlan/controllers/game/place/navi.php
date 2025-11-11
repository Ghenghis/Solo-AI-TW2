<?php
namespace Twlan;
?>
<table class="vis modemenu" width="100%">
    <tbody>
        <tr>
        <?php foreach($_modes as $_mode) { ?>
            <td <?php if($mode == $_mode) echo 'class="selected"'; ?> style="min-width: 80px">
                <a href="game.php?village=<?php echo $vid; ?>&amp;screen=place&amp;mode=<?php echo $_mode; ?>">
                    <?php l('buildingPlaceNavi.'.$_mode); ?>
                </a>
            </td>
        <?php } ?>
        </tr>
        <!-- Lang Notes: -->
        <!-- Befehle -->
        <!--Geheimnis-->
        <!--Truppen-->
        <!--Simulator-->
        <!--Truppen-Vorlagen-->
    </tbody>
</table>