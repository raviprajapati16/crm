<table style="width: 420px; font-size: 10pt; font-family: Arial, sans-serif; background: transparent !important;" cellpadding="0" cellspacing="0" border="0">
    <tbody>
        <tr>
            <td style="font-size: 12pt; font-family: Arial, sans-serif; width:200px; padding-right: 10px; vertical-align: bottom;  padding-bottom: 10px;" valign="bottom">
                <p style="margin-bottom:10px; padding-bottom: 0px; line-height:1.0">
                    <strong><span style="font-size: 12pt; font-family: Arial, sans-serif; color:#0c542b; line-height: 18pt;">
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
                        </span></strong>

                    <?php
                    if (isset($title) && !empty($title)) {
                    ?>
                        <span style="font-family: Arial, sans-serif; font-size:9pt; color:#010100;  line-height: 14pt;"><br><?= $title ?></span>
                    <?php
                    }
                    ?>
                </p>
                <span>
                    <a href="<?= (isset($website) && !empty($website)) ? $website : '#'  ?>" target="_blank"><img border="0" alt="Logo" width="100" style="width: 100px;     height:auto; border:0;" src="<?= site_url('uploads/company/') ?>logo-2.png"></a>
                </span>
            </td>



            <td valign="top" style="vertical-align: top; padding-left: 30px; padding-bottom: 6px;">

                <?php
                if (isset($email) && !empty($email)) {
                ?>
                    <span>
                        <span style="color: #010100;"><strong>E:</strong></span> <a href="mailto:<?= $email ?>" style="text-decoration: none; font-size: 9pt; font-family: Arial, sans-serif; color:#010100;"><span style="text-decoration: none; font-size: 9pt; font-family: Arial, sans-serif; color:#010100;"><?= $email ?></span></a><br>
                    </span>
                <?php
                }
                ?>

                <?php
                if (isset($mobile) && !empty($mobile)) {
                ?>
                    <span>
                        <span style="color: #010100;"><strong>M:</strong></span><span style="font-size: 9pt; font-family: Arial, sans-serif; color:#010100;"> <?= $mobile ?><br></span>
                    </span>
                <?php
                }
                ?>

                <?php
                if (isset($phone) && !empty($phone)) {
                ?>
                    <span>
                        <span style="color: #010100;"><strong>P:</strong></span><span style="font-size: 9pt; font-family: Arial, sans-serif; color:#010100;"> <?= $phone ?><br></span>
                    </span>
                <?php
                }
                ?>

                <?php
                if ((isset($company) && !empty($company)) || (isset($address1) && !empty($address1))) {
                ?>
                    <span style="color:#010100;"><strong>A:</strong></span>
                    <?php
                    if (isset($company) && !empty($company)) {
                    ?>
                        <span style="font-family: Arial, sans-serif; font-size:9pt; color:#010100;"><?= $company ?><span>,</span></span>
                    <?php
                    }
                    ?>
                    <span>
                        <?php
                        if (isset($address1) && !empty($address1)) {
                        ?>
                            <span style="font-size: 9pt; font-family: Arial, sans-serif; color: #010100;"><?= $address1 ?><span>, </span></span>
                            <?php
                            if (isset($address2) && !empty($address2)) {
                            ?>
                                <span style="font-size: 9pt; font-family: Arial, sans-serif; color: #010100;"><?= $address2 ?></span>
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
        <tr>
            <?php
            if (isset($website) && !empty($website)) {
            ?>
                <td style="vertical-align: top;border-top: 1px solid #0c542b; padding-top: 15px;" valign="top">
                    <a href="<?= $website ?>" target="_blank" rel="noopener" style="font-size: 9pt; font-family: Arial, sans-serif; text-decoration:none; color: #0c542b; font-weight: bold;"><span style="font-size: 9pt; font-family: Arial, sans-serif; text-decoration:none; color: #0c542b; font-weight: bold;"><?= $website ?></span></a>
                </td>
            <?php
            }
            ?>

            <td valign="top" align="right" style="padding-left: 30px;border-top: 1px solid #0c542b; padding-top: 15px; text-align:right">
                <table cellpadding="0" cellspacing="0" border="0" style="float: right; background: transparent !important;">
                    <tbody>
                        <tr>
                            <?php
                            if (isset($facebook) && !empty($facebook)) {
                            ?>
                                <td style="padding-left:3px;" width="30"><a href="<?= $facebook ?>" target="_blank"><img border="0" width="20" height="22" src="<?= site_url('assets/images/email-signature-images/template-4/') ?>fb.png" alt="facebook icon" style="border:0; height:22px; width:20px;"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($twitter) && !empty($twitter)) {
                            ?>
                                <td style="padding-left:3px;" width="30"><a href="<?= $twitter ?>" target="_blank"><img border="0" width="20" height="22" src="<?= site_url('assets/images/email-signature-images/template-4/') ?>tt.png" alt="twitter icon" style="border:0; height:22px; width:20px;"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($youtube) && !empty($youtube)) {
                            ?>
                                <td style="padding-left:3px;" width="30"><a href="<?= $youtube ?>" target="_blank"><img border="0" width="20" height="22" src="<?= site_url('assets/images/email-signature-images/template-4/') ?>yt.png" alt="youtube icon" style="border:0; height:22px; width:20px;"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($linkedin) && !empty($linkedin)) {
                            ?>
                                <td style="padding-left:3px;" width="30"><a href="<?= $linkedin ?>" target="_blank"><img border="0" width="20" height="22" src="<?= site_url('assets/images/email-signature-images/template-4/') ?>ln.png" alt="linkedin icon" style="border:0; height:22px; width:20px;"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($instagram) && !empty($instagram)) {
                            ?>
                                <td style="padding-left:3px;" width="30"><a href="<?= $instagram ?>" target="_blank"><img border="0" width="20" height="22" src="<?= site_url('assets/images/email-signature-images/template-4/') ?>it.png" alt="instagram icon" style="border:0; height:22px; width:20px;"></a></td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($pinterest) && !empty($pinterest)) {
                            ?>
                                <td style="padding-left:3px;" width="30"><a href="<?= $pinterest ?>" target="_blank"><img border="0" width="20" height="22" src="<?= site_url('assets/images/email-signature-images/template-4/') ?>pt.png" alt="pinterest icon" style="border:0; height:22px; width:20px;"></a></td>
                            <?php
                            }
                            ?>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <?php
            if (isset($disclaimer) && !empty($disclaimer)) {
            ?>
                <td colspan="2" style="text-align: justify; FONT-SIZE: 7pt; color: #1e425a; line-height: 10px; padding-top: 15px;">
                    <?= $disclaimer ?>
                </td>
            <?php
            }
            ?>
        </tr>
    </tbody>
</table>