<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">Trade India APIs Settings</h4>
                        <hr class="hr-panel-heading" />
                        <p class="text-muted">
                            This information will be used for fetching new leads from Tradeindia.
                        </p>
                        <span style="color:red">* Changing settings incorreclty may stop Tradeindia leads import.</span>
                        <div class="tab-content mtop30">
                            <?php echo form_open($this->uri->uri_string(), ["id" => "tradeindiaform"]); ?>
                            <div role="tabpanel" class="tab-pane active" id="misc">
                                <?php echo render_input('tradeindia_api_link', 'Tradeindia API Link', get_option('tradeindia_api_link')); ?>
                            </div>
                            <div role="tabpanel" class="tab-pane active" id="misc">
                                <?php echo render_input('tradeindia_userid', 'Tradeindia User Id', get_option('tradeindia_userid')); ?>
                            </div>
                            <div role="tabpanel" class="tab-pane active" id="misc">
                                <?php echo render_input('tradeindia_profile_id', 'Tradeindia Profile Id', get_option('tradeindia_profile_id')); ?>
                            </div>
                            <div role="tabpanel" class="tab-pane active" id="misc">
                                <?php echo render_input('tradeindia_key', 'Tradeindia Key', get_option('tradeindia_key')); ?>
                            </div>
                        </div>
                        <button type="submit" name="type" value="tradeindia" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">India Mart APIs Settings</h4>
                        <hr class="hr-panel-heading" />
                        <p class="text-muted">
                            This information will be used for fetching new leads from IndiaMart.
                        </p>
                        <span style="color:red">* Changing settings incorreclty may stop IndiaMart leads import.</span>
                        <div class="tab-content mtop30">
                            <?php echo form_open($this->uri->uri_string(), ["id" => "indiamartform"]); ?>
                            <?php $attrs = (isset($indiamart) ? array() : array('autofocus' => true)); ?>
                            <?php $value = (isset($indiamart) ? $indiamart->indiamart_key : ''); ?>
                            <?php echo render_input('indiamart_key', 'indiamart_key', $value, 'text', $attrs); ?>

                            <?php $attrs = (isset($indiamart) ? array() : array('autofocus' => true)); ?>
                            <?php $value = (isset($indiamart) ? $indiamart->indiamart_number : ''); ?>
                            <?php echo render_input('indiamart_number', 'indiamart_id', $value, 'text', $attrs); ?>
                        </div>
                        <button type="submit" name="type" value="indiamart" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript">
    $(function() {
        appValidateForm($('form'), {
            indiamart_number: 'required',
            indiamart_key: 'required'
        });
    });
</script>