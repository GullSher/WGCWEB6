<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Generator;

use Staatic\Vendor\Ramsey\Uuid\Converter\TimeConverterInterface;
use Staatic\Vendor\Ramsey\Uuid\Provider\NodeProviderInterface;
use Staatic\Vendor\Ramsey\Uuid\Provider\TimeProviderInterface;
class TimeGeneratorFactory
{
    /**
     * @var \Staatic\Vendor\Ramsey\Uuid\Provider\NodeProviderInterface
     */
    private $nodeProvider;
    /**
     * @var TimeConverterInterface
     */
    private $timeConverter;
    /**
     * @var \Staatic\Vendor\Ramsey\Uuid\Provider\TimeProviderInterface
     */
    private $timeProvider;
    public function __construct(NodeProviderInterface $nodeProvider, TimeConverterInterface $timeConverter, TimeProviderInterface $timeProvider)
    {
        $this->nodeProvider = $nodeProvider;
        $this->timeConverter = $timeConverter;
        $this->timeProvider = $timeProvider;
    }
    public function getGenerator() : TimeGeneratorInterface
    {
        return new DefaultTimeGenerator($this->nodeProvider, $this->timeConverter, $this->timeProvider);
    }
}
