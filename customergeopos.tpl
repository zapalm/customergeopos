{**
* Customer geo-position provider and Yandex Map example module: module for PrestaShop 1.5-1.6.
*
* @author      zapalm <zapalm@ya.ru>
* @copyright   (c) 2013, zapalm
* @link        https://prestashop.modulez.ru/en/administrative-tools/47-customer-geo-position-provider.html The module's homepage
* @license     https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
*}

<h2>{l s='Customer geoposition example' mod='customergeopos'}</h2>

<div class="customergeopos-map" id="map">
    {* Блок для загруки карты *}
</div>

{literal}
    <script type="text/javascript">
        ymaps.ready(showMap);

        function showMap() {
            // Создаем карту, по центру - г. Владивосток
            var myMap = new ymaps.Map("map", {center: [43.134019, 131.928379], zoom: 11});

            // Задаем параметры для колекции объектов
            var myCollection = new ymaps.GeoObjectCollection({}, {
                preset: 'twirl#redDotIcon',
                cursor: 'help',
                draggable: true
            });

            // Задаем вид всплывающего окна (отображается при нажатии на метку)
            var myBalloonLayout = ymaps.templateLayoutFactory.createClass('<h3>$[properties.name]</h3>');
            ymaps.layout.storage.add('my#superlayout', myBalloonLayout);
            myCollection.options.set({
                balloonContentBodyLayout: 'my#superlayout',
                balloonMaxWidth: 300
            });

            // Способ определения местоположения пользователя - Yandex
            if (CUSTOMERGEOPOS_YANDEX) {
                customergeopos_yandex(myCollection, "yandex");
            }

            // Способ определения местоположения пользователя - IpGeoBase
            if (CUSTOMERGEOPOS_IPGEOBASE) {
                customergeopos_ipgeobase(myCollection, "ipgeobase");
            }

            // Способ определения местоположения пользователя - W3C (задействуем в последнюю очередь, т.к. запрашивается подтверждение у пользователя)
            if (CUSTOMERGEOPOS_W3C) {
                customergeopos_w3c(myCollection, "w3c");
            }

            // Добавляем на карту колекцию меток с местоположением пользователя
            myMap.geoObjects.add(myCollection);
        }
    </script>
{/literal}
