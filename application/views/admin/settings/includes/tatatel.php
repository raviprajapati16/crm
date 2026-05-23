<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
    <p class="text-muted">
        This information will be used for Tata Tele Services backend integration.
    </p>
    <span style="color:red">* Changing settings incorreclty may stop Tata Tele Services utilities.</span>
    <div class="tab-content mtop30">
        <div role="tabpanel" class="tab-pane active" id="misc">
            <?php echo render_input('settings[tatatel_email]','Email',get_option('tatatel_email')); ?>
        </div>
        <div role="tabpanel" class="tab-pane active" id="misc">
            <?php echo render_input('settings[tatatel_password]', 'Password', get_option('tatatel_password'), 'password'); ?>
        </div>
        <div role="tabpanel" class="tab-pane active" id="misc">
            <?php echo render_input('settings[tatatel_url]','Api Url',get_option('tatatel_url')); ?>
        </div>
        <div role="tabpanel" class="tab-pane active" id="misc">
            <?php echo render_input('settings[tatatel_access_token]','Access Token',get_option('tatatel_access_token')); ?>
        </div>
    </div>