<?php 
namespace Twlan;
?>
<table id="content_value" class="inner-border main" cellspacing="0">
    <tr>
        <td>
            <h1><img src="graphic/rabe_38x40.png" alt="" /> <?php echo l('join.title');?></h1>
            <?php if(isset($error)){?>
            <div id="error" class="error" style="line-height: 20px"><?php echo $error;?></div>
            <?php }?>
            <b><?php echo l('join.description');?></b>
            <table class="vis" style="border:1px solid #000" width="400">
                <tr>
                    <td>
                        <ul style="margin:2px">
                            <?php foreach($world['description'] as $value) {?>
                            <li><?php echo $value;?></li>
                            <?php }?>
                        </ul>
                        <a class="small" href="stat.php?mode=settings"><?php echo l('join.settings', array('t' => '»', 'world'=>'<strong>'.$world['name'].'</strong>'));?></a>
                    </td>
                </tr>
            </table>
            <p><?php echo l('join.wannaJoin', array('world'=>'<strong>'.$world['name'].'</strong>'));?></p>
            <form method="post" action="create_account.php?action=confirm">
                <input type="submit" value="<?php echo l('join.join');?>" />
            </form>
        </td>
    </tr>
</table>