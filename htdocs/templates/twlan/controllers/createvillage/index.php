<?php
namespace Twlan;
?>
<table id="content_value" class="inner-border main" cellspacing="0">
    <tr>
        <td>
            <h3><?php echo l('createVillage.title');?></h3>
            <?php if(isset($error)) {?>
            <div id="error" class="error" style="line-height: 20px"><?php echo $error;?></div>
            <?php }?>
            <h4><?php echo l('createVillage.description');?></h4>
            <table class="vis">
                <tr>
                    <td width="200">
                        <form action="create_village.php?action=create" method="post">
                            <label><input type="radio" name="direction" value="random" checked="checked" /><?php echo l('createVillage.drandom');?></label><br />
                            <label><input type="radio" name="direction" value="sw" /><?php echo l('createVillage.dsw');?></label><br />
                            <label><input type="radio" name="direction" value="nw" /><?php echo l('createVillage.dnw');?></label><br />
                            <label><input type="radio" name="direction" value="so" /><?php echo l('createVillage.dso');?></label><br />
                            <label><input type="radio" name="direction" value="no" /><?php echo l('createVillage.dno');?></label><br />
                            <?php if(isset($ally)){?>
                            <label><input type="radio" name="direction" value="ally" /><?php echo l('createVillage.dally');?></label><br />
                            <?php }?>
                            <br />
                            <input type="submit" value="<?php echo l('createVillage.submit');?>" />
                      </form>
                    </td>
                    <td>
                        <img src="graphic/richtung/richtung.png" alt="" />
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>