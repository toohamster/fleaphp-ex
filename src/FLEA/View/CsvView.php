<?php

namespace FLEA\View;

/**
 * CSV 数据视图
 *
 * 用于导出 CSV 文件，支持自定义分隔符和文件名
 * 支持 Excel 兼容模式（添加 UTF-8 BOM，让 Excel 正确识别中文）
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.1.0
 */
class CsvView implements ViewInterface
{
    /**
     * @var array 数据行
     */
    private array $rows;

    /**
     * @var string 分隔符
     */
    private string $delimiter;

    /**
     * @var string 文件名
     */
    private string $filename;

    /**
     * @var bool Excel 兼容模式
     */
    private bool $excelCompatible;

    /**
     * 构造函数
     *
     * @param array $rows 数据行
     * @param string $delimiter 分隔符
     * @param string $filename 文件名
     * @param bool $excelCompatible Excel 兼容模式
     */
    public function __construct(
        array $rows,
        string $delimiter = ',',
        string $filename = 'export.csv',
        bool $excelCompatible = false
    ) {
        $this->rows = $rows;
        $this->delimiter = $delimiter;
        $this->filename = $filename;
        $this->excelCompatible = $excelCompatible;
    }

    /**
     * 获取内容类型
     *
     * @return string
     */
    public function getContentType(): string
    {
        return $this->excelCompatible
            ? 'application/vnd.ms-excel'
            : 'text/csv';
    }

    /**
     * 获取 CSV 内容
     *
     * @return string
     */
    public function getContent(): string
    {
        $output = fopen('php://memory', 'w');

        // Excel 兼容模式：添加 UTF-8 BOM，让 Excel 正确识别中文
        if ($this->excelCompatible) {
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        }

        foreach ($this->rows as $row) {
            fputcsv($output, $row, $this->delimiter);
        }
        $result = stream_get_contents($output);
        fclose($output);
        return $result;
    }

    /**
     * 获取下载文件名
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }
}
