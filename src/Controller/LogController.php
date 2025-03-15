<?php

namespace App\Controller;

use App\Dto\LogRequestDto;
use App\Repository\LogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class LogController extends AbstractController
{
    public function __construct(
        private readonly LogRepository $logRepository
    ) {
    }

    #[Route('/logs/count', name: 'app_log_count', methods: ['GET'])]
    public function count(
        #[MapQueryString] ?LogRequestDto $logRequestDto,
    ): JsonResponse {
        return $this->json([
            'counter' => $this->logRepository->countByCriteria($logRequestDto),
        ]);
    }

    #[Route('/logs', name: 'app_log_list', methods: ['GET'])]
    public function list(
        Request $request
    ): JsonResponse {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, (int) $request->query->get('limit', 10));

        $logs = $this->logRepository->getPaginatedLogs($page, $limit);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $logs['total'],
            'data' => $logs['data'],
        ]);
    }

    #[Route('/logs', name: 'app_log_delete', methods: ['DELETE'])]
    public function truncate(): JsonResponse {
        $this->logRepository->deleteAll();

        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}
