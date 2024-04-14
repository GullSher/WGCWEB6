<?php

declare(strict_types=1);

namespace Staatic\WordPress\Factory;

use Staatic\Framework\ResourceRepository\FilesystemResourceRepository;
use Staatic\Framework\ResourceRepository\InMemoryResourceRepository;
use Staatic\Framework\ResourceRepository\ResourceRepositoryInterface;
use Staatic\WordPress\Setting\Advanced\WorkDirectorySetting;

final class ResourceRepositoryFactory
{
    /**
     * @var \Staatic\WordPress\Setting\Advanced\WorkDirectorySetting
     */
    private $workDirectory;

    public function __construct(WorkDirectorySetting $workDirectory)
    {
        $this->workDirectory = $workDirectory;
    }

    public function __invoke() : ResourceRepositoryInterface
    {
        $resourceDirectory = \untrailingslashit($this->workDirectory->value()) . '/resources';
        if (!\is_dir($resourceDirectory)) {
            if (!\mkdir($resourceDirectory, 0777, \true)) {
                return new InMemoryResourceRepository();
            }
        }

        return new FilesystemResourceRepository($resourceDirectory);
    }
}
