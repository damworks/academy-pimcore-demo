<?php

namespace App\Controller;

use Pimcore\Bundle\AdminBundle\Controller\Admin\LoginController;
use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BlogController extends FrontendController
{
    public function indexAction(Request $request): Response
    {
        return $this->render('blog/index.html.twig');
    }

}
