<?php
if (!empty($contract->contacts)) {
?>
    <style>
        .table-pdf {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
    <table border="1" class="table-pdf">
        <tr>
            <td style="text-align:center;" colspan="2">
                <strong>From Purchaser's MS. </strong> <?= $contract->company; ?>
            </td>
        </tr>
        <?php
        $partner = 1;
        foreach ($contract->contacts as $contact) {
            $signature_file = ($contact['default_signature'] == "physical") ? $contact['physical_signature'] : $contact['digital_signature'];
            $signature =  site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('contract') . $contract->id . '/' . $signature_file));
            $name = $contact['name'];
        ?>
            <tr>
                <td>
                    <?= $partner  ?>. PARTNER
                    <br><br><br>
                    SIGNED BY
                    <br>
                    <h4><?= strtoupper($name)  ?></h4>
                </td>
                <td><img width="150px" src="<?= $signature ?>"></td>
            </tr>
        <?php
            $partner++;
        } ?>
    </table>
    <br>
<?php
}
?>