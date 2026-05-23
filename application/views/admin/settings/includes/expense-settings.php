<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4>Expense Receipt Settings</h4>
<br>
<div class="row">
    <div class="form-group col-md-12">
        <div>Expense Receipt Required</div>
        <div class="onoffswitch">
            <input type="checkbox" id="expense_receipt_required" class="onoffswitch-checkbox" value="1" name="expense_receipt_required" <?= (get_option('expense_receipt_required') == '1') ? "checked" : "" ?>>
            <label class="onoffswitch-label" for="expense_receipt_required">
                <span class="onoffswitch-inner" data-swchon-text="YES" data-swchoff-text="NO"></span>
                <span class="onoffswitch-switch"></span>
            </label>
        </div>
    </div>

    <div class="form-group col-md-4 receiptSection" app-field-wrapper="settings[expense_receipt_amount_threshold]">
        <label for="settings[expense_receipt_amount_threshold]" class="control-label">Receipt Required Above Amount</label>
        <div class="input-group">
            <span class="input-group-addon"><?= get_base_currency()->symbol ?></span>
            <input type="text" id="settings[expense_receipt_amount_threshold]" name="settings[expense_receipt_amount_threshold]" class="form-control amount-field" value="<?= get_option('expense_receipt_amount_threshold', '0.00') ?>" placeholder="0.00">
        </div>
    </div>

    <div class="form-group col-md-12 receiptSection">
        <span style="font-size: 12px;">Receipt will be required for expenses greater than the specified amount. Set to 0 to require receipts for all expenses.</span>
    </div>
</div>