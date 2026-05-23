<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<style>
    .onoffswitch-main .onoffswitch-label:before {
        height: 20px;
    }

    .onoffswitch-main {
        top: -20px;
    }
</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-5">
                <div class="panel_s">
                    <div class="panel-body">
                        <?= form_open(
                            admin_url('stock_settings/save'),
                            array(
                                'id' => 'stockSettingForm'
                            )
                        ) ?>

                        <!-- Domestic Section -->
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="section-title">Stock Settings</h4>
                                <hr>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="stock_low_alert_limit">Low Stock Alert Limit</label>
                                    <input type="number" class="form-control" id="stock_low_alert_limit" name="stock_low_alert_limit" rows="5" value="<?= get_option('stock_low_alert_limit') ?>" required placeholder="Enter Low Stock Alert Limit" />
                                    <p>If available stock lower than entered limit you then you will see alert icon on that product in stock</p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group text-right">
                                    <button type="submit" class="btn btn-primary">
                                        Save
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?= form_close() ?>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        $(window).off('beforeunload');
        init_editor('textarea[name="purchase_terms_and_condition"]');
        $('#stockSettingForm').appFormValidator({
            rules: {
                stock_low_alert_limit: 'required'
            },
            errorPlacement: function(error, element) {
                var formGroup = $(element).closest('.form-group');
                formGroup.append(error);
            }
        });
    });
</script>
</body>

</html>