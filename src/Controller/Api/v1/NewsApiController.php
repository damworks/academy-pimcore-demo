<?php declare(strict_types=1);

namespace App\Controller\Api\v1;

use App\Controller\Api\ApiController;
use App\Model\DataObject\News;
use Pimcore\Model\DataObject\News\Listing;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class NewsApiController extends ApiController
{
    #[Route('/api/v1/news', name: 'v1_news_list', methods: ['GET'])]
    #[Route('{_locale}/api/v1/news', name: 'v1_news_list_translation', methods: ['GET'])]
    /**
     * @return JsonResponse
    */
    public function getNewsList(Request $request):JsonResponse
    {
        $request->get('lang') ? $request->setLocale($request->get('lang')) : 'en';

        $news = new Listing();
        $newsItems = $news->getData();

        return $this->json(['news' => $newsItems]);
    }

    #[Route('{_locale}/api/v1/news/{newsId}', name: 'v1_news_detail', methods: ['GET'])]
    /**
     * @return JsonResponse
     */
    public function getNewsDetail(int $newsId):JsonResponse
    {
        $newsItem = News::getById($newsId);
        if(!$newsItem) {
            return $this->json(['error' => 'News item not found'], 404);
        }

        return $this->json($newsItem);
    }
}
