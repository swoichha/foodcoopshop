<?php

namespace App\View\Helper;

use Cake\Core\Configure;

/**
 * FoodCoopShop - The open source software for your foodcoop
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @since         FoodCoopShop 3.1.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @author        Mario Rothauer <office@foodcoopshop.com>
 * @copyright     Copyright (c) Mario Rothauer, https://www.rothauer-it.com
 * @link          https://www.foodcoopshop.com
 */
class MyTimeDailyHelper extends MyTimeHelper
{

    public function getDeliveryDay($orderDay, $sendOrderListsWeekday = null, $deliveryRhythmType = null, $deliveryRhythmCount = null)
    {
        $preparedOrderDay = date($this->getI18Format('DateShortAlt'), $orderDay);
        $deliveryDate = strtotime($preparedOrderDay . '+ ' . Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') . ' day');
        return $deliveryDate;
    }
    
    public function getOrderPeriodLastDay($day)
    {
        $dateDiff = Configure::read('appDb.FCS_DEFAULT_SEND_ORDER_LISTS_DAY_DELTA') * -1;
        $date = date($this->getI18Format('DateShortAlt'), strtotime($dateDiff . ' day ', $day));
        return $date;
    }
    
    /**
     * see tests for implementations
     * @param $day
     * @return $day
     */
    public function getOrderPeriodFirstDay($day)
    {
        $date = $day;
        $date = date($this->getI18Format('DateShortAlt'), $date);
        return $date;
    }
    

}
