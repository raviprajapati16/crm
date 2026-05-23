<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade email-template" data-editor-id=".<?php echo 'tinymce-contract-verification-' . $contract->id; ?>" id="contract_verification_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <?php echo form_open('admin/contracts/contract_verificaton/' . $contract->id); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">
                    Agreement Verification
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php
                            if ($verification_template['template_disabled']) {
                                echo '<div class="alert alert-danger">';
                                echo 'The email template <b><a href="' . admin_url('emails/email_template/' . $verification_template['template_id']) . '" target="_blank">' . $verification_template['template_system_name'] . '</a></b> is disabled. Click <a href="' . admin_url('emails/email_template/' . $verification_template['template_id']) . '" target="_blank">here</a> to enable the email template in order to be sent successfully.';
                                echo '</div>';
                            }
                            $selected = array();
                            if ($contract->rel_type == "customer") {
                                $contacts = $this->clients_model->get_contacts($contract->client, array('active' => 1, 'contract_emails' => 1));
                            } else if ($contract->rel_type == "vendor") {
                                $this->load->model('leads_model');
                                $contacts = $this->leads_model->get('', [db_prefix() . "leads.id" => $contract->client]);
                            } else if ($contract->rel_type == "contact_book") {
                                $this->load->model('contact_book_model');
                                $contacts[] = $this->contact_book_model->get($contract->client);
                            }
                            foreach ($contacts as $key => $contact) {
                                array_push($selected, $contact['id']);
                                if (!isset($contact['firstname'])) {
                                    $contacts[$key]['firstname'] = $contact['name'];
                                    $contacts[$key]['lastname'] = "";
                                }
                            }
                            if (count($selected) == 0) {
                                echo '<p class="text-danger">' . _l('sending_email_contact_permissions_warning', _l('customer_permission_contract')) . '</p><hr />';
                            }
                            echo render_select('sent_to[]', $contacts, array('id', 'email', 'firstname,lastname'), 'contract_send_to', $selected, array('multiple' => true), array(), '', '', false);

                            ?>
                        </div>
                        <?php echo render_input('cc', 'CC'); ?>

                        <p class="bold">Email Body</p>

                        <?php echo render_textarea('email_template_custom_for_verification', '', $verification_template['template']->message, array(), array(), '', 'tinymce-contract-verification-' . $contract->id); ?>
                        <?php echo form_hidden('template_name', $template_name); ?>
                        <hr />
                        <div class="checkbox checkbox-primary">
                            <input type="checkbox" <?php if (empty($contract->content)) {
                                                        echo 'disabled';
                                                    } else {
                                                        echo 'checked';
                                                    } ?> name="attach_pdf" id="attach_pdf">
                            <label for="attach_pdf"><?php echo _l('contract_send_to_client_attach_pdf'); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" class="btn btn-info"><?php echo _l('send'); ?></button>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>