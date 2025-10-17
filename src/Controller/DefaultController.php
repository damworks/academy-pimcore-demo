<?php

namespace App\Controller;

use App\DataMapper\Areas\LatestBlogPostsDataMapper;
use Pimcore\Bundle\AdminBundle\Controller\Admin\LoginController;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\BlogPost;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends FrontendController
{

    #[Route('/testing-url', name: 'testing_url')]
    public function test(Request $request)
    {
        $test = BlogPost::getById(403);
        //dd($test);
        dd((new LatestBlogPostsDataMapper($test))->toArray($request));
    }
    public function defaultAction(Request $request): Response
    {
        return $this->render('default/default.html.twig');
    }

    public function footerAction(Request $request): Response
    {
        return $this->render('include/footer.html.twig');
    }

    /**
     * Forwards the request to admin login
     */
    public function loginAction(): Response
    {
        return $this->forward(LoginController::class.'::loginCheckAction');
    }
}
