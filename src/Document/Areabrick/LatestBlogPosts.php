<?php

declare(strict_types=1);

namespace App\Document\Areabrick;

use App\DataMapper\Areas\LatestBlogPostsDataMapper;
use Pimcore\Model\DataObject\BlogPost;
use Pimcore\Model\Document\Editable\Area\Info;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LatestBlogPosts extends AbstractAreaBrick
{
    public function getName(): string
    {
        return 'Latest Blog Posts';
    }

    public function action(Info $info): ?Response
    {
        $blogPosts = new BlogPost\Listing();
        $blogPosts->setOrderKey('date');
        $blogPosts->setOrder('DESC');
        $blogPosts->setLimit(3);

        $info->setParams(
            [
                'blog_posts' => LatestBlogPostsDataMapper::list($blogPosts->load())->all(new Request())
            ]
        );

        return null;
    }
}
