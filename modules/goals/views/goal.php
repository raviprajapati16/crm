<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <?php if (has_permission('goals', '', 'edit') || has_permission('goals', '', 'create')) { ?>
                <div class="col-md-<?php if (!isset($goal)) {
                                        echo '8 col-md-offset-2';
                                    } else {
                                        echo '6';
                                    } ?>">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin"><?php echo $title; ?></h4>
                            <hr class="hr-panel-heading" />
                            <?php echo form_open($this->uri->uri_string()); ?>
                            <?php $attrs = (isset($goal) ? array() : array('autofocus' => true)); ?>
                            <?php $value = (isset($goal) ? $goal->subject : ''); ?>
                            <?php echo render_input('subject', 'goal_subject', $value, 'text', $attrs); ?>
                            <div class="form-group select-placeholder">
                                <label for="goal_type" class="control-label"><?php echo _l('goal_type'); ?></label>
                                <select name="goal_type" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value=""></option>
                                    <?php foreach (get_goal_types() as $type) { ?>
                                        <option value="<?php echo $type['key']; ?>" data-subtext="<?php if (isset($type['subtext'])) {
                                                                                                        echo _l($type['subtext']);
                                                                                                    } ?>" <?php if (isset($goal) && $goal->goal_type == $type['key']) {
                                                                                                            echo 'selected';
                                                                                                        } ?>><?php echo _l($type['lang_key']); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <?php
                            $selected = (isset($goal)) ? get_goal_staff_ids($goal->id) : [];
                            echo render_select('staff_id[]', $members, array('staffid', array('firstname', 'lastname')), 'staff_member', $selected, array('data-none-selected-text' => "Select Staff", "multiple" => true)); ?>
                            <?php $value = (isset($goal) ? $goal->achievement : ''); ?>
                            <?php echo render_input('achievement', 'Goal Target', $value, 'number'); ?>
                            <div class="form-group select-placeholder">
                                <label for="goal_duration_type" class="control-label">Goal Duration</label>
                                <select name="goal_duration_type" class="selectpicker" data-width="100%" data-none-selected-text="Select Goal Duration">
                                    <option value=""></option>
                                    <?php foreach (get_goal_duration_types() as $type) { ?>
                                        <option value="<?php echo $type['key']; ?>" <?php if (isset($goal) && $goal->goal_duration_type == $type['key']) {
                                                                                        echo 'selected';
                                                                                    } ?>><?php echo $type['title']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>

                            <?php $value = (isset($goal) ? _d($goal->start_date) : _d(date('Y-m-d'))); ?>
                            <?php echo render_date_input('start_date', 'goal_start_date', $value, [], [], 'hide'); ?>
                            <?php $value = (isset($goal) ? _d($goal->end_date) : ''); ?>
                            <?php echo render_date_input('end_date', 'goal_end_date', $value, [], [], 'hide'); ?>

                            <div class="hide" id="contract_types">
                                <?php $selected = (isset($goal) ? $goal->contract_type : ''); ?>
                                <?php echo render_select('contract_type', $contract_types, array('id', 'name'), 'goal_contract_type', $selected); ?>
                            </div>
                            <?php $value = (isset($goal) ? $goal->description : ''); ?>
                            <?php echo render_textarea('description', 'goal_description', $value); ?>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="email_when_assign" id="email_when_assign" <?php if (isset($goal)) {
                                                                                                            if ($goal->email_when_assign == 1) {
                                                                                                                echo 'checked';
                                                                                                            }
                                                                                                        } else {
                                                                                                            echo 'checked';
                                                                                                        } ?>>
                                <label for="email_when_assign">Send email when goal assign to staff</label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="notify_when_achieve" id="notify_when_achieve" <?php if (isset($goal)) {
                                                                                                                if ($goal->notify_when_achieve == 1) {
                                                                                                                    echo 'checked';
                                                                                                                }
                                                                                                            } else {
                                                                                                                echo 'checked';
                                                                                                            } ?>>
                                <label for="notify_when_achieve">Send notification when goal achieve</label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="notify_when_fail" id="notify_when_fail" <?php if (isset($goal)) {
                                                                                                            if ($goal->notify_when_fail == 1) {
                                                                                                                echo 'checked';
                                                                                                            }
                                                                                                        } else {
                                                                                                            echo 'checked';
                                                                                                        } ?>>
                                <label for="notify_when_fail">Send notification when goal failed to achieve</label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="email_when_achieve" id="email_when_achieve" <?php if (isset($goal)) {
                                                                                                            if ($goal->email_when_achieve == 1) {
                                                                                                                echo 'checked';
                                                                                                            }
                                                                                                        } else {
                                                                                                            echo 'checked';
                                                                                                        } ?>>
                                <label for="email_when_achieve">Send email when goal achieve</label>
                            </div>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="email_when_fail" id="email_when_fail" <?php if (isset($goal)) {
                                                                                                            if ($goal->email_when_fail == 1) {
                                                                                                                echo 'checked';
                                                                                                            }
                                                                                                        } else {
                                                                                                            echo 'checked';
                                                                                                        } ?>>
                                <label for="email_when_fail">Send email when goal failed to achieve</label>
                            </div>
                            <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                            <?php echo form_close(); ?>
                        </div>
                    </div>
                </div>
                <?php if (isset($goal)) { ?>
                    <div class="col-md-6">
                        <div class="panel_s">
                            <div class="panel-body">
                                <h4 class="no-margin"><?php echo _l('goal_achievement'); ?></h4>
                                <hr class="hr-panel-heading" />
                                <?php
                                $show_acchievement_ribbon = false;
                                $help_text = '';
                                if ($achievement['percent'] == 100) {

                                    $achieve_indicator_class = 'success';
                                    $show_acchievement_ribbon = true;
                                    if ($goal->notified == 1) {
                                        $help_text = '<p class="text-muted text-center">' . _l('goal_staff_members_notified_about_achievement') . '</p>';
                                    }

                                    $notify_type = 'success';
                                    $finished = true;
                                    $lang_key = 'goal_achieved';
                                } else if ($achievement['percent'] >= 80) {
                                    $achieve_indicator_class = 'warning';
                                    $show_acchievement_ribbon = true;
                                    $lang_key = 'goal_close';
                                }
                                if ($show_acchievement_ribbon == true) {
                                    echo '<div class="ribbon ' . $achieve_indicator_class . '"><span>' . _l($lang_key) . '</span></div>';
                                }

                                ?>
                                <h3 class="text-center no-mtop"><?php echo _l('goal_result_heading'); ?>
                                    <small><?php echo _l('goal_total', $achievement['total']); ?></small>
                                </h3>
                                <?php if ($goal->goal_type == 1) {
                                    echo '<p class="text-muted text-center no-mbot">' . _l('goal_income_shown_in_base_currency') . '</p>';
                                }
                                // if ((isset($finished) && $goal->notified == 0) && ($goal->notify_when_achieve == 1 || $goal->notify_when_fail == 1)) {
                                //     echo '<p class="text-center text-info">' . _l('goal_notify_when_end_date_arrives') . '</p>';

                                //     echo '<div class="text-center"><a href="' . admin_url('goals/notify/' . $goal->id . '/' . $notify_type) . '" class="btn btn-default">' . _l('goal_notify_staff_manually') . '</a></div>';
                                // }
                                echo $help_text;
                                ?>
                                <div class="achievement mtop30" data-toggle="tooltip" title="<?php echo _l('goal_total', $achievement['total']); ?>">
                                    <div class="goal-progress" data-thickness="40" data-reverse="true">
                                        <strong class="goal-percent"></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
            <?php if (isset($goal)) { ?>
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin">Staff Goal Progress</h4>
                            <hr class="hr-panel-heading" />
                            <?php
                            if (has_permission('goals', '', 'edit')) {
                                render_datatable(array(
                                    _l('staff_member'),
                                    "Goal Progress",
                                    "Action",
                                ), 'staff-goals');
                            } else {
                                render_datatable(array(
                                    _l('staff_member'),
                                    "Goal Progress",
                                ), 'staff-goals');
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<div class="modal fade" id="goals_details_modal" tabindex="-1" role="dialog" data-backdrop="static">

</div>
<?php init_tail(); ?>
<?php if (isset($goal)) { ?>
    <script>
        var table = initDataTable('.table-staff-goals', "<?= admin_url('goals/staff_table/' . $goal->id) ?>", [], []);
        var table_staff_details;
        $('.table-staff-goals').DataTable().on('draw', function() {
            var rows = $('.table-staff-goals').find('tr');
            $.each(rows, function() {
                var td = $(this).find('td').eq(1);
                var percent = $(td).find('input[name="percent"]').val();
                $(td).find('.goal-progress').circleProgress({
                    value: percent,
                    size: 45,
                    animation: false,
                    fill: {
                        gradient: ["#28b8da", "#059DC1"]
                    }
                })
            })
        })
        $(document).on('change', '.onoffswitch', function() {
            var checkbox = $(this).find('input[type="checkbox"]');
            var status = "";
            if (checkbox.prop('checked')) {
                status = true;
            } else {
                status = false;
            }
            $.ajax({
                url: checkbox.attr("data-switch-url"),
                method: "POST",
                data: {
                    status: status,
                },
                dataType: 'json'
            }).done(function(result) {
                if (result.success) {
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                    alert_float('success', result.message);
                } else {
                    alert_float('danger', result.message);
                }
            });
        });
    </script>
<?php } ?>
<script>
    $(function() {

        $('select[name="goal_duration_type"]').on('change', function() {
            var goal_type = $(this).val();
            if (goal_type == 6) {
                $('#start_date').closest('.form-group').removeClass('hide');
                $('#end_date').closest('.form-group').removeClass('hide');
            } else {
                $('#start_date').closest('.form-group').addClass('hide');
                $('#end_date').closest('.form-group').addClass('hide');
            }
        });
        $('select[name="goal_duration_type"]').trigger("change");
        appValidateForm($('form'), {
            subject: 'required',
            goal_type: 'required',
            goal_duration_type: 'required',
            end_date: 'required',
            start_date: 'required',
            "staff_id[]": 'required',
            start_date: {
                required: {
                    depends: function(element) {
                        return $('select[name="goal_duration_type"]').val() == '6';
                    }
                }
            },
            end_date: {
                required: {
                    depends: function(element) {
                        return $('select[name="goal_duration_type"]').val() == '6';
                    }
                }
            },
            contract_type: {
                required: {
                    depends: function(element) {
                        return $('select[name="goal_type"]').val() == 5 || $('select[name="goal_type"]').val() == 7;
                    }
                }
            }
        });
        <?php if (isset($goal)) { ?>
            var circle = $('.goal-progress').circleProgress({
                value: '<?php echo $achievement['progress_bar_percent']; ?>',
                size: 250,
                fill: {
                    gradient: ["#28b8da", "#059DC1"]
                }
            }).on('circle-animation-progress', function(event, progress, stepValue) {
                $(this).find('strong.goal-percent').html(parseInt(100 * stepValue) + '<i>%</i>');
            });
        <?php } ?>
        var goal_type = $('select[name="goal_type"]').val();
        if (goal_type == 5 || goal_type == 7) {
            $('#contract_types').removeClass('hide');
        }

        $('select[name="goal_type"]').on('change', function() {
            var goal_type = $(this).val();
            if (goal_type == 5 || goal_type == 7) {
                $('#contract_types').removeClass('hide');
            } else {
                $('#contract_types').addClass('hide');
                $('#contract_type').selectpicker('val', '');
            }
        });
    });

    function openGoalModal(goal_id, staff_id) {
        $.ajax({
            url: "<?= admin_url('goals/staff_goal_modal') ?>",
            method: "POST",
            data: {
                goal_id: goal_id,
                staff_id: staff_id
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                $('#goals_details_modal').html(result.html);
                $('#goals_details_modal').modal('show');
                appSelectPicker();
            }
        });
    }
</script>
</body>

</html>