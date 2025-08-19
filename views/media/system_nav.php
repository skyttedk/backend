<div style="padding-bottom: 20px; text-align: right;">
    Navigation:

    <?php if(\GFCommon\Model\Access\BackendPermissions::session()->hasPermission(\GFCommon\Model\Access\BackendPermissions::PERMISSION_SYSTEM)) { ?>
        <?php if(\GFCommon\Model\Access\BackendPermissions::isAdmin()) { ?>
            <button onclick="systemUser.show()">Brugere</button>
        <?php } ?>

        <button onclick="cardshopSettings.showDashboard()">Cardshops</button>
        <button onclick="giftcertificateBatch.showDashboard()">Gavekort</button>

        <?php if(\GFCommon\Model\Access\BackendPermissions::isDeveloper()) { ?><button onclick="systemErrorLog.showLatest()">Seneste fejl</button>
        <button onclick="systemErrorLog.showStats()" style="font-weight: bold;">Fejl oversigt</button>
        <button onclick="systemErrorLog.showMailStats()" style="">Mail stats</button>
        <button onclick="systemErrorLog.showObjectFactory()">Kode generering</button>
        <?php } ?>
    <?php } ?>
</div><br>