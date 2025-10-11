<?php

namespace App\Model\DataObject;

use JsonSerializable;

class Warehouse extends \Pimcore\Model\DataObject\Warehouse implements JsonSerializable
{

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'location' => [
                'latitude' => $this->getLocation()?->getLatitude(),
                'longitude' => $this->getLocation()?->getLongitude()
            ]
        ];
    }
}
