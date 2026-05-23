<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Free Sans', sans-serif;
            font-size: 12px;
        }

        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .goal-section {
            margin-bottom: 30px;
            border: 1px solid #000;
            padding: 10px;
            border-radius: 10px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .description {
            margin-bottom: 10px;
            white-space: pre-line;
        }

        .summary-table,
        .staff-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .summary-table th,
        .summary-table td,
        .staff-table th,
        .staff-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        .staff-header {
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php
    $staff_selected = $data[0]['staff_selected'];
    if (!$staff_selected) {
    ?>
        <div class="title">Goals Report</div>
    <?php
    } else {
    ?>
        <div class="title">Goals Report Of <u><?= $data[0]['staffData'][0]['staff_name'] ?></u></div>
    <?php
    }
    ?>

    <?php foreach ($data as $goal): ?>
        <div class="goal-section">
            <div class="section-title"><?= $goal['subject']; ?></div>
            <table class="summary-table">
                <tr>
                    <th>Goal Type</th>
                    <th>Goal Duration Type</th>
                    <th>Goal Period</th>
                </tr>
                <tr>
                    <td><?= format_goal_type($goal['goal_type']); ?></td>
                    <td><?= get_goal_duration_title_by_key($goal['goal_duration_type']); ?></td>
                    <td>
                        <?php
                        $start = strtotime($goal['start_date']);
                        $end = strtotime($goal['end_date']);
                        switch ($goal['goal_duration_type']) {
                            case 1:
                                echo ($start == $end)
                                    ? date('d M, Y', $start)
                                    : date('d M, Y', $start) . ' - ' . date('d M, Y', $end);
                                break;
                            case 2:
                                $weekNumber = date('W', $start);
                                $year = date('Y', $start);
                                echo "Week {$weekNumber} of {$year} (" . date('d M, Y', $start) . ' - ' . date('d M, Y', $end) . ")";
                                break;
                            case 3:
                                echo date('F Y', $start);
                                break;
                            case 4:
                                $month = (int)date('n', $start);
                                $quarter = ceil($month / 3);
                                $year = date('Y', $start);
                                echo "Quarter {$quarter} of {$year}";
                                break;
                            case 5:
                                $month = (int)date('n', $start);
                                $half = ($month <= 6) ? 'First Half' : 'Second Half';
                                $year = date('Y', $start);
                                echo "{$half} of {$year}";
                                break;
                            case 7:
                                echo date('Y', $start);
                                break;
                            case 6:
                                echo date('d M, Y', $start) . ' to ' . date('d M, Y', $end);
                                break;
                            default:
                                echo date('d M, Y', $start) . ' to ' . date('d M, Y', $end);
                                break;
                        }
                        ?>
                    </td>

                </tr>
            </table>
            <?php
            if (!$staff_selected) {
            ?>
                <div class="staff-header">Overall Goal Performance:</div>
                <table class="summary-table">
                    <tr>
                        <th>Total Staff</th>
                        <th>Total Target</th>
                        <th>Total Achievement</th>
                        <th>Total %</th>
                    </tr>
                    <tr>
                        <td><?= $goal['total_staff']; ?></td>
                        <td><?= $goal['total_target']; ?></td>
                        <td><?= $goal['total_achievement']; ?></td>
                        <td><?= $goal['total_percentage']; ?></td>
                    </tr>
                </table>
            <?php
            }
            ?>
            <?php if (!empty($goal['staffData'])): ?>
                <div class="staff-header">Staff Performance:</div>
                <table class="staff-table">
                    <tr>
                        <th>Name</th>
                        <th>Target</th>
                        <th>Achievement</th>
                        <th>Percentage</th>
                    </tr>
                    <?php foreach ($goal['staffData'] as $staff): ?>
                        <tr>
                            <td><?= $staff['staff_name']; ?></td>
                            <td><?= $staff['target']; ?></td>
                            <td><?= $staff['staff_achievements']; ?></td>
                            <td><?= $staff['percentage']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

</body>

</html>