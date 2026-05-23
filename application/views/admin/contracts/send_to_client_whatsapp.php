<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="send_to_client_whatsapp_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <?php echo form_open('', ["id" => "contractWhatsappShareForm"]); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">WhatsApp Share</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <?php
                            $selected = array();
                            if ($contract->rel_type == "customer") {
                                $contacts = $this->clients_model->get_contacts($contract->client, array('active' => 1));
                            } else if ($contract->rel_type == "vendor") {
                                $this->load->model('leads_model');
                                $contacts = $this->leads_model->get('', [db_prefix() . "leads.id" => $contract->client]);
                            } else if ($contract->rel_type == "contact_book") {
                                $this->load->model('contact_book_model');
                                $contacts[] = $this->contact_book_model->get($contract->client);
                            }
                            $countryData = get_country($contract->country);

                            foreach ($contacts as $key => $contact) {
                                if (!empty($contact['phonenumber'])) {
                                    $contacts[$key]['phonenumber'] = convert_phonenumer_by_country($contact['phonenumber'], $countryData->iso2);
                                }
                                if (!empty($contact['phone'])) {
                                    $contacts[$key]['phonenumber'] = convert_phonenumer_by_country($contact['phone'], $countryData->iso2);
                                }
                                if (!isset($contact['firstname'])) {
                                    $contacts[$key]['firstname'] = $contact['name'];
                                    $contacts[$key]['lastname'] = "";
                                }
                            }
                            echo render_select('mobilenumber', $contacts, array('id', 'phonenumber', 'firstname,lastname'), 'contract_send_to', [], [], array(), '', '', false);
                            ?>
                        </div>
                        <hr />
                        <h5 class="bold">Message Preview</h5>
                        <textarea id="whatsapp_message" name="whatsapp_message" class="form-control" rows="14">
                            <?= message_html_to_text($template->message) ?>
                        </textarea>
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
<script>
    $(document).ready(function() {

        $('#contractWhatsappShareForm').on('submit', function(e) {
            e.preventDefault();
            whatsappShare();
            return false;
        })
    });

    function whatsappShare() {
        $.ajax({
            url: "<?php echo admin_url('contracts/whatsapp_share') ?>",
            method: "POST",
            data: {
                contract_id: "<?= $contract->id ?>",
                contact_id: $("#mobilenumber").val(),
                content: $("#whatsapp_message").val(),
            },
            dataType: 'json'
        }).done(function(result) {
            if (result.success) {
                alert_float('success', result.message);
                window.open(result.link, '_blank');
                setTimeout(() => {
                    location.reload();
                }, 700);
            } else {
                alert_float('danger', result.message);
            }
        });
    }
</script>