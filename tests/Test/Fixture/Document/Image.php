<?php

namespace Test\Fixture\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * @ODM\Document
 */
#[ODM\Document]
final class Image
{
    /**
     * @ODM\Id
     */
    #[ODM\Id]
    private $id;

    /**
     * @ODM\Field
     */
    #[ODM\Field]
    private ?string $title = null;

    /**
     * @ODM\File
     * @var int|string
     */
    #[ODM\File]
    private $file;

    /**
     * @param int|string $file
     */
    public function setFile($file): void
    {
        $this->file = $file;
    }

    /**
     * @return int|string
     */
    public function getFile()
    {
        return $this->file;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
