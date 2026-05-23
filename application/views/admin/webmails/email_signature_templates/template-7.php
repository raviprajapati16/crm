<table style="width: 400px; font-size: 8pt; FONT-FAMILY: Tahoma, sans-serif; background: transparent !important;" cellpadding="0" cellspacing="0" border="0">
    <tbody>
        <tr>
            <td style="width: 88px; vertical-align: top; padding-top: 5px;" valign="top">
                <a href="<?= (isset($website) && !empty($website)) ? $website : '#'  ?>" target="_blank"><img border="0" alt="Logo" width="100" style="width:100px; height:auto; border:0;" src="<?= site_url('uploads/company/') ?>logo-2.png"></a>

            </td>
            <td style="width: 30px;"></td>
            <td style="width: 282px;">
                <table style="width: 282px; font-size: 8pt; FONT-FAMILY: Tahoma, sans-serif; background: transparent !important;" cellpadding="0" cellspacing="0" border="0">
                    <tbody>
                        <tr>
                            <td colspan="3">
                                <span style="font-size: 13pt; font-family: Tahoma, sans-serif; color:#0c542b; font-weight: bold;">
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
                                    <br>
                                </span>
                                <span style="font-family: Tahoma, sans-serif; font-size: 8pt; line-height: 16px; color:#262626;">
                                    <?php
                                    if (isset($title) && !empty($title)) {
                                        echo $title;
                                    }
                                    ?>
                                    <br>
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <?php
                            if (isset($email) && !empty($email)) {
                            ?>
                                <td style="width: 150px; vertical-align: top; padding-top: 10px;" valign="top">
                                    <span style="FONT-FAMILY: Tahoma, sans-serif; font-size: 8pt; line-height: 16px;">
                                        <span style="font-size: 8pt;  line-height: 16px; color: #262626;"><strong>Email:</strong><br></span>
                                        <a href="mailto:<?= $email ?>" style="font-size: 8pt; color:#262626; text-decoration: none;"><span style="text-decoration: none; font-size: 8pt;  line-height: 12px; color:#262626; FONT-FAMILY: Tahoma, sans-serif;"><?= $email ?></span></a>
                                    </span>
                                </td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($mobile) && !empty($mobile)) {
                            ?>
                                <?php (isset($email) && !empty($email)) ? '<td style="width: 15px;"></td>' : '' ?>
                                <td style="width: 117px; vertical-align: top;  padding-top: 10px;" valign="top">
                                    <span>
                                        <span style="font-size: 8pt; color:#262626;line-height: 16px; font-weight: bold">Mobile:<br></span>
                                        <span style="font-size: 8pt; color:#262626;line-height: 16px;"> <?= $mobile ?></span>
                                    </span>
                                </td>
                            <?php
                            }
                            ?>
                        </tr>
                        <tr>

                            <?php
                            if ((isset($address1) && !empty($address1)) || (isset($address2) && !empty($address2))) {
                            ?>
                                <td style="vertical-align: top; padding-top: 10px;" valign="top">
                                    <span>
                                        <?php
                                        if (isset($address1) && !empty($address1)) {
                                        ?>
                                            <span style="line-height: 16px; font-weight: bold">Address:<br></span>
                                            <span style="line-height: 12px;">
                                                <span style="font-size: 8pt; line-height: 12px;font-family: Tahoma, sans-serif; color: #262626;"><?= $address1 ?><span style="font-size: 8pt; line-height: 12px;"><br></span></span>
                                                <?php
                                                if (isset($address2) && !empty($address2)) {
                                                ?>
                                                    <span style="font-size: 8pt; line-height: 12px;font-family: Tahoma, sans-serif; color: #262626;"><?= $address2 ?></span>
                                                <?php
                                                }
                                                ?>

                                            <?php
                                        }
                                            ?>
                                            </span>
                                    </span>
                                </td>
                            <?php
                            }
                            ?>
                            <?php
                            if (isset($phone) && !empty($phone)) {
                            ?>
                                <?php (isset($address1) && !empty($address1)) ? '<td style="width: 15px;"></td>' : '' ?>
                                <td style="vertical-align: top; padding-top: 10px;" valign="top">
                                    <span>
                                        <strong>Telephone:</strong><br>
                                        <span style="font-size: 8pt; color:#262626;"> <?= $phone ?></span>
                                    </span>
                                </td>
                            <?php
                            }
                            ?>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </tbody>
</table>
<table style="width: 400px; font-size: 8pt; FONT-FAMILY: Tahoma, sans-serif; background: transparent !important;" cellpadding="0" cellspacing="0" border="0">
    <tbody>
        <tr>
            <?php
            if (isset($website) && !empty($website)) {
            ?>
                <td style=" padding-top: 15px;">
                    <span style="font-size: 8pt;  line-height: 12px; color:#0c542b; FONT-FAMILY: Tahoma, sans-serif;"> <a href="<?= $website ?>" target="_blank" style="color:#262626; text-decoration: none; "><span style="text-decoration: none; font-size: 8pt;  line-height: 12px; color:#0c542b; FONT-FAMILY: Tahoma, sans-serif; font-weight: bold;"><?= $website ?></span></a></span>
                </td>
            <?php
            }
            ?>
            <td style="text-align: right; padding-top: 15px;">
                <?php
                if (isset($facebook) && !empty($facebook)) {
                ?>
                    <span>   <a href="<?= $facebook ?>" target="_blank" rel="noopener"><img border="0" width="13" src="<?= site_url('assets/images/email-signature-images/template-7/') ?>fb.png" alt="facebook icon" style="border:0; height:13px; width:13px"></a></span>
                <?php
                }
                ?>
                <?php
                if (isset($twitter) && !empty($twitter)) {
                ?>
                    <span>   <a href="<?= $twitter ?>" target="_blank" rel="noopener"><img border="0" width="13" src="<?= site_url('assets/images/email-signature-images/template-7/') ?>tt.png" alt="twitter icon" style="border:0; height:13px; width:13px"></a></span>
                <?php
                }
                ?>
                <?php
                if (isset($youtube) && !empty($youtube)) {
                ?>
                    <span>   <a href="<?= $youtube ?>" target="_blank" rel="noopener"><img border="0" width="13" src="<?= site_url('assets/images/email-signature-images/template-7/') ?>yt.png" alt="youtube icon" style="border:0; height:13px; width:13px"></a></span>
                <?php
                }
                ?>
                <?php
                if (isset($linkedin) && !empty($linkedin)) {
                ?>
                    <span>   <a href="<?= $linkedin ?>" target="_blank" rel="noopener"><img border="0" width="13" src="<?= site_url('assets/images/email-signature-images/template-7/') ?>ln.png" alt="linkedin icon" style="border:0; height:13px; width:13px"></a></span>
                <?php
                }
                ?>
                <?php
                if (isset($instagram) && !empty($instagram)) {
                ?>
                    <span>   <a href="<?= $instagram ?>" target="_blank" rel="noopener"><img border="0" width="13" src="<?= site_url('assets/images/email-signature-images/template-7/') ?>it.png" alt="instagram icon" style="border:0; height:13px; width:13px"></a></span>
                <?php
                }
                ?>
                <?php
                if (isset($pinterest) && !empty($pinterest)) {
                ?>
                    <span>   <a href="<?= $pinterest ?>" target="_blank" rel="noopener"><img border="0" width="13" src="<?= site_url('assets/images/email-signature-images/template-7/') ?>pt.png" alt="pinterest icon" style="border:0; height:13px; width:13px"></a></span>
                <?php
                }
                ?>
            </td>
        </tr>
        <?php
        if (isset($disclaimer) && !empty($disclaimer)) {
        ?>
            <tr>
                <td colspan="2" style="padding-top: 14px; text-align: justify; FONT-SIZE: 7pt; line-height: 10px;  max-width: 400px;">
                    <?= $disclaimer ?>
                </td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>