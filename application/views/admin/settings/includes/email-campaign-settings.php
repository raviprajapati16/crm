<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4>Email Campaigns Settings</h4>
<br>
<div class="row">
    <div class="form-group col-md-12">
        <div>Email Campaign Operational Hours Set</div>
        <div class="onoffswitch">
            <input type="checkbox" id="onofftime" class="onoffswitch-checkbox" value="1" name="email_campaign_operation_hours" <?= (get_option('email_campaign_operation_hours') == '1') ? "checked" : "" ?>>
            <label class="onoffswitch-label" for="onofftime"></label>
        </div>
    </div>
        <div class="form-group col-md-2 timeSection" app-field-wrapper="settings[email_campaigns_start_time]">
            <label for="settings[email_campaigns_start_time]" class="control-label">Email Campaign Start Time</label>
            <input type="time" id="settings[email_campaigns_start_time]" name="settings[email_campaigns_start_time]" class="form-control" value="<?= get_option('email_campaigns_start_time') ?>">
        </div>
        <div class="form-group col-md-2 timeSection" app-field-wrapper="settings[email_campaigns_end_time]">
            <label for="settings[email_campaigns_end_time]" class="control-label">Email Campaign End Time</label>
            <input type="time" id="settings[email_campaigns_end_time]" name="settings[email_campaigns_end_time]" class="form-control" value="<?= get_option('email_campaigns_end_time') ?>">
        </div>
        <div class="form-group col-md-12 timeSection">
            <span style="font-size: 12px;">Emails campaigns will run between this selected hours only.</span>
        </div>
</div>