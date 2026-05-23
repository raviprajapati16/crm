<?php
$output = false;
if (!empty($goals_list)) {
    foreach ($goals_list as $goal) {
        $staffids = get_goal_staff_ids($goal['id'], true);
        if (!empty($staff) && !in_array($staff, $staffids)) {
            continue;
        }
        $output = true;
        $this->load->view('admin/goals_dashboard/render/chart-render', ["goal" => $goal, "staff" => $staff]);
    } ?>
<?php }
if (!$output) { ?>
    <div class="row">
        <div class="col-md-12 text-center">
            <h3><b>No goals found.</b></h3>
        </div>
    </div>
<?php } ?>
<script>
    $(document).ready(function() {

        let overallGoalCharts = {};
        let staffGoalCharts = {};
        $('.main-panel').each(function() {
            let goal_id = $(this).data('goal-id');
            let type = $(this).data('goal-duration-type');
            if (type == "1") {
                $('#date-range-' + goal_id).daterangepicker({
                    locale: {
                        format: 'DD-MM-YYYY'
                    },
                    opens: 'left',
                    showCustomRangeLabel: true,
                    alwaysShowCalendars: true,
                    startDate: moment(),
                    endDate: moment(),
                    maxDate: moment(),
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment()],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    }
                }).on('apply.daterangepicker', function(ev, picker) {
                    goalsDataRefresh(goal_id, type);
                });

                var lastClickedDate = null;
                var lastClickTime = null;
                $(document).off('click.daterangepicker-' + goal_id);
                $(document).on('click.daterangepicker-' + goal_id, '.calendar-table td.available', function() {
                    var currentDate = $(this).attr('data-date');
                    var currentTime = new Date().getTime();

                    if (lastClickedDate === currentDate && currentTime - lastClickTime < 500) {
                        var selectedDate = moment(currentDate);
                        $('#date-range-' + goal_id).data('daterangepicker').setStartDate(selectedDate);
                        $('#date-range-' + goal_id).data('daterangepicker').setEndDate(selectedDate);

                        lastClickedDate = null;
                        lastClickTime = null;
                    } else {
                        lastClickedDate = currentDate;
                        lastClickTime = currentTime;
                    }
                });
            }
            goalsDataRefresh(goal_id, type);
        });

        $(document).off('.week-dropdown');
        $(document).on('change', '.week-dropdown', function() {
            var id = $(this).closest('.main-panel').data('goal-id');
            var type = $(this).closest('.main-panel').data('goal-duration-type');
            goalsDataRefresh(id, type);
        });

        $(document).off('.month-dropdown');
        $(document).on('change', '.month-dropdown', function() {
            var id = $(this).closest('.main-panel').data('goal-id');
            var type = $(this).closest('.main-panel').data('goal-duration-type');
            goalsDataRefresh(id, type);
        });

        $(document).off('.quarter-dropdown');
        $(document).on('change', '.quarter-dropdown', function() {
            var id = $(this).closest('.main-panel').data('goal-id');
            var type = $(this).closest('.main-panel').data('goal-duration-type');
            goalsDataRefresh(id, type);
        });

        $(document).off('.year-half-dropdown');
        $(document).on('change', '.year-half-dropdown', function() {
            var id = $(this).closest('.main-panel').data('goal-id');
            var type = $(this).closest('.main-panel').data('goal-duration-type');
            goalsDataRefresh(id, type);
        });

        $(document).off('.year-dropdown').on('change', '.year-dropdown', function() {
            var $panel = $(this).closest('.main-panel');
            var id = $panel.data('goal-id');
            var type = $panel.data('goal-duration-type');
            var $dropdown;
            if (type == 2) {
                $dropdown = $("#weekly-week-" + id);
            } else if (type == 3) {
                $dropdown = $("#monthly-" + id);
            } else if (type == 4) {
                $dropdown = $("#quarterly-" + id);
            } else if (type == 5) {
                $dropdown = $("#year-half-" + id);
            }
            if ($dropdown) {
                $dropdown.empty();
                $.ajax({
                    url: "<?php echo admin_url('goals_dashboard/get_duration_list') ?>",
                    method: "POST",
                    data: {
                        goal_id: id,
                        year: $(this).val(),
                    },
                    dataType: "json"
                }).done(function(result) {
                    if (result.success) {
                        $dropdown.empty();
                        $.each(result.data, function(key, item) {
                            var selected = item.status === "current" ? " selected" : "";
                            $dropdown.append('<option value="' + item.start_date + ' - ' + item.end_date + '"' + selected + '>' + item.title + '</option>');
                        });
                    }
                });
            }
            setTimeout(() => {
                goalsDataRefresh(id, type);
            }, 200);
        });

        function goalsDataRefresh(id, type) {
            const $overallChart = $("#overall-goal-chart-" + id);
            const $staffChart = $("#staff-goal-chart-" + id);
            const spinnerHtml = `
                <div style="display: flex; justify-content: center; align-items: center; height: 350px; font-size: 18px; color: #888;" class="spinner-container">
                    <div class="dt-loader"><span></span></div>
                </div>
            `;

            $overallChart.html(spinnerHtml);
            $staffChart.html(spinnerHtml);

            var staffId = "<?= get_staff_user_id(); ?>";
            if ($('#staffid').length > 0) {
                staffId = $('#staffid').val();
            }
            const inputSelectors = {
                '1': '#date-range-',
                '2': '#weekly-week-',
                '3': '#monthly-',
                '4': '#quarterly-',
                '5': '#year-half-',
                '7': '#yearly-year-'
            };

            let payload = {
                goal_id: id,
                staff: staffId
            };
            if (type !== '6') {
                const selector = inputSelectors[type];
                if (selector) {
                    payload[type === '7' ? 'year' : 'date_range'] = $(selector + id).val();
                }
            }

            $.ajax({
                url: "<?php echo admin_url('goals_dashboard/goals_data') ?>",
                method: "POST",
                data: payload,
                dataType: 'json'
            }).done(function(result) {
                $overallChart.empty();
                $staffChart.empty();

                if (result.success) {
                    const {
                        total_target: target,
                        total_achievement: achievement
                    } = result.overalldata;
                    initializeOverallGoalChart(id, target, achievement);

                    const {
                        staff_names: staffNames,
                        staff_achievements: achievementData,
                        total_target: staffTarget,
                        max_value: maxValue
                    } = result.staffchartdata;
                    initializeStaffGoalChart(id, staffNames, achievementData, staffTarget, maxValue);
                }
            });
        }


        function initializeOverallGoalChart(goal_id, target, achievement) {
            console.log($('.main-panel[data-goal-id="' + goal_id + '"] .total-target').length)
            $('.main-panel[data-goal-id="' + goal_id + '"] .total-target').html(target);
            $('.main-panel[data-goal-id="' + goal_id + '"] .total-achievement').html(achievement);
            let percentage = achievement === 0 ? 0 : Number(((achievement * 100) / target).toFixed(2));
            let overallGoalOptions = {
                series: [percentage],
                chart: {
                    height: 415,
                    type: 'radialBar',
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    radialBar: {
                        startAngle: -135,
                        endAngle: 225,
                        hollow: {
                            size: '70%',
                            background: '#fff'
                        },
                        dataLabels: {
                            show: true,
                            value: {
                                formatter: function(val) {
                                    if (val == 0) return '0%';
                                    if (val > 100) return '100%';
                                    return val + '%';
                                },
                                color: '#456e36',
                                fontSize: '36px',
                                fontWeight: 600,
                                offsetY: -10
                            },
                            name: {
                                show: true,
                                offsetY: 50,
                                color: '#666',
                                fontSize: '16px',
                                formatter: function() {
                                    return achievement + ' / ' + target;
                                }
                            }
                        }
                    }
                },
                fill: {
                    type: 'solid',
                    colors: ['#fdc900']
                },
                stroke: {
                    lineCap: 'round'
                },
                labels: ['Overall Progress'],
                responsive: [{
                    breakpoint: 992,
                    options: {
                        chart: {
                            height: 350
                        },
                        plotOptions: {
                            radialBar: {
                                hollow: {
                                    size: '65%'
                                },
                                dataLabels: {
                                    value: {
                                        fontSize: '28px',
                                        offsetY: -8
                                    },
                                    name: {
                                        fontSize: '14px',
                                        offsetY: 40
                                    }
                                }
                            }
                        }
                    }
                }, {
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 300
                        },
                        plotOptions: {
                            radialBar: {
                                hollow: {
                                    size: '65%'
                                },
                                dataLabels: {
                                    value: {
                                        fontSize: '24px',
                                        offsetY: -5
                                    },
                                    name: {
                                        fontSize: '14px',
                                        offsetY: 30
                                    }
                                }
                            }
                        }
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 250
                        },
                        plotOptions: {
                            radialBar: {
                                hollow: {
                                    size: '60%'
                                },
                                dataLabels: {
                                    value: {
                                        fontSize: '20px',
                                        offsetY: -5
                                    },
                                    name: {
                                        fontSize: '12px',
                                        offsetY: 25
                                    }
                                }
                            }
                        }
                    }
                }]
            };

            if (overallGoalCharts[goal_id]) overallGoalCharts[goal_id].destroy();
            overallGoalCharts[goal_id] = new ApexCharts(document.querySelector("#overall-goal-chart-" + goal_id), overallGoalOptions);
            overallGoalCharts[goal_id].render();
        }

        function initializeStaffGoalChart(goal_id, staffNames, achivementData, total_target, max_value) {
            let staffGoalOptions = {
                series: [{
                    name: 'Achievements',
                    data: achivementData
                }],
                chart: {
                    type: 'bar',
                    height: 380,
                    stacked: false
                },
                colors: ['#fdc900'],
                plotOptions: {
                    bar: {
                        horizontal: true,
                        barHeight: '70%'
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function(val) {
                        if (val == 0) return '';
                        if (val > 0) return val.toLocaleString();
                    },
                    style: {
                        colors: ['#456e36']
                    }
                },
                xaxis: {
                    categories: staffNames,
                    forceNiceScale: true,
                    tickAmount: 5,
                    max: max_value,
                    labels: {
                        formatter: function(val) {
                            return Number.isInteger(val) ? val : Math.round(val);
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Staff'
                    }
                },
                annotations: {
                    xaxis: [{
                        x: total_target,
                        borderColor: '#456e36',
                        label: {
                            borderColor: '#456e36',
                            style: {
                                color: '#fff',
                                background: '#456e36'
                            },
                            text: 'Target : ' + total_target.toLocaleString(),
                        }
                    }]
                },
                grid: {
                    xaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: val => val.toLocaleString()
                    },
                    theme: 'dark',
                    style: {
                        fontSize: '12px',
                        fontFamily: 'Helvetica, Arial, sans-serif'
                    },
                    marker: {
                        show: true
                    },
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                responsive: [{
                    breakpoint: 992,
                    options: {
                        chart: {
                            height: 360
                        },
                        plotOptions: {
                            bar: {
                                barHeight: '65%'
                            }
                        }
                    }
                }, {
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 350
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                barHeight: '60%'
                            }
                        },
                        dataLabels: {
                            style: {
                                fontSize: '10px'
                            }
                        },
                        xaxis: {
                            labels: {
                                style: {
                                    fontSize: '10px'
                                }
                            },
                            tickAmount: 4
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    fontSize: '10px'
                                }
                            }
                        },
                        legend: {
                            fontSize: '12px',
                            position: 'bottom',
                            horizontalAlign: 'center'
                        },
                        annotations: {
                            xaxis: [{
                                label: {
                                    style: {
                                        fontSize: '10px'
                                    }
                                }
                            }]
                        }
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 300
                        },
                        plotOptions: {
                            bar: {
                                horizontal: staffNames.length > 4 ? false : true,
                                barHeight: '50%'
                            }
                        },
                        dataLabels: {
                            style: {
                                fontSize: '9px'
                            },
                            offsetX: staffNames.length > 4 ? 0 : undefined,
                            offsetY: staffNames.length > 4 ? -5 : undefined
                        },
                        xaxis: {
                            labels: {
                                rotate: staffNames.length > 4 ? -45 : 0,
                                style: {
                                    fontSize: '9px'
                                }
                            },
                            tickAmount: 3
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    fontSize: '9px'
                                }
                            },
                            title: {
                                text: staffNames.length > 4 ? '' : 'Staff',
                            }
                        },
                        annotations: {
                            xaxis: [{
                                label: {
                                    style: {
                                        fontSize: '8px'
                                    }
                                }
                            }]
                        }
                    }
                }]
            };
            if (staffNames.length > 4) {
                staffGoalOptions.responsive[2].options.xaxis.categories =
                    staffNames.length > 4 ? staffNames : undefined;
                staffGoalOptions.responsive[2].options.yaxis = staffNames.length > 4 ? {
                    categories: staffNames,
                    min: 0,
                    max: total_target,
                    tickAmount: 3,
                    labels: {
                        style: {
                            fontSize: '9px'
                        }
                    }
                } : staffGoalOptions.responsive[2].options.yaxis;

                staffGoalOptions.responsive[2].options.annotations = staffNames.length > 4 ? {
                    yaxis: [{
                        y: total_target,
                        borderColor: '#775DD0',
                        label: {
                            borderColor: '#775DD0',
                            style: {
                                color: '#fff',
                                background: '#775DD0',
                                fontSize: '8px'
                            },
                            text: 'Target: ' + total_target.toLocaleString(),
                        }
                    }]
                } : staffGoalOptions.responsive[2].options.annotations;
            }

            if (staffGoalCharts[goal_id]) staffGoalCharts[goal_id].destroy();
            staffGoalCharts[goal_id] = new ApexCharts(document.querySelector("#staff-goal-chart-" + goal_id), staffGoalOptions);
            staffGoalCharts[goal_id].render();
        }

        function addResponsiveStyles() {
            const style = document.createElement('style');
            style.textContent = `
                .chart-container {
                    width: 100%;
                    max-width: 100%;
                    overflow-x: hidden;
                }
                @media (max-width: 768px) {
                    .chart-container {
                        margin-bottom: 20px;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        function setupResizeHandlers() {
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    Object.keys(overallGoalCharts).forEach(id => {
                        overallGoalCharts[id].render();
                    });
                    Object.keys(staffGoalCharts).forEach(id => {
                        staffGoalCharts[id].render();
                    });
                }, 250);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            addResponsiveStyles();
            setupResizeHandlers();

            document.querySelectorAll('[id^="overall-goal-chart-"], [id^="staff-goal-chart-"]').forEach(el => {
                el.classList.add('chart-container');
            });
        });

    });
</script>