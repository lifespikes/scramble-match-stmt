<?php

namespace LifeSpikes\ScrambleMatchStmt;

use Dedoc\Scramble\Support\RouteInfo;
use Dedoc\Scramble\Support\Type\Type;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\Generator\Components;
use Dedoc\Scramble\Extensions\OperationExtension;

class ResourceWithMatchExtension extends OperationExtension
{
    protected JsonResourceWithMatchExtension $extension;
    protected Components $components;

    public function handle(Operation $operation, RouteInfo $routeInfo)
    {
        /**
         * Scramble is prioritizing their own extensions over ours apparently, so we are going with a more
         * forceful approach by overriding the 200 response body.
         *
         * What we're doing here essentially is manually running the shouldHandle check in our extension, if it
         * doesn't need to handle it, we just do nothing.
         */
        if (!$this->shouldExecuteExtension($type = $routeInfo->getReturnType())) {
            return;
        }

        /**
         * If we reached here, we should handle. What we do now is create a new 200 response and add it
         * to the operation. From the looks of it, it will force Scramble to override any existing 200 responses
         * already in the operation.
         */
        $operation->addResponse(
            Response::make(200)
                ->description('`'.$this->components->uniqueSchemaName($type->name).'`')
                ->setContent(
                    'application/json',
                    Schema::fromType($this->extension->toSchema($type))
                )
        );
    }

    public function shouldExecuteExtension(Type $type): bool
    {
        $this->extension = new JsonResourceWithMatchExtension(
            $this->infer,
            $this->openApiTransformer,
            $this->components = $this->openApiTransformer->getComponents()
        );

        return $this->extension->shouldHandle($type);
    }
}
