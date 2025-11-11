<?php
namespace Twlan;
?>
<div id="world-select" class="dialog worlds">
    <div class="container">
        <div class="container-inner">
            <div class="world-select-header">
                <p><?php l('index.loginTitle', array('user' => $user)); ?></p>
            </div>
            <!-- end .world-select-header -->
            <div class="inner-content">
                <?php if (isset($worlds) && count($worlds['active'])) { ?>
                <div class="world-select entered l-clearfix">
                    <h3><?php l('index.activeWorlds'); ?></h3>
                    <ul class="l-list-vertical is-multiple">
                        <?php foreach($worlds['active'] as $key => $value) { ?>
                        <li><a href="/page/play/<?php echo $key; ?>"><?php echo $value; ?></a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>
                <?php if (isset($worlds) && count($worlds['inactive'])) { ?>
                <div class="world-select suggested l-clearfix">
                    <h3><?php l('index.availableWorlds'); ?></h3>
                    <ul class="l-list-vertical is-multiple">
                        <?php foreach($worlds['inactive'] as $key => $value) { ?>
                        <li><a href="/page/play/<?php echo $key; ?>"><?php echo $value; ?></a>
                        </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php } ?>
            </div>
            <!-- end .inner-content -->
            <div class="container-extension apps l-center-block l-clearfix">
               <?php if(isset($error)) { var_dump($error); /*echo "<div class=\"error\">".$error."</div>";*/ } ?>
            </div>
            <!-- end .container-extension -->
        </div>
        <!-- end .container-inner -->
        <div class="top-left"></div>
        <div class="top-right"></div>
        <div class="middle-top"></div>
        <div class="middle-bottom"></div>
        <div class="middle-left"></div>
        <div class="middle-right"></div>
        <div class="bottom-left"></div>
        <div class="bottom-right"></div>
    </div>
    <!-- end .container -->
</div>