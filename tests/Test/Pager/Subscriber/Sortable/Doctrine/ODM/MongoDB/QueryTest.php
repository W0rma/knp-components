<?php

namespace Test\Pager\Subscriber\Sortable\Doctrine\ODM\MongoDB;

use Knp\Component\Pager\ArgumentAccess\RequestArgumentAccess;
use Knp\Component\Pager\Event\Subscriber\Paginate\PaginationSubscriber;
use Knp\Component\Pager\Event\Subscriber\Sortable\Doctrine\ODM\MongoDB\QuerySubscriber as Sortable;
use Knp\Component\Pager\Paginator;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Test\Fixture\Document\Article;
use Test\Tool\BaseTestCaseMongoODM;

final class QueryTest extends BaseTestCaseMongoODM
{
    #[Test]
    public function shouldSortSimpleDoctrineQuery(): void
    {
        $this->populate();

        $dispatcher = new EventDispatcher;
        $dispatcher->addSubscriber(new PaginationSubscriber);
        $dispatcher->addSubscriber(new Sortable($this->createRequestStack([])->getCurrentRequest()));
        $requestStack = $this->createRequestStack(['sort' => 'title', 'direction' => 'asc']);
        $accessor = new RequestArgumentAccess($requestStack);
        $p = new Paginator($dispatcher, $accessor);

        $qb = $this->dm->createQueryBuilder(Article::class);
        $query = $qb->getQuery();
        $view = $p->paginate($query, 1, 10);

        $items = \array_values($view->getItems());
        $this->assertCount(4, $items);
        $this->assertEquals('autumn', $items[0]->getTitle());
        $this->assertEquals('spring', $items[1]->getTitle());
        $this->assertEquals('summer', $items[2]->getTitle());
        $this->assertEquals('winter', $items[3]->getTitle());

        $requestStack = $this->createRequestStack(['sort' => 'title', 'direction' => 'desc']);
        $accessor = new RequestArgumentAccess($requestStack);
        $p = new Paginator($dispatcher, $accessor);
        $view = $p->paginate($query, 1, 10);
        $items = \array_values($view->getItems());
        $this->assertCount(4, $items);
        $this->assertEquals('winter', $items[0]->getTitle());
        $this->assertEquals('summer', $items[1]->getTitle());
        $this->assertEquals('spring', $items[2]->getTitle());
        $this->assertEquals('autumn', $items[3]->getTitle());

        $requestStack = $this->createRequestStack(['sort' => 'status+created_at', 'direction' => 'desc']);
        $accessor = new RequestArgumentAccess($requestStack);
        $p = new Paginator($dispatcher, $accessor);
        $view = $p->paginate($query);
        $items = \array_values($view->getItems());
        $this->assertCount(4, $items);
        $this->assertEquals('autumn', $items[0]->getTitle());
        $this->assertEquals('summer', $items[1]->getTitle());
        $this->assertEquals('winter', $items[2]->getTitle());
        $this->assertEquals('spring', $items[3]->getTitle());
    }

    #[Test]
    public function shouldSortOnAnyField(): void
    {
        $query = $this
            ->getMockDocumentManager()
            ->createQueryBuilder(Article::class)
            ->getQuery()
        ;

        $requestStack = $this->createRequestStack(['sort' => '"title\'', 'direction' => 'asc']);
        $p = $this->getPaginatorInstance($requestStack);
        $view = $p->paginate($query, 1, 10);
    }

    private function populate(): void
    {
        $em = $this->getMockDocumentManager();
        $summer = new Article;
        $summer->setTitle('summer');
        $summer->setStatus('published');
        $summer->setCreatedAt(new \DateTime('2016-06-06'));

        $winter = new Article;
        $winter->setTitle('winter');
        $summer->setStatus('draft');
        $summer->setCreatedAt(new \DateTime('2019-09-09'));

        $autumn = new Article;
        $autumn->setTitle('autumn');
        $summer->setStatus('published');
        $summer->setCreatedAt(new \DateTime('2018-08-08'));

        $spring = new Article;
        $spring->setTitle('spring');
        $summer->setStatus('draft');
        $summer->setCreatedAt(new \DateTime('2017-07-07'));

        $em->persist($summer);
        $em->persist($winter);
        $em->persist($autumn);
        $em->persist($spring);
        $em->flush();
    }
}
