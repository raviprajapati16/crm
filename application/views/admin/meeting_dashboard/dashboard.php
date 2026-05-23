<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    #calendar-pvd {
        width: 80%;
    }

    .calendar-pvd-section {
        text-align: -webkit-center !important;
    }

    .date-section {
        margin-top: -16%;
    }

    .date-clear-button {
        border: none;
        background: none;
        color: #6c757d;
        font-size: 20px;
        cursor: pointer;
        position: absolute;
        top: 2%;
        right: 35px;
    }



    .date-clear-button:hover {
        color: #dc3545;
    }


    @media (max-width: 768px) {
        #calendar-pvd {
            width: 100%;
        }
    }
</style>
<div id="wrapper" data-module="meeting_dashboard">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel">
                    <div class="panel-heading">
                        <div class="panel-title">Meeting Dashboard</div>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row filter-section">
                                    <div class="col-md-2">
                                        <label for="meeting_type">Meeting Type</label>
                                        <div class="form-group">
                                            <select id="meeting_type" name="meeting_type" class="selectpicker" data-live-search="true" data-none-selected-text="Select Meeting Type" data-width="100%">
                                                <option value=""></option>
                                                <option value="Online Meeting">Online Meeting</option>
                                                <option value="Face To Face">Face To Face Meeting</option>
                                                <option value="Plant Visit">Plant Visit</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="status">Status</label>
                                        <div class="form-group">
                                            <select id="status" name="status" class="selectpicker" data-live-search="true" data-none-selected-text="Select Status" data-width="100%">
                                                <option value="" selected></option>
                                                <?php foreach (get_lead_reminder_status() as $key => $item) {
                                                    if ($item['type'] != "Call") {
                                                ?>
                                                        <option data-type="<?= $item['type'] ?>" value="<?= $item['status'] ?>"><?= $item['status'] ?></option>
                                                <?php  }
                                                } ?>
                                                <option data-type="Plant Visit" value="Pending">Pending</option>
                                                <option data-type="Plant Visit" value="Approved">Approved</option>
                                                <option data-type="Plant Visit" value="Not Approved">Not Approved</option>
                                            </select>
                                        </div>
                                    </div>
                                    <?php
                                    $is_manager = (get_staff(get_staff_user_id())->role == "3") ? true : false;
                                    ?>
                                    <?php if (is_super_admin() || $is_manager) { ?>
                                        <div class="col-md-2">
                                            <label for="staff_id">Staff</label>
                                            <div class="form-group">
                                                <select id="staff_id" name="staff_id" class="selectpicker" data-live-search="true" data-none-selected-text="Select Staff" data-width="100%">
                                                    <?php if (is_super_admin()) { ?>
                                                        <option value=""></option>
                                                    <?php } ?>
                                                    <?php if (!empty($staff)) {
                                                        $selected = false;
                                                        foreach ($staff as $user) {
                                                            if ($is_manager) {
                                                                if (!in_array($user['staffid'], get_manager_assigned_staff_ids()) && $user['staffid'] != get_staff_user_id()) {
                                                                    continue;
                                                                }
                                                                if ($user['staffid'] == get_staff_user_id()) {
                                                                    $selected = true;
                                                                }
                                                            }
                                                            if (!is_staff_in_sales_department($user['staffid'])) {
                                                                continue;
                                                            }
                                                    ?>
                                                            <option value="<?= $user['staffid'] ?>" <?= ($selected) ? "selected" : "" ?>><?= $user['firstname'] ?> <?= $user['lastname'] ?></option>
                                                    <?php
                                                        }
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <input type="hidden" name="staff_id" id="staff_id" value="<?= get_staff_user_id() ?>" />
                                    <?php }  ?>
                                </div>
                            </div>
                            <div class="col-md-12 text-center">
                                <div class="panel_s">
                                    <div class="panel-heading">
                                        <div class="panel-title">Meetings Calendar</div>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-12 calendar-pvd-section">
                                                <div id="calendar-pvd"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="panel_s">
                                    <div class="panel-heading">
                                        <div class="panel-title">Meetings Details</div>
                                        <div class="pull-right">
                                            <div class="form-group date-section position-relative">
                                                <input type="text" class="form-control datepicker" id="dateinput" placeholder="Select Date">
                                                <div class="date-clear-button" title="Clear Date">
                                                    &times;
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <input type="hidden" id="hiddeYear" value="" />
                                                <input type="hidden" id="hiddenMonth" value="" />
                                                <?php render_datatable(
                                                    array("ID", "Lead ID", "Meeting Type", "Staff", "Status", "Date & Time"),
                                                    'meeting-data',
                                                    [],
                                                    array(
                                                        'id' => 'table-meeting-data',
                                                    )
                                                ); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
    <link href="<?= site_url("assets/plugins/fullcalendar/fullcalendar.min.css"); ?>" rel="stylesheet" type="text/css" />
    <script src="<?= site_url("assets/plugins/fullcalendar/fullcalendar.min.js"); ?>"></script>
    <script>
        var table;
        var calendar;
        $(document).ready(function() {
            $(window).off('beforeunload');
            $('#status option').hide();
            $('#status').selectpicker('refresh');
            setTimeout(() => {
                $(document).find('#meeting_type').trigger('change');
            }, 100);

            $('.date-section .date-clear-button').on('click', function() {
                $(this).siblings('#dateinput').val('');
                if (table) {
                    table.draw();
                }
            });

            $('.date-section #dateinput').on('change', function() {
                if (table) {
                    table.draw();
                }
            });

            calendar = $('#calendar-pvd').fullCalendar({
                defaultView: 'month',
                viewRender: function(view, element) {
                    $('.dataTables_wrapper input[type="search"]').val("").trigger('keyup');
                    getCalendarData();
                    var startOfMonth = view.intervalStart.format('YYYY-MM-DD');
                    var endOfMonth = view.intervalEnd.subtract(1, 'days').format('YYYY-MM-DD');
                    $('.datepicker').datetimepicker({
                        format: 'd-m-Y',
                        timepicker: false,
                        minDate: moment(startOfMonth).toDate(),
                        maxDate: moment(endOfMonth).toDate(),
                        onShow: function(ct, $input) {
                            this.setOptions({
                                minDate: moment(startOfMonth).toDate(),
                                maxDate: moment(endOfMonth).toDate()
                            });
                        }
                    });
                },
                eventClick: function(event) {
                    if (event.customData.lead_id) {
                        init_lead(event.customData.lead_id);
                    }
                },
                eventRender: function(event, element) {
                    if (event.description) {
                        element.attr('title', event.description);
                    }

                    element.tooltip({
                        content: event.description,
                        track: true
                    });
                },
                selectable: true,
                select: function(start, end) {
                    var selectedDate = moment(start).format('DD-MM-YYYY');
                    $('#dateinput').val(selectedDate).trigger('change');
                }
            });

            $(document).on('change', '#status', function() {
                getCalendarData();
            });

            $(document).on('change', '#staff_id', function() {
                getCalendarData();
            });

            $(document).on('change', '#meeting_type', function() {
                var meeting_type = $('#meeting_type :selected').val();
                console.log(meeting_type)
                $('#status option').hide();
                $('#status option[data-type="' + meeting_type + '"]').show();
                $('#status :first').show();
                $('#status :first').prop("selected", true);
                $('#status').selectpicker('refresh');
                getCalendarData();
            });

        });

        function getCalendarData() {
            $('body').append('<div id="loading-spinner" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 9999;"><div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);"><div class="dt-loader"><span></span></div></div></div>');
            setTimeout(() => {
                var currentDate = calendar.fullCalendar('getDate');
                var month = currentDate.month() + 1;
                var year = currentDate.year();
                $('#hiddenMonth').val(month);
                $('#hiddeYear').val(year);
                initMeetingTableData();
                $.ajax({
                    url: "<?php echo admin_url('meeting_dashboard/get_calendar_data') ?>",
                    method: "POST",
                    data: {
                        month: month,
                        year: year,
                        meeting_type: $('#meeting_type').val(),
                        status: $('#status').val(),
                        staff_id: $('#staff_id').val(),
                    },
                    dataType: 'json',
                    success: function(result) {
                        clearCalendarEvents();
                        if (result.success && result.data) {
                            result.data.forEach(function(event) {
                                var eventDate = event.date;
                                var eventDetails = event.title;
                                var color = "#ab8c00";
                                if (event.status == "Approved" || event.status == "Attend" || event.status == "Visited" || event.status == "Present") {
                                    color = "#008f34";
                                } else if (event.status == "Not Approved" || event.status == "Not Attend" || event.status == "Absent" || event.status == "Declined" || event.status == "Not Reachable") {
                                    color = "#b31010";
                                }
                                addEventToCalendar(eventDate, eventDetails, color, event.lead_id, event.tooltip);
                            });
                        }
                        if (table) {
                            table.draw();
                        }
                    },
                    complete: function() {
                        $('#loading-spinner').remove();
                    }
                });

            }, 100);

        }

        function initMeetingTableData() {
            var payloadData = [];
            payloadData['year'] = "#hiddeYear";
            payloadData['month'] = "#hiddenMonth";
            payloadData['type'] = "#meeting_type";
            payloadData['status'] = "#status";
            payloadData['staff_id'] = "#staff_id";
            payloadData['date'] = "#dateinput";
            table = initDataTable('.table-meeting-data', window.location.href, undefined, [0, 1, 2, 3, 4, 5], payloadData, []);
        }

        function addEventToCalendar(date, details, color, lead_id, tooltip = "") {
            $('#calendar-pvd').fullCalendar('renderEvent', {
                title: details,
                start: date,
                backgroundColor: color,
                allDay: true,
                description: tooltip,
                customData: {
                    lead_id: lead_id
                }
            });
        }

        function clearCalendarEvents() {
            $('#calendar-pvd').fullCalendar('removeEvents');
        }
    </script>
    </body>

    </html>