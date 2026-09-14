<?php
// ============================================================
// assets/idcardtemplates/front/shared_front.php
//
// ONE front template for every college. Layout is identical
// across colleges, only the theme color differs (yellow, blue,
// green, etc, driven by colleges.primary_color). A new college
// is a data row, not a new file.
//
// Expects in scope: $student (array), $college (array), $photoPath (string)
// ============================================================
?>
<div class="card-front">
    <div class="card-inner">
        <div>
            <?php include TEMPLATES_PATH . '/partials/header_logo.php'; ?>
        </div>

        <div class="content-area">
            <?php include TEMPLATES_PATH . '/partials/middle.php'; ?>
        </div>
        <div class="footer-wrap">
            <?php include TEMPLATES_PATH . '/partials/footer.php'; ?>
        </div>
    </div>

</div>
<style>
    .card-front {
        width: <?= CARD_WIDTH_MM ?>mm;
        height: <?= CARD_HEIGHT_MM ?>mm;
        box-sizing: border-box;
        overflow: hidden;
        margin: 0;
        padding: 0;
    }

    .card-inner {
        width: 100%;
        height: 100%;
        display: block;
        margin: 0;
        padding: 0;
    }

    .content-area {
        width: 100%;
        height: <?= MIDDLE_HEIGHT_MM ?>mm;
        margin: 0;
        padding: 0;
    }

    .footer-wrap {
       height: <?= CARD_HEIGHT_MM - HEADER_HEIGHT_MM - MIDDLE_HEIGHT_MM ?>mm;
        width: 100%;
        margin: 0;
        padding: 0;
}
</style>
