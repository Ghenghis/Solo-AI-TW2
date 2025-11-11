<?php
namespace Twlan;
?>
<?php $_output = function($val) { if($val == 0) return; echo $val; }; 
    if(isset($simulate)) require('sim_result.php'); ?>
<h3><?php l('buildingPlace.simulator'); ?></h3>

<form action="game.php?village=<?php echo $vid; ?>&amp;mode=sim&amp;screen=place" method="post" name="simulator">
    <input name="simulate" type="hidden">

    <table class="vis" id="simulator_units_table">
        <tbody>
            <tr>
                <th></th>
                <th><?php l('buildingPlace.attacker'); ?></th>
                <th><?php l('buildingPlace.defender'); ?></th>
            </tr>

            <tr>
                <td></td>
                <td><?php l('buildingPlace.units'); ?></td>
                <td><?php l('buildingPlace.units'); ?></td>
            </tr>

            <?php foreach($this->world->units->getAll() as $unit) { ?>
            <tr>
                <td>
                    <a class="unit_link" href="#" onclick="return UnitPopup.open(event, '<?php echo $unit->id; ?>')">
                        <img alt="" class="" src="graphic/unit/unit_<?php echo $unit->id; ?>.png" title=""> <?php echo $unit->getLocalizedId(); ?>
                    </a>
                </td>

                <td>
                    <input name="att_<?php echo $unit->id; ?>" style="width: 50px" type="text" value="<?php if(isset($result['attackingArmy']['before'][$unit->id]))
                        echo $_output($result['attackingArmy']['before'][$unit->id]); ?>">
                </td>

                <td>
                    <input name="def_<?php echo $unit->id; ?>" style="width: 50px" type="text" value="<?php if(isset($result['defendingArmy']['before'][$unit->id]))
                        echo $_output($result['defendingArmy']['before'][$unit->id]); ?>">
                </td>
            </tr>
            <?php } ?>

            <tr>
                <td></td>

                <td>
                    <input onclick="$(&quot;#simulator_units_table input[name*=att_]&quot;).val(&quot;&quot;)" type="button" value="<?php l('buildingPlace.resetAttacker'); ?>">
                </td>

                <td>
                    <input onclick="$(&quot;#simulator_units_table input[name*=def_]&quot;).val(&quot;&quot;)" type="button" value="<?php l('buildingPlace.resetDefender'); ?>">
                </td>
            </tr>

            <tr>
                <td></td>
                <td><label><input name="belief_att" type="checkbox" <?php if(isset($_POST['belief_att'])) echo 'checked="checked"';?>><?php l('buildingPlace.believing'); ?></label></td>
                <td><label><input name="belief_def" type="checkbox" <?php if(isset($_POST['belief_def'])) echo 'checked="checked"';?>><?php l('buildingPlace.believing'); ?></label></td>
            </tr>

            <tr>
                <td><?php l('buildingPlace.wall'); ?></td>
                <td></td>
                <td colspan="2"><input name="def_wall" style="width: 50px" type="text" value="<?php if(isset($_POST['def_wall'])) echo $_POST['def_wall'];?>"></td>
            </tr>

            
            <?php if ($this->world->knightItems) {  ?>
            <tr>
                <td><?php l('knight.items'); ?></td>

                <td>
                    <select multiple="multiple" name="att_knight_items[]" size="6">
                        <option value="0">
                            <?php l('knight.noItem'); ?>
                        </option>
                        <?php foreach ($this->world->knightItems as $knightItemShort => $knightItem) { ?>
                        <option value="<?php echo $knightItemShort; ?>" <?php 
                            if (isset($_POST["att_knight_items"]) && in_array($knightItemShort, $_POST["att_knight_items"]))
                                echo "selected";
                            ?>>
                            <?php l('knight.'.$knightItemShort); ?>
                        </option>
                        <?php } ?>
                    </select>
                </td>

                <td colspan="2">
                    <select multiple="multiple" name="def_knight_items[]" size="6">
                        <option value="none">
                            <?php l('knight.noItem'); ?>
                        </option>

                        <?php foreach ($this->world->knightItems as $knightItemShort => $knightItem) { ?>
                        <option value="<?php echo $knightItemShort; ?>" <?php 
                            if (isset($_POST["def_knight_items"]) && in_array($knightItemShort,  $_POST["def_knight_items"]))
                                echo "selected";
                            ?>>
                            <?php l('knight.'.$knightItemShort); ?>
                        </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <?php } ?>

            <tr>
                <td>Flagge</td>

                <td><select name="att_flag">
                    <option value="0">
                        Keine
                    </option>

                    <option value="1">
                        +2% Angriffsstärke
                    </option>

                    <option value="2">
                        +3% Angriffsstärke
                    </option>

                    <option value="3">
                        +4% Angriffsstärke
                    </option>

                    <option value="4">
                        +5% Angriffsstärke
                    </option>

                    <option value="5">
                        +6% Angriffsstärke
                    </option>

                    <option value="6">
                        +7% Angriffsstärke
                    </option>

                    <option value="7">
                        +8% Angriffsstärke
                    </option>

                    <option value="8">
                        +9% Angriffsstärke
                    </option>

                    <option value="9">
                        +10% Angriffsstärke
                    </option>
                </select></td>

                <td><select name="def_flag">
                    <option value="0">
                        Keine
                    </option>

                    <option value="1">
                        +2% Verteidigungsstärke
                    </option>

                    <option value="2">
                        +3% Verteidigungsstärke
                    </option>

                    <option value="3">
                        +4% Verteidigungsstärke
                    </option>

                    <option value="4">
                        +5% Verteidigungsstärke
                    </option>

                    <option value="5">
                        +6% Verteidigungsstärke
                    </option>

                    <option value="6">
                        +7% Verteidigungsstärke
                    </option>

                    <option value="7">
                        +8% Verteidigungsstärke
                    </option>

                    <option value="8">
                        +9% Verteidigungsstärke
                    </option>

                    <option value="9">
                        +10% Verteidigungsstärke
                    </option>
                </select></td>
            </tr>
            
            <tr>
                <td><?php l('buildingPlace.catapult'); ?></td>
                <td></td>
                <td colspan="2"><input name="def_building" style="width: 50px" type="text" value="<?php if(isset($_POST['def_building'])) echo $_POST['def_building'];?>">
                    <input id="is_church" name="building" type="checkbox" value="church" <?php if(isset($_POST['building']) && $_POST['building'] == 'church') echo 'checked="checked"';?>> 
                    <label for="is_church"><?php l('buildingPlace.church'); ?></label>
                </td>
            </tr>

            <tr>
                <td><?php l('buildingPlace.moral'); ?></td>

                <td colspan="2"><input id="moral" name="moral" style="width: 50px" type="text" value="<?php echo isset($_POST['moral']) ? $_POST['moral'] : '100'; ?>">% <a href="javascript:popup_scroll('page.php?page=moralcalc',%20400,%20290)">
                » <?php l('buildingPlace.moralCalc'); ?></a></td>
            </tr>

            <tr>
                <td><?php l('buildingPlace.night'); ?></td>

                <td></td>

                <td colspan="2"><label><input name="night" <?php if(isset($_POST['night'])) echo 'checked="checked"';?> type="checkbox"> <?php l('buildingPlace.defenderBonus'); ?></label></td>
            </tr>

            <tr>
                <td><?php l('buildingPlace.luck'); ?></td>
                <td colspan="2"><input name="luck" size="5" type="text" value="<?php echo isset($_POST['luck']) ? $_POST['luck'] : '0'; ?>">%</td>
            </tr>
        </tbody>
    </table>
    <input class="btn" type="submit" value="<?php l('buildingPlace.calculate'); ?>">
</form>