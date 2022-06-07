<?php

namespace App\Model;

use Pimcore\Model\DataObject\Tank\Listing;

class Tank extends \Pimcore\Model\DataObject\Tank
{
    public static function getAllTanks(): Listing
    {
        return new Listing();
    }

    public static function getTankById($id)
    {
        $listing = Tank::getByIdentification($id);
        $tank = array();
        foreach ($listing as $item) {
            $tank = $item;
        }

        return $tank;
    }
}
