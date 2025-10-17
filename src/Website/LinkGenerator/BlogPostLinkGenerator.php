<?php declare(strict_types=1);

namespace App\Website\LinkGenerator;

use Pimcore\Model\DataObject\BlogPost;
use Pimcore\Model\DataObject\ClassDefinition\LinkGeneratorInterface;
use Pimcore\Tool;
use Pimcore\Twig\Extension\Templating\PimcoreUrl;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class BlogPostLinkGenerator implements LinkGeneratorInterface
{
    public function __construct(
        private SluggerInterface $slugger,
        private PimcoreUrl $pimcoreUrl,
        private ParameterBagInterface $parameterBag
    )
    {
    }

    public function generate(object $object, array $params = []): string
    {
        if (!$object instanceof BlogPost) {
            throw new \InvalidArgumentException('Object must be an instance of BlogPost');
        }

        $slug = $this->slugger->slug($object->getTitle())->lower();
        $link = $this->pimcoreUrl->__invoke(
            [
                'blogPostId' => $object->getId(),
                'slug' => $slug
            ],
            'blog_post_show',
            true
        );

        if (!str_contains($link, 'https://') && !str_contains($link, 'http://')) {
            $protocol = $this->parameterBag->get('site_protocol');
            $link = Tool::getHostUrl($protocol).$link;
        }

        return $link;
    }
}
