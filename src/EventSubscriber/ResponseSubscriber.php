<?php
declare(strict_types=1);

namespace Vim\Api\EventSubscriber;

use JMS\Serializer\SerializationContext;
use Symfony\Component\Security\Core\Security;
use Vim\Api\Attribute\Collection;
use Vim\Api\Attribute\Filter\FilterInterface;
use Vim\Api\Attribute\Groups;
use Vim\Api\Attribute\Paginate;
use Vim\Api\Attribute\Resource;
use Vim\Api\Exception\InvalidArgumentException;
use Vim\Api\Exception\LogicException;
use Vim\Api\Exception\ResponseInterface;
use Vim\Api\Service\QueryFilter\QueryService;
use Vim\Api\Service\RequestAttributeService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RequestAttributeService $requestAttributeService,
        private SerializerInterface $serializer,
        private QueryService $queryService,
        private Security $security,
    ) {}

    public function onKernelView(ViewEvent $event)
    {
        $data = $event->getControllerResult();
        if ($data instanceof Response) {
            return;
        }

        $request = $event->getRequest();
        $result = [
            'data' => $data,
        ];

        /** @var Collection|null $collectionAttribute */
        $collectionAttribute = $this
            ->requestAttributeService
            ->getAttributeForCurrentAction($request, Collection::class);
        if ($collectionAttribute) {
            /** @var Resource|null $resource */
            $resourceAttribute = $this->requestAttributeService
                ->getAttributeForCurrentAction($request, Resource::class);
            if (!$resourceAttribute) {
                throw new LogicException(
                    'Resource not found or has not been configured. Use ' . Resource::class . ' for configuration.'
                );
            }

            $sortBy = $request->get('sortBy');
            $orderDesc = (bool) $request->get('orderDesc');

            $query = $this->queryService
                ->getGlobalQuery(
                    $resourceAttribute->entity,
                    $request->get('filter', []),
                    $this->requestAttributeService->getAttributesForCurrentAction($request, FilterInterface::class),
                    $sortBy,
                    $orderDesc
                );

            $meta = [
                'sortBy' => $sortBy,
                'orderDesc' => $orderDesc,
            ];

            if ($collectionAttribute instanceof Paginate) {
                $page = (int) $request->get('page', 1);
                if ($page < 1) {
                    throw new InvalidArgumentException('Expected page to be 1 or more. Got "' . $page . '"');
                }

                $perPage = $request->get('perPage', $collectionAttribute->perPage);

                $query
                    ->setMaxResults($perPage)
                    ->setFirstResult(($page - 1) * $perPage);

                $paginator = new Paginator($query);

                $meta = array_merge(
                    $meta,
                    [
                        'total' => $paginator->count(),
                        'page' => $page,
                        'perPage' => (int)$perPage,
                    ]
                );
            }

            $result['data'] = $query->getResult();
            $result['meta'] = $meta;
        }

        if ($data instanceof ResponseInterface) {
            $result = $data;
        }

        $authUser = $this->security->getUser();
        /** @var Groups|null $resource */
        $groupsAttribute = $this->requestAttributeService
            ->getAttributeForCurrentAction($request, Groups::class);
        $groups = $groupsAttribute ? array_merge($groupsAttribute->groups, $authUser?->getRoles() ?? []) : [];
        $serializationContext = $groups ? SerializationContext::create()->setGroups($groups) : null;

        $responseData = $this->serializer->toArray($result, $serializationContext);
        $event->setResponse(new JsonResponse($responseData));
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onKernelView', 30]
        ];
    }
}
