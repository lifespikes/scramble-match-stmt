<?php

namespace LifeSpikes\ScrambleMatchStmt\Traits;

use ReflectionClass;
use Dedoc\Scramble\Infer\Reflector\ClassReflector;

/**
 * Utility trait for managing class reflections. Scramble has an internal class reflection,
 * but we also need to use the native ReflectionClass from PHP in order to use the `hasMethod` method.
 *
 * We need the internal one so we can later on pass it over to the `FileNameResolver` that Scramble
 * Scopes need.
 *
 * @see ScrambleTypeCaster
 */
trait ReflectsResourceFile
{
    protected ClassReflector $reflector;
    protected ReflectionClass $reflection;

    protected function reflection(): ReflectionClass
    {
        return $this->reflector()->getReflection();
    }

    protected function reflector(): ClassReflector
    {
        return $this->reflector ??= ClassReflector::make($this->type->name);
    }
}
