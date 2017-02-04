/**
 * Customer geo-position provider and Yandex Map example module: module for PrestaShop 1.5-1.6.
 *
 * @author      zapalm <zapalm@ya.ru>
 * @copyright   (c) 2013, zapalm
 * @link        https://prestashop.modulez.ru/en/administrative-tools/47-customer-geo-position-provider.html The module's homepage
 * @license     https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

function customergeopos_yandex(Collection, title) {
    if (ymaps != null && ymaps.geolocation != null) {
        Collection.add(
            new ymaps.Placemark(
                [ymaps.geolocation.latitude, ymaps.geolocation.longitude],
                {
                    name: title
                }
            )
        );
    }
}

function customergeopos_w3c(Collection, title) {
    if (navigator.geolocation) {
        var timeoutVal = 10000000;
        navigator.geolocation.getCurrentPosition(
            function (position) {
                Collection.add(
                    new ymaps.Placemark(
                        [position.coords.latitude, position.coords.longitude],
                        {
                            name: title
                        }
                    )
                );
            },
            function (error) {
                return error.code;
            },
            {
                enableHighAccuracy: true,
                timeout: timeoutVal,
                maximumAge: 0
            }
        );
    }
}

function customergeopos_ipgeobase(Collection, title) {
    var geopos = CUSTOMERGEOPOS_IPGEOBASE_GEOPOS;
    if (geopos[0] !== 0 && geopos[1] !== 0) {
        Collection.add(
            new ymaps.Placemark(
                geopos,
                {
                    name: title
                }
            )
        );
    }
}