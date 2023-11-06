<?php

namespace LifeSpikes\ScrambleMatchStmt\Traits;

use PhpParser\Node\Expr\ArrayItem;
use Dedoc\Scramble\Infer\Scope\Scope;
use Dedoc\Scramble\Support\Type\StringType;
use Dedoc\Scramble\Infer\Scope\ScopeContext;
use Dedoc\Scramble\Support\Type\ArrayItemType_;
use Dedoc\Scramble\Infer\Scope\NodeTypesResolver;
use Dedoc\Scramble\Infer\Analyzer\MethodAnalyzer;
use Dedoc\Scramble\Infer\Handler\ArrayItemHandler;
use Dedoc\Scramble\Infer\Services\FileNameResolver;
use Dedoc\Scramble\Support\Generator\Types\UnknownType;

/**
 * This is our ScrambleTypeCaster trait. Everything here is meant for transforming
 * resulting values from our static analysis using PhpParser over to types that Scramble
 * understands.
 */
trait ScrambleTypeCaster
{
    /**
     * Here, we leverage some of Scramble's internal functionality to use the AST nodes
     * that we received earlier in our file parsing and turn it into types Scramble can understand.
     *
     * First we instantiate a local scope, necessary for Scramble to cast types from PhpParser,
     * then, if any of the types results in an "UnknownType", we default it to a "StringType" to
     * avoid errors. Everything else is passed as-is.
     *
     * @see ArrayItemHandler
     * @param ArrayItem[] $items
     * @return ArrayItemType_[]
     */
    protected function castToScrambleTypes(array $items): array
    {
        $scope = $this->getUsableScope();

        return collect($items)
            ->filter()
            ->map(function (ArrayItem $arrayItem) use ($scope) {
                $type = $scope->getType($arrayItem->value);

                if ($type instanceof UnknownType) {
                    $type = new StringType();
                }

                return new ArrayItemType_(
                    $arrayItem->key->value,
                    $type,
                    false,
                    $arrayItem->unpack
                );
            })
            ->all();
    }

    /**
     * This is some logic we took from a method analyzer in scramble. Proper instantiation of a scope
     * is necessary for easy casting of types.
     *
     * @see MethodAnalyzer
     */
    protected function getUsableScope(): Scope
    {
        $classDefinition = $this->infer->analyzeClass($this->type->name);
        $nameResolver = new FileNameResolver($this->reflector()->getNameContext());
        return new Scope($this->infer->index, new NodeTypesResolver(), new ScopeContext($classDefinition), $nameResolver);
    }
}
