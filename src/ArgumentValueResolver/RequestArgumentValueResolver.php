<?php
declare(strict_types=1);

namespace Vim\Api\ArgumentValueResolver;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Vim\Api\Event\RequestArgumentValidated;
use Vim\Api\Request\RequestInterface;
use Vim\Api\Request\RequestSourceDataAwareInterface;
use Vim\Api\Service\ValidationService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RequestArgumentValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidationService $validationService,
        private EventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_subclass_of($argument->getType(), RequestInterface::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($request->isMethod('GET')) {
            $requestSourceData = $request->query->all();
            $requestData = $this->serializer->fromArray($requestSourceData, $argument->getType());
        } else {
            $requestSourceData = $request->getContent() ?: '{}';
            $requestData = $this->serializer->deserialize($requestSourceData, $argument->getType(), 'json');
        }

        if ($requestData instanceof RequestSourceDataAwareInterface) {
            $requestData->setRequestSourceData($requestSourceData);
        }

        $this->validationService->validateObject($requestData);
        $this->eventDispatcher->dispatch(new RequestArgumentValidated($request, $requestData));

        yield $requestData;
    }
}
