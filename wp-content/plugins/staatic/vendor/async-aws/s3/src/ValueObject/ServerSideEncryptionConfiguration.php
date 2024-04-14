<?php

namespace Staatic\Vendor\AsyncAws\S3\ValueObject;

final class ServerSideEncryptionConfiguration
{
    private $rules;
    public function __construct(array $input)
    {
        $this->rules = isset($input['Rules']) ? \array_map([ServerSideEncryptionRule::class, 'create'], $input['Rules']) : null;
    }
    public static function create($input) : self
    {
        return $input instanceof self ? $input : new self($input);
    }
    public function getRules() : array
    {
        return $this->rules ?? [];
    }
}
