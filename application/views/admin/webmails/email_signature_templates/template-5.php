<table cellspacing="0" cellpadding="0" border="0" style="COLOR: #262626; font-family: Arial, sans-serif; width:420px; background: transparent !important;">
    <tbody>
        <tr>
            <td style="font-size: 10pt; font-family: Arial, sans-serif; WIDTH: 200px; COLOR: #262626;line-height:18px; border-right: solid 1px #0c542b;">
                <span style="font-size: 12pt; COLOR: #0c542b;font-family: Arial, sans-serif;"><strong>
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
                    </strong>
                </span>
                <span style="font-size: 9pt; COLOR: #717171; font-family: Arial, sans-serif;"><br>
                    <?php
                    if (isset($title) && !empty($title)) {
                        echo $title;
                    }
                    ?>
                </span>
                <table style="margin-top: 20px; background: transparent !important;">
                    <tbody>
                        <tr>
                            <?php
                            if (isset($facebook) && !empty($facebook)) {
                            ?>
                                <td style="width: 30px"><a href="<?= $facebook  ?>" target="_blank" rel="noopener"><img border="0" src="<?= site_url('assets/images/email-signature-images/template-5/') ?>fb.png" alt="facebook icon" width="26" height="26" style="border:0; width:26px; height:26px"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($twitter) && !empty($twitter)) {
                            ?>
                                <td style="width: 30px"><a href="<?= $twitter ?>" target="_blank" rel="noopener"><img border="0" src="<?= site_url('assets/images/email-signature-images/template-5/') ?>tt.png" alt="twitter icon" width="26" height="26" style="border:0; width:26px; height:26px"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($youtube) && !empty($youtube)) {
                            ?>
                                <td style="width: 30px"><a href="<?= $youtube ?>" target="_blank" rel="noopener"><img border="0" src="<?= site_url('assets/images/email-signature-images/template-5/') ?>yt.png" alt="youtube icon" width="26" height="26" style="border:0; width:26px; height:26px"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($linkedin) && !empty($linkedin)) {
                            ?>
                                <td style="width: 30px"><a href="<?= $linkedin ?>" target="_blank" rel="noopener"><img border="0" src="<?= site_url('assets/images/email-signature-images/template-5/') ?>ln.png" alt="linkedin icon" width="26" height="26" style="border:0; width:26px; height:26px"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($instagram) && !empty($instagram)) {
                            ?>
                                <td style="width: 30px"><a href="<?= $instagram ?>" target="_blank" rel="noopener"><img border="0" src="<?= site_url('assets/images/email-signature-images/template-5/') ?>it.png" alt="instagram icon" width="26" height="26" style="border:0; width:26px; height:26px"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($pinterest) && !empty($pinterest)) {
                            ?>
                                <td style="width: 30px"><a href="<?= $pinterest ?>" target="_blank" rel="noopener"><img border="0" src="<?= site_url('assets/images/email-signature-images/template-5/') ?>pt.png" alt="pinterest icon" width="26" height="26" style="border:0; width:26px; height:26px"></a></td>
                            <?php
                            }
                            ?>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td style="font-size: 9pt; width: 220px; line-height:18px; padding-left: 20px;">
                <?php
                if (isset($email) && !empty($email)) {
                ?>
                    <span style="font-size: 9pt; COLOR: #0c542b;font-family: Arial, sans-serif;"><strong>E:</strong> <a href="mailto:<?= $email ?>" style="COLOR: #262626; text-decoration: none;"><span style="font-family: Arial, sans-serif; text-decoration: none;"><?= $email ?></span></a><br></span>
                <?php
                }
                ?>
                <?php
                if (isset($mobile) && !empty($mobile)) {
                ?>
                    <span style="font-size: 9pt; COLOR: #0c542b;font-family: Arial, sans-serif;"><strong>M:</strong> <span style="COLOR: #262626;font-family: Arial, sans-serif; "><?= $mobile ?></span><br></span>
                <?php
                }
                ?>
                <?php
                if (isset($phone) && !empty($phone)) {
                ?>
                    <span style="font-size: 9pt; COLOR: #0c542b;font-family: Arial, sans-serif;"><strong>T:</strong> <span style="COLOR: #262626;font-family: Arial, sans-serif; "><?= $phone ?></span><br></span>
                <?php
                }
                ?>

                <?php
                if ((isset($company) && !empty($company)) || (isset($address1) && !empty($address1))) {
                ?>
                    <span style="font-size: 9pt; COLOR: #262626;font-family: Arial, sans-serif">
                        <span style="COLOR: #0c542b;"><strong>A: </strong></span>
                        <?php
                        if (isset($company) && !empty($company)) {
                        ?>
                            <span><?= $company ?><span>, </span></span>
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
                                <span> <?= $address2 ?></span>
                            <?php
                            }
                            ?>
                        <?php
                        }
                        ?>
                    </span>
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