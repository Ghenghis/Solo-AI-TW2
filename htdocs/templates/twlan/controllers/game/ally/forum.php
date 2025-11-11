<?php
// We use iframe because it's much easier...
?>
<iframe id="iframe" width="100%" style="border: none !important;"></iframe>
<script type="text/javascript">
($("#iframe")
    .attr({"scrolling": "no", "src":"forum.php"})
    .load(function() {
        $(this).css("height", $(this).contents().height() + "px");
}));
</script>
