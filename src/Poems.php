<?php

declare(strict_types=1);

namespace Youvision\ThinkStoreExample;

use RuntimeException;

class Poems
{
    protected static array $cache = [];

    protected string $file;

    public function __construct(array $config = [])
    {
        $this->file = trim((string) ($config['data_file'] ?? ''));
    }

    public function all(): array
    {
        $file = $this->file !== '' ? $this->file : dirname(__DIR__) . '/data/300.json';

        if (isset(self::$cache[$file])) {
            return self::$cache[$file];
        }

        if (!is_file($file) || !is_readable($file)) {
            throw new RuntimeException('诗库文件不存在或不可读：' . $file);
        }

        $data = json_decode((string) file_get_contents($file), true);

        if (!is_array($data)) {
            throw new RuntimeException('诗库数据格式错误：' . $file);
        }

        $poems = array_values(array_filter($data, static fn ($item): bool => is_array($item)));

        return self::$cache[$file] = $poems;
    }

    public function count(): int
    {
        return count($this->all());
    }

    public function random(): array
    {
        $poems = $this->all();

        if ($poems === []) {
            throw new RuntimeException('诗库数据为空');
        }

        return $poems[random_int(0, count($poems) - 1)];
    }
}