<?php
// ============================================================
// assets/idcardtemplates/partials/header_logo.php
//
// Shared header block: crest + university name. Included by every front template
//
// ============================================================
?>
<div class="card-header">

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img
                    src="<?= htmlspecialchars(mpdfAssetUri(__DIR__ . '/../../images/university-logo-mpdf.jpg')) ?>"
                    class="crest"
                    alt="University Logo"
                >
            </td>

            <td class="name-cell">
                <div class="uni-name">
                    Covenant<br>University
                </div>
            </td>
        </tr>
    </table>

</div>
<style>

.card-header {
    width: 100%;
    background-color: #1a3fa0;
    color: #ffffff;
    padding: 2mm;
    box-sizing: border-box;
    flex-shrink: 0;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    line-height: 0;
}

.logo-cell {
    width: 15mm;
    vertical-align: middle;
    padding: 0;
}

.name-cell {
    vertical-align: middle;
    padding: 0;
}

.card-header .crest {
    width: 12mm;
    height: 12mm;
    object-fit: contain;
}

.card-header .uni-name {
    font-family: Arial, sans-serif;
    font-size: 16pt;
    font-weight: bold;
    line-height: 1.05;
    margin: 0;
    color: #ffffff;
}

</style>
