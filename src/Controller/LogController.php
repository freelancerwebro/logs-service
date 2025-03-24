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
        private readonly LogRepository $logRepository,
    ) {
    }

    /**
     * @OA\Get(
     *     path="/logs/count",
     *     summary="Get the number of logs matching criteria",
     *     tags={"Logs"},
     *     @OA\Parameter(
     *         name="serviceNames[]",
     *         in="query",
     *         description="Filter by one or more service names",
     *         required=false,
     *         @OA\Schema(type="array", @OA\Items(type="string")),
     *         explode=true
     *     ),
     *     @OA\Parameter(
     *         name="statusCode",
     *         in="query",
     *         description="HTTP status code to filter by",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="startDate",
     *         in="query",
     *         description="Start date in YYYY-MM-DD format",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="endDate",
     *         in="query",
     *         description="End date in YYYY-MM-DD format",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with log count",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="counter", type="integer", example=42)
     *         )
     *     )
     * )
     */
    #[Route('/logs/count', name: 'app_log_count', methods: ['GET'])]
    public function count(
        #[MapQueryString] ?LogRequestDto $logRequestDto,
    ): JsonResponse {
        return $this->json([
            'counter' => $this->logRepository->countByCriteria($logRequestDto),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/logs",
     *     summary="List logs with pagination",
     *     tags={"Logs"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number (default: 1)",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         required=false,
     *         description="Number of results per page (default: 10)",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with paginated logs",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="page", type="integer", example=1),
     *             @OA\Property(property="limit", type="integer", example=10),
     *             @OA\Property(property="total", type="integer", example=100),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="serviceName", type="string", example="AUTH-SERVICE"),
     *                     @OA\Property(property="method", type="string", example="GET"),
     *                     @OA\Property(property="endpoint", type="string", example="/auth"),
     *                     @OA\Property(property="statusCode", type="integer", example=200),
     *                     @OA\Property(property="created", type="string", format="date-time", example="2025-03-24T14:00:00Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    #[Route('/logs', name: 'app_log_list', methods: ['GET'])]
    public function list(
        Request $request,
    ): JsonResponse {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, (int) $request->query->get('limit', 10));

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'total' => $this->logRepository->getTotalLogsCount(),
            'data' => $this->logRepository->getPaginatedLogs($page, $limit),
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/logs",
     *     summary="Delete all logs",
     *     tags={"Logs"},
     *     description="Truncates the log table and clears the cache.",
     *     @OA\Response(
     *         response=204,
     *         description="Logs successfully deleted (no content)"
     *     )
     * )
     */

    #[Route('/logs', name: 'app_log_delete', methods: ['DELETE'])]
    public function truncate(): JsonResponse
    {
        $this->logRepository->deleteAll();
        $this->logRepository->clearCache();

        return $this->json([], Response::HTTP_NO_CONTENT);
    }
}
