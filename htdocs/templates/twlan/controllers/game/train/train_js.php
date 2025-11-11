<?php 
namespace Twlan;
$_relpath = framework\Router::getRelativePath(); ?>
<script type="text/javascript">
//<![CDATA[
    $(document).ready(function(){
        TrainOverview.init();
        <?php if ($isTrain) { ?>
            TrainOverview.initSingleVillageMode();
            TrainOverview.pop_max = <?php echo $village->getMaxRes('population'); ?>;
        <?php } ?>
        <?php $overviewAction = $isTrain ? "train" : "decommission"; ?>
        TrainOverview.train_link = "<?php echo $_relpath; ?>?village=<?php echo $vid; ?>&ajaxaction=<?php echo $overviewAction; 
            ?>&mode=<?php echo $overviewAction; ?>&screen=<?php echo $screen; ?>";
        TrainOverview.cancel_link = "<?php echo $_relpath; ?>?village=<?php echo $vid; ?>&ajaxaction=cancel&screen=<?php echo $screen; ?>&mode=<?php echo $overviewAction; ?>";
    });

    unit_managers = {};

    <?php if ($isTrain) { ?>
        unit_managers.units = <?php 
            $outUnits = array(); 
            foreach($units as $unit) {
                $out[$unit->id] = array();
                foreach($unit->getRecruitCosts() as $name => $value) { 
                    $out[$unit->id][$name] = $value;
                }
                $out[$unit->id]["build_time"] = $unitPopupData[$unit->id]["build_time"];
                $out[$unit->id]["requirements_met"] = $unit->canBeRecruited($this->village);
            }
            echo json_encode($out).";";
            ?>
        var unit_build_block = new UnitBuildManager(0, {
            res: {
                <?php foreach($this->world->getPhysicalResources() as $res) { ?>
                <?php echo $res; ?>: <?php echo $village->getRes($res); ?>,
                <?php } ?>
                pop: <?php echo $village->getMaxRes('population') - $village->getPopulation(); ?>
            }
        });
    <?php } else {
        $outUnits = array();
        foreach ($units as $unit) {
            $outUnits[$unit->id] = $village->getOwnArmy()->getUnit($unit->id);
        }
        ?>
        unit_managers.units = <?php echo json_encode($outUnits, JSON_FORCE_OBJECT); ?>;

        var unit_build_block = new UnitBuildManager(0, false);
    <?php } ?>
    // in case of partial reload, wait for inputs to be restored first
    setTimeout(function() {
        unit_build_block._onchange();
    }, 1);
//]]>
</script>
