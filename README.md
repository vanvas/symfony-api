## Installation

```shell
composer require vim/symfony-api
```

## Configuration

`config/packages/api.yaml`
```yaml
api:
  cors:
    allow_origin: '%env(CORS_ALLOW_ORIGIN)%'
    allow_headers: 'Authorization, Content-Type'
#    allow_origin: '^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
```

`api/config/bundles.php`
```PHP
<?php
return [
  // ...
  Vim\Api\ApiBundle::class => ['all' => true],
];
```

## Example

```PHP
<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Vim\Api\Attribute\Resource;
use Vim\Api\Attribute\Paginate;
use Vim\Api\Attribute\Filter;
use Vim\Api\Attribute\Hydrate;
use Vim\Api\Attribute\Validate;
use Vim\Api\Attribute\Flush;
use App\Entity\Post;

#[Route('/post')]
class PostController
{
    #[Route('')]
    #[Resource(Post::class)]
    #[Paginate]
    #[Filter\MultiSelect('category.group.id')]
    #[Filter\DateFrom('postedAt', 'postedAtFrom')]
    #[Filter\DateTo('postedAt', 'postedAtTo')]
    #[Filter\Like('content')]
    public function index(): void
    {
    }
    
    #[Route('', methods: ['POST'])]
    #[Resource('post')]
    #[Hydrate]
    #[Validate]
    #[Flush]
    public function create(Post $post): void
    {
    }

    #[Route('/{id}', methods: ['PUT'])]
    #[Resource('post')]
    #[Hydrate]
    #[Validate]
    #[Flush]
    public function update(Post $post): void
    {
    }

    #[Route('/{id}', methods: ['DELETE'])]
    #[Resource('post')]
    #[Flush]
    public function delete(Post $post): void
    {
    }
}
```
