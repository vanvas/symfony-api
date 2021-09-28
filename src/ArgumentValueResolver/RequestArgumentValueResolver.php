<?php
declare(strict_types=1);

namespace Vim\Api\ArgumentValueResolver;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Vim\Api\Request\RequestInterface;
use Vim\Api\Service\ValidationService;

class RequestArgumentValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(private SerializerInterface $serializer, private ValidationService $validationService)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return is_subclass_of($argument->getType(), RequestInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        if ($request->isMethod('GET')) {
            $requestData = $this->serializer->fromArray($request->query->all(), $argument->getType());
        } else {
            $requestData = $this->serializer->deserialize($request->getContent() ?: '{}', $argument->getType(), 'json');
        }

        $this->validationService->validateObject($requestData);

        yield $requestData;
    }
}
