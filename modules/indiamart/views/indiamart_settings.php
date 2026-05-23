<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
        	<div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open($this->uri->uri_string()); ?>
                        <?php $attrs = (isset($indiamart) ? array() : array('autofocus'=>true)); ?>
                        <?php $value = (isset($indiamart) ? $indiamart->indiamart_key : ''); ?>
                        <?php echo render_input('indiamart_key','indiamart_key',$value,'text',$attrs); ?>

                        <?php $attrs = (isset($indiamart) ? array() : array('autofocus'=>true)); ?>
                        <?php $value = (isset($indiamart) ? $indiamart->indiamart_number : ''); ?>
                        <?php echo render_input('indiamart_number','indiamart_id',$value,'text',$attrs); ?>

                        <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>    
<?php init_tail(); ?>
<script type="text/javascript">
	$(function(){
       appValidateForm($('form'), {
        indiamart_number: 'required',
        indiamart_key: 'required'
      });
    });
</script>