<?php

declare(strict_types=1);

namespace Staatic\WordPress\Publication;

use InvalidArgumentException;
use RuntimeException;
use Staatic\WordPress\Publication\Task\TaskInterface;

final class PublicationTaskProvider
{
    /** @var TaskInterface[] */
    private $tasks;

    /**
     * @var bool
     */
    private $initialized = \false;

    /**
     * @param iterable $publicationTasks
     */
    public function __construct(iterable $publicationTasks)
    {
        foreach ($publicationTasks as $task) {
            $this->tasks[\get_class($task)] = $task;
        }
        if (empty($this->tasks)) {
            throw new InvalidArgumentException('No tasks provided!');
        }
    }

    /**
     * @return TaskInterface[]
     */
    public function getTasks() : array
    {
        if (!$this->initialized) {
            $this->tasks = \apply_filters('staatic_publication_tasks', $this->tasks);
            $this->initialized = \true;
        }

        return $this->tasks;
    }

    public function getTask(string $taskName) : TaskInterface
    {
        $tasks = $this->getTasks();
        if (!isset($tasks[$taskName])) {
            throw new InvalidArgumentException("Task with name {$taskName} does not exist");
        }

        return $tasks[$taskName];
    }

    public function firstTask() : TaskInterface
    {
        $tasks = $this->getTasks();
        $firstTask = \array_shift($tasks);
        if (!$firstTask) {
            throw new RuntimeException('No publication tasks are configured');
        }

        return $firstTask;
    }

    public function nextTask(TaskInterface $currentTask)
    {
        $tasks = $this->getTasks();
        $keys = \array_keys($tasks);
        $index = \array_search(\get_class($currentTask), $keys);
        if (isset($keys[$index + 1])) {
            return $tasks[$keys[$index + 1]];
        } else {
            return null;
        }
    }

    public function nextSupportedTask(TaskInterface $currentTask, Publication $publication)
    {
        $task = $currentTask;
        do {
            $task = $this->nextTask($task);
        } while ($task && !$task->supports($publication));

        return $task;
    }
}
