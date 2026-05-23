<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .mt10 {
        margin-top: 10px;
    }

    .mt5 {
        margin-top: 10px;
    }

    .totaltxt {
        font-size: 16px;
    }

    .select-container select {
        width: 100%;
    }

    .panel-heading {
        display: flex;
        justify-content: space-between;
        padding: 0px 10px 0px 10px;
        align-items: center;
    }

    .panel-heading {
        display: flex;
        justify-content: space-between;
        padding: 5px 10px 5px 10px;
        align-items: center;
    }

    .select-container {
        display: inline-block;
    }

    .d-flex {
        display: flex;
    }

    .jc-space-between {
        justify-content: space-between;
    }

    .chart-title {
        text-align: center;
        margin-bottom: 10px;
        font-size: 15px;
        font-weight: bold;
    }

    .header-filter-section select {
        margin-left: 5px;
        width: 150px;
    }

    .header-filter-section {
        display: flex;
        gap: 10px;
    }

    @media (max-width: 768px) {
        .panel-heading {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .panel-title {
            margin-bottom: 10px;
        }

        .select-container {
            width: 100%;
        }

        .select-container select {
            width: 100%;
            margin-bottom: 10px;
        }

        .header-filter-section {
            flex-direction: column;
            width: 100%;
        }

        .header-filter-section select {
            width: 100% !important;
            margin-bottom: 10px;
        }

    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <p class="text-info inline-block" data-placement="bottom" data-toggle="tooltip" data-title="<?php echo _l('leads_report_converted_notice'); ?>"><i class="fa fa-question-circle"></i></p>
                    <div class="panel-body">
                        <a href="<?php echo admin_url('reports/leads?type=staff'); ?>" class="btn btn-success"><?php echo _l('switch_to_general_report'); ?></a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 animated fadeIn">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title">Weekly Leads Conversion</div>
                        <div class="pull-right d-flex header-filter-section select-container">
                            <select id="weekChartYears" class="form-control">

                            </select>
                            <select id="weekChartWeeks" class="form-control">

                            </select>
                        </div>
                    </div>
                    <div class="panel-body">
                        <canvas class="leads-this-week" height="400" id="leads-this-week"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6 animated fadeIn">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title"><?php echo _l('report_leads_sources_conversions'); ?></div>
                        <div class="pull-right d-flex header-filter-section select-container">
                            <select id="sourceChartYears" class="form-control">

                            </select>
                            <select id="sourceChartMonths" class="form-control">

                            </select>
                        </div>
                    </div>
                    <div class="panel-body">
                        <canvas class="leads-sources-report" height="400" id="leads-sources-report"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-12 animated fadeIn">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title"><?php echo _l('report_leads_monthly_conversions'); ?></div>
                        <div class="pull-right d-flex header-filter-section select-container">
                            <select id="monthlyChartYears" class="form-control">

                            </select>
                            <select id="monthlyChartMonths" class="form-control">

                            </select>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="relative" style="max-height:400px;">
                            <canvas class="leads-monthly chart mtop20" id="leads-monthly" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 animated fadeIn">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title">Lost Leads</div>
                        <div class="pull-right d-flex header-filter-section select-container">
                            <select id="yearSelect" class="form-control" data-none-selected-text="Select Year">
                                <?php
                                $currentYear = date('Y');
                                for ($y = 2019; $y <= $currentYear; $y++) {
                                    $_selected = ($y == $currentYear) ? 'selected' : '';
                                    echo '<option value="' . $y . '" ' . $_selected . '>' . $y . '</option>' . PHP_EOL;
                                }
                                ?>
                            </select>
                            <?php
                            echo '<select id="monthSelect" class="form-control" data-none-selected-text="Select Month">' . PHP_EOL;
                            echo '<option value="">Select Month</option>' . PHP_EOL;
                            for ($m = 1; $m <= 12; $m++) {
                                $_selected = '';
                                if ($m == date('m')) {
                                    $_selected  = ' selected';
                                }
                                echo '  <option value="' . $m . '"' . $_selected . '>' . _l(date('F', mktime(0, 0, 0, $m, 1))) . '</option>' . PHP_EOL;
                            }
                            echo '</select>' . PHP_EOL;
                            ?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6 mt5 totaltxt"></div>
                            <div class="col-md-12 mt10">
                                <?php render_datatable(array(
                                    'Lead ID',
                                    'Lead name',
                                    'Proposal(s)',
                                    'Total Amount',
                                ), 'lost-leads'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 animated fadeIn">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="panel-title">Leads Conversion</div>
                        <div class="pull-right d-flex header-filter-section select-container">
                            <select id="yearLeadConversion" class="form-control">
                                <?php
                                $currentYear = date('Y');
                                for ($y = 2019; $y <= $currentYear; $y++) {
                                    $_selected = ($y == $currentYear) ? 'selected' : '';
                                    echo '<option value="' . $y . '" ' . $_selected . '>' . $y . '</option>' . PHP_EOL;
                                }
                                ?>
                            </select>
                            <?php
                            echo '<select id="monthLeadConversion" class="form-control">' . PHP_EOL;
                            echo '<option value="">Select Month</option>' . PHP_EOL;
                            for ($m = 1; $m <= 12; $m++) {
                                $_selected = '';
                                if ($m == date('m')) {
                                    $_selected  = ' selected';
                                }
                                echo '  <option value="' . $m . '"' . $_selected . '>' . _l(date('F', mktime(0, 0, 0, $m, 1))) . '</option>' . PHP_EOL;
                            }
                            echo '</select>' . PHP_EOL;
                            ?>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12 mt10">
                                <?php render_datatable(array(
                                    'Lead ID',
                                    'Lead name',
                                    'Conversion Date',
                                ), 'leads-conversion'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        const currentYear = new Date().getFullYear();
        yearDropdownInit('#weekChartYears');
        weekDropdownInit('#weekChartWeeks', currentYear);
        yearDropdownInit('#sourceChartYears');
        monthDropdownInit('#sourceChartMonths', currentYear);
        yearDropdownInit('#monthlyChartYears');
        monthDropdownInit('#monthlyChartMonths', currentYear);

        // Leads conversion table
        updateMonths('#yearLeadConversion', '#monthLeadConversion');
        var fnServerParamsLs = {
            "year": '#yearLeadConversion',
            "month": '#monthLeadConversion',
        }
        var leadsConversionTable = initDataTable('.table-leads-conversion', "<?= admin_url('reports/leads_conversion') ?>", false, false, fnServerParamsLs);

        $('#yearLeadConversion').on('change', function() {
            updateMonths('#yearLeadConversion', '#monthLeadConversion');
            if (leadsConversionTable) {
                leadsConversionTable.draw();
            }
        });

        $('#monthLeadConversion').on('change', function() {
            if (leadsConversionTable) {
                leadsConversionTable.draw();
            }
        });

        // Leads lost table
        updateMonths('#yearSelect', '#monthSelect');
        var fnServerParams = {
            "year": '#yearSelect',
            "month": '#monthSelect',
        }
        var lostLeadTable = initDataTable('.table-lost-leads', "<?= admin_url('reports/lost_leads') ?>", false, [0, 1, 2, 3], fnServerParams);
        lostLeadTable.on('draw', function() {
            var columnData = [];
            var currencySums = {};

            $('.table-lost-leads tbody tr').each(function() {
                var cellData = $(this).find('td').eq(3).text().trim();
                columnData.push(cellData);
            });

            columnData.forEach(function(data) {
                var match = data.match(/^([₹$€])\s*(\d+[\d,]*)/);
                if (match) {
                    var currency = match[1];
                    var amount = parseFloat(match[2].replace(/,/g, ''));

                    if (!currencySums[currency]) {
                        currencySums[currency] = 0;
                    }

                    currencySums[currency] += amount;
                }
            });

            var allZero = Object.values(currencySums).every(function(sum) {
                return sum === 0;
            });

            if (allZero || Object.keys(currencySums).length === 0) {
                $('.totaltxt').html("");
            } else {
                $('.totaltxt').html("Total Loss : ");
                for (var currency in currencySums) {
                    if (currencySums.hasOwnProperty(currency)) {
                        var formattedSum = currency + " " + currencySums[currency].toLocaleString();
                        $('.totaltxt').append(formattedSum + "&nbsp;&nbsp;&nbsp;&nbsp;");
                    }
                }
            }
        });

        $('#yearSelect').on('change', function() {
            updateMonths('#yearSelect', '#monthSelect');
            if (lostLeadTable) {
                lostLeadTable.draw();
            }
        });

        $('#monthSelect').on('change', function() {
            if (lostLeadTable) {
                lostLeadTable.draw();
            }
        });

        //Weekly chart
        weeklyChart();
        $('#weekChartYears').on('change', function() {
            weekDropdownInit('#weekChartWeeks', $(this).val());
            weeklyChart();
        });
        $('#weekChartWeeks').on('change', function() {
            weeklyChart();
        });

        // Source chart
        sourceChart();
        $('#sourceChartYears').on('change', function() {
            monthDropdownInit('#sourceChartMonths', $(this).val());
            sourceChart();
        });
        $('#sourceChartMonths').on('change', function() {
            sourceChart();
        });

        // monthly chart
        monthlyChart();
        $('#monthlyChartYears').on('change', function() {
            monthDropdownInit('#monthlyChartMonths', $(this).val());
            monthlyChart();
        });
        $('#monthlyChartMonths').on('change', function() {
            monthlyChart();
        });

    });

    let weekChart = null;

    function weeklyChart() {
        if (weekChart instanceof Chart) {
            weekChart.destroy();
            weekChart = null;
        }
        const canvas = $('#leads-this-week');
        const ctx = canvas.get(0).getContext('2d');
        ctx.clearRect(0, 0, canvas.width(), canvas.height());
        $.ajax({
            url: "<?php echo admin_url('reports/leads_weekly_report') ?>",
            method: "POST",
            data: {
                dateRange: $('#weekChartWeeks').val()
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                const hasNonZeroValue = result.data.datasets[0].data.some(value => value > 0);
                if (!hasNonZeroValue) {
                    if (weekChart) {
                        weekChart.destroy();
                        weekChart = null;
                    }
                    ctx.clearRect(0, 0, canvas.width(), canvas.height());
                    weekChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: [],
                            datasets: [{
                                data: []
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            events: [],
                            animation: false,
                            legend: {
                                display: false
                            },
                            plugins: {

                                tooltip: {
                                    enabled: false
                                }
                            }
                        }
                    });

                    ctx.font = '14px Arial';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#666';
                    ctx.fillText('No data available', canvas.width() / 2, canvas.height() / 2);

                } else {
                    weekChart = new Chart(ctx, {
                        type: 'pie',
                        data: result.data,
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top'
                                }
                            }
                        }
                    });
                }
            } else {
                if (weekChart) {
                    weekChart.destroy();
                    weekChart = null;
                }
                ctx.clearRect(0, 0, canvas.width(), canvas.height());
                $("#leads-this-week").html("");
            }
        });
    }

    let soruceChartContainer = null;

    function sourceChart() {
        if (soruceChartContainer instanceof Chart) {
            soruceChartContainer.destroy();
            soruceChartContainer = null;
        }
        const canvas = $('#leads-sources-report');
        const ctx = canvas.get(0).getContext('2d');
        ctx.clearRect(0, 0, canvas.width(), canvas.height());
        $.ajax({
            url: "<?php echo admin_url('reports/source_report') ?>",
            method: "POST",
            data: {
                year: $('#sourceChartYears').val(),
                month: $('#sourceChartMonths').val()
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                const hasNonZeroValue = result.data.datasets[0].data.some(value => value > 0);

                if (!hasNonZeroValue) {
                    if (soruceChartContainer) {
                        soruceChartContainer.destroy();
                        soruceChartContainer = null;
                    }

                    ctx.clearRect(0, 0, canvas.width(), canvas.height());
                    soruceChartContainer = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: [],
                            datasets: [{
                                data: []
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            events: [],
                            animation: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes: [{
                                    display: false,
                                }],
                                xAxes: [{
                                    display: false,
                                }]
                            }
                        }
                    });

                    ctx.font = '14px Arial';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#666';
                    ctx.fillText('No data available', canvas.width() / 2, canvas.height() / 2);

                } else {
                    soruceChartContainer = new Chart(ctx, {
                        type: 'bar',
                        data: result.data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function(value) {
                                            if (Math.floor(value) === value) {
                                                return value;
                                            }
                                        }
                                    }
                                }]
                            },
                        }
                    });
                }
            } else {
                if (soruceChartContainer) {
                    soruceChartContainer.destroy();
                    soruceChartContainer = null;
                }
                ctx.clearRect(0, 0, canvas.width(), canvas.height());
                $("#leads-sources-report").html("");
            }
        });
    }

    let MonthlyLeadsChart = null;

    function monthlyChart() {
        if (MonthlyLeadsChart instanceof Chart) {
            MonthlyLeadsChart.destroy();
            MonthlyLeadsChart = null;
        }
        const canvas = $('#leads-monthly');
        const ctx = canvas.get(0).getContext('2d');
        ctx.clearRect(0, 0, canvas.width(), canvas.height());
        $.ajax({
            url: "<?php echo admin_url('reports/leads_monthly_report') ?>",
            method: "POST",
            data: {
                year: $('#monthlyChartYears').val(),
                month: $('#monthlyChartMonths').val()
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                const hasNonZeroValue = result.data.datasets[0].data.some(value => value > 0);

                if (!hasNonZeroValue) {
                    if (MonthlyLeadsChart instanceof Chart) {
                        MonthlyLeadsChart.destroy();
                        MonthlyLeadsChart = null;
                    }

                    ctx.clearRect(0, 0, canvas.width(), canvas.height());
                    MonthlyLeadsChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: [],
                            datasets: [{
                                data: []
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            events: [],
                            animation: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes: [{
                                    display: false,
                                }],
                                xAxes: [{
                                    display: false,
                                }]
                            }
                        }
                    });

                    ctx.font = '14px Arial';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillStyle = '#666';
                    ctx.fillText('No data available', canvas.width() / 2, canvas.height() / 2);

                } else {
                    MonthlyLeadsChart = new Chart(ctx, {
                        type: 'bar',
                        data: result.data,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: {
                                display: false
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true,
                                        callback: function(value) {
                                            if (Math.floor(value) === value) {
                                                return value;
                                            }
                                        }
                                    }
                                }]
                            },
                        }
                    });
                }
            } else {
                if (MonthlyLeadsChart instanceof Chart) {
                    MonthlyLeadsChart.destroy();
                    MonthlyLeadsChart = null;
                }
                ctx.clearRect(0, 0, canvas.width(), canvas.height());
                $("#leads-monthly").html("");
            }
        });
    }

    function getWeekNumber(date) {
        const firstJan = new Date(date.getFullYear(), 0, 1);
        const days = Math.floor((date - firstJan) / (24 * 60 * 60 * 1000));
        return Math.ceil((days + firstJan.getDay() + 1) / 7);
    }

    function yearDropdownInit(selector) {
        $main = $(selector)
        const currentYear = new Date().getFullYear();
        for (let year = currentYear; year >= 2019; year--) {
            $main.append(`<option value="${year}">${year}</option>`);
        }
        $main.val(currentYear);
    }

    function monthDropdownInit(selector, year) {
        $main = $(selector);
        const currentMonth = new Date().getMonth();
        const months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        $main.empty();
        months.forEach((month, index) => {
            if (year == new Date().getFullYear() && index > currentMonth) {
                return;
            }
            $main.append(`<option value="${index + 1}" ${index === currentMonth && year == new Date().getFullYear() ? 'selected' : ''}>${month}</option>`);
        });
        $main.attr('data-year', year);
    }

    function weekDropdownInit(selector, year) {
        year = Number(year);
        $(selector).empty();
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentWeek = getWeekNumber(currentDate);

        const maxWeeks = (year === currentYear) ? currentWeek : getWeeksInYear(year);

        for (let week = 1; week <= maxWeeks; week++) {
            const weekDates = getWeekDates(week, year);

            if (week === 53 && weekDates.startDate.getFullYear() !== year) {
                continue;
            }

            const startText = formatDate(weekDates.startDate);
            const endText = formatDate(weekDates.endDate);
            const startValue = formatValueDate(weekDates.startDate);
            const endValue = formatValueDate(weekDates.endDate);

            const isSelected = (year === currentYear && week === currentWeek) ? 'selected' : '';

            $(selector).append(
                `<option value="${startValue}/${endValue}" ${isSelected}>Week ${week} (${startText} - ${endText})</option>`
            );
        }
    }

    function getWeeksInYear(year) {
        const lastDay = new Date(year, 11, 31);
        const lastWeek = getWeekNumber(lastDay);
        const lastDayWeekYear = getWeekYear(lastDay);

        if (lastDayWeekYear === year) {
            return lastWeek;
        }

        const previousDay = new Date(year, 11, 30);
        return getWeekNumber(previousDay);
    }

    function getWeekYear(date) {
        const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
        return d.getUTCFullYear();
    }

    function getWeekDates(week, year) {
        const firstDayOfYear = new Date(year, 0, 1);
        const firstWeekday = firstDayOfYear.getDay();
        const daysToAdd = (1 - firstWeekday + 7) % 7;

        const firstMonday = new Date(year, 0, 1 + daysToAdd);

        const startDate = new Date(firstMonday);
        startDate.setDate(firstMonday.getDate() + (week - 1) * 7);

        const endDate = new Date(startDate);
        endDate.setDate(startDate.getDate() + 6);

        return {
            startDate,
            endDate
        };
    }

    function getWeekNumber(date) {
        const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
        const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        return Math.ceil(((d - yearStart) / 86400000 + 1) / 7);
    }

    function formatValueDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function formatDate(date) {
        return date.toLocaleDateString('en-GB', {
            day: 'numeric',
            month: 'short'
        });
    }

    function updateMonths(yearSelector, monthSelector) {
        var selectedYear = parseInt($(yearSelector).val());
        var currentYear = new Date().getFullYear();
        var currentMonth = new Date().getMonth() + 1;
        $(monthSelector).empty();
        $(monthSelector).append('<option value="">Select Month</option>');
        for (var m = 1; m <= 12; m++) {
            if (selectedYear === currentYear && m > currentMonth) {
                break;
            }
            var monthName = new Date(selectedYear, m - 1).toLocaleString('default', {
                month: 'long'
            });
            $(monthSelector).append('<option value="' + m + '">' + monthName + '</option>');
        }
    }
</script>
</body>

</html>