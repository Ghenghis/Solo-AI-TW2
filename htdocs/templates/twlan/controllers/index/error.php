<div id="content">
    <div class="container-block-full">
        <div class="container-top-full"></div>
        <div class="container">
            <div class="info-block register" style="margin-left:10px">
                <h2>Error <?php echo $error['code']; ?></h2>
                <br />
                <div class="error">
                    <?php echo $error['message'];?>
                    <br /><br />
                    <?php echo str_replace("\n", '<br />', "TRACE");?>
                </div>
                <br />
                Requested File: <?php echo $path;?>
            </div>
        </div>
        <div class="container-bottom-full"></div>
    </div>
</div>