<?php

declare(strict_types=1);

namespace App\Console\Style;

use App\DependencyInjection\Compiler\DefinitionListConverterPass;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * @see DefinitionListConverterPass
 * @see DefinitionListConverterAwareTrait
 */
#[AutoconfigureTag(name: DefinitionListConverterPass::TAG)]
interface DefinitionListConverterAwareInterface
{
    /**
     * @param DefinitionListConverter $definitionListConverter
     * @return void
     * @internal
     */
    public function setDefinitionListConverter(DefinitionListConverter $definitionListConverter): void;
}
