<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4>Firebase Settings</h4>
<div class="smtp-fields mtop20">
    <?php echo render_input('settings[firebase_api_key]', 'API Key', get_option('firebase_api_key')); ?>
    <?php echo render_input('settings[firebase_auth_domain]', 'Auth Doamin', get_option('firebase_auth_domain')); ?>
    <?php echo render_input('settings[firebase_project_id]', 'Project ID', get_option('firebase_project_id')); ?>
    <?php echo render_input('settings[firebase_storage_bucket]', 'Storage Bucket', get_option('firebase_storage_bucket')); ?>
    <?php echo render_input('settings[firebase_messaging_sender_id]', 'Messaging Sender ID', get_option('firebase_messaging_sender_id')); ?>
    <?php echo render_input('settings[firebase_app_id]', 'App ID', get_option('firebase_app_id')); ?>
    <?php echo render_input('settings[firebase_measurement_id]', 'Measurement ID', get_option('firebase_measurement_id')); ?>
</div>