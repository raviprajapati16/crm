<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade email-template" data-editor-id=".<?php echo 'tinymce-' . $payment->paymentid; ?>" id="payment_send_to_client" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <?php echo form_open('admin/payments/send_to_email/' . $payment->paymentid); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo _l('send_payment_receipt_to_client'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php

                            if ($template_disabled) {
                                echo '<div class="alert alert-danger">';
                                echo 'The email template <b><a href="' . admin_url('emails/email_template/' . $template_id) . '" target="_blank">' . $template_system_name . '</a></b> is disabled. Click <a href="' . admin_url('emails/email_template/' . $template_id) . '" target="_blank">here</a> to enable the email template in order to be sent successfully.';
                                echo '</div>';
                            }
                            $client_id = "";
                            if ($payment->invoiceid == "0") {
                                $client_id = get_proposal_client_id($payment->proposal_id);
                            } else {
                                $client_id = $payment->invoice->clientid;
                            }
                            if (!empty($client_id)) {
                                $selected = array();
                                $contacts = $this->clients_model->get_contacts($client_id, array('active' => 1, 'invoice_emails' => 1));

                                foreach ($contacts as $contact) {
                                    array_push($selected, $contact['id']);
                                }

                                if (count($selected) == 0) {
                                    echo '<p class="text-danger">' . _l('sending_email_contact_permissions_warning', _l('customer_permission_invoice')) . '</p><hr />';
                                }

                                echo render_select('sent_to[]', $contacts, array('id', 'email', 'firstname,lastname'), 'invoice_estimate_sent_to_email', $selected, array('multiple' => true), array(), '', '', false);
                            } else {
                                $this->load->model('leads_model');
                                $this->load->model('proposals_model');
                                $proposal_data = $this->proposals_model->get($payment->proposal_id);
                                $leadData = $this->leads_model->get($proposal_data->rel_id);
                                $contacts[] = array("id" => $leadData->id, "email" => $leadData->email, "name" => $leadData->name );
                                $selected = [$leadData->id];
                                echo render_select('lead_sent_to[]', $contacts, array('id', 'email', 'name'), 'invoice_estimate_sent_to_email', $selected, array('multiple' => true), array(), '', '', false);
                            }
                            ?>
                        </div>
                        <hr />
                        <h5 class="bold"><?php echo _l('invoice_send_to_client_preview_template'); ?></h5>
                        <hr />
                        <?php echo render_textarea('email_template_custom', '', $template->message, array(), array(), '', 'tinymce-' . $payment->paymentid); ?>
                        <?php echo form_hidden('template_name', $template_name); ?>
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