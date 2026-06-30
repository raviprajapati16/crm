<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="lead-color-legend mtop10 mbot15">
    <div class="lead-color-legend-row">
        <span class="lead-color-legend-label"><?php echo _l('lead_color_legend_title'); ?>:</span>
        <span class="lead-color-legend-chips">
            <?php foreach ($legend_items as $item) { ?>
            <span class="lead-color-legend-item <?php echo html_escape($item['class']); ?>">
                <?php echo html_escape($item['label']); ?>
            </span>
            <?php } ?>
        </span>
    </div>
    <?php if (!empty($statuses)) { ?>
    <div class="lead-color-legend-row">
        <span class="lead-color-legend-label"><?php echo _l('lead_status_color_legend_title'); ?>:</span>
        <span class="lead-status-legend-chips">
            <?php foreach ($statuses as $status) {
                if (empty($status['name'])) {
                    continue;
                }
                $color = !empty($status['color']) ? $status['color'] : '#757575';
            ?>
            <span class="lead-status-legend-item label label-default"
                style="color:<?php echo html_escape($color); ?>;border:1px solid <?php echo html_escape($color); ?>;">
                <?php echo html_escape($status['name']); ?>
            </span>
            <?php } ?>
        </span>
    </div>
    <?php } ?>
</div>
<style>
.lead-color-legend-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px 8px;
    line-height: 1.6;
}

.lead-color-legend-row + .lead-color-legend-row {
    margin-top: 6px;
}

.lead-color-legend-label {
    font-weight: bold;
    white-space: nowrap;
    flex-shrink: 0;
}

.lead-color-legend-chips,
.lead-status-legend-chips {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
}

.lead-color-legend-item {
    display: inline-block;
    padding: 2px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: normal;
    border: 1px solid transparent;
}

.lead-color-legend .alert-default-light {
    color: #666;
    background-color: #f5f5f5;
    border-color: #ddd;
}

.lead-status-legend-item {
    display: inline-block;
    font-size: 11px;
    font-weight: normal;
    margin: 0;
    padding: 2px 8px;
}
</style>
