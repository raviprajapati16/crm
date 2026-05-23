<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12 col-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('google_sheets/index'); ?>" class="btn btn-info mright5 pull-left display-block"><i class="fa fa-arrow-left" aria-hidden="true"></i> <?php echo _l('back'); ?></a>
                        </div>
                        <div class="clearfix"></div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <h4 class="no-margin" id="head_title">Edit Google Sheet</h4>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />

                        <?php echo form_open(admin_url('google_sheets/edit/' . $sheet->id), array('id' => 'sheet_form')); ?>
                        <div class="row">
                            <div class="col-md-4">
                                <?php echo render_input('sheet_title', 'Sheet Title', $sheet->sheet_title, 'text', []); ?>
                            </div>
                            <div class="col-md-8">
                                <?php echo render_input('sheet_url', 'Google Sheet URL', $sheet->sheet_url, 'text', ['readonly' => 'readonly']); ?>
                                <p class="text-muted"><small>The sheet URL cannot be changed after creation.</small></p>
                            </div>
                        </div>

                        <hr class="hr-panel-heading" />
                        <h4>Default Settings</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <?php
                                echo render_leads_status_select($statuses, $sheet->status, 'lead_add_edit_status');
                                ?>
                            </div>
                            <div class="col-md-4">
                                <?php
                                echo render_leads_source_select($sources, $sheet->source, 'lead_add_edit_source');
                                ?>
                            </div>
                            <div class="col-md-4">
                                <?php
                                $staff_users = [];
                                foreach ($staff as $user) {
                                    if (is_staff_in_sales_department($user['id'])) {
                                        $staff_users[] = $user;
                                    }
                                }
                                echo render_select('assignee', $staff_users, array('staffid', array('firstname', 'lastname')), 'Assignee', $sheet->assignee, array('data-width' => '100%', 'data-size' => 6, 'data-none-selected-text' => _l('leads_dt_assigned')), array(), 'no-mbot'); ?>
                            </div>
                        </div>

                        <hr class="hr-panel-heading" />
                        <h4>Column Mapping (Read Only)</h4>
                        <p>Column mapping cannot be changed after creation.</p>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Lead Fields</th>
                                                <th>Google Sheet Columns</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><input type="text" class="form-control" value="name" readonly></td>
                                                <td>
                                                    <input type="text" class="form-control" value="<?php echo isset($mapping['name']) ? $mapping['name'] : ''; ?>" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" value="email" readonly></td>
                                                <td>
                                                    <input type="text" class="form-control" value="<?php echo isset($mapping['email']) ? $mapping['email'] : ''; ?>" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" value="phonenumber" readonly></td>
                                                <td>
                                                    <input type="text" class="form-control" value="<?php echo isset($mapping['phonenumber']) ? $mapping['phonenumber'] : ''; ?>" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" value="company" readonly></td>
                                                <td>
                                                    <input type="text" class="form-control" value="<?php echo isset($mapping['company']) ? $mapping['company'] : ''; ?>" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" value="address" readonly></td>
                                                <td>
                                                    <input type="text" class="form-control" value="<?php echo isset($mapping['address']) ? $mapping['address'] : ''; ?>" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" value="country" readonly></td>
                                                <td>
                                                    <input type="text" class="form-control" value="<?php echo isset($mapping['country']) ? $mapping['country'] : ''; ?>" readonly>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Lead Fields</th>
                                                <th>Google Sheet Columns</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><input type="text" class="form-control" value="state" readonly></td>
                                                <td>
                                                    <input type="text" class="form-control" value="<?php echo isset($mapping['state']) ? $mapping['state'] : ''; ?>" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" value="city" readonly></td>
                                                <td>
                                                    <input type="text" class="form-control" value="<?php echo isset($mapping['city']) ? $mapping['city'] : ''; ?>" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" value="zipcode" readonly></td>
                                                <td>
                                                    <input type="text" class="form-control" value="<?php echo isset($mapping['zipcode']) ? $mapping['zipcode'] : ''; ?>" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" value="product" readonly></td>
                                                <td>
                                                    <input type="text" class="form-control" value="<?php echo isset($mapping['product']) ? $mapping['product'] : ''; ?>" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" value="website" readonly></td>
                                                <td>
                                                    <input type="text" class="form-control" value="<?php echo isset($mapping['website']) ? $mapping['website'] : ''; ?>" readonly>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><input type="text" class="form-control" value="description" readonly></td>
                                                <td>
                                                    <input type="text" class="form-control" value="<?php echo isset($mapping['description']) ? $mapping['description'] : ''; ?>" readonly>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <hr class="hr-panel-heading" />
                                <button type="submit" class="btn btn-success pull-right">Update</button>
                            </div>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php init_tail(); ?>
    <script type="text/javascript">
        $(function() {
            // Form validation
            $("#sheet_form").appFormValidator({
                rules: {
                    sheet_title: 'required',
                    lead_add_edit_status: 'required',
                    lead_add_edit_source: 'required',
                    assignee: 'required'
                }
            });
        });
    </script>
</div>
</body>
</html>