<?php

namespace App\Controller;

use App\Repository\LogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class LogDeleteController extends AbstractController
{
    #[Route('/log', name: 'app_log_delete', methods: ['DELETE'])]
    public function index(
        LogRepository $logRepository,
    ): JsonResponse {
        $logRepository->deleteAll();

        return $this->json([], 204);
    }
}
