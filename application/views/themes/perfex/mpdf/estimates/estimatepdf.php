<style>
    body {
        font-family: 'Free Sans', sans-serif;
    }
</style>
<table width="100%">
    <tr>
        <td colspan="2" style="text-align: center;">
            <span style="font-weight:bold;font-size:27px;">ESTIMATE</span><br />
            <b style="color:#4e4e4e;"># <?= format_estimate_number($estimate->id) ?></b>
            <?php
            if (get_option('show_status_on_pdf_ei') == 1) {
            ?>
                <br />
                <span style="color:rgb(<?= estimate_status_color_pdf($estimate->status) ?>);text-transform:uppercase;">
                    <?= format_estimate_status($estimate->status, '', false) ?>
                </span>
            <?php
            }
            ?>
        </td>
    </tr>
    <br /><br />
    <tr>
        <td style="text-align: center; font-size:18px;">
            <div><b>From : </b></div>
            <div>
                <?= format_organization_info(); ?>
            </div>
        </td>
    </tr>
    <br /><br />
    <tr>
        <td style="text-align: center; font-size:18px;">
            <div>
                <div><b>To : </b></div>
                <?= format_customer_info($estimate, 'estimate', 'billing'); ?>
            </div>
        </td>
    </tr>
    <?php
    if ($estimate->include_shipping == 1 && $estimate->show_shipping_on_estimate == 1) {
    ?>
        <br /><br />
        <tr>
            <td style="text-align: center; font-size:18px;">
                <div>
                    <div><b>Ship To : </b></div>
                    <?= format_customer_info($estimate, 'estimate', 'billing'); ?>
                </div>
            </td>
        </tr>
    <?php
    }
    ?>

    <br /><br />
    <tr>
        <td style="text-align: center; font-size:17px;">
            <div>
                <div><b>Estimate Date</b></div>
                <?= _d($estimate->date) ?>
            </div>
        </td>
    </tr>

    <?php
    if (!empty($estimate->expirydate)) {
    ?>
        <br />
        <tr>
            <td style="text-align: center; font-size:17px;">
                <div>
                    <div><b>Expiry Date</b></div>
                    <?= _d($estimate->expirydate) ?>
                </div>
            </td>
        </tr>
    <?php
    }
    ?>
    <?php
    if (!empty($estimate->reference_no)) {
    ?>
        <br />
        <tr>
            <td style="text-align: center; font-size:17px;">
                <div>
                    <div><b>Reference No.</b></div>
                    <?= _d($estimate->reference_no) ?>
                </div>
            </td>
        </tr>
    <?php
    }
    ?>

    <?php
    if ($estimate->sale_agent != 0 && get_option('show_sale_agent_on_estimates') == 1) {
    ?>
        <br />
        <tr>
            <td style="text-align: center; font-size:17px;">
                <div>
                    <div><b>Sale Agent</b></div>
                    <?= get_staff_full_name($estimate->sale_agent); ?>
                </div>
            </td>
        </tr>
    <?php
    }
    ?>

    <?php
    if ($estimate->project_id != 0 && get_option('show_project_on_estimate') == 1) {
    ?>
        <br />
        <tr>
            <td style="text-align: center; font-size:17px;">
                <div>
                    <div><b>Project</b></div>
                    <?= get_project_name_by_id($estimate->project_id); ?>
                </div>
            </td>
        </tr>
    <?php
    }
    ?>

    <?php
    $pdf_custom_fields = get_custom_fields('estimate', array('show_on_pdf' => 1));
    if (!empty($pdf_custom_fields)) {
    ?>
        <br />
        <?php
        foreach ($pdf_custom_fields as $field) {
            $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
            if ($value == '') {
                continue;
            }
        ?>
            <tr>
                <td style="text-align: center; font-size:17px;">
                    <div><?= $field['name'] . ': ' . $value ?></div>
                </td>
            </tr>
        <?php
        }
        ?>
    <?php
    }
    ?>
</table>


<?php
$CI = &get_instance();
echo $contract_items = $CI->load->view(
    'themes/' . active_clients_theme() . '/mpdf/estimates/estimate-items-table',
    ['estimate' => $estimate],
    true
);

if (!empty($estimate->clientnote)) {
?>
    <br /><br />
    <div><b><?= _l('note'); ?> : </b></div>
    <div><?= $estimate->clientnote ?> </div>
<?php
}

if (!empty($estimate->terms)) {
?>
    <br /><br />
    <div><b><?= _l('terms_and_conditions'); ?> : </b></div>
    <div><?= $estimate->terms ?> </div>
<?php
}
?>

<?php
$company_signature = get_option('signature_image');
?>
<br /><br />
<table style="width: 100%; vertical-align: top;">
    <tr>
        <td style="text-align: left; vertical-align: top;">
            <div><b>Authorized Signature</b></div><br />
            <div><img style="" width="220px" src="<?php echo base_url('uploads/company/' . $company_signature); ?>" style="display: block;">
            </div>
        </td>
    </tr>
</table>