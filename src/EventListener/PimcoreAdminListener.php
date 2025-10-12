<?php

namespace App\EventListener;

use Pimcore\Event\BundleManager\PathsEvent;

class PimcoreAdminListener
{
    public function addCSSFiles(PathsEvent $event): void
    {
    }

    public function addJSFiles(PathsEvent $event): void
    {
        $event->setPaths(
            array_merge(
                $event->getPaths(),
                [
                    '/static/js/pimcore/data-importer/mapping/operator/LoadOrCreateRelatedArticles.js',
                    '/static/js/pimcore/data-importer/mapping/operator/CaseConversion.js'
                ]
            )
        );
    }
}
