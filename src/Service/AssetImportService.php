<?php declare(strict_types=1);

namespace App\Service;

use Pimcore\Model\Asset;

class AssetImportService
{
    public function import(string $url, string $destinationPath): Asset
    {
        $media = file_get_contents($url);
        $filename = basename($url);

        $parentFolder = Asset\Folder::getByPath($destinationPath);
        if (!$parentFolder) {
            $parentFolder = new Asset\Folder();
            $parentFolder->setParent(Asset::getByPath('/'));
            $parentFolder->setFilename(trim($destinationPath, '/'));
            $parentFolder->save();
        }

        /** @var Asset|null $asset */
        $asset = Asset::getByPath($destinationPath . '/' . $filename);
        if (!$asset) {
            $asset = new Asset();
            $asset->setParent($parentFolder);
            $asset->setFilename($filename);
        }

        $asset->setData($media);
        $asset->save();

        return $asset;
    }

    /**
     * Importa un asset da URL remoto
     *
     * @param string $url URL del file da importare
     * @param string $targetPath Path della cartella in Pimcore dove salvare l'asset
     * @return Asset Asset creato
     * @throws \Exception
     */
    public function importAssetFromUrl(string $url, string $targetPath): Asset
    {
        // Valida l'URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('URL non valido');
        }

        // Scarica il contenuto del file
        $context = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Pimcore Asset Importer',
                'follow_location' => true,
                'max_redirects' => 5
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        $fileContent = @file_get_contents($url, false, $context);

        if ($fileContent === false) {
            throw new \Exception('Impossibile scaricare il file dall\'URL: ' . $url);
        }

        // Estrai il filename dall'URL
        $urlPath = parse_url($url, PHP_URL_PATH);
        $filename = basename($urlPath);

        // Se il filename non ha estensione o non è valido, genera uno con timestamp
        if (!$filename || !preg_match('/\.[a-zA-Z0-9]{2,4}$/', $filename)) {
            $extension = $this->getExtensionFromContent($fileContent);
            $filename = 'imported_' . time() . '.' . $extension;
        }

        // Crea la cartella parent se non esiste
        $parentFolder = Asset\Service::createFolderByPath($targetPath);

        // Verifica se esiste già un asset con lo stesso nome
        $fullPath = rtrim($targetPath, '/') . '/' . $filename;
        $existingAsset = Asset::getByPath($fullPath);

        if ($existingAsset) {
            $filename = $this->generateUniqueFilename($targetPath, $filename);
        }

        // Crea il nuovo asset
        $asset = new Asset();
        $asset->setFilename($filename);
        $asset->setData($fileContent);
        $asset->setParent($parentFolder);
        $asset->save();

        return $asset;
    }

    /**
     * Determina l'estensione del file dal contenuto binario
     */
    private function getExtensionFromContent(string $content): string
    {
        // Controlla i magic bytes del file
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($content);

        $mimeToExtension = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            'application/zip' => 'zip',
            'text/plain' => 'txt',
            'text/html' => 'html',
            'application/json' => 'json',
            'application/xml' => 'xml',
        ];

        return $mimeToExtension[$mimeType] ?? 'bin';
    }

    /**
     * Genera un filename univoco se esiste già un asset con lo stesso nome
     */
    private function generateUniqueFilename(string $path, string $filename): string
    {
        $pathInfo = pathinfo($filename);
        $baseName = $pathInfo['filename'];
        $extension = $pathInfo['extension'] ?? '';

        $path = rtrim($path, '/') . '/';
        $counter = 1;
        $newFilename = $filename;

        while (Asset::getByPath($path . $newFilename)) {
            $newFilename = $baseName . '_' . $counter . ($extension ? '.' . $extension : '');
            $counter++;
        }

        return $newFilename;
    }

}
