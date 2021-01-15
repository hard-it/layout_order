<?php

//Customize order send layout
use \Bitrix\Main\Loader;
use \Bitrix\Main\SystemException;
use \Bitrix\Sale;
use \Bitrix\Main\UserTable;
use \Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();
$eventManager->addEventHandler('sale', 'OnOrderNewSendEmail', 'customSaleMails');

function customSaleMails($orderId, &$eventName, &$arFields)
{
    try {
        if (Loader::includeModule('sale')) {
            /**
             *
             * $order - Загруженный заказ на D7.
             * $arOrder - Загруженный заказ на старом ядре, понадобился, т.к на D7 не удалось получить свойство USER_DESCRIPTION
             * $propsCollection - Список свойств заказа.
             * $priceOrder - Стоимость заказа.
             * $priceDeliveryOrder - Стоимость доставки.
             * $currencyOrder - Валюта заказа.
             *
             */
            $order = Sale\Order::load($orderId);
            $arOrder = CSaleOrder::GetByID($orderId);
            $propsCollection = $order->getPropertyCollection();
            $priceOrder = $order->getPrice();
            $priceDeliveryOrder = $order->getDeliveryPrice();
            $currencyOrder = $order->getCurrency();

            if (!empty($arOrder) && !empty($propsCollection)) {
                if (is_array($arOrder)) {
                    $arFields['CLIENT_COMMENT_ORDER'] = $order->getField('USER_DESCRIPTION');
                }

                if ($emailPropValue = $propsCollection->getUserEmail()) {
                    $arFields['CLIENT_EMAIL'] = $emailPropValue->getValue();
                }

                if ($namePropValue = $propsCollection->getPayerName()) {
                    $arFields['CLIENT_NAME'] = $namePropValue->getValue();
                }

                if ($phonePropValue = $propsCollection->getPhone()) {
                    $arFields['CLIENT_PHONE'] = $phonePropValue->getValue();
                }

                if ($locationPropValue = $propsCollection->getAddress()) {
                    $arFields['DELIVERY_ADDRESS'] = $locationPropValue->getValue();
                }

            }

            $arFields['PRICE_ORDER'] = $priceOrder;
            $arFields['PRICE_DELIVERY_ORDER'] = $priceDeliveryOrder;
            $arFields['CURRENCY_ORDER'] = $currencyOrder;
        }
    } catch (LoaderException $e) {
        ShowError($e->getMessage());
    } catch (\Bitrix\Main\ObjectPropertyException $e) {
        ShowError($e->getMessage());
    } catch (\Bitrix\Main\SystemException $e) {
        ShowError($e->getMessage());
    }
}

