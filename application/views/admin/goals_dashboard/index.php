<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<link rel="stylesheet" href="<?= site_url('assets/plugins/daterangepicker/daterangepicker.css') ?>" />
<?php init_tail(); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.41.0/apexcharts.min.js"></script>
<script src="<?= site_url('assets/plugins/daterangepicker/daterangepicker.min.js') ?>"></script>
<script src="<?= site_url('assets/plugins/html2canvas/html2canvas.min.js') ?>"></script>
<script src="<?= site_url('assets/plugins/js-pdf/jspdf.umd.min.js') ?>"></script>
<style>
    #loadingOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .panel .panel-charts {
        color: #fff;
        background-color: #456e36;
        border-color: #456e36;
    }

    .panel .panel-primary {
        color: #fff;
        background-color: #456e36;
        border-color: #456e36;
    }

    .main-panel>.panel-heading {
        color: #fff;
        background-color: #456e36;
        border-color: #456e36;
    }

    .table.table {
        margin-top: 0px;
        margin-bottom: 10px;
    }

    .report-btn {
        margin-top: 25px;
    }

    .dynamicSection {
        margin-top: 10px;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <div class="panel-title">Goals Dashboard</div>
                    </div>
                    <div class="panel-body">
                        <div class="row filter-section">
                            <?php if (has_permission('goals_dashboard', '', 'view')) { ?>
                                <div class="col-md-3">
                                    <label for="staffid">Staff</label>
                                    <div class="form-group">
                                        <select id="staffid" name="staffid" class="selectpicker" data-live-search="true" data-none-selected-text="Select Staff" data-width="100%">
                                            <option value="" selected></option>
                                            <?php if (!empty($staff)) {
                                                foreach ($staff as $user) {
                                            ?>
                                                    <option value="<?= $user['staffid'] ?>" <?= ($selected) ? "selected" : "" ?>><?= $user['firstname'] ?> <?= $user['lastname'] ?></option>
                                            <?php
                                                }
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if (has_permission('goals_dashboard', '', 'report_generate')) { ?>
                                <div class="col-md-3">
                                    <div class="btn-group report-btn hide">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-file-pdf-o"></i> Report <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="#" onclick="generatePDF('view')">View</a></li>
                                            <li><a href="#" onclick="generatePDF('download')">Download</a></li>
                                        </ul>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="row">
                            <div class="col-md-12 dynamicSection">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
<script>
    $(document).ready(function() {
        dynamicSection();
        $(document).on('change', '#staffid', function() {
            dynamicSection();
        });
    });

    function dynamicSection() {
        var staff = "<?= get_staff_user_id(); ?>";
        if ($('#staffid').length > 0) {
            staff = $('#staffid').val();
        }
        $('.report-btn').addClass('hide');
        $('.dynamicSection').html('<div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;" id="spinner" class="spinner-container"><div class="dt-loader"><span></span></div></div>');
        $.ajax({
            url: "<?php echo admin_url('goals_dashboard/main_render') ?>",
            method: "POST",
            data: {
                staff: staff
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                $('.report-btn').removeClass('hide');
                $('.dynamicSection').html(result.html);
            }
        });
    }

    function generatePDF(type) {
        $('body').append('<div id="loadingOverlay"><div style="display: flex; justify-content: center; align-items: center; margin-top: 30vh; height: 350px; font-size: 18px; color: #888;" id="spinner" class="spinner-container"><div class="dt-loader"><span></span></div></div></div>');
        var staff = "<?= get_staff_user_id(); ?>";
        if ($('#staffid').length > 0) {
            staff = $('#staffid').val();
        }
        const payload = {
            staff: staff,
            goals: []
        };
        $('.main-panel').each(function() {
            const $panel = $(this);
            const goal_duration_type = $panel.attr('data-goal-duration-type');
            const item = {
                id: $panel.attr('data-goal-id'),
                goal_duration_type: goal_duration_type
            };

            switch (goal_duration_type) {
                case '1':
                    item.date_range = $panel.find('.daterange').val();
                    break;
                case '2':
                    item.date_range = $panel.find('.week-dropdown').val();
                    break;
                case '3':
                    item.date_range = $panel.find('.month-dropdown').val();
                    break;
                case '4':
                    item.date_range = $panel.find('.quarter-dropdown').val();
                    break;
                case '5':
                    item.date_range = $panel.find('.year-half-dropdown').val();
                    break;
                case '7':
                    item.year = $panel.find('.year-dropdown').val();
                    break;
            }
            payload.goals.push(item);
        });

        $.ajax({
            url: "<?php echo admin_url('goals_dashboard/pdf') ?>",
            method: "POST",
            data: payload,
            dataType: 'json'
        }).done(function(result) {
            $('#loadingOverlay').remove();
            if (result.success) {
                const a = document.createElement('a');
                if (type === "download") {
                    a.href = result.pdf_url;
                    a.setAttribute('download', 'Goals Report.pdf');
                } else {
                    a.href = result.view_url;
                    a.target = '_blank';
                    a.rel = 'noopener noreferrer';
                }
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } else {
                alert_float('danger', result.message);
            }
        });
    }
</script>

</html>