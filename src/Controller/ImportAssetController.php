<?php declare(strict_types=1);

namespace App\Controller;

use App\Service\AssetImportService as AssetImportService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ImportAssetController
{
    private const string DESTINATION = '/import-assets';

    #[Route("/import-assets", name: "import_asset", methods: ['GET'])]
    public function index(Request $request, AssetImportService $assetImportService): JsonResponse
    {
        // https://www.arsenalia.com/images/meta_image.jpg
        $url = $request->query->get('url');
        if (!$url) {
            return new JsonResponse(['error' => 'URL parameter is required'], 400);
        }

        try {
            $asset = $assetImportService->import($url, self::DESTINATION);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        //return new JsonResponse(['success' => 'ok'], 200);
        return new JsonResponse(['success' => true, 'assetId' => $asset->getId(), 'assetPath' => $asset->getFullPath()]);
    }
}
