<?php
// src/Controller/QueryApiController.php
namespace App\Controller;

use App\Service\QueryPreviewService;

class QueryApiController
{
    public function __construct(private QueryPreviewService $previewService) {}

    public function preview(array $postData): array
    {
        $sql = $postData['sql'] ?? '';
        $sourceConfig = $postData['sourceConfig'] ?? []; // Pega o objeto que o JS enviou

        return $this->previewService->getPreview($sourceConfig, $sql);
    }
}