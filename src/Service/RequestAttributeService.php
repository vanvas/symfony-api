<?php
declare(strict_types=1);

namespace Vim\Api\Service;

use Symfony\Component\HttpFoundation\Request;

class RequestAttributeService
{
    public function getAttributeForCurrentAction(Request $request, string $attribute): ?object
    {
        $attributes = $this->getAttributesForCurrentAction($request, $attribute);

        return $attributes[0] ?? null;
    }

    public function getAttributesForCurrentAction(Request $request, string $attribute): array
    {
        $currentAction = $this->getCurrentAction($request);

        $attributes = array_filter(
            $currentAction->getAttributes(),
            fn(\ReflectionAttribute $reflectionAttribute)
            => $reflectionAttribute->getName() === $attribute
                || is_subclass_of($reflectionAttribute->getName(), $attribute)
        );

        return array_map(
            fn(\ReflectionAttribute $reflectionAttribute) => $reflectionAttribute->newInstance(),
            array_values($attributes)
        );
    }

    private function getCurrentAction(Request $request): \ReflectionMethod
    {
        return $this->getCurrentController($request)->getMethod($this->getActionAndControllerFromRequest($request)['action']);
    }

    private function getCurrentController(Request $request): \ReflectionClass
    {
        return new \ReflectionClass($this->getActionAndControllerFromRequest($request)['controller']);
    }

    private function getActionAndControllerFromRequest(Request $request): array
    {
        preg_match('/(?<controller>.*)::(?<action>.*)/', $request->get('_controller'), $matches);

        return [
            'controller' => $matches['controller'],
            'action' => $matches['action'],
        ];
    }
}
