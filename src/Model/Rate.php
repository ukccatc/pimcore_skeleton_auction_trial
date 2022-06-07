<?php

namespace App\Model;

use phpDocumentor\Reflection\Types\Null_;
use Pimcore\Model\DataObject\Rate\Listing;

class Rate extends \Pimcore\Model\DataObject\Rate
{

    public static function validateFields(array $data, Listing $rates, $error)
    {
        if (self::validatePrice($data, $rates, $error)) {
            return true;
        }else {
            return $error = 'Enter price greater than other bids';
        }
    }

    public static function validatePrice (array $data, Listing $rates)
    {
        $arrayRates = array();
        foreach ($rates as $rate) {
            $arrayRates[] = $rate->getBid();
        }
        if (empty($arrayRates)) {
            $arrayRates[] = $data['bid'];
            return true;
        }
        $max = max($arrayRates);
        if ($data['bid'] > $max) {
            return true;
        }
        else {
            return false;
        }
    }
}
