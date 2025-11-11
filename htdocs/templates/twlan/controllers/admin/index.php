<?php
namespace TWLan;

function checkNode(&$node, $depth = 0)
{
    if($depth == 0)
    {
        $node['print'] = true;
    }
    if($node['active'])
    {
        if(isset($node['children']))
        {
            foreach($node['children'] as $key => $value)
            {
                $node['children'][$key]['print'] = (isset($node['children'][$key]['show'])) ? ($node['children'][$key]['show'] == 'self') : true;
            }
        }
        return TRUE;
    }
    else
    {
        if(isset($node['children']))
        {
            foreach($node['children'] as $key => $value)
            {
                if(checkNode($node['children'][$key], $depth + 1))
                {
                    foreach($node['children'] as $k => $v)
                    {
                        $node['children'][$k]['print'] = (isset($node['children'][$key]['show'])) ? ($node['children'][$key]['show'] == 'self') : true;
                    }
                    return TRUE;
                }
            }
        }
    }
    return FALSE;
}

function printNode(&$node, $depth = 0)
{
    if(isset($node['print']) && $node['print'])
    {
        //echo '<a href="/admin/'.$node['url'].'" style="padding-left: '.(10 + 15 * $depth).'px;"'.($node['active'] ? ' class="active"' : '').'>'.$node['name'].'</a>';
        echo '<a href="/admin/'.$node['url'].'" style="margin-left: '.(20 * $depth).'px;"'.($node['active'] ? ' class="active"' : '').'>'.$node['name'].'</a>';
        if(isset($node['children']))
        {
            foreach($node['children'] as $k => $v)
            {
                printNode($node['children'][$k], $depth + 1);
            }
        }
    }
}

function printNav(&$sections)
{
    foreach($sections as $key => $section)
    {
        echo '<div class="section">'.$section['name'].'</div>';
        foreach($section['children'] as $k => $v)
        {
            checkNode($sections[$key]['children'][$k]);
            printNode($sections[$key]['children'][$k]);
        }
    }
}
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php l('admin.title');?></title>
        <script src="js/jquery.js"></script>
        <script src="js/admin.js"></script>
        <link href="/css/admin.css" rel="stylesheet">
        <link href="/css/debug_toolbar.css" rel="stylesheet">
    </head>
    <body>
        <div id="container">
            <div id="nav">
                <div class="headline"><?php l('admin.adminPanel');?></div>
                <div class="content">
                    <?php printNav($nav);?>
                </div>
            </div>
            <div class="vertical-separator"></div>
            <div id="main">
                <div class="headline"><?php echo $page;?></div>
                <div class="content">
                    <?php echo $content;?>
                </div>
            </div>
        </div>
    </body>
</html>
<?php /*
<body class="hamburg">
    <div id="page-wrapper" class="open">
        <div id="sidebar-wrapper">
        <ul class="sidebar">
            <li class="sidebar-main" id="toggle-sidebar">
                <a href="#">
                <?php l('admin.adminPanel'); ?>
                <span class="menu-icon glyphicon glyphicon-transfer"></span>
              </a>
            </li>
            <?php parseNavi($navi, $activeEntry); ?>
        </ul>
        <div class="sidebar-footer">
            <div class="col-xs-4">
                <a href="https://twlan.org" target="_blank">
                    TWLan.org
                </a>
            </div>
        </div>
    </div>

    <!-- End Sidebar -->

    <div id="content-wrapper">
        <div class="page-content">

            <!-- Header Bar -->
            <div class="row header">
                <div class="col-xs-12">
                    <div class="meta">
                        <div class="page">
                            <?php echo $page; ?>
                            <div>
                                <?php if ($tasks) { ?>
                                    <small class="label label-info"><b>
                                    <?php l('admin.adminTaskStatus', array('tasks' => $tasks, 'task' => $task, 'progress' => $taskProgress)); ?>
                                    </b></small>
                                 <?php } ?>
                            </div>
                        </div>
                        <div class="breadcrumb-links">
                        </div>
                    </div>
                </div>
            </div>

            <!-- End Header Bar -->

            <!-- Main Content -->
            <div class="row">
                <div class="col-xs-12">
                    <div class="widget">
                        <?php if(!isset($bypassWBody)) { ?><div class="widget-body"><?php } ?>
                            <?php if (!isset($adminContent)) { ?>
                                <img class="twlan_logo" src="/graphic/twlan_logo.jpg">
                                <!-- from debugtoolbar -->
                                <div class="debugt-nav-entry"><?php l('game.version'); ?><div><small><?php echo Globals::TWLAN_VERSION.'-r'.Globals::TWLAN_REVISION.' <br> ';
                                    l('game.basedOn'); echo ' TW'.Globals::DS_VERSION; ?></small></div></div>
                                <div class="debugt-nav-entry">PHP-<?php l('game.version'); ?><div><small><?php echo phpversion(); ?></small></div></div>
                            <?php } else { echo $adminContent; } ?>
                        <?php if(!isset($bypassWBody)) { ?></div><?php } ?>
                    </div>

                </div>
            </div>
            <!-- End Page Content -->
        </div><!-- End Content Wrapper -->
    </div><!-- End Page Wrapper -->
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <!--<script src="js/bootstrap.min.js"></script>-->

</body>*/?>
