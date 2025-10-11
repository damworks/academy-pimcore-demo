<?php

namespace App\Controller\Api\v1;

use App\Controller\Api\ApiController;
use Illuminate\Support\Str;
use Pimcore\Model\DataObject\Data\GeoCoordinates;
use Pimcore\Model\DataObject\Folder;
use App\Model\DataObject\Warehouse;
use Pimcore\Model\DataObject\Warehouse\Listing;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WarehouseApiController extends ApiController
{
    #[Route('/api/v1/warehouses', name: 'v1_warehouses', methods: ['GET'])]
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $listing = new Listing();
        $warehouses = $listing->getData();

        return $this->json(['warehouses' => $warehouses]);
    }

    #[Route('/api/v1/warehouses/{id}', name: 'v1_warehouses_show', methods: ['GET'])]
    /**
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $warehouse = Warehouse::getById($id);

        if (!$warehouse) {
            return $this->json(['error' => 'Warehouse not found'], 404);
        }

        return $this->json($warehouse);
    }

    #[Route('/api/v1/warehouses', name: 'v1_warehouses_store', methods: ['POST'])]
    /**
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        /** @var array $data */
        $data = json_decode($request->getContent(), true);

        $parentFolder = Folder::getByPath('/warehouses');
        if (!$parentFolder) {
            $parentFolder = new Folder();
            $parentFolder->setKey('warehouses');
            $parentFolder->setParent(Folder::getById(1)); // 1 è la root folder
            $parentFolder->setPublished(true);
            $parentFolder->save();
        }

        $warehouse = new Warehouse();
        $warehouse->setKey(Str::slug($data['name']));
        $warehouse->setParent($parentFolder);
        $warehouse->setPublished(true);
        $warehouse->setName($data['name']);

        $coordinate = new GeoCoordinates($data['latitude'], $data['longitude']);
        $warehouse->setLocation($coordinate);

        $warehouse->save();

        return $this->json($warehouse, Response::HTTP_CREATED);
    }
}
