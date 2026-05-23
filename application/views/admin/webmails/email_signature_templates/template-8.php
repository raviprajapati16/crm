<table style="width: 420px; font-size: 10pt; font-family: Arial, sans-serif;" cellspacing="0" cellpadding="0" border="0">
    <tbody>
        <tr>
            <td style="font-size: 10pt; font-family: Arial, sans-serif; border-right: 1px solid #0c542b; width:200px; padding-right: 10px; vertical-align: top;  padding-bottom: 20px;" valign="top">
                <p style="margin-bottom:25px; padding-bottom: 0px; line-height:1.0">
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
                    <span style="font-family: Arial, sans-serif; font-size:9pt; color:#717171;  line-height: 14pt;"><br>
                        <?php
                        if (isset($title) && !empty($title)) {
                            echo $title;
                        }
                        ?>
                    </span>
                </p>
                <span>
                    <a href="<?= (isset($website) && !empty($website)) ? $website : '#'  ?>" target="_blank"><img alt="Logo" style="width:159px; height:auto; border:0;" src="<?= site_url('uploads/company/') ?>logo.png" width="159" border="0"></a>

                </span>
            </td>

            <td style="padding-left: 30px; padding-bottom: 20px;" valign="top">
                <?php
                if (isset($email) && !empty($email)) {
                ?>
                    <span><span style="color: #262626;"><strong>E:</strong></span> <a href="mailto:<?= $email ?>" style="text-decoration: none; font-size: 9pt; font-family: Arial, sans-serif; color:#262626;"><span style="text-decoration: none; font-size: 9pt; font-family: Arial, sans-serif; color:#262626;"><?= $email ?></span></a><br></span>
                <?php
                }
                ?>
                <?php
                if (isset($mobile) && !empty($mobile)) {
                ?>
                    <span><span style="color: #262626;"><strong>M:</strong></span><span style="font-size: 9pt; font-family: Arial, sans-serif; color:#262626;"> <?= $mobile ?><br></span></span>
                <?php
                }
                ?>
                <?php
                if (isset($phone) && !empty($phone)) {
                ?>
                    <span><span style="color: #262626;"><strong>P:</strong></span><span style="font-size: 9pt; font-family: Arial, sans-serif; color:#262626;"> <?= $phone ?><br></span></span>
                <?php
                }
                ?>
                <?php
                if ((isset($company) && !empty($company)) || (isset($address1) && !empty($address1))) {
                ?>
                    <span><strong>A:</strong></span>
                    <?php
                    if (isset($company) && !empty($company)) {
                    ?>
                        <span style="font-family: Arial, sans-serif; font-size:9pt; color:#262626;"><?= $company ?> ,</span>
                    <?php
                    }
                    ?>
                    <span>
                        <?php
                        if (isset($address1) && !empty($address1)) {
                        ?>
                            <span style="font-size: 10pt; font-family: Arial, sans-serif; color: #262626;"><?= $address1 ?><span>, </span></span>
                            <?php
                            if (isset($address2) && !empty($address2)) {
                            ?>
                                <span style="font-size: 10pt; font-family: Arial, sans-serif; color: #262626;"><?= $address2 ?></span>
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
            <td style="border-right: 1px solid #0c542b;vertical-align: top;" valign="top">
                <?php
                if (isset($website) && !empty($website)) {
                ?>
                    <a href="<?= $website ?>" target="_blank" rel="noopener" style="font-size: 9pt; font-family: Arial, sans-serif; text-decoration:none; color: #0c542b; font-weight: bold;"><span style="font-size: 9pt; font-family: Arial, sans-serif; text-decoration:none; color: #0c542b; font-weight: bold;"><?= $website ?></span></a>
                <?php
                }
                ?>
            </td>

            <td style="padding-left: 30px;" valign="top">
                <?php
                if (isset($facebook) && !empty($facebook)) {
                ?>
                    <span><a href="<?= $facebook ?>" target="_blank" rel="noopener"><img src="<?= site_url('assets/images/email-signature-images/template-8/') ?>fb.png" alt="facebook icon" style="border:0; height:26px; width:26px" width="26" border="0"></a> </span>
                <?php
                }
                ?>
                <?php
                if (isset($twitter) && !empty($twitter)) {
                ?>
                    <span><a href="<?= $twitter ?>" target="_blank" rel="noopener"><img src="<?= site_url('assets/images/email-signature-images/template-8/') ?>tt.png" alt="twitter icon" style="border:0; height:26px; width:26px" width="26" border="0"></a> </span>
                <?php
                }
                ?>
                <?php
                if (isset($youtube) && !empty($youtube)) {
                ?>
                    <span><a href="<?= $youtube ?>" target="_blank" rel="noopener"><img src="<?= site_url('assets/images/email-signature-images/template-8/') ?>yt.png" alt="youtube icon" style="border:0; height:26px; width:26px" width="26" border="0"></a> </span>
                <?php
                }
                ?>
                <?php
                if (isset($linkedin) && !empty($linkedin)) {
                ?>
                    <span><a href="<?= $linkedin ?>" target="_blank" rel="noopener"><img src="<?= site_url('assets/images/email-signature-images/template-8/') ?>ln.png" alt="linkedin icon" style="border:0; height:26px; width:26px" width="26" border="0"></a> </span>
                <?php
                }
                ?>
                <?php
                if (isset($instagram) && !empty($instagram)) {
                ?>
                    <span><a href="<?= $instagram ?>" target="_blank" rel="noopener"><img src="<?= site_url('assets/images/email-signature-images/template-8/') ?>it.png" alt="instagram icon" style="border:0; height:26px; width:26px" width="26" border="0"></a> </span>
                <?php
                }
                ?>
                <?php
                if (isset($pinterest) && !empty($pinterest)) {
                ?>
                    <span><a href="<?= $pinterest ?>" target="_blank" rel="noopener"><img src="<?= site_url('assets/images/email-signature-images/template-8/') ?>pt.png" alt="pinterest icon" style="border:0; height:26px; width:26px" width="26" border="0"></a></span>
                <?php
                }
                ?>
            </td>
        </tr>
        <?php
        if (isset($disclaimer) && !empty($disclaimer)) {
        ?>
            <tr>
                <td colspan="2" style="padding-top:20px;">
                    <p style="font-size:7pt; line-height:9pt; COLOR: #717171; FONT-FAMILY: Arial, sans-serif; text-align:justify;" align="justify">
                        <?= $disclaimer ?>
                    </p>
                </td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>