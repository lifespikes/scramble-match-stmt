<?php

namespace LifeSpikes\ScrambleMatchStmt;

use Dedoc\Scramble\Support\Type\Type;
use Dedoc\Scramble\Support\Type\ArrayType;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\TypeHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use Dedoc\Scramble\Extensions\TypeToSchemaExtension;
use LifeSpikes\ScrambleMatchStmt\Traits\AnalyzesMatchStmt;
use LifeSpikes\ScrambleMatchStmt\Traits\ParsesResourceFile;
use LifeSpikes\ScrambleMatchStmt\Traits\ScrambleTypeCaster;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JsonResourceWithMatchExtension extends TypeToSchemaExtension
{
    use ParsesResourceFile;
    use AnalyzesMatchStmt;
    use ScrambleTypeCaster;

    protected Type $type;

    public function shouldHandle(Type $type)
    {
        $this->type = $type;

        /**
         * This function checks if the
         */
        return $type instanceof ObjectType
            && $type->isInstanceOf(JsonResource::class)
            && ! $type->isInstanceOf(AnonymousResourceCollection::class)
            && $this->containsMatchInToArray();
    }

    public function toSchema(Type $type)
    {
        /**
         * We call our type caster, and then unpack it (Prepare it so Spotlight can show nested structures).
         */
        $array = TypeHelper::unpackIfArrayType(new ArrayType(
            $this->castToScrambleTypes($this->firstMatch->items)
        ));

        /**
         * Now we transform the Scramble type to an OpenAPI type.
         */
        return $this->openApiTransformer->transform($array);
    }

    /**
     * This method checks if the return type we're dealing with has a toArray method,
     * additionally, it verifies that the value of the first arm of the match statement
     * is the type we expect it to be.
     *
     * @return bool
     */
    protected function containsMatchInToArray(): bool
    {
        return $this->reflection()->hasMethod('toArray') && $this->getFirstMatchValue();
    }
}
