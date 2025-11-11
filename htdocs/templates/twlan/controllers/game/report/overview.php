<?php
namespace Twlan;
use Twlan\framework\Text;
use Twlan\framework\Time;
?>
<script type="text/javascript">
//<![CDATA[
    $(function(){
        JToggler.init('#report_list input[type="checkbox"]');
    });
//]]>
</script>
<table class="vis" width="100%">
    <tbody><tr>
                <td style="width: 40px; text-align: center">
         <a href="game.php?village=<?php echo $vid; ?>&amp;mode=all&amp;group_id=-1&amp;screen=report">[Alle]</a> 
        </td>
                <td align="center" colspan="2">
             <strong>&gt;Neue Berichte&lt; </strong>  <a href="/game.php?village=981&amp;mode=all&amp;group_id=127&amp;screen=report">[Archiv]</a> 
        </td>
                <td width="140">
            <a href="game.php?village=<?php echo $vid; ?>&amp;mode=groups&amp;screen=report">» Ordner erstelle</a>
        </td>
            </tr>
    </tbody>
</table>

<form action="game.php?village=<?php echo $vid; ?>&amp;mode=process_reports&amp;refmode=all&amp;screen=report" method="post">
    <table id="report_list" class="vis" width="100%">
        <tbody>
            <tr>
                <th colspan="2"><?php l('game.report.subject'); ?></th>
                <th><?php l('game.report.received'); ?></th>
            </tr>
            <?php $world = $this->world;
            foreach($reports as $report) {
                $fromVillage = $report->fromVillage;
                $fromUser = isset($fromVillage->owner) ? $fromVillage->owner : null;
                $toVillage = $report->toVillage;
                $toUser = isset($toVillage->owner) ? $toVillage->owner : null;
            ?>
            <tr>
                <td><input name="id_<?php echo $report->id_report; ?>" type="checkbox"></td>
                <td style="overflow: hidden">
                    <div class="nowrap float_right" style="margin-top: 2px">
                        <img src="graphic/command/<?php echo $report->type(); ?>.png" class="">
                    </div>
                    <?php if(isset($report->data['color'])) { ?> 
                    <img src="graphic/dots/<?php echo $report->data['color']; ?>.png" class="">  
                    <?php } ?>
                    <span class="quickedit" data-id="<?php echo $report->id_report; ?>">
                        <span class="quickedit-content">
                            <a href="game.php?village=<?php echo $vid; ?>&amp;mode=all&amp;view=<?php echo $report->id_report; ?>&amp;screen=report">
                                <span class="quickedit-label">
                                    <?php l('game.report.'.$report->type(), array(
                                        'fromUser' => Text::formatAll($fromUser->global->name), 
                                        'fromVillage' => Text::formatAll($fromVillage->name), 
                                        'toVillage' => $toVillage->getDisplayName()
                                    ), true); 
                                    ?>
                                </span>
                            </a>
                            <?php if (!$report->is_read) echo '('.ll('game.report.new').')'; ?>
                            <a class="rename-icon" href="#" title="<?php l('game.report.rename'); ?>"></a>
                        </span>
                    </span>
                    
                </td>
                <td class="nowrap"><?php echo Time::onTime($report->data['time']); ?></td>
            </tr>
            <?php } ?>
            <tr>
                <th colspan="2">
                    <input name="all" type="checkbox" class="selectAll" id="select_all" onclick="selectAll(this.form, this.checked)"> 
                    <label for="select_all"><?php l('game.report.selectAll'); ?></label></th>
                <th></th>
            </tr>
        </tbody>
    </table>

    <table class="vis" align="left" style="float: left;">
        <tbody>
            <tr>
                <td>
                    <input type="hidden" value="0" name="from">
                    <input type="hidden" value="3" name="num_reports">
                    <input type="hidden" value="0" name="current_group_id">
                    <input class="btn btn-cancel" type="submit" value="Löschen" name="del">
                    <input class="btn" type="submit" value="Veröffentlichen" name="forward">
                    <input class="btn" type="submit" value="Weiterleiten" name="real_forward">
                </td>
                <td>
                    <select name="group_id">
                        <option value="127">Archiv</option>
                    </select>
                    <input class="btn" type="submit" value="Verschieben" name="arch">
                </td>
            </tr>
        </tbody>
    </table>
</form>

<form action="game.php?village=<?php echo $vid; ?>&amp;action=change_page_size&amp;mode=all&amp;from=0&amp;screen=report" method="post">
    <table class="vis nowrap" align="left" style="float: left;">
        <tbody><tr>
            <th colspan="2">Berichte pro Seite:</th>
                <td><input name="page_size" type="text" style="width: 50px" value="12"></td>
            <td><input class="btn" type="submit" value="Ok"></td>
        </tr>
    </tbody></table>
</form>

<div style="clear:both;"> </div>

<script>
    $(function(){
        $('.quickedit').QuickEdit( { url: TribalWars.buildURL('POST', 'report', { ajaxaction: 'edit_subject', report_id: '__ID__' } ) } );
    });
</script>