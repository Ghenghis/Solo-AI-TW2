<form action="" method="POST">
    <table class="box">
        <tr>
            <td colspan="100%">
                <div class="head"><?php l('admin.manageVillages.createAbandoned');?></div>
            </td>
        </tr>
        <tr>
            <td colspan="100%">
                <label for="<?php $id = 'village_amount'; echo $id; ?>"><?php l('admin.manageVillages.villageAmount'); ?></label>
                <input id="<?php echo $id;?>" name="<?php echo $id;?>" type="text" tabindex="1" value="<?php echo isset($_POST[$id])?$_POST[$id]:'1'; ?>">
            </td>
        </tr>
        <tr>
            <td>
                <div class="head">Gebäude</div>
            </td>
            <td>
                <div class="head">Einheiten</div>
            </td>
        </tr>
        <tr>
            <td>
                <table>
                    <?php foreach ($world->buildings->getAll() as $building) { $id = 'building_input_'.$building->id; ?>
                    <tr>
                        <td>
                            <a href="#" class="unit_link">
                                <img src="/graphic/buildings/<?php echo $building->id; ?>.png" title="<?php echo $building->getLocalizedId(); ?>" alt="" class="faded">
                            </a>
                        </td>
                        <td class="nowrap">
                            <input name="<?php echo $id; ?>" type="text" tabindex="1" value="<?php echo isset($_POST[$id])?$_POST[$id]:$building->getStartLevel(); ?>" class="form-control buildingsInput">
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </td>
            <td>
                <table>
                    <tr>
                        <td></td>
                        <td>
                            <div class="head">Anzahl</div>
                        </td>
                        <td>
                            <div class="head">Tech-Level</div>
                        </td>
                    </tr>
                    <?php foreach ($world->units->getAll() as $unit) { ?>
                    <tr>
                        <?php $id = 'unit_input_'.$unit->id; ?>
                        <td>
                            <label for="<?php echo $id;?>">
                                <img src="/graphic/unit/unit_<?php echo $unit->id; ?>.png" title="<?php echo $unit->getLocalizedId(); ?>" alt="">
                            </label>
                        </td>
                        <td class="nowrap">
                            <input id="<?php echo $id;?>" name="<?php echo $id;?>" type="text" tabindex="1" value="<?php echo isset($_POST[$id])?$_POST[$id]:'0'; ?>">
                        </td>
                        <td>
                            <?php if ($unit->canBeResearched()) { $id = 'research_input_'.$unit->id; ?>
                            <input name="<?php echo $id;?>" type="text" tabindex="1" value="<?php echo isset($_POST[$id])?$_POST[$id]:'0'; ?>">
                            <?php } ?>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="100%">
                <div class="head">Erstellen</div>
            </td>
        </tr>
        <tr>
            <td colspan="100%">
                Punkte: <input id="points" type="text" value="0" readonly>
            </td>
        </tr>
        <tr>
            <td colspan="100%">
                <input type="submit" name="create_abandoned_villages" value="<?php l('admin.manageVillages.createVillages'); ?>">
            </td>
        </tr>
    </table>
</form>
