<?php namespace Twlan;
use Twlan\framework\Time;
?>
<div class="vis_item">
    <?php l('game.overview.beginnerProtection', array('date'=>Time::onTime($user->start + $this->world->getConfig('world.beginner_protection'))));?>
</div>
