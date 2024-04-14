<?php

namespace Staatic\Vendor\AsyncAws\CloudFront\ValueObject;

use DateTimeImmutable;
final class Invalidation
{
    private $id;
    private $status;
    private $createTime;
    private $invalidationBatch;
    public function __construct(array $input)
    {
        $this->id = $input['Id'] ?? null;
        $this->status = $input['Status'] ?? null;
        $this->createTime = $input['CreateTime'] ?? null;
        $this->invalidationBatch = isset($input['InvalidationBatch']) ? InvalidationBatch::create($input['InvalidationBatch']) : null;
    }
    public static function create($input) : self
    {
        return $input instanceof self ? $input : new self($input);
    }
    public function getCreateTime() : DateTimeImmutable
    {
        return $this->createTime;
    }
    public function getId() : string
    {
        return $this->id;
    }
    public function getInvalidationBatch() : InvalidationBatch
    {
        return $this->invalidationBatch;
    }
    public function getStatus() : string
    {
        return $this->status;
    }
}
