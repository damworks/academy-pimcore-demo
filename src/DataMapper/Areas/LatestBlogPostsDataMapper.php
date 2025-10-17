<?php declare(strict_types=1);

namespace App\DataMapper\Areas;

use App\DataMapper\AbstractDataMapper;
use Pimcore\Model\DataObject\BlogPost;

/**
 * @property BlogPost $resource
 */
class LatestBlogPostsDataMapper extends AbstractDataMapper
{

    public function toArray($request): array
    {
        return [
            'id' => $this->resource->getId(),
            'image' => $this->resource->getImage() ?->getImage(),
            'title' => $this->resource->getTitle(),
            'short_description' => $this->resource->getShortDescription(),
            'posted' => $this->resource->getDate()?->setTimezone('Europe/Berlin')->format('F j, Y'),
            'slug' => '',
            'tags' => []
        ];
    }
}
