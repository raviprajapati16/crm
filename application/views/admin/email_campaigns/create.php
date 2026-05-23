<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .template-preview-section {
        min-height: 1025px;
    }

    .no-preview-text {
        margin-top: 30%;
    }
</style>
<div id="wrapper">
    <div class="content">
        <?php echo form_open(admin_url('email_campaigns/save'), array('id' => 'campaign_form')); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">New Campaign</h4>
                        <hr class="hr-panel-heading" />
                        <?php echo render_input('title', 'Campaign Title', "", 'text', []); ?>
                        <?php echo render_datetime_input('start_date', 'Campaign Start Date & Time'); ?>
                        <?php echo render_input('max_send_limit', 'Max. Limit to Send Emails Per Day', 800, 'number', []); ?>
                        <div class="form-group select-placeholder">
                            <label for="template_id" class="control-label">Template</label>
                            <select name="template_id" id="template_id" class="form-control selectpicker" data-size="7" data-live-search="true" required>
                                <option value="">Select Template</option>
                                <?php foreach ($templates as $template) {
                                ?>
                                    <option value="<?= $template->id; ?>"><?= $template->title; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reply_to" class="control-label">Reply To</label>
                            <input type="text" id="reply_to" name="reply_to" class="form-control email-input" value="" tabindex="-1" multiple>
                        </div>
                        <div class="form-group">
                            <label>Send From</label>
                            <div class="radio-inline">
                                <label><input type="radio" id="mail_send_from_1" name="mail_send_from" value="staff" checked> Staff</label>
                            </div>
                            <?php if (has_permission('email_campaigns', '', 'view')) { ?>
                                <div class="radio-inline">
                                    <label><input type="radio" id="mail_send_from_2" name="mail_send_from" value="custom_email"> Custom Email</label>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="form-group select-placeholder">
                            <label for="mail_id" class="control-label">Select Email Sent From</label>
                            <select name="mail_id[]" id="mail_id" class="form-control selectpicker" data-size="7" data-live-search="true" multiple required>
                                <option value="">Select Option</option>
                                <?php foreach ($mails as $mail) {
                                    if (!has_permission('email_campaigns', '', 'view')) {
                                        if (!in_array($mail->staffid, get_manager_assigned_staff_ids())) {
                                            continue;
                                        }
                                    }
                                ?>
                                    <option data-type="staff" value="<?= $mail->staffid; ?>"><?= $mail->webmail_email; ?></option>
                                <?php } ?>
                                <?php
                                if (has_permission('email_campaigns', '', 'view')) {
                                    foreach ($custom_mails as $mail) {
                                ?>
                                        <option data-type="custom_emails" value="<?= $mail['id']; ?>"><?= $mail['email']; ?></option>
                                <?php }
                                } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div><strong> Email Recipients</strong></div>
                        <div class="row">
                            <?php
                            $staff_users = [];
                            if (is_manager()) {
                            ?>
                                <?php if (!empty($staff)) {
                                    foreach ($staff as $user) {
                                        if (in_array($user['staffid'], get_manager_assigned_staff_ids()) || $user['staffid'] == get_staff_user_id()) {
                                            $staff_users[] = $user;
                                ?>
                                        <?php
                                        }
                                        ?>
                            <?php
                                    }
                                }
                            } else {
                                $staff_users = $staff;
                            } ?>

                            <?php
                            if (has_permission('email_campaigns', '', 'view') || is_manager()) {
                            ?>
                                <br />
                                <div class="col-md-6">
                                    <label for="staff_filter" class="control-label">Staff</label>
                                    <?php echo render_select('staff[]', $staff_users, array('staffid', array('firstname', 'lastname')), '', '', array('data-width' => '100%', 'multiple' => true, 'data-size' => 8,  'data-none-selected-text' => "All Staff"), array(), 'no-mbot', 'staffid'); ?>
                                </div>
                            <?php
                            }
                            ?>
                            <div class="col-md-6">
                                <label for="countries" class="control-label">Country</label>
                                <?php
                                echo render_select('countries[]', $lead_countries, array('id', 'name'), '', [], array('data-width' => '100%','data-size' => 8, 'data-none-selected-text' => "All Countries", 'multiple' => false, 'data-actions-box' => false), array(), 'no-mbot', 'countries');
                                ?>
                            </div>
                        </div>
                        <br />
                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="send_to[clients]" id="ml_clients">
                                <label for="ml_clients"><?php echo _l('survey_send_mail_list_clients'); ?></label>
                            </div>
                            <div class="customer-groups" style="display:none;">
                                <?php
                                $allCustomers = campaign_email_count(['type' => 'all_customer']);
                                ?>
                                <div class="clearfix"></div>
                                <div class="checkbox checkbox-primary mleft10">
                                    <input type="checkbox" name="ml_customers_all" id="ml_customers_all">
                                    <label for="ml_customers_all"><?php echo _l('survey_customers_all'); ?> <span class="recipient-count">(<?= $allCustomers ?>)</span></label>
                                </div>
                                <div class="checkbox checkbox-primary mleft10">
                                    <input type="checkbox" name="specific_customers" id="specific_customers">
                                    <label for="specific_customers">Specific Customers <span class="recipient-count"></span></label>
                                </div>
                                <div class="form-group select-placeholder customers_selection hide">
                                    <label for="customers_selection">Select Customers</label>
                                    <div id="customers_selection_select">
                                        <select name="customers[]" id="customers_selection" label-id="specific_customers" class="ajax-search" data-width="100%" data-live-search="true" data-none-selected-text="Select Customers" multiple>
                                        </select>
                                    </div>
                                </div>
                                <div class="checkbox checkbox-primary mleft10">
                                    <input type="checkbox" name="customer_group" id="customer_group">
                                    <label for="customer_group">Customer Group</label>
                                </div>
                                <div class="checkbox checkbox-primary mleft10 customer-group-section hide">
                                    <?php foreach ($customers_groups as $group) { ?>
                                        <?php
                                        $groupCount = campaign_email_count(['type' => 'customer_group', 'group_id' => $group['id']]);
                                        ?>
                                        <div class="checkbox checkbox-primary mleft10 camapign-customer-groups" data-customer-group="<?= $group['id']; ?>">
                                            <input type="checkbox" name="customer_group[<?php echo $group['id']; ?>]" id="ml_customer_group_<?php echo $group['id']; ?>" <?= ($groupCount == 0) ? 'disabled' : '' ?>>
                                            <label for="ml_customer_group_<?php echo $group['id']; ?>"><?php echo $group['name']; ?> <span class="recipient-count">(<?= $groupCount ?>)</span></label>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <hr />
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="send_to[leads]" id="ml_leads">
                                <label for="ml_leads"><?php echo _l('leads'); ?></label>
                            </div>
                            <div class="leads-statuses" style="display:none;">
                                <?php
                                $allLeadsCount = campaign_email_count(['type' => 'all_leads']);
                                ?>
                                <div class="clearfix"></div>
                                <div class="checkbox checkbox-primary mleft10">
                                    <input type="checkbox" name="leads_all" id="ml_leads_all">
                                    <label for="ml_leads_all"><?php echo _l('leads_all'); ?> <span class="recipient-count">(<?= $allLeadsCount ?>)</span></label>
                                </div>
                                <div class="checkbox checkbox-primary mleft10">
                                    <input type="checkbox" name="specific_leads" id="specific_leads">
                                    <label for="specific_leads">Specific Leads <span class="recipient-count"></span></label>
                                </div>
                                <div class="form-group select-placeholder leads_selection hide">
                                    <label for="leads_selection">Select Leads</label>
                                    <div id="leads_selection_select">
                                        <select name="lead_ids[]" id="leads_selection" label-id="specific_leads" class="ajax-search" data-width="100%" data-live-search="true" data-none-selected-text="Select Leads" multiple>
                                        </select>
                                    </div>
                                </div>
                                <div class="checkbox checkbox-primary mleft10">
                                    <input type="checkbox" name="lead_status" id="lead_status">
                                    <label for="lead_status">Leads Status</label>
                                </div>
                                <div class="checkbox checkbox-primary mleft10">
                                    <input type="checkbox" name="lead_source" id="lead_source">
                                    <label for="lead_source">Leads Source</label>
                                </div>
                                <hr class="hr-10" />
                                <div class="row">
                                    <div class="col-md-12 lead-source-section hide">
                                        <b>Lead Source</b>
                                        <?php foreach ($leads_sources as $source) {
                                            $sourceCount = campaign_email_count(['type' => 'leads_source', 'source_id' => $source['id']]);
                                        ?>
                                            <div class="checkbox checkbox-primary mleft10 campaign-lead-source" data-source="<?php echo $source['id']; ?>">
                                                <input type="checkbox" name="leads_source[<?php echo $source['id']; ?>]" id="ml_leads_source_<?php echo $source['id']; ?>" <?= ($sourceCount == 0) ? 'disabled' : '' ?>>
                                                <label for="ml_leads_source_<?php echo $source['id']; ?>"><?php echo $source['name']; ?> <span class="recipient-count">(<?= $sourceCount ?>)</span></label>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div class="col-md-12 lead-status-section hide">
                                        <b>Lead Status</b>
                                        <?php foreach ($leads_statuses as $status) {
                                            $statusCount = campaign_email_count(['type' => 'leads_status', 'status_id' => $status['id']]);
                                        ?>
                                            <div class="checkbox checkbox-primary mleft10 campaign-lead-status" data-status="<?php echo $status['id']; ?>">
                                                <input type="checkbox" name="leads_status[<?php echo $status['id']; ?>]" id="ml_leads_status_<?php echo $status['id']; ?>" <?= ($statusCount == 0) ? 'disabled' : '' ?>>
                                                <label for="ml_leads_status_<?php echo $status['id']; ?>"><?php echo $status['name']; ?> <span class="recipient-count">(<?= $statusCount ?>)</span></label>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="send_to[staff]" id="ml_staff">
                                <label for="ml_staff"><?php echo _l('survey_send_mail_list_staff'); ?></label>
                            </div>
                            <div class="staff-groups" style="display:none;">
                                <?php
                                $allStaffCount = campaign_email_count(['type' => 'all_staff']);
                                ?>
                                <div class="clearfix"></div>
                                <div class="checkbox checkbox-primary mleft10">
                                    <input type="checkbox" name="staff_all" id="ml_staff_all">
                                    <label for="ml_staff_all">All Staff <span class="recipient-count">(<?= $allStaffCount ?>)</span></label>
                                </div>
                                <div class="checkbox checkbox-primary mleft10">
                                    <input type="checkbox" name="specific_staff" id="specific_staff">
                                    <label for="specific_staff">Specific Staff <span class="recipient-count"></span></label>
                                </div>
                                <div class="form-group select-placeholder staff_selection hide">
                                    <label for="staff_selection">Select Staff</label>
                                    <div id="staff_selection_select">
                                        <select name="staff_ids[]" id="staff_selection" label-id="specific_staff" class="ajax-search" data-width="100%" data-live-search="true" data-none-selected-text="Select Staff" multiple>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="send_to[list]" id="ml_list">
                                <label for="ml_list">Email Lists</label>
                            </div>
                            <div class="list-groups hide">
                                <?php
                                $allListCount = campaign_email_count(['type' => 'all_list']);
                                ?>
                                <div class="clearfix"></div>
                                <div class="checkbox checkbox-primary mleft10">
                                    <input type="checkbox" name="all_list" id="ml_all_list">
                                    <label for="ml_all_list">All Lists <span class="recipient-count">(<?= $allListCount ?>)</span></label>
                                </div>
                                <div class="checkbox checkbox-primary mleft10">
                                    <input type="checkbox" name="specific_list" id="specific_list">
                                    <label for="specific_list">Specific Email Lists</label>
                                </div>
                                <div class="row">
                                    <hr class="hr-10" />
                                    <div class="col-md-12 specific-email-list-section hide">
                                        <?php
                                        if (!empty($email_lists)) {
                                            foreach ($email_lists as $list) {
                                                $listCount = campaign_email_count(['type' => 'specific_list', 'list_id' => $list['id']]);
                                        ?>
                                                <div class="checkbox checkbox-primary mleft10 campaign-email-list">
                                                    <input type="checkbox" name="email_list[<?php echo $list['id']; ?>]" id="ml_email_list_<?php echo $list['id']; ?>" <?= ($listCount == 0) ? 'disabled' : '' ?>>
                                                    <label for="ml_email_list_<?php echo $list['id']; ?>"><?php echo $list['title']; ?> <span class="recipient-count">(<?= $listCount ?>)</span> </label>
                                                </div>
                                        <?php }
                                        } ?>
                                    </div>
                                </div>
                            </div>
                            <hr />
                            <div>
                                <div class="total-emails pull-left">Total Selected Emails : 0</div>
                                <button type="submit" class="btn btn-info pull-right">Start Campaign</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-header">
                        <h4>Template Preview</h4>
                    </div>
                    <div class="panel-body template-preview-section">
                        <p class="text-center text-muted no-preview-text">No preview available</p>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<?php init_tail(); ?>
<link link rel="stylesheet" type="text/css" href="<?= site_url('assets/plugins/tagify/tagify.css') ?>" />
<script src="<?= site_url('assets/plugins/tagify/tagify.js') ?>"></script>
<script>
    $(document).ready(function() {
        $(window).off('beforeunload');
        init_editor('.tinymce-email-description');
        init_editor('.tinymce-view-description');
        init_ajax_search('staff', '#staff_selection.ajax-search');
        init_ajax_search_for_campaign('customer', '#customers_selection.ajax-search');
        init_ajax_search_for_campaign('leads', '#leads_selection.ajax-search');

        function init_ajax_search_for_campaign(type, selector, url) {
            var ajaxSelector = $('body').find(selector);
            if (ajaxSelector.length) {
                var options = {
                    ajax: {
                        url: "<?= admin_url('email_campaigns/get_relation_data') ?>",
                        data: function() {
                            var data = {};
                            data.type = type;
                            data.rel_id = '';
                            data.q = '{{{q}}}';
                            const staff = $('.staffid :selected').map(function() {
                                return $(this).val();
                            }).get();
                            data.assignee = staff;
                            return data;
                        }
                    },
                    locale: {
                        emptyTitle: app.lang.search_ajax_empty,
                        statusInitialized: app.lang.search_ajax_initialized,
                        statusSearching: app.lang.search_ajax_searching,
                        statusNoResults: app.lang.not_results_found,
                        searchPlaceholder: app.lang.search_ajax_placeholder,
                        currentlySelected: app.lang.currently_selected
                    },
                    requestDelay: 500,
                    cache: false,
                    preprocessData: function(processData) {
                        var bs_data = [];
                        var len = processData.length;
                        for (var i = 0; i < len; i++) {
                            var tmp_data = {
                                'value': processData[i].id,
                                'text': processData[i].name,
                            };
                            if (processData[i].subtext) {
                                tmp_data.data = {
                                    subtext: processData[i].subtext
                                };
                            }
                            bs_data.push(tmp_data);
                        }
                        return bs_data;
                    },
                    preserveSelectedPosition: 'after',
                    preserveSelected: true
                };
                if (ajaxSelector.data('empty-title')) {
                    options.locale.emptyTitle = ajaxSelector.data('empty-title');
                }
                ajaxSelector.selectpicker().ajaxSelectPicker(options);
            }
        }

        $("#campaign_form").appFormValidator({
            rules: {
                title: 'required',
                start_date: 'required',
                template_id: 'required',
                mail_id: 'required',
                max_send_limit: 'required',
            },
            errorPlacement: function(error, element) {
                var formGroup = $(element).closest('.form-group');
                var inputType = $(element).attr('type')
                if (element.hasClass('selectpicker')) {
                    var selectpickerContainer = $(element).closest('.bootstrap-select');
                    error.insertAfter(selectpickerContainer);
                } else {
                    error.insertAfter(element);
                }
            },
            submitHandler: function(form) {
                var $submitBtn = $(form).find('button[type="submit"]');
                $submitBtn.prop('disabled', true);
                var originalText = $submitBtn.html();
                $submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Please wait...');
                form.submit();
            }
        });

        $('select').on('change', function() {
            $(this).closest('.form-group').find('p.text-danger').remove();
            $(this).closest('.form-group').removeClass('has-error');
        });


        $('#leads_selection, #customers_selection, #staff_selection, .countries').on('change', function() {
            const label = $(`label[for="${$(this).attr('label-id')}"]`);
            const selectedCount = $(this).val() ? $(this).val().length : 0;
            let countSpan = label.find('.recipient-count');
            if (selectedCount > 0) {
                if (countSpan.length === 0) {
                    countSpan = $('<span class="recipient-count"></span>');
                    label.append(countSpan);
                }
                countSpan.text("(" + selectedCount + ")");
            } else {
                countSpan.remove();
            }
            countTotalEmails();
        });

        $('input[type="checkbox"]').on('change', function() {
            setTimeout(() => {
                countTotalEmails();
            }, 100);
        });

        $('#template_id').on('change', function() {
            templatePreviewRender($(this).val());
        });

        $('#start_date').on('keydown', function() {
            return false;
        });

        $('#max_send_limit').on('keydown', function(e) {
            var key = e.key;
            if (['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'].includes(key)) {
                return true;
            }
            if (!/^\d$/.test(key)) {
                e.preventDefault();
                return;
            }
            var value = $(this).val();

            if (value === '0') {
                e.preventDefault();
            }
        });

        $('input[name="send_to[leads]"]').on('change', function() {
            if (!$(this).prop('checked')) {
                $('.staff_selection').addClass('hide');
                $('.lead-status-section').addClass('hide');
                $('.lead-source-section').addClass('hide');
                $('.leads_selection').addClass('hide');
                $('.leads-statuses input').prop('checked', false);
            }
            $('.leads-statuses').slideToggle();
        });

        $('input[name="send_to[clients]"]').on('change', function() {
            if (!$(this).prop('checked')) {
                $('.customer-group-section').addClass('hide');
                $('.customers_selection').addClass('hide');
                $('.customer-groups input').prop('checked', false);
            }
            $('.customer-groups').slideToggle();
        });

        $('input[name="send_to[staff]"]').on('change', function() {
            $('.staff-groups input').prop('checked', false);
            $('.staff_selection').addClass('hide');
            $('.staff-groups').slideToggle();
        });

        // leads
        $('#lead_status').on('change', function() {
            if ($(this).prop('checked')) {
                $('.lead-status-section').removeClass('hide');
                // Uncheck other options
                $('#lead_source').prop('checked', false);
                $('.lead-source-section').addClass('hide');
                $('#ml_leads_all').prop('checked', false);
                $('#specific_leads').prop('checked', false);
                $('.leads_selection').addClass('hide');
            } else {
                $('.lead-status-section').addClass('hide');
                $('.campaign-lead-status input').prop('checked', false);
            }
        });

        // Lead source toggle
        $('#lead_source').on('change', function() {
            if ($(this).prop('checked')) {
                $('.lead-source-section').removeClass('hide');
                // Uncheck other options
                $('#lead_status').prop('checked', false);
                $('.lead-status-section').addClass('hide');
                $('#ml_leads_all').prop('checked', false);
                $('#specific_leads').prop('checked', false);
                $('.leads_selection').addClass('hide');
            } else {
                $('.lead-source-section').addClass('hide');
                $('.campaign-lead-source input').prop('checked', false);
            }
        });

        // All leads toggle
        $('#ml_leads_all').on('change', function() {
            if ($(this).prop('checked')) {
                // Uncheck other options
                $('#specific_leads').prop('checked', false);
                $('.leads_selection').addClass('hide');
                $('#lead_status').prop('checked', false);
                $('.lead-status-section').addClass('hide');
                $('#lead_source').prop('checked', false);
                $('.lead-source-section').addClass('hide');
                $('.campaign-lead-status input').prop('checked', false);
                $('.campaign-lead-source input').prop('checked', false);
            }
        });

        // Specific leads toggle
        $('#specific_leads').on('change', function() {
            if ($(this).prop('checked')) {
                $('.leads_selection').removeClass('hide');
                // Uncheck other options
                $('#ml_leads_all').prop('checked', false);
                $('#lead_status').prop('checked', false);
                $('.lead-status-section').addClass('hide');
                $('#lead_source').prop('checked', false);
                $('.lead-source-section').addClass('hide');
                $('.campaign-lead-status input').prop('checked', false);
                $('.campaign-lead-source input').prop('checked', false);
            } else {
                $('.leads_selection').addClass('hide');
            }
        });

        // Individual status checkboxes
        $('.campaign-lead-status input').on('change', function() {
            if ($('.campaign-lead-status input:checked').length > 0) {
                $('#ml_leads_all').prop('checked', false);
                $('#specific_leads').prop('checked', false);
                $('.leads_selection').addClass('hide');
                $('#lead_source').prop('checked', false);
                $('.lead-source-section').addClass('hide');
            }
        });

        // Individual source checkboxes
        $('.campaign-lead-source input').on('change', function() {
            if ($('.campaign-lead-source input:checked').length > 0) {
                $('#ml_leads_all').prop('checked', false);
                $('#specific_leads').prop('checked', false);
                $('.leads_selection').addClass('hide');
                $('#lead_status').prop('checked', false);
                $('.lead-status-section').addClass('hide');
            }
        });

        //customer
        $('#ml_customers_all').on('change', function() {
            $('.camapign-customer-groups input').prop('checked', false);
            $('#customer_group').prop('checked', false);
            $('#specific_customers').prop('checked', false);
            $('.customer-group-section').addClass('hide');
            $('.customers_selection').addClass('hide');
        });
        $('#specific_customers').on('change', function() {
            $('#ml_customers_all').prop('checked', false);
            $('.camapign-customer-groups input').prop('checked', false);
            $('#customer_group').prop('checked', false);
            $('.customer-group-section').addClass('hide');
            if ($(this).prop('checked')) {
                $('.customers_selection').removeClass('hide');
            } else {
                $('.customers_selection').addClass('hide');
            }
        });
        $('#customer_group').on('change', function() {
            $('#ml_customers_all').prop('checked', false);
            $('#specific_customers').prop('checked', false);
            $('.customers_selection').addClass('hide');
            if ($(this).prop('checked')) {
                $('.customer-group-section').removeClass('hide');
            } else {
                $('.customer-group-section').addClass('hide');
            }
        });
        $('.camapign-customer-groups input').on('change', function() {
            $('.customers_selection').addClass('hide');
            $('#ml_customers_all').prop('checked', false);
            $('#specific_customers').prop('checked', false);
        });


        //Staff
        $('#ml_staff').on('change', function() {
            if ($(this).prop('checked')) {
                $('.staff-groups').removeClass('hide');
            } else {
                $('#ml_staff_all').prop('checked', false);
                $('#specific_staff').prop('checked', false);
                $('.staff_selection').addClass('hide');
                $('.staff-groups').addClass('hide');
            }
        });

        $('#ml_staff_all').on('change', function() {
            if ($(this).prop('checked')) {
                $('#specific_staff').prop('checked', false);
                $('.staff_selection').addClass('hide');
            }
        });

        $('#specific_staff').on('change', function() {
            if ($(this).prop('checked')) {
                $('#ml_staff_all').prop('checked', false);
                $('.staff_selection').removeClass('hide');
            } else {
                $('.staff_selection').addClass('hide');
            }
        });

        //Email List
        $('#ml_list').on('change', function() {

            if ($(this).prop('checked')) {
                $('.list-groups').removeClass('hide');
            } else {
                $('.list-groups input').prop('checked', false);
                $('#ml_all_list').prop('checked', false);
                $('#specific_staff').prop('checked', false);
                $('.specific-email-list-section').addClass('hide');
                $('.list-groups').addClass('hide');
            }
        });

        $('#ml_all_list').on('change', function() {
            if ($(this).prop('checked')) {
                $('#specific_list').prop('checked', false);
                $('.specific-email-list-section input').prop('checked', false);
                $('.specific-email-list-section').addClass('hide');
                $('.specific-email-list-sectionection').addClass('hide');
            }
        });

        $('#specific_list').on('change', function() {
            if ($(this).prop('checked')) {
                $('#ml_all_list').prop('checked', false);
                $('.specific-email-list-section').removeClass('hide');
            } else {
                $('.specific-email-list-sectionection').addClass('hide');
            }
        });

        $(document).on('change', 'input[name="mail_send_from"]', function() {
            var selected = $('input[name="mail_send_from"]:checked').val();
            $('#mail_id option').hide();
            if (selected === "staff") {
                $('#mail_id option[data-type="staff"]').show();
            } else {
                $('#mail_id option[data-type="custom_emails"]').show();
            }
            $('#mail_id').selectpicker('refresh');
        });
        $('input[name="mail_send_from"][value="staff"]').prop('checked', true).trigger('change');

        $(document).on('change', '.staffid, .countries', function() {
            countEmailsAccordingStaff();
        });

        $('.email-input').each(function(index) {
            var tagify = new Tagify(this, {
                enforceWhitelist: false,
                pattern: /^[\w+\-.]+@[a-z\d\-.]+\.[a-z]+$/i,
                maxTags: 10,
                dropdown: {
                    maxItems: 20,
                    classname: 'tags-look',
                    enabled: 0,
                    closeOnSelect: true
                }
            }).on('input', function(e) {
                tagify.whitelist = null;
                tagify.loading(true);
                $.ajax({
                    url: "<?= admin_url('webmails/email_suggestions') ?>",
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        query: e.detail.value,
                        type: 'staff'
                    },
                    success: function(result) {
                        tagify.settings.whitelist = result.concat(tagify.value);
                        tagify.loading(false);
                        tagify.dropdown.show(e.detail.value);
                    },
                    error: function(err) {
                        console.error('Error fetching data:', err);
                        tagify.dropdown.hide();
                    }
                });
            });
            tagify.on('add', function(e) {
                if (!tagify.settings.whitelist.includes(e.detail.data.value)) {
                    tagify.settings.whitelist.push(e.detail.data.value);
                }
            });
        });

    });

    function countTotalEmails() {
        var total = 0;
        $('input[type="checkbox"]:checked').each(function() {
            var countText = $(this).closest('.checkbox').find('.recipient-count').text();
            var count = parseInt(countText.replace(/[()]/g, ''), 10) || 0;
            total += count;
        });
        $('.total-emails').text("Total Selected Emails : " + total);
        if (total == 0) {
            $('button[type="submit"]').prop('disabled', true);
        } else {
            $('button[type="submit"]').prop('disabled', false);
        }
    }


    function countEmailsAccordingStaff() {

        const customer_group = $('.checkbox').map(function() {
            return $(this).data('customer-group');
        }).get();

        const source = $('.checkbox').map(function() {
            return $(this).data('source');
        }).get();

        const status = $('.checkbox').map(function() {
            return $(this).data('status');
        }).get();

        const staff = $('.staffid :selected').map(function() {
            return $(this).val();
        }).get();

        const countries = $('.countries :selected').map(function() {
            return $(this).val();
        }).get();

        $.ajax({
            url: "<?php echo admin_url('email_campaigns/countEmails') ?>",
            method: "POST",
            data: {
                staff: staff,
                status: status,
                source: source,
                customer_group: customer_group,
                countries : countries
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                $('label[for="ml_leads_all"]').find('.recipient-count').text("(" + result.data.all_leads + ")");
                $('label[for="ml_customers_all"]').find('.recipient-count').text("(" + result.data.all_customer + ")");
                if (Array.isArray(result.data.customer_group)) {
                    result.data.customer_group.forEach(function(item) {
                        var parent = $('.checkbox[data-customer-group="' + item.id + '"]');
                        parent.find('.recipient-count').text("(" + item.count + ")");
                        if (item.count == 0) {
                            parent.find('input[type="checkbox"]').attr('disabled', true);
                        } else {
                            parent.find('input[type="checkbox"]').removeAttr('disabled');
                        }
                    });
                }
                if (Array.isArray(result.data.status)) {
                    result.data.status.forEach(function(item) {
                        var parent = $('.checkbox[data-status="' + item.id + '"]');
                        parent.find('.recipient-count').text("(" + item.count + ")");
                        if (item.count == 0) {
                            parent.find('input[type="checkbox"]').attr('disabled', true);
                        } else {
                            parent.find('input[type="checkbox"]').removeAttr('disabled');
                        }
                    });
                }

                if (Array.isArray(result.data.source)) {
                    result.data.source.forEach(function(item) {
                        var parent = $('.checkbox[data-source="' + item.id + '"]');
                        parent.find('.recipient-count').text("(" + item.count + ")");
                        if (item.count == 0) {
                            parent.find('input[type="checkbox"]').attr('disabled', true);
                        } else {
                            parent.find('input[type="checkbox"]').removeAttr('disabled');
                        }
                    });
                }

                countTotalEmails();
            }
        });
    }

    function templatePreviewRender(id) {
        var htmlSection = $('.template-preview-section');
        $.ajax({
            url: "<?php echo admin_url('email_campaign_templates/template_preview'); ?>",
            method: "POST",
            data: {
                id: id
            },
            dataType: 'json'
        }).done(function(result) {
            htmlSection.empty();
            if (result.success && result.template_url) {
                var iframe = $('<iframe>', {
                    width: '100%',
                    height: '1020px',
                    frameborder: '0',
                    allowfullscreen: true,
                    src: result.template_url
                });
                htmlSection.append(iframe);
            } else {
                htmlSection.html('<p class="text-center text-muted">No preview available</p>');
            }
        }).fail(function() {
            htmlSection.html('<p class="text-center text-danger">Failed to load preview</p>');
        });
    }
</script>
</body>

</html>