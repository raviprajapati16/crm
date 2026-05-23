<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php $this->load->view('include_top'); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
        	<div class="col-md-12 col-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php
                        $form_attributes = ['id'=>'indiamart_fetch_leads']; 
                        echo form_open($this->uri->uri_string(),$form_attributes); ?>
                        <div class="row">
                            <div class="col-md-4">
                                <?php echo render_date_input('start_date','start_date'); ?>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_date_input('end_date','end_date'); ?>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">&nbsp;</label>
                                    <div class="input-group">
                                        <input type="reset" name="reset" class="btn btn-dafault" value="Reset"> &nbsp;
                                        <button type="submit" id="submit_btn" class="btn btn-info" data-loading-text="<i class='fa fa-circle-o-notch fa-spin'></i> Loading"><?php echo _l('search'); ?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                        <div class="text-danger" id="error"></div>
                    </div>
                </div>
                <div class="row" id="instructions">
                    <div class="col-md-12">
                        <div class="callout callout-info">
                            <h4>Hit "Search" Button</h4>
                            Fetch latest leads Or specify start & end date to fetch leads between those dates.
                        </div>
                    </div>
                </div>

                <div class="panel_s hide" id="response">
                    <div class="panel-body">
                        <h4 class="no-margin" id="head_title">Search Leads</h4>
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