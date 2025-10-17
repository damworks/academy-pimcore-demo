<?php

declare(strict_types=1);

namespace App\Document\Areabrick;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

class GalleryCarousel extends AbstractAreaBrick
{
    public function getName(): string
    {
        return 'Gallery Carousel';
    }
}
