<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <?php echo form_open($this->uri->uri_string()); ?>
         <div class="col-md-7">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin">
                     <?php echo $title; ?>
                  </h4>
                  <hr class="hr-panel-heading" />
                  <?php if (isset($role)) { ?>
                     <a href="<?php echo admin_url('roles/role'); ?>" class="btn btn-success pull-right mbot20 display-block"><?php echo _l('new_role'); ?></a>
                     <div class="clearfix"></div>
                  <?php } ?>
                  <?php if (isset($role)) { ?>
                     <?php if (total_rows(db_prefix() . 'staff', array('role' => $role->roleid)) > 0) { ?>
                        <div class="alert alert-warning bold">
                           <?php echo _l('change_role_permission_warning'); ?>
                           <div class="checkbox">
                              <input type="checkbox" name="update_staff_permissions" id="update_staff_permissions">
                              <label for="update_staff_permissions"><?php echo _l('role_update_staff_permissions'); ?></label>
                           </div>
                        </div>
                     <?php } ?>
                  <?php } ?>
                  <?php $attrs = (isset($role) ? array() : array('autofocus' => true)); ?>
                  <?php $value = (isset($role) ? $role->name : ''); ?>
                  <?php echo render_input('name', 'role_add_edit_name', $value, 'text', $attrs); ?>
                  <?php
                  $permissionsData = ['funcData' => ['role' => isset($role) ? $role : null]];
                  $this->load->view('admin/staff/permissions', $permissionsData);
                  ?>
                  <hr />
                  <button type="submit" class="btn btn-info pull-right"><?php echo _l('submit'); ?></button>
               </div>
            </div>
         </div>
         <?php if (isset($role_staff)) { ?>
            <div class="col-md-5">
               <div class="panel_s">
                  <div class="panel-body">
                     <h4 class="no-margin">
                        <?php echo _l('staff_which_are_using_role'); ?>
                     </h4>
                     <hr class="hr-panel-heading" />
                     <div class="table-responsive">
                        <table class="table dt-table">
                           <thead>
                              <tr>
                                 <th><?php echo _l('staff_dt_name'); ?></th>
                              </tr>
                           </thead>
                           <tbody>
                              <?php foreach ($role_staff as $staff) { ?>
                                 <tr>
                                    <td>
                                       <?php
                                       echo '<a href="' . admin_url('staff/profile/' . $staff['staffid']) . '">' . staff_profile_image($staff['staffid'], [
                                          'staff-profile-image-small',
                                       ]) . '</a>';
                                       echo ' <a href="' . admin_url('staff/member/' . $staff['staffid']) . '">' . $staff['firstname'] . ' ' . $staff['lastname'] . '</a>';
                                       ?>
                                    </td>
                                 </tr>
                              <?php } ?>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         <?php } ?>
         <?php
         if (isset($role) && $role->roleid == 3) {
            $managerRightModule = get_available_manager_permissions_for_under_staff();
         ?>
            <div class="col-md-5">
               <div class="panel_s">
                  <div class="panel-body">
                     <h4 class="no-margin">Allow manager to access employee data</h4>
                     <hr class="hr-panel-heading" />
                     <div>
                        <?php
                        $checkedarr = unserialize($role->employee_permissions);
                        foreach ($managerRightModule as $key => $permission) {
                           $checked = (in_array($key, $checkedarr)) ? "checked" : "";
                        ?>
                           <tr data-name="<?= $permission ?>">
                              <td>
                                 <div class="checkbox">
                                    <input type="checkbox" class="capability" id="manager_<?= $permission ?>" name="employee_permissions[]" value="<?= $key ?>" <?= $checked ?>>
                                    <label for="manager_<?= $permission ?>"><?= $permission ?></label>
                                 </div>
                              </td>
                           </tr>
                        <?php
                        }
                        ?>
                     </div>
                  </div>
               </div>
            </div>
         <?php
         }
         ?>
         <?php echo form_close(); ?>
      </div>
   </div>
   <?php init_tail(); ?>
   <script>
      $(function() {
         appValidateForm($('form'), {
            name: 'required'
         });
      });
   </script>
   </body>

   </html>