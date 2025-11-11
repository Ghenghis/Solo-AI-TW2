<?php
namespace TWLan;
use TWLan\Framework\Text;
?>
<table id="group_table" class="vis" width="100%" cellpadding="5" cellspacing="0">
    <tbody>
        <tr>
            <th class="group_label" colspan="2"></th>
        </tr>
    </tbody>
</table>

<div id="group_popup_content_container">
    <table id="group_table" class="vis" width="100%" cellpadding="5" cellspacing="0">
        <tbody>
            <tr>
                <?php foreach($villages as $groupAssoc) { $village = $groupAssoc->village; ?>
                <td id="selected_popup_village" class="selected">
                    <a href="javascript:selectVillage(<?php echo $village->id_village; ?>, 0)"><?php echo Text::formatAll($village->name); ?></a>
                </td>
                <td style="font-weight:bold; width:100px; text-align:right" class="selected">
                    <?php echo $village->x; ?>|<?php echo $village->y; ?>
                </td>
                <?php } ?>
            </tr>
        </tbody>
    </table>
</div>