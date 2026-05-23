<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
    <p class="text-muted">
        This information will be used for fetching new leads from Tradeindia.
    </p>
    <span style="color:red">* Changing settings incorreclty may stop Tradeindia leads import.</span>
    <div class="tab-content mtop30">
        <div role="tabpanel" class="tab-pane active" id="misc">
            <?php echo render_input('settings[tradeindia_api_link]','Tradeindia API Link',get_option('tradeindia_api_link')); ?>
        </div>
        <div role="tabpanel" class="tab-pane active" id="misc">
            <?php echo render_input('settings[tradeindia_userid]','Tradeindia User Id',get_option('tradeindia_userid')); ?>
        </div>
        <div role="tabpanel" class="tab-pane active" id="misc">
            <?php echo render_input('settings[tradeindia_profile_id]','Tradeindia Profile Id',get_option('tradeindia_profile_id')); ?>
        </div>
        <div role="tabpanel" class="tab-pane active" id="misc">
            <?php echo render_input('settings[tradeindia_key]','Tradeindia Key',get_option('tradeindia_key')); ?>
        </div>
    </div>
