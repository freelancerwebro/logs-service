<?php

namespace App\Controller;

use App\Dto\LogRequestDto;
use App\Repository\LogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class LogCountController extends AbstractController
{
    #[Route('/log/count', name: 'app_log_count', methods: ['GET'])]
    public function index(
        #[MapQueryString] ?LogRequestDto $logRequestDto,
        LogRepository $logRepository,
    ): JsonResponse {
        $count = $logRepository->countByCriteria($logRequestDto);

        return $this->json([
            'log' => $logRequestDto,
            'counter' => $count,
        ]);
    }
}
