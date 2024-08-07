<?php

namespace Test\Pager\Pagination;

use PHPUnit\Framework\Attributes\Test;
use Test\Tool\BaseTestCase;

final class TraversableItemsTest extends BaseTestCase
{
    #[Test]
    public function shouldBeAbleToUseTraversableItems(): void
    {
        $p = $this->getPaginatorInstance();

        $items = new \ArrayObject(\range(1, 23));
        $view = $p->paginate($items, 3, 10);

        $view->renderer = static fn($data) => 'custom';
        $this->assertEquals('custom', (string)$view);

        $items = $view->getItems();
        $this->assertInstanceOf(\ArrayObject::class, $items);
        $i = 21;
        foreach ($view as $item) {
            $this->assertEquals($i++, $item);
        }
    }
}
