<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\Command;

use Distantmagic\Resonance\CoroutineCommand;
use Distantmagic\Resonance\LlamaCppClient;
use Distantmagic\Resonance\SwooleConfiguration;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class LlamaCppGenerate extends CoroutineCommand
{
    abstract protected function executeLlamaCppCommand(InputInterface $input, OutputInterface $output, string $prompt): int;

    public function __construct(
        protected LlamaCppClient $llamaCppClient,
        SwooleConfiguration $swooleConfiguration,
    ) {
        parent::__construct($swooleConfiguration);
    }

    protected function configure(): void
    {
        $this->addArgument(
            name: 'prompt',
            mode: InputArgument::OPTIONAL,
            default: 'How to make a cat happy? Be brief, respond in 1 sentence.',
        );
    }

    protected function executeInCoroutine(InputInterface $input, OutputInterface $output): int
    {
        /**
         * @var string $prompt
         */
        $prompt = $input->getArgument('prompt');

        return $this->executeLlamaCppCommand($input, $output, $prompt);
    }
}