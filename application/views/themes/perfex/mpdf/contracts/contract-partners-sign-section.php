<?php
if (!empty($contract->contacts)) {
?>
    <style>
        .table-pdf {
            width: 100%;
            border-collapse: collapse;
        }

        .table-pdf th,
        .table-pdf td {
            border: 1px solid black;
            padding: 8px;
        }
    </style>
    <table class="table-pdf">
        <?php
        if ($contract->rel_type != "vendor") {
        ?>
            <tr>
                <td style="text-align:center;" colspan="2">
                    <strong>From Purchaser's MS. <?= $contract->company; ?></strong>
                </td>
            </tr>
        <?php
        }
        ?>
        <?php
        $partner = 1;
        foreach ($contract->contacts as $contact) {
            $signature_file = ($contact['default_signature'] == "physical") ? $contact['physical_signature'] : $contact['digital_signature'];
            $signature =  site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('contract') . $contract->id . '/' . $signature_file));
            $selfie =  site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('contract') . $contract->id . '/' . $contact['acceptance_selfie']));
            $name = $contact['name'];
        ?>
            <tr>
                <td>
                    <?php
                    if ($contract->rel_type != "vendor") {
                    ?>
                        <?= $partner  ?>. PARTNER
                    <?php
                    }
                    ?>
                    <br><br><br>
                    SIGNED BY
                    <br>
                    <h4><?= strtoupper($name)  ?></h4>
                </td>
                <?php if ($contact['signed']) { ?>
                    <td>
                        <div style="display: flex;">
                            <img width="150px" src="<?= $signature ?>" style="margin-right: 10px;">
                            <img width="150px" src="<?= $selfie ?>">
                        </div>
                    </td>
                <?php } else { ?>
                    <td style="color:red;text-align:center;font-weight:bold;">Not signed yet</td>
                <?php } ?>
            </tr>
        <?php
            $partner++;
        } ?>
    </table>
    <br>
<?php
}
?>