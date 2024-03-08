<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class LogRequestDto
{
    /**
     * @param array<string>|null $serviceNames
     */
    public function __construct(
        #[Assert\All([
            new Assert\Regex(
                pattern: '/^[A-Z-]{5,30}+$/',
                message: 'ServiceName is invalid'
            ),
        ])]
        public ?array $serviceNames = null,

        #[Assert\Sequentially([
            new Assert\Range(
                notInRangeMessage: 'StatusCode is not a valid HTTP code',
                min: 200,
                max: 599,
            ),
        ])]
        public ?string $statusCode = null,

        #[Assert\DateTime(message: 'StartDate is not a valid datetime')]
        public ?string $startDate = null,

        #[Assert\DateTime(message: 'EndDate is not a valid datetime')]
        public ?string $endDate = null,
    ) {
    }
}
