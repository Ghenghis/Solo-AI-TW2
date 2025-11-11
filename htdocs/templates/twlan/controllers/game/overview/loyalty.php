<?php 
$loyaltyColor = '';
if ($village->loyalty >= 75) $loyaltyColor = 'style="color: green;"';
elseif ($village->loyalty >= 50) $loyaltyColor = 'style="color: orange;"';
elseif ($village->loyalty < 50) $loyaltyColor = 'style="color: red;"';
?>
<div class="vis_item" <?php echo $loyaltyColor; ?>><?php echo floor($village->loyalty); ?></div>