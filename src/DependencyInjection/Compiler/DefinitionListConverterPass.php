<?php

declare(strict_types=1);

namespace App\DependencyInjection\Compiler;

use App\Console\Style\DefinitionListConverter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @see \App\Console\Style\DefinitionListConverterAwareInterface
 * @codeCoverageIgnore
 */
final readonly class DefinitionListConverterPass implements CompilerPassInterface
{
    public const string TAG = 'app.definition_list_converter';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(DefinitionListConverter::class)) {
            return;
        }

        $taggedProviders = $container->findTaggedServiceIds(self::TAG);

        foreach (array_keys($taggedProviders) as $id) {
            $container->findDefinition($id)
                ->addMethodCall(
                    'setDefinitionListConverter',
                    [new Reference(DefinitionListConverter::class)]
                );
        }
    }
}
