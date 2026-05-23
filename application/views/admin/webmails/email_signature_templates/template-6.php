<table cellspacing="0" cellpadding="0" border="0" style="COLOR: #808080; FONT-FAMILY: Arial, sans-serif; width: 440px; background: transparent !important;">
    <tbody>
        <tr>
            <td colspan="2">
                <span style="FONT-SIZE: 18pt; COLOR: #0c542b; line-height: 22pt; FONT-FAMILY: Arial, sans-serif;"><strong>
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
                    </strong></span>
            </td>
        </tr>
        <tr>
            <?php
            if (isset($title) && !empty($title)) {
            ?>
                <td style="FONT-SIZE: 12pt; FONT-FAMILY: Arial, sans-serif; WIDTH: 260px; COLOR: #808080;line-height:14pt;  padding-bottom: 15px;">
                    <span style="FONT-SIZE: 12pt; LINE-HEIGHT: 20px; COLOR: #808080; FONT-FAMILY: Arial, sans-serif;"><?= $title ?></span>
                </td>
            <?php
            }
            ?>

            <td style="FONT-SIZE: 10pt; FONT-FAMILY: Arial, sans-serif; WIDTH: 160px; COLOR: #808080;padding-bottom: 4px; vertical-align: bottom; text-align: right; padding-bottom: 15px;">
                <table style="float: right;  background: transparent !important;">
                    <tbody>
                        <tr>
                            <?php
                            if (isset($facebook) && !empty($facebook)) {
                            ?>
                                <td style="width: 30px;"><a href="<?= $facebook ?>" target="_blank" rel="noopener"><img border="0" src="<?= site_url('assets/images/email-signature-images/template-6/') ?>fb.png" alt="facebook icon" width="20" height="20" style="border:0; width:20px; height:20px"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($twitter) && !empty($twitter)) {
                            ?>
                                <td style="width: 30px;"><a href="<?= $twitter ?>" target="_blank" rel="noopener"><img border="0" src="<?= site_url('assets/images/email-signature-images/template-6/') ?>tt.png" alt="twitter icon" width="20" height="20" style="border:0; width:20px; height:20px"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($youtube) && !empty($youtube)) {
                            ?>
                                <td style="width: 30px;"><a href="<?= $youtube ?>" target="_blank" rel="noopener"><img border="0" src="<?= site_url('assets/images/email-signature-images/template-6/') ?>yt.png" alt="youtube icon" width="20" height="20" style="border:0; width:20px; height:20px"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($linkedin) && !empty($linkedin)) {
                            ?>
                                <td style="width: 30px;"><a href="<?= $linkedin ?>" target="_blank" rel="noopener"><img border="0" src="<?= site_url('assets/images/email-signature-images/template-6/') ?>ln.png" alt="linkedin icon" width="20" height="20" style="border:0; width:20px; height:20px"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($instagram) && !empty($instagram)) {
                            ?>
                                <td style="width: 30px;"><a href="<?= $instagram ?>" target="_blank" rel="noopener"><img border="0" src="<?= site_url('assets/images/email-signature-images/template-6/') ?>it.png" alt="instagram icon" width="20" height="20" style="border:0; width:20px; height:20px"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($pinterest) && !empty($pinterest)) {
                            ?>
                                <td style="width: 30px;"><a href="<?= $pinterest ?>" target="_blank" rel="noopener"><img border="0" src="<?= site_url('assets/images/email-signature-images/template-6/') ?>pt.png" alt="pinterest icon" width="20" height="20" style="border:0; width:20px; height:20px"></a></td>
                            <?php
                            }
                            ?>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding-top: 15px; border-top: solid 1px #0c542b; FONT-SIZE: 9pt; line-height: 14px;">

                <?php
                if ((isset($mobile) && !empty($mobile)) || (isset($phone) && !empty($phone))) {
                ?>
                    <?php
                    if (isset($mobile) && !empty($mobile)) {
                    ?>
                        <span style="FONT-SIZE: 9pt; COLOR: #808080; FONT-FAMILY: Arial, sans-serif;">
                            <span style="color: #000000;"><strong>Mobile:</strong></span> <?= $mobile ?>
                            <?= (isset($phone) && !empty($phone)) ? '<span> | </span>' : '' ?>
                        </span>
                    <?php
                    }
                    ?>
                    <?php
                    if (isset($phone) && !empty($phone)) {
                    ?>
                        <span style="FONT-SIZE: 9pt; COLOR: #808080;FONT-FAMILY: Arial, sans-serif;">
                            <span style="color: #000000;"><strong>Phone:</strong></span> <?= $phone ?>
                        </span>
                    <?php
                    }
                    ?>
                <?php
                }
                ?>
                <?php
                if (isset($email) && !empty($email)) {
                ?>
                    <span style="FONT-SIZE: 9pt; COLOR: #000000;"><br><span style="color: #000000;"><strong>Email:</strong></span> <a href="mailto:<?= $email ?>" style="FONT-SIZE: 9pt; COLOR: #808080; text-decoration: none;"><span style="FONT-SIZE: 9pt; COLOR: #808080; text-decoration: none;"><?= $email ?></span></a></span>
                <?php
                }
                ?>
                <span style="FONT-SIZE: 9pt; COLOR: #808080;"><br>
                    <?php
                    if ((isset($company) && !empty($company)) || (isset($address1) && !empty($address1))) {
                    ?>
                        <?php
                        if (isset($company) && !empty($company)) {
                        ?>
                            <span style="color: #000000;FONT-FAMILY: Arial, sans-serif"><strong><?= $company ?></strong><span>, </span></span>
                        <?php
                        }
                        ?>
                        <?php
                        if (isset($address1) && !empty($address1)) {
                        ?>
                            <span><?= $address1 ?><span>, </span></span>
                            <?php
                            if (isset($address2) && !empty($address2)) {
                            ?>
                                <span><?= $address2 ?></span>
                            <?php
                            }
                            ?>
                        <?php
                        }
                        ?>
                    <?php
                    }
                    ?>
                </span>
                <span>
                    <br><br>
                    <?php
                    if (isset($website) && !empty($website)) {
                    ?>
                        <a href="<?= $website ?>" target="_blank" rel="noopener" style=" text-decoration:none;FONT-FAMILY: Arial, sans-serif"><strong style="color:#0c542b; font-family:Arial, sans-serif; font-size:9pt"><?= $website ?></strong></a>
                    <?php
                    }
                    ?>
                </span>
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