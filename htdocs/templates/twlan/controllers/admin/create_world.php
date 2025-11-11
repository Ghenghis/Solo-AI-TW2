<?php
namespace TWLan;
?>
<form method="POST">
<table class="box">
    <tr>
        <td>
            <div class="head">
                <label for="name"><?php l('admin.configeditor.entername'); ?></label>
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <input type="text" name="name"/>
            <input type="submit" value="<?php l('admin.configeditor.newworld'); ?>"/>
        </td>
    </tr>
    <tr>
        <td>
            <?php if (isset($error)) echo $error ? $error : ll('admin.configeditor.worldcreated'); ?>
        </td>
    </tr>
</table>
</form>
