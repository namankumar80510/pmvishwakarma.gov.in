<?php

namespace App\Lib\Git;

use Exception;

/**
 * Commits a message via shell_exec
 */
class Commit
{
    /**
     * The path to the Git repository.
     *
     * @var string
     */
    private $repositoryPath;

    /**
     * Creates a new Commit instance.
     *
     * @param string $repositoryPath The path to the Git repository.
     */
    public function __construct()
    {
        $this->repositoryPath = ROOT_DIR;
    }

    /**
     * Commits a message to the Git repository.
     *
     * @param string $message The commit message.
     * @return string The output of the Git commit command.
     * @throws Exception If the commit fails.
     */
    public function commit(string $message): string
    {
        // Sanitize the commit message to prevent command injection
        $sanitizedMessage = escapeshellarg($message);

        // Change to the repository directory
        $oldWorkingDir = getcwd();
        chdir($this->repositoryPath);

        // Execute the Git commit command
        $command = "git commit -m $sanitizedMessage";
        $output = shell_exec($command);

        // Restore the original working directory
        chdir($oldWorkingDir);

        // Check for errors
        if ($output === null || strpos($output, 'nothing to commit') !== false) {
            throw new Exception('Failed to commit: ' . $output);
        }

        return $output;
    }
}
