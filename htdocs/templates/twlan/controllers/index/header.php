<?php
namespace Twlan;
?>
<header>
    <div id="top"></div>
    <!-- end #top -->
    <nav class="l-header">
        <div class="l-constrained l-constrained-alt">
            <div class="menu-mobile"> <span></span>
                <div class="menu-text">Menu</div>
            </div>
            <!-- end .menu-mobile -->
            <ul class="menu-primary l-clearfix l-inline-list">
                <li><a id="headerlink-home" class="is-active " href="/"><?php l('index.twlan'); ?></a> </li>
                <li><a id="headerlink-forum" class=" external" href="http://twlan.org" target="_blank"><?php l('index.twlanLink'); ?></a> </li>
                <?php if ($user!='') { ?>
                <li><a id="headerlink-logout" class=" " href="/page/logout"><?php l('index.logout'); ?></a> </li>
                <?php } ?>
                <?php if (isset($this->user) && $this->user->isAdmin()) { ?>
                <li><a id="headerlink-admin" class=" " href="/admin" target="_blank"><?php l('admin.adminPanel'); ?></a> </li>
                <?php } ?>
            </ul>
            <!-- end .menu-primary l-clearfix l-inline-list -->
            <?php if($user == ''){ ?>
            <form action="#" method="post" id="login">
                <div class="login">
                    <input type="text" placeholder="User name" name="username" tabindex="1" autofocus />
                    <input type="password" placeholder="Password" name="password" tabindex="2" />
                    <button class="btn-login" type="submit"><?php l('index.login'); ?></button>
                    <div class="login-meta">
                        <div class="remember-me-container"> 
                            <span class="checkbox-label"> 
                                <input type="checkbox" class="checkbox" id="remember-me" name="remember-me" checked="checked"> 
                            </span>
                            <label for="remember-me"><?php l('index.stayLoggedIn'); ?></label>
                        </div> | <a href="/page/recovery" id="change-password"><?php l('index.forgotPassword'); ?></a> </div>
                    <!-- end .login-meta-->
                </div>
                <!--end .login-->
            </form>
            <?php } ?>
            <!-- end .logo -->
        </div>
        <!-- end .l-constrained -->
    </nav>
    <div class="l-header-content">
    <div class="l-constrained l-constrained-reg l-clearfix">
        <!-- REGISTER -->
        <?php echo $this->viewPartial("container"); ?>
        <!-- end .register -->
        <?php if ($user == '') { ?>
         <div class="aside">
            <div class="slider-primary">
                <div class="container">
                    <div class="container-inner">
                        <div class="inner-content">
                            <ul id="page2" class="rslides">
                                <li>
                                    <a title="Tribal Wars - Village View" class="screenshot-open" href="/graphic/twlan_logo.jpg"> <img src="/graphic/twlan_logo.jpg" alt="" /> </a>
                                </li>
                            </ul>
                        </div>
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
            <!-- end .slider-primary -->
            <div class="meta">
                <div class="container">
                    <div class="container-inner">
                        
                    <!-- end .container-inner -->
                    <div class="container-extension">
                        <p class="l-align-center teaser-text"><?php l('index.followUs'); ?>:</p>
                        <div class="social l-center-block"> 
                            <a class="social-media fb ir" title="" href="https://www.facebook.com/TribalWarsLan/" target="_blank">Facebook</a>  
                        </div>
                        <!-- end .social -->
                    </div>
                    <!-- end .container-extension -->
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
            <!-- end .meta-info -->
        </div>
        <!-- end .aside -->
    </div>
    <?php } ?>
    <!-- end .l-constrained -->
</div>
<!-- end header-content-->
</header>