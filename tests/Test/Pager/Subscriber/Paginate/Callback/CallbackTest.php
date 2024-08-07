<?php

use Knp\Component\Pager\ArgumentAccess\ArgumentAccessInterface;
use Knp\Component\Pager\Event\Subscriber\Paginate\Callback\CallbackPagination;
use Knp\Component\Pager\Event\Subscriber\Paginate\Callback\CallbackSubscriber;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Paginator;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Test\Mock\PaginationSubscriber as MockPaginationSubscriber;
use Test\Tool\BaseTestCase;

final class CallbackTest extends BaseTestCase
{
    public const ITEMS_PER_PAGE = 10;
    public const TOTAL_NUMBER_OF_ITEMS = 35;

    #[Test]
    public function shouldPaginate(): void
    {
        $p = $this->givenPaginatorConfigured();
        $items = $this->givenCallbackPagination();
        $page = 1;

        $view = $p->paginate($items, $page, self::ITEMS_PER_PAGE);
        $this->assertInstanceOf(PaginationInterface::class, $view);

        $this->assertEquals($page, $view->getCurrentPageNumber());
        $this->assertEquals(self::ITEMS_PER_PAGE, $view->getItemNumberPerPage());
        $this->assertCount(self::ITEMS_PER_PAGE, $view->getItems());
        $this->assertEquals([1,2,3,4,5,6,7,8,9,10], $view->getItems());
        $this->assertEquals(self::TOTAL_NUMBER_OF_ITEMS, $view->getTotalItemCount());
    }

    #[Test]
    public function shouldSlicePaginate(): void
    {
        $p = $this->givenPaginatorConfigured();
        $items = $this->givenCallbackPagination();
        $page = 2;

        $view = $p->paginate($items, $page, self::ITEMS_PER_PAGE);

        $this->assertEquals($page, $view->getCurrentPageNumber());
        $this->assertEquals(self::ITEMS_PER_PAGE, $view->getItemNumberPerPage());
        $this->assertCount(self::ITEMS_PER_PAGE, $view->getItems());
        $this->assertEquals([11,12,13,14,15,16,17,18,19,20], $view->getItems());
        $this->assertEquals(self::TOTAL_NUMBER_OF_ITEMS, $view->getTotalItemCount());
    }

    private function givenPaginatorConfigured(): Paginator
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new CallbackSubscriber);
        $dispatcher->addSubscriber(new MockPaginationSubscriber); // pagination view
        $accessor = $this->createMock(ArgumentAccessInterface::class);

        return new Paginator($dispatcher, $accessor);
    }

    private function givenCallbackPagination(): CallbackPagination
    {
        $data = \range(1, self::TOTAL_NUMBER_OF_ITEMS);

        $count = fn() => \count($data);
        $items = fn($offset, $limit) => \array_slice($data, $offset, $limit);

        return new CallbackPagination($count, $items);
    }
}
