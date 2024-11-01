<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class EnUspsComposerStaticInit1ed1fea8bfd107ca92e94cf1a2b758d4
{
    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'EnLocation' => __DIR__ . '/../..' . '/admin/tab/location/en-location.php',
        'EnSBS\\EnSBS' => __DIR__ . '/../..' . '/admin/tab/sbs/en-sbs.php',
        'EnUspsFreightdeskOnline\\EnUspsFreightdeskOnline' => __DIR__ . '/../..' . '/admin/tab/freightdesk-online/en-freightdesk-online.php',
        'EnUspsValidateAddress\\EnUspsValidateAddress' => __DIR__ . '/../..' . '/admin/tab/validate-address/en-validate-address.php',
        'EnUspsOrderWidget\\EnUspsOrderWidget' => __DIR__ . '/../..' . '/admin/order/en-order-widget.php',
        'EnUspsConfig\\EnUspsConfig' => __DIR__ . '/../..' . '/common/en-config.php',
        'EnUspsConnectionSettings\\EnUspsConnectionSettings' => __DIR__ . '/../..' . '/admin/tab/connection-settings/en-connection-settings.php',
        'EnUspsCsvExport\\EnUspsCsvExport' => __DIR__ . '/../..' . '/common/en-csv.php',
        'EnUspsCurl\\EnUspsCurl' => __DIR__ . '/../..' . '/http/en-curl.php',
        'EnUspsDistance\\EnUspsDistance' => __DIR__ . '/../..' . '/admin/tab/location/includes/en-distance.php',
        'EnUspsDropshipTemplate\\EnUspsDropshipTemplate' => __DIR__ . '/../..' . '/admin/tab/location/dropship/en-dropship.php',
        'EnUspsFdo\\EnUspsFdo' => __DIR__ . '/../..' . '/fdo/en-fdo.php',
        'EnUspsFilterQuotes\\EnUspsFilterQuotes' => __DIR__ . '/../..' . '/server/common/en-filter-quotes.php',
        'EnUspsGuard\\EnUspsGuard' => __DIR__ . '/../..' . '/common/en-guard.php',
        'EnUspsLoad\\EnUspsLoad' => __DIR__ . '/../..' . '/common/en-app-load.php',
        'EnUspsLocationAjax\\EnUspsLocationAjax' => __DIR__ . '/../..' . '/admin/tab/location/includes/en-location-ajax.php',
        'EnUspsMessage\\EnUspsMessage' => __DIR__ . '/../..' . '/common/en-message.php',
        'EnUspsOrderRates\\EnUspsOrderRates' => __DIR__ . '/../..' . '/admin/order/en-order-rates.php',
        'EnUspsOrderScript\\EnUspsOrderScript' => __DIR__ . '/../..' . '/admin/order/en-order-script.php',
        'EnUspsOtherRates\\EnUspsOtherRates' => __DIR__ . '/../..' . '/server/api/en-other-rates.php',
        'EnUspsPackage\\EnUspsPackage' => __DIR__ . '/../..' . '/server/package/en-package.php',
        'EnUspsPlans\\EnUspsPlans' => __DIR__ . '/../..' . '/common/en-plans.php',
        'EnUspsProductDetail\\EnUspsProductDetail' => __DIR__ . '/../..' . '/admin/product/en-product-detail.php',
        'EnUspsQuoteSettingsDetail\\EnUspsQuoteSettingsDetail' => __DIR__ . '/../..' . '/server/common/en-quote-settings.php',
        'EnUspsQuoteSettings\\EnUspsQuoteSettings' => __DIR__ . '/../..' . '/admin/tab/quote-settings/en-quote-settings.php',
        'EnUspsReceiverAddress\\EnUspsReceiverAddress' => __DIR__ . '/../..' . '/server/common/en-receiver-address.php',
        'EnUspsResponse\\EnUspsResponse' => __DIR__ . '/../..' . '/server/api/en-response.php',
        'EnUspsShippingRates' => __DIR__ . '/../..' . '/server/en-shipping-rates.php',
        'EnUspsTab' => __DIR__ . '/../..' . '/admin/tab/en-tab.php',
        'EnUspsTestConnection\\EnUspsTestConnection' => __DIR__ . '/../..' . '/admin/tab/connection-settings/en-connection-ajax.php',
        'EnUspsUserGuide\\EnUspsUserGuide' => __DIR__ . '/../..' . '/admin/tab/user-guide/en-user-guide.php',
        'EnUspsVersionCompact\\EnUspsVersionCompact' => __DIR__ . '/../..' . '/server/common/en-version-compact.php',
        'EnUspsWarehouseTemplate\\EnUspsWarehouseTemplate' => __DIR__ . '/../..' . '/admin/tab/location/warehouse/en-warehouse.php',
        'EnUspsWarehouse\\EnUspsWarehouse' => __DIR__ . '/../..' . '/db/en-warehouse.php',
        'WC_EnUspsShippingRates' => __DIR__ . '/../..' . '/server/en-shipping-rates.php',
        'EnUspsOrderExport\\EnUspsOrderExport' => __DIR__ . '/../..' . '/server/common/en-order-export.php',
        'EnUspsShippingRulesAjaxReq\\EnUspsShippingRulesAjaxReq' => __DIR__ . '/../..' . '/admin/tab/shipping-rules/shipping-rules-save.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->classMap = EnUspsComposerStaticInit1ed1fea8bfd107ca92e94cf1a2b758d4::$classMap;

        }, null, ClassLoader::class);
    }
}
