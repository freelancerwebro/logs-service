<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $response = new JsonResponse([
            'error' => $exception->getMessage(),
        ]);

        $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);

        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);
            $response->headers->replace($exception->getHeaders());
        }

        $event->setResponse($response);
    }
}