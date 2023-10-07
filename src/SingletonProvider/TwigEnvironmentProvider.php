<?php

declare(strict_types=1);

namespace Distantmagic\Resonance\SingletonProvider;

use Distantmagic\Resonance\Attribute\Singleton;
use Distantmagic\Resonance\PHPProjectFiles;
use Distantmagic\Resonance\SingletonContainer;
use Distantmagic\Resonance\SingletonProvider;
use Distantmagic\Resonance\TwigBridgeExtension;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Twig\Cache\FilesystemCache;
use Twig\Environment as TwigEnvironment;
use Twig\Loader\FilesystemLoader;

/**
 * @template-extends SingletonProvider<TwigEnvironment>
 */
#[Singleton(provides: TwigEnvironment::class)]
final readonly class TwigEnvironmentProvider extends SingletonProvider
{
    public function __construct(private TwigBridgeExtension $twigBridgeExtension) {}

    public function provide(SingletonContainer $singletons, PHPProjectFiles $phpProjectFiles): TwigEnvironment
    {
        $cacheDirectory = DM_ROOT.'/cache/twig';

        $filesystem = new Filesystem();
        $filesystem->remove($cacheDirectory);

        $loader = new FilesystemLoader(DM_APP_ROOT.'/views');
        $cache = new FilesystemCache($cacheDirectory, FilesystemCache::FORCE_BYTECODE_INVALIDATION);

        $environment = new TwigEnvironment($loader, [
            'cache' => $cache,
            'strict_variables' => false,
        ]);

        $environment->addExtension($this->twigBridgeExtension);

        $this->warmupCache($environment);

        return $environment;
    }

    private function warmupCache(TwigEnvironment $environment): void
    {
        $finder = new Finder();
        $found = $finder
            ->files()
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->ignoreVCS(true)
            ->name('*.twig')
            ->in(DM_APP_ROOT.'/views')
        ;

        foreach ($found as $template) {
            $environment->load($template->getRelativePathname());
        }
    }
}
