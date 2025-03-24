<?php

declare(strict_types=1);

namespace App\Tests\Behavioral\Context;

use App\Command\GenerateLogsCommand;
use App\Repository\LogRepositoryInterface;
use App\Service\LogBatchProcessorService;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestAssertionsTrait;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

class ApiContext implements Context
{
    use WebTestAssertionsTrait;

    private KernelBrowser $client;
    private Response $response;

    private string $logFilePath;

    public function __construct(
        readonly KernelInterface $kernel,
        readonly LogRepositoryInterface $logRepository,
        readonly LogBatchProcessorService $logProcessorService,
    ) {
        $this->client = $kernel->getContainer()->get('test.client');

        $this->logFilePath = sys_get_temp_dir().'/behat_generated_logs.log';
    }

    /**
     * @Given the log table is empty
     */
    public function theLogTableIsEmpty(): void
    {
        $this->logRepository->deleteAll();
        $this->logRepository->clearCache();
    }

    /**
     * @Given the log file is empty
     */
    public function theLogFileIsEmpty(): void
    {
        if (isset($this->logFilePath) && file_exists($this->logFilePath)) {
            file_put_contents($this->logFilePath, '');
        }
    }

    /**
     * @Given I have generated :count logs into the file
     */
    public function iHaveGeneratedLogsIntoTheFile(int $count): void
    {
        $generateCommand = new GenerateLogsCommand();
        $tester = new CommandTester($generateCommand);
        $tester->execute([
            'filePath' => $this->logFilePath,
            'generateRowsNo' => $count,
        ]);
    }

    /**
     * @When I request :url
     */
    public function iRequest(string $url): void
    {
        $this->client->request('GET', $url);
    }

    /**
     * @When I send a DELETE request to :url
     */
    public function iSendDeleteRequest(string $url): void
    {
        $this->client->request('DELETE', $url);
    }

    /**
     * @Then the response status code should be :code
     */
    public function theResponseStatusCodeShouldBe(int $code): void
    {
        Assert::assertSame($code, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @Then the JSON should contain :key equal to :value
     */
    public function theJsonShouldContainEqualTo(string $key, int $value): void
    {
        $json = json_decode($this->client->getResponse()->getContent(), true);
        Assert::assertArrayHasKey($key, $json);
        Assert::assertSame($value, $json[$key]);
    }

    /**
     * @Then the log table should be empty
     */
    public function theLogTableShouldBeEmpty(): void
    {
        $count = $this->logRepository->countByCriteria();
        Assert::assertSame(0, $count);
    }

    /**
     * @Given I have processed the log file
     */
    public function iHaveProcessedTheLogFile(): void
    {
        $lines = count(file($this->logFilePath));
        $this->logProcessorService->process($this->logFilePath, 1, $lines);
    }
}
