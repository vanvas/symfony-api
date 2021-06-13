<?php
declare(strict_types=1);

namespace Vim\Api\Decorator\SensioFrameworkExtraBundle;

use Vim\Api\Attribute\Resource;
use Vim\Api\Service\RequestAttributeService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter as DoctrineParamConverterSource;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class DoctrineParamConverter implements ParamConverterInterface
{
    public function __construct(
        private DoctrineParamConverterSource $source,
        private RequestAttributeService $requestAttributeService
    )
    {}

    /**
     * @inheritDoc
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        try {
            $result = $this->source->apply($request, $configuration);
        } catch (\Throwable $throwable) {
            if (!$request->isMethod('POST')) {
                throw $throwable;
            }

            $result = false;
            /** @var Resource|null $resourceAttribute */
            $resourceAttribute = $this->requestAttributeService->getAttributeForCurrentAction($request, Resource::class);
            if ($resourceAttribute && $resourceAttribute->entity === $configuration->getName()) {
                $class = $configuration->getClass();
                $request->attributes->set($configuration->getName(), new $class);
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function supports(ParamConverter $configuration)
    {
        return $this->source->supports($configuration);
    }
}
