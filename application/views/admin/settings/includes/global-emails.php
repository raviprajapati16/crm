<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="mtop15">Global Email(s)
    <p><span style="font-size: 12px;">This emails(s) will be used in CC / BCC in all mail services and system mails. Use , comma to add multiple emails.</span></p>
</h4>
<?php echo render_input('settings[global_cc_emails]', 'CC Email(s)', get_option('global_cc_emails')); ?>
<?php echo render_input('settings[global_bcc_emails]', 'BCC Email(s)', get_option('global_bcc_emails')); ?>