<?php
if (!empty($contract->contacts)) {
    $contact = [];
    foreach ($contract->contacts as $contact) {
        if ($contact['signed']) {
            $contact = $contact;
            break;
        }
    }

?>
    <table style="width:100%; border-collapse: collapse; border:1px solid black;">
        <tr>
            <td width="50%" style="height:150px; vertical-align: bottom; padding:10px; text-align:center;">
                <?php
                $company_signature_path = 'uploads/company/' . get_option('signature_image');
                if (file_exists($company_signature_path) && !empty(get_option('signature_image'))) {
                    $company_signature = base_url('uploads/company/' . get_option('signature_image'));
                ?>
                    <div style="display: flex;">
                        <img width="100px" src="<?= $company_signature ?>" style="margin-right: 10px;">
                    </div>
                <?php } ?>
                <div style="font-size: 14px; margin-top: 10px;"><?= get_option('companyname'); ?></div>
            </td>
            <td width="50%" style="height:150px; vertical-align: bottom; padding:10px; text-align:center;">
                <?php
                $signature_file = ($contact['default_signature'] == "physical") ? $contact['physical_signature'] : $contact['digital_signature'];
                $sign_path = protected_file_url_by_path(get_upload_path_by_type('contract') . $contract->id . '/' . $signature_file);
                if (file_exists($sign_path) && !empty($signature_file)) {
                    $signature =  site_url('download/preview_image?path=' . $sign_path);
                ?>
                  <div style="display: flex;">
    <img src="<?= $signature ?>" width="100" height="80" style="margin-right: 10px;">
</div>

                    <br>
                <?php } ?>
                <div style="font-size: 12px; margin-top: 10px;"><?= $contact['name']; ?></div>
                <div style="font-size: 14px; margin-top: 10px;"><?= $contact['company']; ?></div>
            </td>
        </tr>
    </table>

    </div>
    <br>
<?php
}
?>