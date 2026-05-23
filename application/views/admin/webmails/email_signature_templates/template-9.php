<table style="font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif; color: #333333; font-size: 12px; line-height: 1.4;" cellpadding="0" cellspacing="0">
    <tr>
        <td style="padding-bottom: 10px;">
            <strong style="font-size: 14px; color: #000000; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;"><?php
                                                                                                                                                        if (isset($firstName) && !empty($firstName)) {
                                                                                                                                                            echo $firstName;
                                                                                                                                                        }
                                                                                                                                                        ?>
                <?php
                if (isset($lastName)) {
                    echo $lastName;
                }
                ?></strong><br>
            <span style="font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;"><?php
                                                                                                                    if (isset($title) && !empty($title)) {
                                                                                                                        echo $title;
                                                                                                                    }
                                                                                                                    ?></span>
            <br> <br>
            <a href="<?= (isset($website) && !empty($website)) ? $website : '#'  ?>" target="_blank"><img alt="Logo" style="width:159px; height:auto; border:0;" src="<?= site_url('uploads/company/') ?>logo.png" width="159" border="0"></a>
        </td>
    </tr>
    <tr>
        <td style="padding-bottom: 10px; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;">
            <?php
            if (isset($mobile) && !empty($mobile)) {
            ?><strong><img data-emoji="📞" class="an1" alt="📞" aria-label="📞" draggable="false" src="https://fonts.gstatic.com/s/e/notoemoji/15.1/1f4de/72.png" loading="lazy" style="height: 1.2em;width: 1.2em;vertical-align: middle;"> Mobile: </strong><a href="tel:<?= $mobile ?>" style="color: #0066cc; text-decoration: none; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;"><?= $mobile ?></a>
            <?php
            }
            ?>
            <?php
            if (isset($phone) && !empty($phone)) {
            ?>
                <br><strong><img data-emoji="📞" class="an1" alt="📞" aria-label="📞" draggable="false" src="https://fonts.gstatic.com/s/e/notoemoji/15.1/1f4de/72.png" loading="lazy" style="height: 1.2em;width: 1.2em;vertical-align: middle;"> Phone: </strong>
                <a href="tel:<?= $phone ?>" style="color: #0066cc; text-decoration: none; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;"><?= $phone ?></a>
            <?php
            }
            ?>
            <?php
            if (isset($email) && !empty($email)) {
            ?>
                <br><strong><img data-emoji="📧" class="an1" alt="📧" aria-label="📧" draggable="false" src="https://fonts.gstatic.com/s/e/notoemoji/15.1/1f4e7/72.png" loading="lazy" style="height: 1.2em;width: 1.2em;vertical-align: middle;"> Email: </strong>
                <a href="mailto:<?= $email ?>" style="color: #0066cc; text-decoration: none; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;"><?= $email ?></a>
            <?php
            }
            ?>
            <br><strong><img data-emoji="🌐" class="an1" alt="🌐" aria-label="🌐" draggable="false" src="https://fonts.gstatic.com/s/e/notoemoji/15.1/1f310/72.png" loading="lazy" style="height: 1.2em;width: 1.2em;vertical-align: middle;"> Website: </strong><a href="<?= (isset($website) && !empty($website)) ? $website : '#'  ?>" style="color: #0066cc; text-decoration: none; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;">
                <?= (isset($website) && !empty($website)) ? $website : ''  ?></a>
        </td>
    </tr>
    <?php
    if ((isset($company) && !empty($company)) || (isset($address1) && !empty($address1))) {
    ?>
        <tr>
            <td style="padding-bottom: 10px; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;">
                <strong style="font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;">
                    <?php
                    if (isset($company) && !empty($company)) {
                    ?>
                        <?= $company ?>
                    <?php
                    }
                    ?>
                </strong><br>

                <?php
                if (isset($address1) && !empty($address1)) {
                ?>
                    <span><?= $address1 ?>, </span>
                    <?php
                    if (isset($address2) && !empty($address2)) {
                    ?>
                        <br><span><?= $address2 ?>, </span>
                    <?php
                    }
                    ?>
                <?php
                }
                ?>
            </td>
        </tr>
    <?php
    }
    ?>

    <tr>
        <td style="padding-bottom: 10px; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;">
            <strong>Follow Us:</strong>
            <?php
            if (isset($linkedin) && !empty($linkedin)) {
            ?>
                <a href="<?= $linkedin ?>" style="color: #0066cc; text-decoration: none; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;">LinkedIn</a>
            <?php
            }
            ?>

            <?php
            if (isset($facebook) && !empty($facebook)) {
            ?>
                |
                <a href="<?= $facebook ?>" style="color: #0066cc; text-decoration: none; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;">Facebook</a>
            <?php
            }
            ?>

            <?php
            if (isset($instagram) && !empty($instagram)) {
            ?>
                |
                <a href="<?= $instagram ?>" style="color: #0066cc; text-decoration: none; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;">Instagram</a>
            <?php
            }
            ?>

            <?php
            if (isset($twitter) && !empty($twitter)) {
            ?>
                |
                <a href="<?= $twitter ?>" style="color: #0066cc; text-decoration: none; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;">Twitter</a>
            <?php
            }
            ?>

            <?php
            if (isset($youtube) && !empty($youtube)) {
            ?>
                |
                <a href="<?= $youtube ?>" style="color: #0066cc; text-decoration: none; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;">Youtube</a>
            <?php
            }
            ?>

            <?php
            if (isset($pinterest) && !empty($pinterest)) {
            ?>
                |
                <a href="<?= $pinterest ?>" style="color: #0066cc; text-decoration: none; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;">Pinterest</a>
            <?php
            }
            ?>
        </td>
    </tr>
    <?php
    if (isset($disclaimer) && !empty($disclaimer)) {
    ?>
        <tr>
            <td colspan="2">
                <p style="text-decoration: none; font-family: Aptos,Aptos_EmbeddedFont,Aptos_MSFontService,Calibri,Helvetica,sans-serif;" align="justify">
                    <?= $disclaimer ?>
                </p>
            </td>
        </tr>
    <?php
    }
    ?>
</table>