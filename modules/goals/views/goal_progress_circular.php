
<?php
    if ($percentage == 100) {
        $progressColor = '#00FF00';
    } elseif ($percentage >= 90) {
        $progressColor = '#FFA500';
    } else {
        $progressColor = '#008ECE';
    }
    $circumference = 2 * pi() * 60;
    $dashArray = $circumference * $percentage / 100;
    $dashOffset = $circumference - $dashArray;
    ?>
    <table cellpadding="0" cellspacing="0" border="0" width="150">
        <tr>
            <td align="center">
                <div style="width: 150px; height: 150px; position: relative; display: inline-block;">
                    <svg width="150" height="150" viewBox="0 0 150 150" style="position: absolute; top: 0; left: 0;">
                        <circle cx="75" cy="75" r="60" fill="none" stroke="#E0E0E0" stroke-width="10"/>
                    </svg>

                    <!-- Progress circle -->
                    <svg width="150" height="150" viewBox="0 0 150 150" style="position: absolute; top: 0; left: 0; transform: rotate(-90deg);">
                        <circle cx="75" cy="75" r="60" fill="none" stroke="<?php echo $progressColor; ?>" stroke-width="10" stroke-dasharray="<?php echo $circumference; ?>" stroke-dashoffset="<?php echo $dashOffset; ?>" />
                    </svg>

                    <!-- Percentage text -->
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 24px; font-family: Arial, sans-serif;">
                        <?php echo $percentage; ?>%
                    </div>
                </div>
            </td>
        </tr>
    </table>