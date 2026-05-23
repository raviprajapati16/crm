<?php
$company_signature = get_option('signature_image');
?>
<table class="table-pdf">
    <tr>
        <td style="text-align:center;" colspan="2">
            <strong>From Seller or Supplier</strong>
        </td>
    </tr>
    <tr>
        <td><strong>For, M/s</strong> <?= get_option('invoice_company_name') ?>
            <br><br><br>
            SIGNED BY
        </td>
        <td><img width="220px" src="<?php echo base_url('uploads/company/' . $company_signature); ?>"></td>
    </tr>
</table>