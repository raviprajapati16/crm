<table style="width: 50%; font-size: 8pt; FONT-FAMILY: Verdana, sans-serif; background: transparent !important;" cellpadding="0" cellspacing="0" border="0">
    <tbody>
        <tr>
            <td style="width: 260px; font-size: 8pt; line-height: 11pt; color:#444444; font-family: Verdana, sans-serif; vertical-align: top;" valign="top">
                <span style="padding: 0px; margin: 0px; font-size: 11pt; line-height: 13pt; font-family: Verdana, sans-serif; color:#0c542b; font-weight: bold;">
                    <?php
                    if (isset($firstName) && !empty($firstName)) {
                        echo $firstName;
                    }
                    ?>
                    <?php
                    if (isset($lastName)) {
                        echo $lastName;
                    }
                    ?>
                </span>
                <span style="padding: 0px; margin: 0px; font-family: Verdana, sans-serif; font-size:8pt; line-height: 14pt; color:#1e425a;"><br>
                    <?php
                    if (isset($title) && !empty($title)) {
                        echo $title;
                    }
                    ?><br>
                </span>
                <?php
                if (isset($mobile) && !empty($mobile)) {
                ?>
                    <span style="font-size: 8pt;  line-height: 11pt; color:#1e425a; FONT-FAMILY: Verdana, sans-serif;"><br>M: <?= $mobile ?></span>
                <?php
                }
                ?>
                <?php
                if (isset($phone) && !empty($phone)) {
                ?>
                    <span style="font-size: 8pt;  line-height: 11pt; color:#1e425a; FONT-FAMILY: Verdana, sans-serif;"><br>T: <?= $phone ?></span>
                <?php
                }
                ?>
                <?php
                if (isset($email) && !empty($email)) {
                ?>
                    <span style="font-size: 8pt;  line-height: 11pt; color:#1e425a; FONT-FAMILY: Verdana, sans-serif;"><br>E: <a href="mailto:<?= $email ?>" style="font-size: 8pt; line-height: 12pt; padding: 0px; margin: 0px; color:#1e425a; text-decoration: none;"><span style="text-decoration: none; font-size: 8pt;  line-height: 11pt; color:#1e425a; FONT-FAMILY: Verdana, sans-serif;"><?= $email ?></span></a>
                    </span>
                <?php
                }
                ?>

                <?php
                if ((isset($company) && !empty($company)) || (isset($address1) && !empty($address1))) {
                ?>
                    <span style="font-family: Verdana, sans-serif; font-size: 8pt; line-height: 11pt; color:#1e425a;"><br>
                        A:
                        <?php
                        if (isset($company) && !empty($company)) {
                        ?>
                            <?= $company ?><span>,</span>
                        <?php
                        }
                        ?>
                        <?php
                        if (isset($address1) && !empty($address1)) {
                        ?>
                            <span style="font-size: 8pt; line-height: 11pt;font-family: Verdana, sans-serif; color: #1e425a;"> <?= $address1 ?>,</span>
                            <?php
                            if (isset($address2) && !empty($address2)) {
                            ?>
                                <br><span style="font-size: 8pt; line-height: 11pt;font-family: Verdana, sans-serif; color: #1e425a;"> <?= $address2 ?></span>
                            <?php
                            }
                            ?>
                        <?php
                        }
                        ?>
                    <?php
                }
                    ?>
            </td>
            <td style="width: 25px;"></td>
            <td style="width: 115px; font-size: 8pt; font-family: Verdana, sans-serif; vertical-align: top; text-align: center;" valign="top">
                <span>
                    <img src="<?= site_url('uploads/company/') ?>logo-2.png" alt="company logo" width="170" border="0" style="border:0; height:auto; width:170px" />
                </span>
                <span style="line-height: 8pt; font-size: 8pt;">
                    <br><br>
                </span>
            </td>
        </tr>
    </tbody>
</table>
<table style="width: 50%; font-size: 8pt; FONT-FAMILY: Verdana, sans-serif; background: transparent !important;" cellpadding="0" cellspacing="0" border="0">
    <tbody>
        <tr>
            <?php
            if (isset($website) && !empty($website)) {
            ?>
                <td style="padding-top: 15px;">
                    <span style="font-size: 8pt; line-height: 12pt; font-weight: bold; padding: 0px; margin: 0px; color:#0c542b; FONT-FAMILY: Verdana, sans-serif;">
                        <a href="<?= $website ?>" target="_blank" style="color:#0c542b; text-decoration: none; font-weight: bold; ">
                            <span style="text-decoration: none; font-size: 8pt; line-height: 12pt; color:#0c542b; FONT-FAMILY: Verdana, sans-serif;font-weight: bold; "><?= $website ?></span>
                        </a>
                    </span>
                </td>
            <?php
            }
            ?>
            <td style="text-align: right; padding-top: 15px; white-space: nowrap">
                <?php
                if (isset($facebook) && !empty($facebook)) {
                ?>
                    <span><a href="<?= $facebook ?>" target="_blank" rel="noopener"><img border="0" width="26" src="<?= site_url('assets/images/email-signature-images/template-1/') ?>fb.png" alt="facebook icon" style="border:0; height:26px; width:26px"></a> </span>
                <?php
                }
                ?>
                <?php
                if (isset($twitter) && !empty($twitter)) {
                ?>
                    <span><a href="<?= $twitter ?>" target="_blank" rel="noopener"><img border="0" width="26" src="<?= site_url('assets/images/email-signature-images/template-1/') ?>tt.png" alt="twitter icon" style="border:0; height:26px; width:26px"></a> </span>
                <?php
                }
                ?>
                <?php
                if (isset($youtube) && !empty($youtube)) {
                ?>
                    <span><a href="<?= $youtube ?>" target="_blank" rel="noopener"><img border="0" width="26" src="<?= site_url('assets/images/email-signature-images/template-1/') ?>yt.png" alt="youtube icon" style="border:0; height:26px; width:26px"></a> </span>
                <?php
                }
                ?>
                <?php
                if (isset($linkedin) && !empty($linkedin)) {
                ?>
                    <span><a href="<?= $linkedin ?>" target="_blank" rel="noopener"><img border="0" width="26" src="<?= site_url('assets/images/email-signature-images/template-1/') ?>ln.png" alt="linkedin icon" style="border:0; height:26px; width:26px"></a> </span>
                <?php
                }
                ?>
                <?php
                if (isset($instagram) && !empty($instagram)) {
                ?>
                    <span><a href="<?= $instagram ?>" target="_blank" rel="noopener"><img border="0" width="26" src="<?= site_url('assets/images/email-signature-images/template-1/') ?>it.png" alt="instagram icon" style="border:0; height:26px; width:26px"></a> </span>
                <?php
                }
                ?>
                <?php
                if (isset($pinterest) && !empty($pinterest)) {
                ?>
                    <span>
                        <a href="<?= $pinterest ?>" target="_blank" rel="noopener"><img border="0" width="26" src="<?= site_url('assets/images/email-signature-images/template-1/') ?>pt.png" alt="pinterest icon" style="border:0; height:26px; width:26px"></a> </span>
                <?php
                }
                ?>
            </td>
        </tr>
        <?php
        if (isset($disclaimer) && !empty($disclaimer)) {
        ?>
            <tr>
                <td colspan="2" style="text-align: justify; FONT-SIZE: 7pt; color: #1e425a; line-height: 10px; padding-top: 15px;">
                    <?= $disclaimer ?>
                </td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>