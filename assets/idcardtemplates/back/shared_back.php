<?php
// ============================================================
// assets/idcardtemplates/back/shared_back.php
//
// One shared back template for ALL colleges. Deliberately pure black on white/transparent,
// no color values anywhere, so this side can be routed to a K-only ribbon panel rather than a full YMC color pass.

// ============================================================
?>

<div class="student">STUDENT</div>

<div class="card-back">
    <div class="line">This is to certify that the bearer</div>
    <div class="line">whose picture appears on this</div>
    <div class="line">card is a Student in</div>
    <div class="line">COVENANT UNIVERSITY.</div>
    <div class="line">No person(s) unless authorized</div>
    <div class="line">by the above instituional authority</div>
    <div class="line">may hold or possess the card.</div>
    <div class="line">Please if found kindly return </div>
    <div class="line">to the Registrar,</div>
    <div class="line">COVENANT UNIVERSITY</div>
    <div class="line">Canaan land, Km. 10 Idiroko</div>
    <div class="line">Rd,Ota ,Ogun State.</div>
    <div class="line">Tel: 7900724</div>
    <div class="signature-area">
        <img
            src="<?= htmlspecialchars(mpdfAssetUri(__DIR__ . '/../../images/registrar_signature-mpdf.jpg')) ?>"
            class="registrar-signature"
            alt="Registrar's Signature"
        >
        <div class="signature-label">Registrar's Signature</div>
        <img
            src="<?= htmlspecialchars(mpdfAssetUri(__DIR__ . '/../../images/registrar_barcode-mpdf.jpg')) ?>"
            class="registrar-barcode"
            alt="Barcode"
        >
    </div>
    
</div>
<style>
    .card-back {
        width: <?= CARD_WIDTH_MM ?>mm;
        height: <?= CARD_HEIGHT_MM ?>mm;
        font-family: Arial, sans-serif;
        font-size: 6.5pt;
        /* pure black only */
        color: #000000;
        background-color: #ffffff;
        padding: 4mm;
        box-sizing: border-box;
        text-align: center;
        font-weight: bold;
    }
    .card-back .line {
        margin-bottom: 0.8mm;
    }
    
    .student {
        font-size: 20px;
        padding-top: 10px;
        padding-bottom: 10px;
        text-align: center;
        font-weight: bold;
        font-family: Arial, sans-serif;
        background-color: #000000;
        color: #ffffff;
    }

    /* Signature section */
    .signature-area {
        margin-top: 1mm;
        text-align: center;
    }

    .registrar-signature {
        width: 22mm;
        height: 4mm;
        display: block;
        margin: 0 auto;
    }

    .signature-label {
    margin-top: 0.8mm;
    line-height: 1;
    }
</style>
