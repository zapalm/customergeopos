<?php
/**
 * Customer geo-position provider and Yandex Map example module: module for PrestaShop 1.5-1.6.
 *
 * @author      zapalm <zapalm@ya.ru>
 * @copyright   (c) 2013, zapalm
 * @link        https://prestashop.modulez.ru/en/administrative-tools/47-customer-geo-position-provider.html The module's homepage
 * @license     https://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomerGeopos extends Module
{
    /** @var array Настройки модуля по-умолчанию */
    private $conf_default = array(
        'CUSTOMERGEOPOS_W3C'       => 1,
        'CUSTOMERGEOPOS_YANDEX'    => 0,
        'CUSTOMERGEOPOS_IPGEOBASE' => 0,
        'CUSTOMERGEOPOS_DEMO'      => 0,
    );

    /** @var array Конфигурация модуля */
    private $conf = array();

    public function __construct()
    {
        $this->name             = 'customergeopos';
        $this->tab              = 'administration';
        $this->version          = '1.0';
        $this->author           = 'zapalm';
        $this->need_instance    = 0;

        parent::__construct();

        $this->displayName = $this->l('Customer geoposition');
        $this->description = $this->l('Allow to get a customer geoposition');

        $this->conf = Configuration::getMultiple(array_keys($this->conf_default));
    }

    public function install()
    {
        foreach ($this->conf_default as $c => $v) {
            Configuration::updateValue($c, $v);
        }

        return parent::install() && $this->registerHook('displayHeader') && $this->registerHook('displayHome');
    }

    public function uninstall()
    {
        foreach ($this->conf_default as $c => $v) {
            Configuration::deleteByName($c);
        }

        return parent::uninstall();
    }

    /**
     * Отображает и обрабатывает форму настроек модуля.
     *
     * @return string
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit_save')) {
            $res = 1;
            foreach ($this->conf_default as $c => $v) {
                $res &= Configuration::updateValue($c, (int)Tools::getValue($c));
            }

            $this->conf = Configuration::getMultiple(array_keys($this->conf_default));

            $output .= $res ? $this->displayConfirmation($this->l('Settings updated')) : $this->displayError($this->l('Some setting not updated'));
        }

        return $output . $this->displayForm();
    }

    /**
     * Формирует форму настроек модуля.
     *
     * @return string
     */
   public function displayForm()
    {
        $output = '
            <fieldset style="width: 800px">
                <legend>
                    <img src="' . _PS_ADMIN_IMG_ . 'cog.gif" alt="" title="" />' . $this->l('Settings') . '
                </legend>
                <form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="fm_submit">
                    <p>'.$this->l('This settings are for demonstration geo-location only to work with map.').'</p>
                    <label>' . $this->l('Demonstration') . ':</label>
                    <div class="margin-form">
                        <input type="checkbox" name="CUSTOMERGEOPOS_DEMO" value="1" ' . ($this->conf['CUSTOMERGEOPOS_DEMO'] ? 'checked="checked"' : '') . '>&nbsp;<b>' . $this->l('Enable demonstration map on the homepage') . '</b><br/>
                    </div>
                    <label>' . $this->l('Select geolocation methods to use') . ':</label>
                    <div class="margin-form">
                        <input type="checkbox" name="CUSTOMERGEOPOS_W3C" value="1" ' . ($this->conf['CUSTOMERGEOPOS_W3C'] ? 'checked="checked"' : '') . '>&nbsp;<b>' . $this->l('Use W3C Geo API') . '</b><br/>
                        <input type="checkbox" name="CUSTOMERGEOPOS_YANDEX" value="1" ' . ($this->conf['CUSTOMERGEOPOS_YANDEX'] ? 'checked="checked"' : '') . '>&nbsp;<b>' . $this->l('Use Yandex API') . '</b><br/>
                        <input type="checkbox" name="CUSTOMERGEOPOS_IPGEOBASE" value="1" ' . ($this->conf['CUSTOMERGEOPOS_IPGEOBASE'] ? 'checked="checked"' : '') . '>&nbsp;<b>' . $this->l('Use IpGeoBase') . '</b><br/>
                    </div>
                    <br><br>
                    <input type="submit" name="submit_save" value="' . $this->l('Save') . '" class="button" />
                </form>
            </fieldset>
            <br class="clear"/>
        ';

        return $output;
    }

    /**
     * Подключает необходимые ресурсы для работы карты Яндекс.
     *
     * @return string
     */
    public function hookDisplayHeader()
    {
        $this->context->controller->addJS('http://api-maps.yandex.ru/2.0/?load=package.full&lang=ru-RU');

        if (!$this->conf['CUSTOMERGEOPOS_DEMO']) {
            return '';
        }

        $this->context->controller->addCSS($this->_path . 'customergeopos.css', 'all');
        $this->context->controller->addJS($this->_path . 'customergeopos.js');

        // Получение местоположения пользователя способом ipgeobase
        $lat = $lng = 0;
        $geopos = self::getGeoposByIpgeobase();
        if (false !== $geopos) {
            list($lat, $lng) = $geopos;
        }

        $this->context->smarty->assign(array(
            'lat'  => $lat,
            'lng'  => $lng,
            'conf' => $this->conf,
        ));

        return $this->display(__FILE__, 'header.tpl');
    }

    /**
     * Отображает демонстрационную карту на главной странице, если включен демо-режим.
     *
     * @return string
     */
    public function hookDisplayHome()
    {
        if ($this->conf['CUSTOMERGEOPOS_DEMO']) {
            return $this->display(__FILE__, 'customergeopos.tpl');
        }

        return '';
    }

    /**
     * Получить координаты через сервис ipgeobase.ru.
     *
     * @return array|bool Массив [lat, lng] или false при ошибках.
     */
    public static function getGeoposByIpgeobase()
    {
        $xml = @file_get_contents('http://ipgeobase.ru:7020/geo?ip=' . Tools::getRemoteAddr());
        if (false !== $xml) {
            $dom = @simplexml_load_string($xml);
            if (false !== $dom && !empty($dom->ip->lat) && !empty($dom->ip->lng)) {
                return array(
                    (string)$dom->ip->lat,
                    (string)$dom->ip->lng,
                );
            }
        }

        return false;
    }
}