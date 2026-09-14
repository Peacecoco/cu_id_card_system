<?php
$buildingImage = mpdfAssetUri(__DIR__ . '/../../images/cu_building.jpg');
?>
<div class="container">
    <table class="middle-table" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <!-- LEFT: BUILDING IMAGE (CSS background-image, stretched via background-image-resize) -->
            <td 
                class="building-cell" 
                width="27mm" 
                height="<?= MIDDLE_HEIGHT_MM ?>mm" 
                style="vertical-align: top; padding: 0;">
            </td>

            <!-- RIGHT: STUDENT DETAILS -->
            <td class="details-cell" width="27mm" height="<?= MIDDLE_HEIGHT_MM ?>mm">
                <table class="details-table" cellpadding="0" cellspacing="0" border="0">
                    <tr><td class="item" height="5mm"><?= strtoupper(htmlspecialchars($student['full_name'])) ?></td></tr>
                    <tr><td class="space" height="1mm">&nbsp;</td></tr>
                    <tr><td class="item" height="5mm"><?= htmlspecialchars($college['code']) ?></td></tr>
                    <tr><td class="space" height="1mm">&nbsp;</td></tr>
                    <tr><td class="item" height="5mm"><?= htmlspecialchars($student['department']) ?></td></tr>
                    <?php if (empty($isTemporary)): ?>
                        <tr><td class="space" height="1mm">&nbsp;</td></tr>
                        <tr><td class="item" height="5mm"><?= htmlspecialchars($student['matric_no']) ?></td></tr>
                    <?php endif; ?>
                </table>
            </td>
        </tr>
    </table>
</div>

<style>
    .container {
        width: 54mm;
        height: <?= MIDDLE_HEIGHT_MM ?>mm;
        margin: 0;
        padding: 0;
        overflow: hidden;
        background-color: <?= htmlspecialchars($college['primary_color'] ?? '#ff0000') ?>;
    }

    /* MAIN TABLE */
    .middle-table {
        width: 54mm;
        height: <?= MIDDLE_HEIGHT_MM ?>mm;
        margin: 0;
        padding: 0;
        border-collapse: collapse;
        table-layout: fixed;
    }

    /* LEFT SIDE - IMAGE CELL */
    .building-cell {
        width: 27mm;
        height: <?= MIDDLE_HEIGHT_MM ?>mm;
        padding: 0;
        margin: 0;
        vertical-align: top;
        background-image: url('<?= $buildingImage ?>');
        background-repeat: no-repeat;
        background-image-resize: 6; /* stretch to fill cell, both width & height */
    }

    /* RIGHT SIDE */
    .details-cell {
        width: 27mm;
        height: <?= MIDDLE_HEIGHT_MM ?>mm;
        padding: 0;
        margin: 0;
        vertical-align: middle;
    }

    /* DETAILS TABLE */
    .details-table {
        width: 100%;
        margin: 0;
        padding: 0;
        border-collapse: collapse;
        table-layout: fixed;
    }

    /* TEXT */
    .item {
        width: 100%;
        padding: 0 1mm;
        text-align: center;
        vertical-align: middle;
        font-family: Arial, sans-serif;
        font-size: 6pt;
        line-height: 9pt;
        font-weight: bold;
        color: #ffffff;
    }

    /* SPACING */
    .space {
        padding: 0;
        margin: 0;
        font-size: 1px;
        line-height: 1px;
    }
</style>
