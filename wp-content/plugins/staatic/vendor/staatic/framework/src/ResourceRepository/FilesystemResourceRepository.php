<?php

namespace Staatic\Framework\ResourceRepository;

use Staatic\Vendor\GuzzleHttp\Psr7\StreamWrapper;
use InvalidArgumentException;
use Staatic\Vendor\Psr\Log\NullLogger;
use Staatic\Vendor\Psr\Log\LoggerAwareInterface;
use Staatic\Vendor\Psr\Log\LoggerAwareTrait;
use RuntimeException;
use Staatic\Framework\Resource;
use Staatic\Vendor\Symfony\Component\Filesystem\Filesystem;
final class FilesystemResourceRepository implements ResourceRepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var string
     */
    private $targetDirectory;
    public function __construct(string $targetDirectory)
    {
        $this->logger = new NullLogger();
        $this->filesystem = new Filesystem();
        $this->setTargetDirectory($targetDirectory);
    }
    private function setTargetDirectory(string $targetDirectory) : void
    {
        if (!\is_dir($targetDirectory)) {
            throw new InvalidArgumentException("Target directory does not exist in {$targetDirectory}");
        }
        $this->targetDirectory = \rtrim($targetDirectory, '/');
    }
    /**
     * @param Resource $resource
     */
    public function write($resource) : void
    {
        $this->logger->debug("Writing resource with sha1 #{$resource->sha1()}");
        $this->filesystem->dumpFile($this->resourcePath($resource->sha1()), StreamWrapper::getResource($resource->content()));
        $this->logger->debug("Wrote resource with sha1 {$resource->sha1()} ({$resource->size()} bytes)");
    }
    /**
     * @param string $sha1
     */
    public function find($sha1)
    {
        $resourcePath = $this->resourcePath($sha1);
        if (!\is_readable($resourcePath)) {
            return null;
        }
        return Resource::create(\fopen($resourcePath, 'r+'));
    }
    /**
     * @param string $sha1
     */
    public function delete($sha1) : void
    {
        $resourcePath = $this->resourcePath($sha1);
        if (!\is_readable($resourcePath)) {
            \clearstatcache();
            if (!\is_readable($resourcePath)) {
                throw new RuntimeException("Unable to find resource with sha1 {$sha1}");
            }
        }
        $this->filesystem->remove($resourcePath);
    }
    private function resourcePath(string $sha1) : string
    {
        return \sprintf('%s/%s/%s', $this->targetDirectory, \substr($sha1, 0, 1), \substr($sha1, 1));
    }
}
