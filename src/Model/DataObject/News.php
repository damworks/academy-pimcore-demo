<?php

namespace App\Model\DataObject;

use JsonSerializable;
class News extends \Pimcore\Model\DataObject\News implements JsonSerializable
{

    public function jsonSerialize(): array
    {
        $categories = [];
        foreach ($this->getCategories() as $category) {
            $categories[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ];
        }

        return [
            'id' => $this->getId(),
            'key' => $this->getKey(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            //'content' => $this->getContent(),
            'date' => $this->getDate() ? $this->getDate()->format('Y-m-d') : null,
            'image' => $this->getImage() ? $this->getImage()->getFrontendPath() : null,
            'image_preview' => $this->getImage() ? $this->getImage()->getThumbnail('news_preview')->getFrontendPath() : null,
            'categories' => $categories,
            //'categories' => $this->getCategories() ? array_map(fn($category) => $category->getName(), $this->getCategories()) : [],
            //'published' => $this->getPublished(),
        ];
    }
}
