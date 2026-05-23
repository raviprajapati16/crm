<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade lead-reminder-modal reminder-modal-<?php echo $name; ?>-<?php echo $id; ?>" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-keyboard="false" data-backdrop="static">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/misc/add_lead_reminder/' . $id . '/' . $name, array('id' => 'lead-reminder-form')); ?>
            <?php echo form_hidden('rel_id', $id); ?>
            <?php echo form_hidden('rel_type', $name); ?>
            <?php echo form_hidden('id', ""); ?>
            <div class="modal-header">
                <button type="button" class="close close-lead-reminder-modal" data-rel-id="<?php echo $id; ?>" data-rel-type="<?php echo $name; ?>" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $reminder_title; ?></h4>
            </div>
            <div class="modal-body">
                <div class="row cur-action-row">
                    <div class="col-md-12">
                        <a target="_blank" href="<?= site_url('admin/leads/index/' . $id) ?>" class="btn btn-xs btn-primary pull-right"> Lead Profile <i class="fa fa-external-link" aria-hidden="true"></i></a>
                    </div>
                    <div class="col-md-12 mtop10">
                        <?php echo form_hidden('reminder_action', ""); ?>
                        <table class="table">
                            <tr class="action-label-section">
                                <td style="width: 25%;"><strong>Action</strong></td>
                                <td style="width: 5%;">:</td>
                                <td><label class="label label-info"></label></td>
                            </tr>
                            <tr class="meeting-platform-label-section">
                                <td><strong>Meeting Platform</strong></td>
                                <td>:</td>
                                <td><label class="label label-success"></label></td>
                            </tr>
                            <tr class="meeting-link-label-section">
                                <td><strong>Meeting Link</strong></td>
                                <td>:</td>
                                <td><a href="https://zoom.us/" target="_blank">https://zoom.us/</a></td>
                            </tr>
                        </table>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-6">
                        <?php echo render_datetime_input('date', 'Reminder Date', '', array('data-date-min-date' => date('Y-m-d',strtotime('-7 days')))); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_select('staff', $members, array('staffid', array('firstname', 'lastname')), 'Staff User', ""); ?>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status" class="control-label">Status</label>
                            <select name="status" id="status<?php echo $name; ?>-<?php echo $id; ?>" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <option value=""></option>
                                <?php foreach (get_lead_reminder_status() as $key => $item) { ?>
                                    <option data-type="<?= $item['type'] ?>" value="<?= $item['status'] ?>"><?= $item['status'] ?></option>
                                <?php } ?>

                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="action_date" class="control-label">Action Date</label>
                            <input type="text" id="action_date" name="action_date" class="form-control datepicker render-input-disabled" autocomplete="off">
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-4">
                        <div class="form-group start-time-section">
                            <label for="starttime" class="control-label"><small class="req text-danger">* </small>Start Time
                                <button class="btn btn-primary btn-xs start-timer-btn" type="button">Start</button>
                            </label>
                            <input type="text" id="starttime" name="starttime" class="form-control" readonly="1" value="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group end-time-section">
                            <label for="endtime" class="control-label">
                                <small class="req text-danger">* </small>End Time
                                <button class="btn btn-primary btn-xs hide end-timer-btn" type="button">Stop</button>
                            </label>
                            <input type="text" id="endtime" name="endtime" class="form-control" readonly="1" value="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <?php echo render_input('duration', "Duration (HH:MM:SS)", "", "text", array("readonly" => true), [], "render-input-disabled duration-section hide"); ?>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-12">
                        <?php echo render_textarea('description', 'Description'); ?>
                    </div>
                </div>
                <div class="row next-action-row">
                    <div class="col-md-6">
                        <?php echo render_datetime_input('next_date', 'set_reminder_date', '', array('data-date-min-date' => date('Y-m-d',strtotime('-7 days')))); ?>
                    </div>
                    <div class="col-md-6">
                        <?php echo render_select('next_staff', $members, array('staffid', array('firstname', 'lastname')), 'reminder_set_to', get_staff_user_id(), array('data-current-staff' => get_staff_user_id())); ?>
                    </div>
                    <div class="col-md-6">
                        <?php $actionArr = array(["name" => "Call"], ["name" => "Online Meeting"], ["name" => "Plant Visit"], ["name" => "Face To Face"]);
                        echo render_select('next_reminder_action', $actionArr, array("name", "name"), "Action"); ?>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group meeting-platform-section hide">
                            <label class="meeting-platform-section-lbl"><span class="text-danger">* </span>Meeting Platform
                                <span class="meeting-create-link-section">
                                    <a class="create-meeting-platform-link hide" href="https://meet.google.com/" target="_blank"> Create Meeting<i class="fa fa-external-link" aria-hidden="true"></i></a>
                                </span>
                            </label>
                            <select name="next_meeting_platform" id="next_meeting_platform" class="form-control">
                                <option value="">Select Platform</option>
                                <option value="Google Meet">Google Meet</option>
                                <option value="Zoom">Zoom</option>
                                <option value="Microsoft Teams">Microsoft Teams</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <?php echo render_input('next_meeting_link', "Online Meeting Link", "", "text", [], [], "meeting-link-section hide"); ?>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group meeting-email-send-section hide">
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="next_is_meeting_email_send" id="is_meeting_email_send">
                                <label for="is_meeting_email_send">Send meeting email to customer</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <?php echo render_textarea('next_description', 'reminder_description'); ?>
                    </div>
                    <div class="col-md-12">
                        <?php if (total_rows(db_prefix() . 'emailtemplates', array('slug' => 'reminder-email-staff', 'active' => 0)) == 0) { ?>
                            <div class="form-group">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="next_notify_by_email" id="notify_by_email">
                                    <label for="notify_by_email"><?php echo _l('reminder_notify_me_by_email'); ?></label>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default close-lead-reminder-modal" data-rel-id="<?php echo $id; ?>" data-rel-type="<?php echo $name; ?>"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-save-reminder btn-info"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
</div>
<script>
    var startTime, endTime, timerInterval;
    $(document).ready(function() {
        $('.lead-reminder-modal .start-timer-btn').prop('disabled', false);
        $('.lead-reminder-modal select[name="status"] option').hide();
        $('.lead-reminder-modal select[name="status"]').selectpicker('refresh');

        $(document).off('change', 'select[name="next_reminder_action"]');
        $(document).on('change', 'select[name="next_reminder_action"]', function() {
            var modal = $(this).closest('.modal')
            var form_group = $(this).closest('.form-group')
            form_group.find('p.text-danger').remove();
            form_group.removeClass('has-error');
            if (this.value == "Online Meeting") {
                modal.find('.meeting-platform-section').removeClass('hide');
                modal.find('.meeting-link-section').removeClass('hide');
                modal.find('.meeting-email-send-section').removeClass('hide');
            } else {
                modal.find('.meeting-platform-section').addClass('hide');
                modal.find('.meeting-link-section').addClass('hide');
                modal.find('.meeting-email-send-section').addClass('hide');
            }
        });

        $(document).off('input', 'input[name="next_meeting_link"]');
        $(document).on('input', 'input[name="next_meeting_link"]', function() {
            var modal = $(this).closest('.modal');
            var formgroup = $(this).closest('.form-group');
            formgroup.find('span.error').remove();
            var url = $(this).val();
            var platformCheck = meetinglinkValidate(url);
            if (platformCheck) {
                modal.find("#next_meeting_platform").val(platformCheck);
                modal.find("#next_meeting_platform").trigger('change');
            } else {
                $(formgroup).append("<span class='error text-danger'>Invalid meeting link</span>")
            }
        });

        $(document).off('change', '#next_meeting_platform');
        $(document).on('change', '#next_meeting_platform', function() {
            var div = $(this).closest('.meeting-platform-section');
            div.find('.create-meeting-platform-link').removeClass('hide');
            if ($(this).val() == "Google Meet") {
                div.find('.create-meeting-platform-link').attr("href", "https://meet.google.com/");
            } else if ($(this).val() == "Zoom") {
                div.find('.create-meeting-platform-link').attr("href", "https://zoom.us/");
            } else if ($(this).val() == "Microsoft Teams") {
                div.find('.create-meeting-platform-link').attr("href", "https://teams.microsoft.com");
            } else {
                div.find('.create-meeting-platform-link').addClass('hide');
            }
        });

        $(document).off('click', '.btn-reminder-modal');
        $(document).on('click', '.btn-reminder-modal', function() {
            reminderFormReset();
            $('.lead-reminder-modal').find('.has-error').removeClass('has-error');
            $('.lead-reminder-modal').find('.text-danger').remove();
            $('.lead-reminder-modal .btn-save-reminder').prop('disabled', false);
            $('.lead-reminder-modal .btn-save-reminder').html('Save');
            var modal = $('.lead-reminder-modal');
            var form = modal.find('form');
            var reminderId = $(this).attr('data-id');
            var type = $(this).attr('data-type');
            $.ajax({
                url: "<?php echo admin_url('misc/get_reminder_single') ?>",
                method: "POST",
                dataType: 'json',
                data: {
                    reminderId: reminderId,
                    leadId: $('input[name="leadid"]').val()
                },
            }).done(function(result) {
                if (result.success) {
                    setReminderAction(result.data);
                    form.find('select[name="status"]').val(result.data.status).trigger('change');
                    form.find('input[name="starttime"]').val(result.data.starttime);
                    form.find('input[name="endtime"]').val(result.data.endtime);
                    form.find('input[name="id"]').val(result.data.id);
                    form.find('input[name="duration"]').val(result.data.duration);
                    form.find('textarea[name="description"]').val(result.data.description);
                    form.find('select[name="staff"]').val(result.data.staff).selectpicker('refresh');
                    if (result.data.action_date == null || result.data.action_date == "") {
                        var today = new Date();
                        var currentDate = ("0" + today.getDate()).slice(-2) + "-" +
                            ("0" + (today.getMonth() + 1)).slice(-2) + "-" +
                            today.getFullYear();
                        form.find('input[name="action_date"]').val(currentDate);
                    } else {
                        form.find('input[name="action_date"]').val(result.data.action_date);
                    }
                    form.find('input[name="date"]').val(result.data.date);
                    $('.cur-action-row').removeClass('hide');
                    $('.cur-action-row').find('input[name="first_reminder"]').remove();

                    //Next reminder hide
                    $('.next-action-row').addClass('hide');
                    $('.next-action-row').find('input[type="text"]').val("");
                    $('.next-action-row').find('select').val("").trigger('change');
                    $('.next-action-row').find('textarea').val("");
                    $('.next-action-row').find('input[type="checkbox"]').prop("checked", false);

                    modal.find('.modal-title').text("Update " + result.data.reminder_action + " Details");
                    modal.modal('show');
                } else {
                    alert_float('danger', result.message);
                }
            });
        });

        $(document).off('click', '.reminder-lead-modal-btn');
        $(document).on('click', '.reminder-lead-modal-btn', function() {
            $('.lead-reminder-modal .btn-save-reminder').prop('disabled', false);
            $('.lead-reminder-modal .btn-save-reminder').html('Save');
            $('.lead-reminder-modal').find('.has-error').removeClass('has-error');
            $('.lead-reminder-modal').find('.text-danger').remove();
            var modal = $('.lead-reminder-modal');
            $.ajax({
                url: "<?php echo admin_url('misc/check_lead_reminder_exists') ?>",
                method: "POST",
                dataType: 'json',
                data: {
                    leadId: $('input[name="leadid"]').val()
                },
            }).done(function(result) {
                if (result.success) {
                    //first time set default action call.
                    if (!result.exists) {
                        setReminderAction({
                            reminder_action: "Call",
                        });
                        $('.cur-action-row').append("<input type='hidden' name='first_reminder' value='1' />");
                        //Cur Reminder Show
                        $('.cur-action-row').removeClass('hide');
                        $('.cur-action-row').find('input[type="text"]').val("");
                        $('.cur-action-row').find('select').val("").trigger('change');
                        $('.cur-action-row').find('textarea').val("");
                        $('.cur-action-row').find('input[type="checkbox"]').prop("checked", false);
                        //Next reminder hide
                        $('.next-action-row').addClass('hide');
                        $('.next-action-row').find('input[type="text"]').val("");
                        $('.next-action-row').find('select').val("").trigger('change');
                        $('.next-action-row').find('textarea').val("");
                        $('.next-action-row').find('input[type="checkbox"]').prop("checked", false);
                        reminderFormReset();
                        //set current date in action date
                        var today = new Date();
                        var curDateTime = new Date();
                        var currentDate = ("0" + today.getDate()).slice(-2) + "-" +
                            ("0" + (today.getMonth() + 1)).slice(-2) + "-" +
                            today.getFullYear();

                        var hours = today.getHours();
                        var minutes = ("0" + today.getMinutes()).slice(-2);
                        var ampm = hours >= 12 ? 'PM' : 'AM';
                        hours = hours % 12;
                        hours = hours ? hours : 12;
                        var currentTime = hours + ":" + minutes + " " + ampm;
                        var currentDateTime = currentDate + " " + currentTime;
                        $('.cur-action-row').find('input[name="action_date"]').val(currentDate);
                        $('.cur-action-row').find('input[name="date"]').val(currentDateTime);
                        $('.cur-action-row').find('select[name="staff"]').val("<?= get_staff_user_id() ?>").selectpicker('refresh');
                        modal.find('.modal-title').text("Update Call Details");
                        modal.modal('show');
                    } else {
                        $('.cur-action-row').find('input[name="first_reminder"]').remove();
                        //Cur Reminder Show
                        $('.cur-action-row').addClass('hide');
                        $('.cur-action-row').find('input[type="text"]').val("");
                        $('.cur-action-row').find('select').val("").trigger('change');
                        $('.cur-action-row').find('textarea').val("");
                        $('.cur-action-row').find('input[type="checkbox"]').prop("checked", false);
                        //Next reminder hide
                        $('.next-action-row').removeClass('hide');
                        $('.next-action-row').find('input[type="text"]').val("");
                        $('.next-action-row').find('select').val("").trigger('change');
                        $('.next-action-row').find('textarea').val("");
                        $('.next-action-row').find('input[type="checkbox"]').prop("checked", false);
                        reminderFormReset();
                        modal.find('.modal-title').text("Create New Reminder");
                        modal.modal('show');
                    }
                }
            });
        });

        $(document).off('change', 'input[name="starttime"]');
        $(document).on('change', 'input[name="starttime"]', function() {
            var form = $(this).closest('.modal').find('form');
            calculateDuration(form);
        });

        $(document).off('change', 'input[name="endtime"]');
        $(document).on('change', 'input[name="endtime"]', function() {
            var form = $(this).closest('.modal').find('form');
            calculateDuration(form);
        });

        $(document).off('change', 'select[name="status"]');
        $(document).on('change', 'select[name="status"]', function() {
            var modal = $(this).closest('.modal');
            resetTimer();
            if (this.value == "Attend") {
                modal.find('.start-time-section').removeClass('hide');
                modal.find('.end-time-section').removeClass('hide');
                modal.find('.duration-section').removeClass('hide');
            } else {
                modal.find('.start-time-section').addClass('hide');
                modal.find('.end-time-section').addClass('hide');
                modal.find('.duration-section').addClass('hide');
            }
        });

        $(document).off("change", 'select');
        $(document).on("change", 'select', function(e) {
            var formgroup = $(this).closest('.form-group');
            formgroup.removeClass('has-error');
            formgroup.find('p.text-danger').remove();
        });

        $('#lead-reminder-form').appFormValidator({
            rules: {
                date: {
                    required: function(element) {
                        return !$('#lead-reminder-form').find('.cur-action-row').hasClass('hide');
                    }
                },
                staff: {
                    required: function(element) {
                        return !$('#lead-reminder-form').find('.cur-action-row').hasClass('hide');
                    }
                },
                status: {
                    required: function(element) {
                        return !$('#lead-reminder-form').find('.cur-action-row').hasClass('hide');
                    },
                },
                action_date: {
                    required: function(element) {
                        return !$('#lead-reminder-form').find('.cur-action-row').hasClass('hide');
                    },
                },
                starttime: {
                    required: function(element) {
                        var status = $('#lead-reminder-form').find('select[name="status"]').val();
                        return !$('#lead-reminder-form').find('.cur-action-row').hasClass('hide') && status == 'Attend';
                    },
                },
                endtime: {
                    required: function(element) {
                        var status = $('#lead-reminder-form').find('select[name="status"]').val();
                        return !$('#lead-reminder-form').find('.cur-action-row').hasClass('hide') && status == 'Attend';
                    },
                },
                duration: {
                    required: function(element) {
                        var status = $('#lead-reminder-form').find('select[name="status"]').val();
                        return !$('#lead-reminder-form').find('.cur-action-row').hasClass('hide') && status == 'Attend';
                    },
                },
                description: {
                    required: function(element) {
                        return !$('#lead-reminder-form').find('.cur-action-row').hasClass('hide');
                    },
                },
                next_date: {
                    required: function(element) {
                        return !$('#lead-reminder-form').find('.next-action-row').hasClass('hide');
                    }
                },
                next_staff: {
                    required: function(element) {
                        return !$('#lead-reminder-form').find('.next-action-row').hasClass('hide');
                    }
                },
                next_reminder_action: {
                    required: function(element) {
                        return !$('#lead-reminder-form').find('.next-action-row').hasClass('hide');
                    }
                },
                next_meeting_platform: {
                    required: function(element) {
                        return !$('#lead-reminder-form').find('.next-action-row').hasClass('hide') && $('#lead-reminder-form').find('select[name="next_reminder_action"]').val() === 'Online Meeting';
                    }
                },
                next_meeting_link: {
                    required: function(element) {
                        return !$('#lead-reminder-form').find('.next-action-row').hasClass('hide') && $('#lead-reminder-form').find('select[name="next_reminder_action"]').val() === 'Online Meeting';
                    }
                },
                next_description: {
                    required: function(element) {
                        return !$('#lead-reminder-form').find('.next-action-row').hasClass('hide');
                    }
                },
            },
            errorPlacement: function(error, element) {
                var formGroup = $(element).closest('.form-group');
                $(formGroup).append(error);
            },
            submitHandler: function(form) {
                var $form = $(form);
                var data = $form.serialize();
                $('.btn-save-reminder').prop('disabled', true);
                $('.btn-save-reminder').html('<i class="fa fa-spinner fa-spin"></i> Please Wait...');
                $.post($form.attr('action'), data).done(function(response) {
                    response = JSON.parse(response);
                    if (response.message !== '') {
                        alert_float(response.alert_type, response.message);
                    }
                    $form.trigger('reinitialize.areYouSure');
                    if ($('#task-modal').is(':visible')) {
                        _task_append_html(response.taskHtml);
                    }
                    reload_reminders_tables();
                    $('.lead-reminder-modal').modal('hide');
                }).always(function() {
                    $('.btn-save-reminder').prop('disabled', false);
                    $('.btn-save-reminder').html('Save Reminder');
                });

                resetTimer();
                return false;

            }
        });

        $(document).off("keydown", '#action_date');
        $(document).on("keydown", '#action_date', function(e) {
            return false;
        });

        $(document).off("click", '.start-timer-btn');
        $(document).on("click", '.start-timer-btn', function(e) {
            $(this).prop('disabled', true);
            startTime = new Date();
            $('#starttime').val(startTime.toLocaleTimeString('en-GB', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
            }));
            timerInterval = setInterval(updateDuration, 1000);
            var formgroup = $(this).closest('.form-group');
            formgroup.removeClass('has-error')
            formgroup.find('.text-danger').remove();
            $('#endtime').val("");
        });

        $(document).off("click", '.end-timer-btn');
        $(document).on("click", '.end-timer-btn', function(e) {
            endReminderTimer();
        });

        $(document).off("click", '.close-lead-reminder-modal');
        $(document).on("click", '.close-lead-reminder-modal', function(e) {
            if ($('.lead-reminder-modal select[name="status"]').val() == "Attend" && startTime != null && endTime == null) {
                alert_float('danger', "You can't close this popup. beacuse timer is running please click on save to stop the timer and save the data.");
                return false;
            }
            $(".reminder-modal-" + $(this).data('rel-type') + '-' + $(this).data('rel-id')).modal('hide');
        });

        $(document).off("click", '.btn-save-reminder');
        $(document).on("click", '.btn-save-reminder', function(e) {
            if (startTime != null) {
                endReminderTimer();
            }
        });
    });

    function endReminderTimer() {
        clearInterval(timerInterval);
        endTime = new Date();
        $('#endtime').val(endTime.toLocaleTimeString('en-GB', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        }));
        updateDuration();
        var formgroup = $('.end-timer-btn').closest('.form-group');
        formgroup.removeClass('has-error')
        formgroup.find('.text-danger').remove();
    }

    function reminderFormReset() {
        resetTimer();
        $('.lead-reminder-modal .start-timer-btn').prop('disabled', false);
        $('.lead-reminder-modal form')[0].reset();
        $('.lead-reminder-modal input[name="id"]').val("");
        $('.lead-reminder-modal select[name="status"]').val("").trigger('change');
        $('.lead-reminder-modal select[name="next_reminder_action"]').val("").trigger('change');
        $('.lead-reminder-modal select[name="next_staff"]').val(<?= get_staff_user_id() ?>).trigger('change');
    }

    function updateDuration() {
        if (startTime) {
            var formgroup = $('#duration').closest('.form-group');
            formgroup.removeClass('has-error')
            formgroup.find('.text-danger').remove();
            var now = new Date();
            var durationInSeconds = Math.floor((now - startTime) / 1000);
            var hours = Math.floor(durationInSeconds / 3600);
            var minutes = Math.floor((durationInSeconds % 3600) / 60);
            var seconds = durationInSeconds % 60;
            var duration = `${hours < 10 ? '0' : ''}${hours}:${minutes < 10 ? '0' : ''}${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            $('#duration').val(duration);
            resetLogoutTimer();
        }
    }

    function resetTimer() {
        clearInterval(timerInterval);
        $('#starttime').val('');
        $('#endtime').val('');
        $('#duration').val('');
        startTime = null;
        endTime = null;
    }

    function setReminderAction(data) {
        var modal = $('.lead-reminder-modal');
        modal.find('select[name="status"] option').hide();
        modal.find('select[name="status"] option[data-type="' + data.reminder_action + '"]').show();
        modal.find('select[name="status"]').selectpicker('refresh');

        modal.find('input[name="reminder_action"]').val(data.reminder_action);
        modal.find('.action-label-section').find('label').text(data.reminder_action);
        if (data.reminder_action == "Online Meeting") {
            modal.find('.meeting-platform-label-section').removeClass('hide');
            modal.find('.meeting-platform-label-section').find('label').text(data.meeting_platform);
            modal.find('.meeting-link-label-section').removeClass('hide');
            modal.find('.meeting-link-label-section').find('a').text(data.meeting_link);
            modal.find('.meeting-link-label-section').find('a').attr('href', data.meeting_link);
        } else {
            modal.find('.meeting-platform-label-section').addClass('hide');
            modal.find('.meeting-platform-label-section').find('label').text("");
            modal.find('.meeting-link-label-section').addClass('hide');
            modal.find('.meeting-link-label-section').find('a').text("");
            modal.find('.meeting-link-label-section').find('a').attr('href', "");
        }
    }

    function calculateDuration(form) {
        var startTime = form.find('input[name="starttime"]').val();
        var endTime = form.find('input[name="endtime"]').val();
        form.find('input[name="duration"]').closest('.form-group').find('.error').remove();
        var duration = "";

        if (startTime !== "" && endTime !== "") {
            const [startHour, startMinute] = startTime.split(':').map(Number);
            const [endHour, endMinute] = endTime.split(':').map(Number);

            if (endHour < startHour || (endHour === startHour && endMinute < startMinute)) {
                duration = "";
            } else {
                let durationInMinutes = (endHour * 60 + endMinute) - (startHour * 60 + startMinute);
                if (durationInMinutes < 0) {
                    durationInMinutes += 24 * 60;
                }
                if (durationInMinutes === 0 || durationInMinutes >= 24 * 60) {
                    duration = "";
                } else {
                    const hours = Math.floor(durationInMinutes / 60);
                    const minutes = durationInMinutes % 60;
                    duration = `${hours < 10 ? '0' : ''}${hours}:${minutes < 10 ? '0' : ''}${minutes}`;
                }
            }
            if (duration === "") {
                form.find('input[name="duration"]').after("<span class='error text-danger'>Invalid duration. Please select correct start and end time</span>");
            }
        } else {
            duration = "";
        }
        if (duration != "") {
            form.find('input[name="duration"]').closest('.form-group').removeClass('has-error');
            form.find('input[name="duration"]').closest('.form-group').find('p.text-danger').remove();
        }
        form.find('input[name="duration"]').val(duration);
    }
</script>
<style>
    .nex-action-title {
        font-size: 16px;
    }

    .lead-reminder-modal table {
        margin-top: 0px;
    }

    .meeting-platform-section-lbl {
        width: 100% !important;
    }

    .meeting-create-link-section {
        margin-left: 20%;
    }

    .start-timer-btn,
    .end-timer-btn {
        font-size: 10px !important;
        padding: 2px 8px !important;
    }
</style>