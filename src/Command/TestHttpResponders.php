<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\Attribute\ConsoleCommand;
use Distantmagic\Resonance\Attribute\RespondsWith;
use Distantmagic\Resonance\Attribute\TestableHttpResponse;
use Distantmagic\Resonance\Command;
use Distantmagic\Resonance\HttpRecursiveResponder;
use Distantmagic\Resonance\HttpResponderAggregate;
use Distantmagic\Resonance\HttpResponderInterface;
use Distantmagic\Resonance\InspectableSwooleResponse;
use Distantmagic\Resonance\JsonSchemaValidator;
use Distantmagic\Resonance\TestableHttpResponseCollection;
use Ds\Map;
use RuntimeException;
use Swoole\Http\Request;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Swoole\Coroutine\run;

#[ConsoleCommand(
    name: 'test:http-responders',
    description: 'Test HTTP responders'
)]
final class TestHttpResponders extends Command
{
    public function __construct(
        private HttpRecursiveResponder $recursiveResponder,
        private HttpResponderAggregate $httpResponderAggregate,
        private JsonSchemaValidator $jsonSchemaValidator,
        private TestableHttpResponseCollection $testableHttpResponseCollection,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var bool $isValid
         */
        $isValid = true;

        foreach ($this->testableHttpResponseCollection->httpResponder as $httpResponder => $testableHttpResponses) {
            foreach ($testableHttpResponses as $testableHttpResponse) {
                $potentialResponses = $this
                    ->testableHttpResponseCollection
                    ->testableHttpResponse
                    ->get($testableHttpResponse)
                ;

                /**
                 * @var bool
                 */
                $coroutineResult = run(function () use (
                    &$isValid,
                    $output,
                    $httpResponder,
                    $testableHttpResponse,
                    $potentialResponses
                ) {
                    $isValid = $isValid and $this->testResponses(
                        $output,
                        $httpResponder,
                        $testableHttpResponse,
                        $potentialResponses,
                    );
                });

                if (!$coroutineResult) {
                    throw new RuntimeException('Unable to start coroutine loop');
                }
            }
        }

        if ($isValid) {
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }

    /**
     * @param Map<int,RespondsWith> $potentialResponses
     */
    private function testResponses(
        OutputInterface $output,
        HttpResponderInterface $httpResponder,
        TestableHttpResponse $testableHttpResponse,
        Map $potentialResponses,
    ): bool {
        $output->write(sprintf('Testing <info>%s</info> ... ', $httpResponder::class));

        $request = new Request();
        $response = new InspectableSwooleResponse();

        $this->recursiveResponder->respondRecursive($request, $response, $httpResponder);

        $respondsWith = $potentialResponses->get($response->mockStatus, null);

        if (!$respondsWith) {
            throw new RuntimeException(sprintf(
                'Unexpected response status code: %d',
                $response->mockStatus,
            ));
        }

        $contentType = $response->mockGetkContentType();

        if (!str_starts_with($contentType, $respondsWith->contentType->value)) {
            throw new RuntimeException(sprintf(
                'Invalid content type: "%s", expected: "%s"',
                $contentType,
                $respondsWith->contentType->value,
            ));
        }

        $jsonSchemaValidationResult = $this
            ->jsonSchemaValidator
            ->validateSchema(
                $respondsWith->jsonSchema,
                $response->mockGetCastedContent(),
            )
        ;

        if (empty($jsonSchemaValidationResult->errors)) {
            $output->writeln('ok');

            return true;
        }

        $output->writeln('<error>error</error>');

        foreach ($jsonSchemaValidationResult->errors as $path => $errors) {
            foreach ($errors as $error) {
                $output->writeln(sprintf('%s -> %s', $path, $error));
            }
        }

        return false;
    }
}
