<?php

namespace Infirsoft\Utils;

use ReflectionClass;

abstract class Cast
{
    /**
     * @template T
     * @param string $class
     * @param mixed $source
     * @return T
     */
    public static function to(string $class, $source)
    {
        if (is_string($source)) {
            $source = json_decode($source, true);
        }
        if ($source instanceof \Psr\Http\Message\ResponseInterface) {
            $source = json_decode($source->getBody()->getContents(), true);
        }
        if (!is_array($source)) {
            $source = (array)$source;
        }

        $content = new $class;
        $reflector = new ReflectionClass($class);

        if ($content instanceof \ArrayIterator) {
            $methodReflector = $reflector->getMethod('current');
            if ($methodReflector->getReturnType()->isBuiltin() === false) {
                foreach ($source as $item) {
                    $childClass = $methodReflector->getReturnType()->getName();
                    $content[] = static::to($childClass, $item);
                }
            } else {
                foreach ($source as $item) {
                    $content[] = $item;
                }
            }

            return $content;
        }

        foreach ($source as $property => $value) {
            $propertyReflector = $reflector->getProperty($property);
            if ($propertyReflector->getType()->isBuiltin() === false) {
                $childClass = $propertyReflector->getType()->getName();
                $propertyReflector->setValue($content, static::to($childClass, $value));
                continue;
            }
            $propertyReflector->setValue($content, $value);
        }

        return $content;
    }
}
