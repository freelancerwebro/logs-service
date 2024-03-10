<?php

namespace App\Controller;

use App\Dto\LogRequestDto;
use App\Repository\LogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

final class LogController extends AbstractController
{
    #[Route('/count', name: 'app_log_count', methods: ['GET'])]
    public function count(
        #[MapQueryString] ?LogRequestDto $logRequestDto,
        LogRepository $logRepository,
    ): JsonResponse {
        return $this->json([
            'counter' => $logRepository->countByCriteria($logRequestDto),
        ]);
    }

    #[Route('/delete', name: 'app_log_delete', methods: ['DELETE'])]
    public function truncate(
        LogRepository $logRepository,
    ): JsonResponse {
        $logRepository->deleteAll();

        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}
