<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-3">
                <ul class="nav navbar-pills navbar-pills-flat nav-tabs nav-stacked">
                    <?php
                    $active_group = $this->input->get('group') ? $this->input->get('group') : 'groups';
                    ?>
                    <li class="<?php if($active_group == 'groups'){echo 'active';} ?>">
                        <a href="<?php echo admin_url('freight_master/manage?group=groups'); ?>" data-group="groups">
                            Groups
                        </a>
                    </li>
                    <li class="<?php if($active_group == 'sizes'){echo 'active';} ?>">
                        <a href="<?php echo admin_url('freight_master/manage?group=sizes'); ?>" data-group="sizes">
                            Sizes
                        </a>
                    </li>
                </ul>
            </div>
            <div class="col-md-9">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if($active_group == 'groups'){ ?>
                            <div class="_buttons">
                                <a href="#" onclick="new_group(); return false;" class="btn btn-info pull-left display-block">
                                    New Group
                                </a>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading" />
                            <?php render_datatable([
                                'Name',
                                'Options',
                            ], 'freight_groups'); ?>
                        <?php } else if($active_group == 'sizes'){ ?>
                            <div class="_buttons">
                                <a href="#" onclick="new_size(); return false;" class="btn btn-info pull-left display-block">
                                    New Size
                                </a>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading" />
                            <?php render_datatable([
                                'Name',
                                'Group',
                                'Options',
                            ], 'freight_sizes'); ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Group Modal -->
<div class="modal fade" id="group_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('freight_master/group'), ['id'=>'group_form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title">Edit Group</span>
                    <span class="add-title">New Group</span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="group_additional"></div>
                        <?php echo render_input('name', 'Group Name'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<!-- Size Modal -->
<div class="modal fade" id="size_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('freight_master/size'), ['id'=>'size_form']); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    <span class="edit-title">Edit Size</span>
                    <span class="add-title">New Size</span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="size_additional"></div>
                        <?php echo render_select('group_id', $groups, ['id', 'name'], 'Group'); ?>
                        <?php echo render_input('name', 'Size Name'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function(){
        <?php if($active_group == 'groups'){ ?>
            initDataTable('.table-freight_groups', admin_url + 'freight_master/groups_table', [1], [1]);
            appValidateForm($('#group_form'), {
                name: 'required'
            }, manage_groups);
        <?php } else if($active_group == 'sizes'){ ?>
            initDataTable('.table-freight_sizes', admin_url + 'freight_master/sizes_table', [2], [2]);
            appValidateForm($('#size_form'), {
                name: 'required',
                group_id: 'required'
            }, manage_sizes);
        <?php } ?>

        $('#group_modal').on('hidden.bs.modal', function(event) {
            $('#group_additional').html('');
            $('#group_modal input[name="name"]').val('');
            $('#group_modal .add-title').removeClass('hide');
            $('#group_modal .edit-title').removeClass('hide');
        });

        $('#size_modal').on('hidden.bs.modal', function(event) {
            $('#size_additional').html('');
            $('#size_modal input[name="name"]').val('');
            $('#size_modal select[name="group_id"]').val('').selectpicker('refresh');
            $('#size_modal .add-title').removeClass('hide');
            $('#size_modal .edit-title').removeClass('hide');
        });
    });
    
    function manage_groups(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            window.location.reload();
        });
        return false;
    }

    function manage_sizes(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function(response) {
            window.location.reload();
        });
        return false;
    }

    function new_group() {
        $('#group_modal').modal('show');
        $('#group_modal .edit-title').addClass('hide');
    }

    function edit_group(invoker, id) {
        var name = $(invoker).data('name');
        $('#group_additional').append(hidden_input('id', id));
        $('#group_modal input[name="name"]').val(name);
        $('#group_modal').modal('show');
        $('#group_modal .add-title').addClass('hide');
    }

    function new_size() {
        $('#size_modal').modal('show');
        $('#size_modal .edit-title').addClass('hide');
    }

    function edit_size(invoker, id) {
        var name = $(invoker).data('name');
        var group_id = $(invoker).data('group-id');
        $('#size_additional').append(hidden_input('id', id));
        $('#size_modal input[name="name"]').val(name);
        $('#size_modal select[name="group_id"]').val(group_id).selectpicker('refresh');
        $('#size_modal').modal('show');
        $('#size_modal .add-title').addClass('hide');
    }
</script>
</body>
</html>
