<?php

class ApacheHttpParser
{
    /**
     * Разбирает конфигурационный файл Apache.
     *
     * @param string $filePath Путь к файлу конфигурации.
     * @return array Массив виртуальных хостов с параметрами.
     * @throws Exception Если файл не найден или не удается его прочитать.
     */
    public function parse(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("Файл не найден: $filePath");
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new Exception("Не удалось прочитать файл: $filePath");
        }

        return $this->parseVirtualHosts($content);
    }

    /**
     * Разбирает содержимое файла конфигурации для виртуальных хостов.
     *
     * @param string $content Содержимое конфигурационного файла.
     * @return array Массив виртуальных хостов.
     */
    private function parseVirtualHosts(string $content): array
    {
        $hosts = [];
        $pattern = '/<VirtualHost\s+(.*?)>(.*?)<\/VirtualHost>/is';

        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $hostConfig = $this->parseHostBlock($match[2]);
                $hostConfig['VirtualHost'] = $match[1]; // IP и порт (например, "*:80" или "192.168.1.1:443")
                $hosts[] = $hostConfig;
            }
        }

        return $hosts;
    }

    /**
     * Разбирает блок VirtualHost.
     *
     * @param string $block Содержимое блока VirtualHost.
     * @return array Массив параметров виртуального хоста.
     */
    private function parseHostBlock(string $block): array
    {
        $config = [];
        $lines = explode("\n", $block);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                // Пропускаем пустые строки и комментарии
                continue;
            }

            if (preg_match('/^(\w+)\s+(.*)$/', $line, $matches)) {
                $directive = $matches[1];
                $value = trim($matches[2]);

                if (isset($config[$directive])) {
                    if (!is_array($config[$directive])) {
                        $config[$directive] = [$config[$directive]];
                    }
                    $config[$directive][] = $value;
                } else {
                    $config[$directive] = $value;
                }
            }
        }

        return $config;
    }
}

