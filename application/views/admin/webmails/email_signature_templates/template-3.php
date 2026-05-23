<table style="width: 500px; font-size: 12px; font-family: Arial,sans-serif; line-height:normal; background: transparent !important;" cellpadding="0" cellspacing="0">
    <tbody>
        <tr>
            <td style="width:92px; vertical-align:top;" valign="top">
                <a href="<?= (isset($website) && !empty($website)) ? $website : '#'  ?>" target="_blank">
                    <img border="0" alt="Logo" height="120" width="100" style="width:100px; height:91px; border:0;" src="<?= site_url('uploads/company/') ?>logo-2.png"></a>

            </td>
            <td style="width:44px; ; text-align:center; vertical-align:top;" valign="top">
                <img border="0" alt="Line" width="11" style="width:11px; height:85px; border:0;" src="<?= site_url('assets/images/email-signature-images/template-3/') ?>line.png">
            </td>
            <td style="width:364px; vertical-align:top;" valign="top">
                <span style="font-size:13px;  font-family: Arial, sans-serif; color:#0c542b; line-height: 20px; font-weight: bold; ">
                    <?php
                    if (isset($firstName) && !empty($firstName)) {
                        echo $firstName;
                    }
                    ?>
                    <?php
                    if (isset($lastName)) {
                        echo $lastName;
                    }
                    ?><br></span>

                <?php
                if (isset($title) && !empty($title)) {
                ?>
                    <span style="font-size:13px; font-family: Arial,sans-serif; line-height: 25px;font-weight: bold; color:#0c542b;"><span style="font-family: Arial, sans-serif; color:#0c542b; line-height: 15px;  "><?= $title ?><br></span></span>
                <?php
                }
                ?>
                <?php
                if ((isset($mobile) && !empty($mobile)) || (isset($phone) && !empty($phone))) {
                ?>
                    <span style="font-size:12px; font-family: Arial, sans-serif; color:#3c3c3b; padding-bottom:1px;">
                        <?php
                        if (isset($phone) && !empty($phone)) {
                        ?>
                            <span style="font-family: Arial, sans-serif; color:#3c3c3b; "><span style="font-weight:bold;">T: </span><?= $phone ?></span>
                            <?= (isset($mobile) && empty($mobile)) ? '<br>' : '' ?>
                        <?php
                        }
                        ?>
                        <?php
                        if (isset($mobile) && !empty($mobile)) {
                        ?>
                            <?= (isset($phone) && !empty($phone)) ? '<span style="font-family: Arial, sans-serif; color:#3c3c3b"> | </span>' : '' ?>
                            <span style="font-family: Arial, sans-serif; color:#3c3c3b"><span style="font-weight:bold;">M: </span><?= $mobile ?></span><br>
                        <?php
                        }
                        ?>
                    </span>
                <?php
                }
                ?>
                <?php
                if ((isset($email) && !empty($email)) || (isset($website) && !empty($website))) {
                ?>
                    <span style="font-size:12px; font-family: Arial, sans-serif; color:#3c3c3b; padding-bottom:1px;">
                        <?php
                        if (isset($email) && !empty($email)) {
                        ?>
                            <span style="font-family: Arial, sans-serif; color:#3c3c3b; ">
                                <span style="font-weight:bold;">E: </span>
                                <a href="mailto:<?= $email ?>" style="font-family: Arial, sans-serif; color:#3c3c3b; text-decoration: none;"><span style="font-family: Arial, sans-serif; color:#3c3c3b; text-decoration: none;"><?= $email ?></span>
                                </a>
                            </span>
                            <?= (isset($website) && empty($website)) ? '<br>' : '' ?>
                        <?php
                        }
                        ?>
                        <?php
                        if (isset($website) && !empty($website)) {
                        ?>
                            <span> <?= (isset($email) && !empty($email)) ? ' | ' : '' ?>
                                <a href="<?= $website ?>" target="_blank" style="text-decoration: none;">
                                    <span style="font-family: Arial, sans-serif; color:#3c3c3b"><?= $website ?></span>
                                </a>
                            </span>
                            <br>
                        <?php
                        }
                        ?>
                    </span>
                <?php
                }
                ?>
                <?php
                if ((isset($address1) && !empty($address1)) || (isset($address2) && !empty($address2))) {
                ?>
                    <span style="font-size:12px; font-family: Arial, sans-serif; color:#3c3c3b; padding-bottom:1px;">
                        <?php
                        if (isset($address1) && !empty($address1)) {
                        ?>
                            <span style="font-family: Arial, sans-serif; color:#3c3c3b; "><?= $address1 ?></span>
                            <?php
                            if (isset($address2) && !empty($address2)) {
                            ?>
                                <span style="font-family: Arial, sans-serif; color:#3c3c3b"> | </span>
                                <span style="font-family: Arial, sans-serif; color:#3c3c3b"><?= $address2 ?></span><br><br>
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
                <span style="padding-top:10px; vertical-align: bottom;">
                    <span style="display:inline-block; height:22px;">
                        <?php
                        if (isset($facebook) && !empty($facebook)) {
                        ?>
                            <span>
                                <a href="<?= $facebook ?>" target="_blank">
                                    <img alt="Facebook icon" border="0" width="20" height="20" style="border:0; height:20px; width:20px" src="<?= site_url('assets/images/email-signature-images/template-3/') ?>fb.png"></a>   </span>
                        <?php
                        }
                        ?>
                        <?php
                        if (isset($linkedin) && !empty($linkedin)) {
                        ?>
                            <span>
                                <a href="<?= $linkedin ?>" target="_blank">
                                    <img alt="LinkedIn icon" border="0" width="20" height="20" style="border:0; height:20px; width:20px" src="<?= site_url('assets/images/email-signature-images/template-3/') ?>ln.png"></a>   </span>
                        <?php
                        }
                        ?>
                        <?php
                        if (isset($twitter) && !empty($twitter)) {
                        ?>
                            <span><a href="<?= $twitter ?>" target="_blank">
                                    <img alt="Twitter icon" border="0" width="20" height="20" style="border:0; height:20px; width:20px" src="<?= site_url('assets/images/email-signature-images/template-3/') ?>tt.png"></a>   </span>
                        <?php
                        }
                        ?>
                        <?php
                        if (isset($youtube) && !empty($youtube)) {
                        ?>
                            <span>
                                <a href="<?= $youtube ?>" target="_blank">
                                    <img alt="Youtube icon" border="0" width="20" height="20" style="border:0; height:20px; width:20px" src="<?= site_url('assets/images/email-signature-images/template-3/') ?>yt.png"></a>   </span>
                        <?php
                        }
                        ?>
                        <?php
                        if (isset($instagram) && !empty($instagram)) {
                        ?>
                            <span>
                                <a href="<?= $instagram ?>" target="_blank">
                                    <img alt="Instagram icon" border="0" width="20" height="20" style="border:0; height:20px; width:20px" src="<?= site_url('assets/images/email-signature-images/template-3/') ?>it.png"></a>   </span>
                        <?php
                        }
                        ?>
                        <?php
                        if (isset($pinterest) && !empty($pinterest)) {
                        ?>
                            <span>
                                <a href="<?= $pinterest ?>" target="_blank"><img alt="Pinterest icon" border="0" width="20" height="20" style="border:0; height:20px; width:20px" src="<?= site_url('assets/images/email-signature-images/template-3/') ?>pt.png">
                                </a>
                            </span>
                        <?php
                        }
                        ?>
                    </span>
                </span>
            </td>
        </tr>
        <tr>
            <?php
            if (isset($disclaimer) && !empty($disclaimer)) {
            ?>
                <td colspan="3" style="text-align: justify; FONT-SIZE: 7pt; color: #1e425a; line-height: 10px; padding-top: 15px;">
                    <?= $disclaimer ?>
                </td>
            <?php
            }
            ?>
        </tr>
    </tbody>
</table>