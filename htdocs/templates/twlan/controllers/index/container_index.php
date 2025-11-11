<?php
namespace Twlan;
?>
<div class="register">  
    <div class="container">
        <div class="container-inner">
            <div class="inner-content">
                <h2 class="visuallyhidden"><?php l('index.register'); ?></h2>
                <form action="#" method="post" id="register">
                    <!-- prevent browsers from autocompleting register form with login data -->
                    <input type="text" name="un" class="hidden" />
                    <input type="password" name="pa" class="hidden" />
                    <input type="hidden" name="server" value="zz1" />
                    <div class="form-element" id="form-element-name">
                        <label for="register_username"><?php l('index.username'); ?></label>
                        <input type="text" id="register_username" name="register_username" class="require-validation" data-type="name" value="" tabindex="3" /> 
                        <span class="validation validation-none"></span>
                        <div class="error-message error-username l-clearfix">
                            <div class="pointer"></div> <i class="icon-error"></i>
                            <p class="message"></p>
                            <div class="message-suggestion-container" style="display: none">
                                <div class="error-divider"></div>
                                <div class="message-suggestion">
                                    <p>Suggested names:</p>
                                    <ul> </ul> <a href="#" id="message-suggestion-more-link">More...</a> </div>
                            </div>
                            <!-- end .error-message-->
                        </div>
                    </div>
                    <!-- end .form-element -->
                    <div class="form-element" id="form-element-password">
                        <label for="register_password"><?php l('index.password'); ?></label>
                        <input type="password" id="register_password" name="register_password" class="require-validation" data-type="password" value="" tabindex="4" /> <span class="validation validation-none"></span>
                        <div class="error-message error-password l-clearfix">
                            <div class="pointer"></div> <i class="icon-error"></i>
                            <p class="message"> </p>
                        </div>
                    </div>
                    <!-- end .form-element -->
                    <!-- end .terms -->
                    <a title="Register now!" id="register-button" href="#" class="btn btn-calltoaction l-align-center" tabindex="7"> 
                        <span><?php l('index.register'); ?></span>
                    </a>
                    <input type="submit" class="is-hidden" />
                    <!-- end .register-alt -->
                </form>
            </div>
            <!-- end .inner-content -->
            <div class="inner-top-left"></div>
            <div class="inner-top"></div>
            <div class="inner-top-right"></div>
            <div class="inner-left"></div>
            <div class="inner-middle"></div>
            <div class="inner-right"></div>
            <div class="inner-bottom-left"></div>
            <div class="inner-bottom"></div>
            <div class="inner-bottom-right"></div>
        </div>
        <!-- end .register-container-inner -->
        <div class="container-extension apps l-center-block l-clearfix">
            
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
</div>