<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if (isset($client)) { ?>
    <h4 class="customer-profile-group-heading"><?php echo _l('contracts_notes_tab'); ?></h4>
    <div class="col-md-12">

        <a href="#" class="btn btn-success mtop15 mbot10" onclick="slideToggle('.usernote'); return false;"><?php echo _l('new_note'); ?></a>
        <div class="clearfix"></div>
        <div class="row">
            <hr class="hr-panel-heading" />
        </div>
        <div class="clearfix"></div>
        <div class="usernote hide">
            <?php echo form_open(admin_url('misc/add_note/' . $client->userid . '/customer'), array('id' => 'customer-notes-form')); ?>
            <?php echo render_textarea('description', 'note_description', '', array('rows' => 5)); ?>
            <div class="row">
                <div class="col-md-6">
                    <?php if (is_admin()) { ?>
                        <p>Is Private Note ?</p>
                        <div class="onoffswitch">
                            <input type="checkbox" id="is_private" class="onoffswitch-checkbox" value="1" name="is_private">
                            <label class="onoffswitch-label" for="is_private"></label>
                        </div>
                    <?php } ?>
                </div>
                <div class="col-md-6">
                    <div class="pull-right" style="width: 250px;">
                        <?php
                        $source_staff = isset($members) ? $members : (isset($staff) ? $staff : []);
                        $filtered_notify_staff = get_staff_for_notification($source_staff);
                        echo render_select('notify_staff_id', $filtered_notify_staff, array('staffid', array('firstname', 'lastname')), 'Select Staff to Notify', '', array('required' => 'true'));
                        ?>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="notify_via_email" id="notify_via_email_client" value="yes">
                            <label for="notify_via_email_client">Email Notification</label>
                        </div>
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" name="notify_via_whatsapp" id="notify_via_whatsapp_client" value="yes">
                            <label for="notify_via_whatsapp_client">WhatsApp Notification</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
            <button class="btn btn-info pull-right mbot15 mtop15">
                <?php echo _l('submit'); ?>
            </button>
            <?php echo form_close(); ?>
        </div>
        <div class="clearfix"></div>
        <div class="mtop15">
            <table class="table dt-table scroll-responsive" data-order-col="2" data-order-type="desc">
                <thead>
                    <tr>
                        <th width="50%">
                            <?php echo _l('clients_notes_table_description_heading'); ?>
                        </th>
                        <th>
                            <?php echo _l('clients_notes_table_addedfrom_heading'); ?>
                        </th>
                        <th>
                            <?php echo _l('clients_notes_table_dateadded_heading'); ?>
                        </th>
                        <?php if (is_admin()) { ?>
                            <th>Is Private Note ?</th>
                        <?php } ?>
                        <th>
                            <?php echo _l('options'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user_notes as $note) {
                        if (!is_admin() && $note['is_private'] == 1) {
                            continue;
                        }
                    ?>
                        <tr>
                            <td width="50%">
                                <div data-note-description="<?php echo $note['id']; ?>">
                                    <?php echo check_for_links($note['description']); ?>
                                </div>
                                <div data-note-edit-textarea="<?php echo $note['id']; ?>" class="hide">
                                    <textarea name="description" class="form-control" rows="4"><?php echo clear_textarea_breaks($note['description']); ?></textarea>
                                    <div class="text-right mtop15">
                                        <button type="button" class="btn btn-default" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><?php echo _l('cancel'); ?></button>
                                        <button type="button" class="btn btn-info" onclick="edit_note(<?php echo $note['id']; ?>);"><?php echo _l('update_note'); ?></button>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php echo '<a href="' . admin_url('profile/' . $note['addedfrom']) . '">' . $note['firstname'] . ' ' . $note['lastname'] . '</a>' ?>
                                <?php if (isset($note['notify_staff_id']) && !empty($note['notify_staff_id'])) { ?>
                                    <span style="color:#03a9f4 !important; font-size:12px;"> | Notify to: <?php echo get_staff_full_name($note['notify_staff_id']); ?></span>
                                <?php } ?>
                            </td>
                            <td data-order="<?php echo $note['dateadded']; ?>">
                                <?php if (!empty($note['date_contacted'])) { ?>
                                    <span data-toggle="tooltip" data-title="<?php echo html_escape(_dt($note['date_contacted'])); ?>">
                                        <i class="fa fa-phone-square text-success font-medium valign" aria-hidden="true"></i>
                                    </span>
                                <?php } ?>
                                <?php echo _dt($note['dateadded']); ?>
                            </td>
                            <?php if (is_admin()) { ?>
                                <td>
                                    <?php if (get_staff_user_id() == $note['addedfrom']) { ?>
                                        <div class="onoffswitch">
                                            <input type="checkbox" data-id="<?= $note['id'] ?>" id="is_private_<?= $note['id'] ?>" class="onoffswitch-checkbox note-private-switch" value="1" <?= ($note['is_private'] == "1") ? "checked" : "" ?>>
                                            <label class="onoffswitch-label" for="is_private_<?= $note['id'] ?>"></label>
                                        </div>
                                    <?php } ?>
                                </td>
                            <?php } ?>
                            <td>
                                <?php if ($note['addedfrom'] == get_staff_user_id() || is_admin()) { ?>
                                    <a href="#" class="btn btn-default btn-icon" onclick="toggle_edit_note(<?php echo $note['id']; ?>);return false;"><i class="fa fa-pencil-square-o"></i></a>
                                    <a href="<?php echo admin_url('misc/delete_note/' . $note['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
    <script>
        window.addEventListener('load', function() {
            if (typeof appValidateForm === 'function') {
                appValidateForm($('#customer-notes-form'), {
                    notify_staff_id: 'required',
                    description: 'required'
                });
            }

            <?php $wa_link = $this->session->flashdata('whatsapp_link');
            if ($wa_link) { ?>
                if (typeof swal !== 'undefined') {
                    swal({
                        title: "Notification Ready",
                        text: "Email sent successfully! Click below to send the WhatsApp message.",
                        type: "success",
                        showCancelButton: true,
                        confirmButtonText: "Send WhatsApp",
                        cancelButtonText: "Close"
                    }, function(isConfirm) {
                        if (isConfirm) {
                            window.open("<?php echo $wa_link; ?>", '_blank');
                        }
                    });
                } else {
                    window.open("<?php echo $wa_link; ?>", '_blank');
                }
            <?php } ?>
        });
    </script>