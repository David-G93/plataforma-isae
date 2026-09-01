<?php

declare(strict_types=1);

$arguments = $argv;
array_shift($arguments);

if ($arguments === []) {
    fwrite(STDERR, "Usage: php scripts/dev/runtime.php <artisan|npm> [arguments...]\n");
    exit(1);
}

$projectRoot = dirname(__DIR__, 2);
$configuredPhp = getenv('ISAE_PHP_BINARY');
$windowsPhp = getenv('LOCALAPPDATA').DIRECTORY_SEPARATOR.'Programs'.DIRECTORY_SEPARATOR.'PHP'.DIRECTORY_SEPARATOR.'php.exe';
$phpBinary = $configuredPhp ?: (PHP_OS_FAMILY === 'Windows' && is_file($windowsPhp) ? $windowsPhp : PHP_BINARY);

if (! is_file($phpBinary) && $phpBinary !== PHP_BINARY) {
    fwrite(STDERR, "The configured ISAE_PHP_BINARY does not exist: {$phpBinary}\n");
    exit(1);
}

$phpVersion = trim((string) shell_exec(escapeshellarg($phpBinary).' -r "echo PHP_VERSION;"'));
if ($phpVersion === '' || version_compare($phpVersion, '8.4.1', '<')) {
    fwrite(STDERR, "Plataforma ISAE requires PHP 8.4.1 or newer. Found {$phpVersion}.\n");
    exit(1);
}

$pathKey = PHP_OS_FAMILY === 'Windows' ? 'Path' : 'PATH';
$existingPath = getenv($pathKey) ?: '';
putenv($pathKey.'='.dirname($phpBinary).PATH_SEPARATOR.$existingPath);

$command = array_shift($arguments);
if ($command === 'artisan') {
    $executable = $phpBinary;
    array_unshift($arguments, $projectRoot.DIRECTORY_SEPARATOR.'artisan');
} elseif ($command === 'npm') {
    $npmCli = getenv('npm_execpath');
    $nodeBinary = getenv('npm_node_execpath');

    if ($npmCli !== false && $nodeBinary !== false) {
        $executable = $nodeBinary;
        array_unshift($arguments, $npmCli);
    } else {
        $executable = PHP_OS_FAMILY === 'Windows' ? 'npm.cmd' : 'npm';
    }
} else {
    fwrite(STDERR, "Unsupported command: {$command}. Use artisan or npm.\n");
    exit(1);
}

$process = proc_open(
    array_merge([$executable], $arguments),
    [0 => STDIN, 1 => STDOUT, 2 => STDERR],
    $pipes,
    $projectRoot,
    null,
    ['bypass_shell' => true],
);

if (! is_resource($process)) {
    fwrite(STDERR, "Unable to start {$command}.\n");
    exit(1);
}

exit(proc_close($process));
