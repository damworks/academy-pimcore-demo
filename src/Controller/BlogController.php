<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\BlogPost;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends FrontendController
{
    public function indexAction(Request $request): Response
    {
        return $this->render('blog/index.html.twig');
    }

    #[Route ('/blog/{slug}-{blogPostId}', name: 'blog_post_show', requirements: ['slug' => '[-a-zA-Z0-9_]+', 'blogPostId' => '\d+'], methods: ['GET'])]
    public function showAction(
        Request $request,
        int $blogPostId
    ): Response
    {
        $blogPost = BlogPost::getById($blogPostId);

        if (empty($blogPost)) {
            throw $this->createNotFoundException('Blog post not found');
        }

        return $this->render('blog/show.html.twig',
            [
                'blogPost' => []
            ]
        );
    }
}
