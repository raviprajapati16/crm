<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $this->load->view('include_top'); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
        	<div class="col-md-12 col-12">
                 <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin" id="head_title"><?= $title ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php
                        $form_attributes = ['id'=>'indiamart_import_leads']; 
                        echo form_open($this->uri->uri_string(),$form_attributes); ?>
                        <div class="row">
                            <div class="col-md-4">
                              <?php
                                echo render_leads_status_select($statuses, '','lead_add_edit_status');
                              ?>
                             </div>
                             <div class="col-md-4">
                                <?php
                                   $selected = (isset($lead) ? $lead->source : get_option('leads_default_source'));
                                   echo render_leads_source_select($sources, $selected,'lead_add_edit_source');
                                ?>
                             </div>
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">&nbsp;</label>
                                    <div class="input-group">
                                        <button type="submit" id="import_btn" class="btn btn-info" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Importing"><?php echo _l('import'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table id="leads_table" width="100%" class="table table-responsive">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select_all"></th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Contact No</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($leads as $lead): $leadData = json_decode($lead->lead_data,true); 
                                    $imported_span = "";
                                    if($lead->is_imported == 1)
                                    {
                                        $imported_span = "<span class='text-info'>Imported</span>";
                                    }
                                    ?>
                                    <tr id="lead_<?= $lead->id ?>">
                                        <td>
                                            <?php if($lead->is_imported == 0): ?>
                                            <input type="checkbox" name="lead_ids[]" class="import_id" value="<?= $lead->id ?>">
                                            <?php endif; ?>
                                        </td>
                                        <td><?= $leadData['SENDERNAME'] ?> <?= $imported_span ?></td>
                                        <td><?= $leadData['SENDEREMAIL'] ?></td>
                                        <td><?= $leadData['MOB'] ?></td>
                                        <td><?= $leadData['SUBJECT'] ?></td>
                                        <td><?= $leadData['ENQ_MESSAGE'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>    
<?php $this->load->view('include_bottom'); ?>