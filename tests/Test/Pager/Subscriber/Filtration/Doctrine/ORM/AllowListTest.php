<?php

namespace Test\Pager\Subscriber\Filtration\Doctrine\ORM;

use Knp\Component\Pager\ArgumentAccess\RequestArgumentAccess;
use Knp\Component\Pager\Event\Subscriber\Filtration\FiltrationSubscriber as Filtration;
use Knp\Component\Pager\Event\Subscriber\Paginate\PaginationSubscriber;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Test\Fixture\Entity\Article;
use Test\Tool\BaseTestCaseORM;

final class AllowListTest extends BaseTestCaseORM
{
    #[Test]
    public function shouldAllowListFiltrationFields(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->populate();
        $query = $this->em->createQuery('SELECT a FROM Test\Fixture\Entity\Article a');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new PaginationSubscriber());
        $dispatcher->addSubscriber(new Filtration());
        $requestStack = $this->createRequestStack(['filterParam' => 'a.title', 'filterValue' => 'summer']);
        $accessor = new RequestArgumentAccess($requestStack);
        $p = new Paginator($dispatcher, $accessor);

        $filterFieldAllowList = ['a.invalid'];
        $view = $p->paginate($query, 1, 10, \compact(PaginatorInterface::FILTER_FIELD_ALLOW_LIST));

        $items = $view->getItems();
        self::assertCount(1, $items);
        self::assertEquals('summer', $items[0]->getTitle());

        $requestStack = $this->createRequestStack(['filterParam' => 'a.id', 'filterValue' => 'summer']);
        $accessor = new RequestArgumentAccess($requestStack);
        $p = new Paginator($dispatcher, $accessor);
        $p->paginate($query, 1, 10, \compact(PaginatorInterface::FILTER_FIELD_ALLOW_LIST));
    }

    #[Test]
    public function shouldFilterWithoutSpecificAllowList(): void
    {
        $this->populate();
        $query = $this->em->createQuery('SELECT a FROM Test\Fixture\Entity\Article a');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new PaginationSubscriber());
        $dispatcher->addSubscriber(new Filtration());
        $requestStack = $this->createRequestStack(['filterParam' => 'a.title', 'filterValue' => 'autumn']);
        $accessor = new RequestArgumentAccess($requestStack);
        $p = new Paginator($dispatcher, $accessor);
        $view = $p->paginate($query, 1, 10);

        $items = $view->getItems();
        self::assertEquals('autumn', $items[0]->getTitle());
    }

    #[Test]
    public function shouldFilterWithoutSpecificAllowList2(): void
    {
        $this->populate();
        $query = $this->em->createQuery('SELECT a FROM Test\Fixture\Entity\Article a');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new PaginationSubscriber());
        $dispatcher->addSubscriber(new Filtration());

        $requestStack = $this->createRequestStack(['filterParam' => 'a.id', 'filterValue' => 'autumn']);
        $accessor = new RequestArgumentAccess($requestStack);
        $p = new Paginator($dispatcher, $accessor);
        $view = $p->paginate($query);

        $items = $view->getItems();
        self::assertCount(0, $items);
    }

    protected function getUsedEntityFixtures(): array
    {
        return [Article::class];
    }

    private function populate(): void
    {
        $em = $this->getMockSqliteEntityManager();
        $summer = new Article();
        $summer->setTitle('summer');

        $winter = new Article();
        $winter->setTitle('winter');

        $autumn = new Article();
        $autumn->setTitle('autumn');

        $spring = new Article();
        $spring->setTitle('spring');

        $em->persist($summer);
        $em->persist($winter);
        $em->persist($autumn);
        $em->persist($spring);
        $em->flush();
    }
}
