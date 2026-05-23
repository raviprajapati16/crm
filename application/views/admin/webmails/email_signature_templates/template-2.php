<table cellspacing="0" cellpadding="0" border="0" style="FONT-FAMILY: Arial, sans-serif; COLOR: #0c542b;width:550px;background: transparent !important;">
    <tbody>
        <tr>
            <td width="126" style="FONT-SIZE: 10pt; FONT-FAMILY: Arial, sans-serif; COLOR: #0c542b; line-height:12pt; padding-bottom:10px; padding-right:10px; text-align:center; width:126px" valign="top" align="center">
                <p><a href="<?= (isset($website) && !empty($website)) ? $website : '#'  ?>" target="_blank"><img border="0" alt="Logo" width="120" style="width:120px; height:auto; border:0;" src="<?= site_url('uploads/company/') ?>logo-2.png"></a></p>

            </td>
            <td valign="top" style="FONT-FAMILY: Arial, sans-serif;">
                <p style="border-bottom:1px solid #0c542b;">
                    <span style="FONT-SIZE: 16px; COLOR: #444444; FONT-FAMILY: Arial, sans-serif;"><strong>
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

                    <?php
                    if (isset($title) && !empty($title)) {
                    ?>
                        <span style="letter-spacing:-1px; FONT-SIZE: 15px; COLOR: #444444;"> | <?= $title ?></span>
                    <?php
                    }
                    ?>
                </p>
                <?php
                if (isset($company) && !empty($company)) {
                ?>
                    <span style="FONT-SIZE: 12px; COLOR: #444444;"><strong> <?= $company ?></strong><br></span>
                <?php
                }
                ?>

                <?php
                if ((isset($mobile) && !empty($mobile)) || (isset($phone) && !empty($phone))) {
                ?>
                    <span style="white-space: nowrap;">
                        <?php
                        if (isset($mobile) && !empty($mobile)) {
                        ?>
                            <span style="FONT-SIZE: 12px; color:#444444;"><img src="<?= site_url('assets/images/email-signature-images/template-2/') ?>mobile-icon.png" alt="phone-icon" border="0" height="13" width="13" style="border:0; height:13px; width:13px">  <?= $mobile ?></span>
                        <?php
                        }
                        ?>
                        <?php
                        if (isset($phone) && !empty($phone)) {
                        ?>
                            <span style="FONT-SIZE: 12px; color:#444444;"><img src="<?= site_url('assets/images/email-signature-images/template-2/') ?>phone-icon.png" alt="phone-icon" border="0" height="15" width="14" style="border:0; height:15px; width:14px">  <?= $phone ?></span>
                        <?php
                        }
                        ?>
                    </span>
                    <br>
                <?php
                }
                ?>
                <?php
                if ((isset($email) && !empty($email)) || (isset($website) && !empty($website))) {
                ?>
                    <span style="white-space: nowrap;">
                        <?php
                        if (isset($email) && !empty($email)) {
                        ?>
                            <span style="FONT-SIZE: 12px;color:#444444;"><img src="<?= site_url('assets/images/email-signature-images/template-2/') ?>email-icon.png" alt="phone-icon" border="0" height="13" width="13" style="border:0; height:13px; width:13px">  <a href="mailto:{email}" target="_blank" rel="noopener" style=" text-decoration:none;"><span style="color:#444444; font-family:Arial, sans-serif; font-size:12px"><?= $email ?></span></a></span>

                        <?php
                        }
                        ?>
                        <?php
                        if (isset($website) && !empty($website)) {
                        ?>
                            <span><span style="display: inline-block"><img src="<?= site_url('assets/images/email-signature-images/template-2/') ?>website-icon.png" alt="website-icon" border="0" height="13" width="14" style="border:0; height:13px; width:14px">  <a href="{website}" target="_blank" rel="noopener" style=" text-decoration:none;"><span style="color:#444444; font-family:Arial, sans-serif; font-size:12px"><?= $website  ?></span></a></span>
                            </span>
                        <?php
                        }
                        ?>
                    </span>
                    <br>
                <?php
                }
                ?>

                <?php
                if ((isset($address1) && !empty($address1)) || (isset($address2) && !empty($address2))) {
                ?>
                    <span style="FONT-SIZE: 12px; color:#444444;"><img src="<?= site_url('assets/images/email-signature-images/template-2/') ?>address-icon.png" alt="phone-icon" border="0" width="15" height="13" style="border:0;width:15px;height:13px; ">
                        <?php
                        if (isset($address1) && !empty($address1)) {
                        ?>
                            <?= $address1; ?>
                            <?php
                            if (isset($address2)) {
                                echo ", " . $address2;
                            }
                            ?>
                        <?php
                        }
                        ?>
                        <br>
                    </span>
                <?php
                }
                ?>
            </td>
        </tr>
        <?php
        if ((isset($disclaimer) && !empty($disclaimer))) {
        ?>
            <tr>
                <td colspan="2" style="text-align: justify; FONT-SIZE: 7pt; color: #1e425a; line-height: 10px;">
                    <?= $disclaimer; ?>
                </td>
            </tr>
        <?php
        }
        ?>
    </tbody>
</table>