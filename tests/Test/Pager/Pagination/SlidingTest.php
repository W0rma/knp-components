<?php

namespace Test\Pager\Pagination;

use PHPUnit\Framework\Attributes\Test;
use Test\Tool\BaseTestCase;

final class SlidingTest extends BaseTestCase
{
    #[Test]
    public function shouldBeAbleToProducePagination(): void
    {
        $p = $this->getPaginatorInstance();

        $items = \range(1, 23);
        $view = $p->paginate($items, 1, 10);

        $view->renderer = static fn($data) => 'custom';
        $this->assertEquals('custom', (string) $view);

        $pagination = $view->getPaginationData();
        $this->assertEquals(3, $pagination['last']);
        $this->assertEquals(1, $pagination['first']);
        $this->assertEquals(1, $pagination['current']);
        $this->assertEquals(10, $pagination['numItemsPerPage']);
        $this->assertEquals(3, $pagination['pageCount']);
        $this->assertEquals(23, $pagination['totalCount']);
        $this->assertEquals(2, $pagination['next']);
        $this->assertEquals([1, 2, 3], $pagination['pagesInRange']);
        $this->assertEquals(1, $pagination['firstPageInRange']);
        $this->assertEquals(3, $pagination['lastPageInRange']);
        $this->assertEquals(10, $pagination['currentItemCount']);
        $this->assertEquals(1, $pagination['firstItemNumber']);
        $this->assertEquals(10, $pagination['lastItemNumber']);
    }

    #[Test]
    public function shouldBeAbleToProduceWiderPagination(): void
    {
        $p = $this->getPaginatorInstance();

        $items = \range(1, 43);
        $view = $p->paginate($items, 4, 5);
        $pagination = $view->getPaginationData();

        $this->assertEquals(9, $pagination['last']);
        $this->assertEquals(1, $pagination['first']);
        $this->assertEquals(4, $pagination['current']);
        $this->assertEquals(5, $pagination['numItemsPerPage']);
        $this->assertEquals(9, $pagination['pageCount']);
        $this->assertEquals(43, $pagination['totalCount']);
        $this->assertEquals(5, $pagination['next']);
        $this->assertEquals(3, $pagination['previous']);
        $this->assertEquals([2, 3, 4, 5, 6], $pagination['pagesInRange']);
        $this->assertEquals(2, $pagination['firstPageInRange']);
        $this->assertEquals(6, $pagination['lastPageInRange']);
        $this->assertEquals(5, $pagination['currentItemCount']);
        $this->assertEquals(16, $pagination['firstItemNumber']);
        $this->assertEquals(20, $pagination['lastItemNumber']);
    }

    #[Test]
    public function shouldHandleOddAndEvenRange(): void
    {
        $p = $this->getPaginatorInstance();

        $items = \range(1, 43);
        $view = $p->paginate($items, 4, 5);
        $view->setPageRange(4);
        $pagination = $view->getPaginationData();

        $this->assertEquals(3, $pagination['previous']);
        $this->assertEquals([3, 4, 5, 6], $pagination['pagesInRange']);
        $this->assertEquals(3, $pagination['firstPageInRange']);
        $this->assertEquals(6, $pagination['lastPageInRange']);

        $view->setPageRange(3);
        $pagination = $view->getPaginationData();

        $this->assertEquals(3, $pagination['previous']);
        $this->assertEquals([3, 4, 5], $pagination['pagesInRange']);
        $this->assertEquals(3, $pagination['firstPageInRange']);
        $this->assertEquals(5, $pagination['lastPageInRange']);
    }

    #[Test]
    public function shouldNotFallbackToPageInCaseIfExceedsItemLimit(): void
    {
        $p = $this->getPaginatorInstance();

        $view = $p->paginate(\range(1, 9), 2, 10);
        $items = $view->getItems();
        $this->assertEmpty($items);
    }
}
