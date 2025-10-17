<?php declare(strict_types=1);

namespace App\DataMapper\Blog;

use App\DataMapper\AbstractDataMapper;
use Pimcore\Model\DataObject\BlogPostTag;

/**
 * @property BlogPostTag $resource
 */
class BlogPostTagDataMapper extends AbstractDataMapper
{

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->getId(),
            'name' => $this->resource->getName(),
        ];
    }
}
