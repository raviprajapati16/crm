<?php
$staffIds = get_goal_staff_ids($goal['id'], true);
$totalStaff = (isset($staff) && !empty($staff)) ? 1 : count($staffIds);
$total_target = $goal['achievement'] * $totalStaff;
$achievement = $this->goals_model->calculate_goal_achievement_new($goal['id'], $staff);
$total_achievement = $achievement['total'] > 0 ? $achievement['total'] : 0;
$class = "";
if ($goal['goal_duration_type'] == 1) {
    $class = "daily-goals-panel";
} else if ($goal['goal_duration_type'] == 2) {
    $class = "weekly-goals-panel";
} else if ($goal['goal_duration_type'] == 3) {
    $class = "monthly-goals-panel";
} else if ($goal['goal_duration_type'] == 4) {
    $class = "quarterly-goals-panel";
} else if ($goal['goal_duration_type'] == 5) {
    $class = "half-yearly-goals-panel";
} else if ($goal['goal_duration_type'] == 6) {
    $class = "custom-goals-panel";
} else if ($goal['goal_duration_type'] == 7) {
    $class = "yearly-goals-panel";
}
?>
<div class="panel panel-primary main-panel <?= (!empty($class)) ? $class : 'hide'  ?>" data-goal-id="<?= $goal['id'] ?>" data-goal-duration-type="<?= $goal['goal_duration_type'] ?>">
    <div class="panel-heading">
        <div class="panel-title"><?= $goal['subject'] ?></div>
    </div>
    <div class="panel-body">
        <div class="row">
            <?php
            if ($goal['goal_duration_type'] == 1) {
            ?>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="date-range">Date Range</label>
                        <input type="text" id="date-range-<?= $goal['id'] ?>" class="form-control daterange">
                    </div>
                </div>
            <?php
            } else if ($goal['goal_duration_type'] == 2) {
            ?>
                <div class="col-md-3">
                    <label for="monthly-year-<?= $goal['id'] ?>">Year</label>
                    <div class="form-group">
                        <select id="monthly-year-<?= $goal['id'] ?>" class="form-control year-dropdown" data-live-search="true" data-none-selected-text="Select Year" data-width="100%">
                            <?php
                            foreach (get_year_list(date("Y-m-d", strtotime($goal['created_at']))) as $year) {
                            ?>
                                <option value="<?= $year['title'] ?>" <?= ($year['status'] == "current" ? "selected" : "") ?>><?= $year['title'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="weekly-week-<?= $goal['id'] ?>">Week</label>
                    <div class="form-group">
                        <select id="weekly-week-<?= $goal['id'] ?>" class="form-control week-dropdown" data-live-search="true" data-none-selected-text="Select Year" data-width="100%">
                            <?php
                            foreach (get_week_list(date('Y'), date("Y-m-d", strtotime($goal['created_at']))) as $week) {
                            ?>
                                <option value="<?= $week['start_date'] ?> - <?= $week['end_date'] ?>" <?= ($week['status'] == "current" ? "selected" : "") ?>><?= $week['title'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php
            } else if ($goal['goal_duration_type'] == 3) {
            ?>
                <div class="col-md-3">
                    <label for="weekly-year-<?= $goal['id'] ?>">Year</label>
                    <div class="form-group">
                        <select id="weekly-year-<?= $goal['id'] ?>" class="form-control year-dropdown" data-live-search="true" data-none-selected-text="Select Year" data-width="100%">
                            <?php
                            foreach (get_year_list(date("Y-m-d", strtotime($goal['created_at']))) as $year) {
                            ?>
                                <option value="<?= $year['title'] ?>" <?= ($year['status'] == "current" ? "selected" : "") ?>><?= $year['title'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="monthly-<?= $goal['id'] ?>">Month</label>
                    <div class="form-group">
                        <select id="monthly-<?= $goal['id'] ?>" class="form-control month-dropdown" data-live-search="true" data-none-selected-text="Select Year" data-width="100%">
                            <?php
                            foreach (array_reverse(get_month_list(date('Y'), date("Y-m-d", strtotime($goal['created_at'])))) as $month) {
                            ?>
                                <option value="<?= $month['start_date'] ?> - <?= $month['end_date'] ?>" <?= ($month['status'] == "current" ? "selected" : "") ?>><?= $month['title'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php
            } else if ($goal['goal_duration_type'] == 4) {
            ?>
                <div class="col-md-3">
                    <label for="quarterly-year-<?= $goal['id'] ?>">Year</label>
                    <div class="form-group">
                        <select id="quarterly-year-<?= $goal['id'] ?>" class="form-control year-dropdown" data-live-search="true" data-none-selected-text="Select Year" data-width="100%">
                            <?php
                            foreach (get_year_list(date("Y-m-d", strtotime($goal['created_at']))) as $year) {
                            ?>
                                <option value="<?= $year['title'] ?>" <?= ($year['status'] == "current" ? "selected" : "") ?>><?= $year['title'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="quarterly-<?= $goal['id'] ?>">Quarter</label>
                    <div class="form-group">
                        <select id="quarterly-<?= $goal['id'] ?>" class="form-control quarter-dropdown" data-live-search="true" data-none-selected-text="Select Quater" data-width="100%">
                            <?php
                            foreach (array_reverse(get_quarter_list(date('Y'), date("Y-m-d", strtotime($goal['created_at'])))) as $quater) {
                            ?>
                                <option value="<?= $quater['start_date'] ?> - <?= $quater['end_date'] ?>" <?= ($quater['status'] == "current" ? "selected" : "") ?>><?= $quater['title'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php
            } else if ($goal['goal_duration_type'] == 5) {
            ?>
                <div class="col-md-3">
                    <label for="halfyearly-year-<?= $goal['id'] ?>">Year</label>
                    <div class="form-group">
                        <select id="halfyearly-year-<?= $goal['id'] ?>" class="form-control year-dropdown" data-live-search="true" data-none-selected-text="Select Year" data-width="100%">
                            <?php
                            foreach (get_year_list(date("Y-m-d", strtotime($goal['created_at']))) as $year) {
                            ?>
                                <option value="<?= $year['title'] ?>" <?= ($year['status'] == "current" ? "selected" : "") ?>><?= $year['title'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="year-half-<?= $goal['id'] ?>">Select Year Period</label>
                    <div class="form-group">
                        <select id="year-half-<?= $goal['id'] ?>" class="form-control year-half-dropdown" data-live-search="true" data-none-selected-text="Select Year" data-width="100%">
                            <?php
                            foreach (get_half_yearly_list(date('Y'), date("Y-m-d", strtotime($goal['created_at']))) as $year) {
                            ?>
                                <option value="<?= $year['start_date'] ?> - <?= $year['end_date'] ?>" <?= ($year['status'] == "current" ? "selected" : "") ?>><?= $year['title'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php
            } else if ($goal['goal_duration_type'] == 7) {
            ?>
                <div class="col-md-3">
                    <label for="yearly-year-<?= $goal['id'] ?>">Year</label>
                    <div class="form-group">
                        <select id="yearly-year-<?= $goal['id'] ?>" class="form-control year-dropdown" data-live-search="true" data-none-selected-text="Select Year" data-width="100%">
                            <?php
                            foreach (get_year_list(date("Y-m-d", strtotime($goal['created_at']))) as $year) {
                            ?>
                                <option value="<?= $year['title'] ?>" <?= ($year['status'] == "current" ? "selected" : "") ?>><?= $year['title'] ?></option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            <?php
            }
            ?>
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr>
                        <td><b>Subject:</b> <?= $goal['subject'] ?></td>
                        <td><b>Goal Type :</b> <?= format_goal_type($goal['goal_type']) ?> (<?= get_goal_duration_title_by_key($goal['goal_duration_type']) ?>)</td>
                    </tr>
                    <tr>
                        <td><b>Total Target:</b> <span class="total-target"><?= $total_target ?></span></td>
                        <td><b>Total Achievement:</b> <span class="total-achievement"><?= $total_achievement ?></span></td>
                    </tr>
                    <?php
                    $class = "";
                    if ($goal['goal_duration_type'] == 6) {
                    ?>
                        <tr>
                            <td><b>Custom Date Period :</b> <?= date('d-m-Y', strtotime($goal['start_date'])) ?> - <?= date('d-m-Y', strtotime($goal['end_date'])) ?></td>
                        </tr>
                    <?php
                    }
                    ?>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="panel panel-charts">
                    <div class="panel-heading">
                        <div class="panel-title">Overall Goal Progress - Target vs Achievement</div>
                    </div>
                    <div class="panel-body">
                        <div id="overall-goal-chart-<?= $goal['id'] ?>"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel panel-charts">
                    <div class="panel-heading">
                        <div class="panel-title">Staff wise Goal Progress Analysis</div>
                    </div>
                    <div class="panel-body">
                        <div id="staff-goal-chart-<?= $goal['id'] ?>"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>