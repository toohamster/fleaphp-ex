#!/usr/bin/env php
<?php
/**
 * Throw语句后Return语句冗余修复脚本
 * 
 * 自动检测并修复PHP代码中throw语句后跟随的冗余return语句
 * 
 * 使用方法:
 * php fix_throw_return_redundancy.php [目录路径]
 * 
 * 示例:
 * php fix_throw_return_redundancy.php ./FLEA
 */

class ThrowReturnRedundancyFixer
{
    private $stats = [
        'files_scanned' => 0,
        'issues_found' => 0,
        'issues_fixed' => 0,
        'errors' => 0
    ];
    
    private $targetDir;
    
    public function __construct($targetDir = './FLEA')
    {
        $this->targetDir = rtrim($targetDir, '/');
        if (!is_dir($this->targetDir)) {
            throw new Exception("目录不存在: {$this->targetDir}");
        }
    }
    
    /**
     * 执行修复任务
     */
    public function execute()
    {
        echo "🔍 开始扫描Throw-Return冗余问题...\n";
        echo "目标目录: {$this->targetDir}\n\n";
        
        $this->scanDirectory($this->targetDir);
        $this->printSummary();
    }
    
    /**
     * 递归扫描目录
     */
    private function scanDirectory($dir)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->processFile($file->getPathname());
            }
        }
    }
    
    /**
     * 处理单个PHP文件
     */
    private function processFile($filePath)
    {
        $this->stats['files_scanned']++;
        
        try {
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new Exception("无法读取文件: {$filePath}");
            }
            
            $fixedContent = $this->fixThrowReturnRedundancy($content, $filePath);
            
            // 如果内容有变化，保存文件
            if ($fixedContent !== $content) {
                if (file_put_contents($filePath, $fixedContent) !== false) {
                    echo "✅ 修复文件: " . basename($filePath) . "\n";
                } else {
                    echo "❌ 保存失败: " . basename($filePath) . "\n";
                    $this->stats['errors']++;
                }
            }
            
        } catch (Exception $e) {
            echo "❌ 处理文件出错: " . basename($filePath) . " - " . $e->getMessage() . "\n";
            $this->stats['errors']++;
        }
    }
    
    /**
     * 修复Throw语句后的Return冗余代码
     */
    private function fixThrowReturnRedundancy($content, $filePath)
    {
        $lines = explode("\n", $content);
        $fixedLines = [];
        $lineCount = count($lines);
        
        for ($i = 0; $i < $lineCount; $i++) {
            $currentLine = $lines[$i];
            $fixedLines[] = $currentLine;
            
            // 检查是否包含throw语句
            if ($this->containsThrowStatement($currentLine)) {
                // 查找接下来的几行是否有冗余的return语句
                $nextLinesToRemove = $this->findRedundantLines($lines, $i + 1);
                
                if (!empty($nextLinesToRemove)) {
                    $this->stats['issues_found']++;
                    
                    // 跳过冗余行
                    $i += count($nextLinesToRemove);
                    $this->stats['issues_fixed']++;
                    
                    // 记录修复详情
                    $this->logFixDetails($filePath, $i, $nextLinesToRemove);
                }
            }
        }
        
        return implode("\n", $fixedLines);
    }
    
    /**
     * 检查行是否包含throw语句
     */
    private function containsThrowStatement($line)
    {
        // 移除注释和字符串中的内容
        $cleanLine = $this->removeCommentsAndStrings($line);
        
        // 匹配throw语句（基本模式）
        return preg_match('/\bthrow\b/i', $cleanLine) === 1;
    }
    
    /**
     * 查找throw语句后的冗余代码行
     */
    private function findRedundantLines($lines, $startIndex)
    {
        $redundantLines = [];
        $lineCount = count($lines);
        
        for ($i = $startIndex; $i < $lineCount; $i++) {
            $line = trim($lines[$i]);
            
            // 跳过空行和注释行
            if (empty($line) || $this->isCommentLine($line)) {
                $redundantLines[] = $line;
                continue;
            }
            
            // 检查是否是明显的冗余代码
            if ($this->isRedundantCode($line)) {
                $redundantLines[] = $line;
            } else {
                // 遇到非冗余代码就停止
                break;
            }
        }
        
        return $redundantLines;
    }
    
    /**
     * 断是否是冗余代码
     */
    private function isRedundantCode($line)
    {
        // 移除注释
        $cleanLine = $this->removeComments($line);
        $cleanLine = trim($cleanLine);
        
        if (empty($cleanLine)) {
            return true; // 空行认为是冗余的
        }
        
        // 匹配常见的冗余模式
        $patterns = [
            '/^\s*return\s*[^;]*;\s*$/',           // return语句
            '/^\s*\$[a-zA-Z_][a-zA-Z0-9_]*\s*=/',  // 变量赋值
            '/^\s*exit\s*;?\s*$/',                 // exit语句
            '/^\s*die\s*;?\s*$/',                  // die语句
            '/^\s*break\s*;?\s*$/',                // break语句
            '/^\s*continue\s*;?\s*$/',             // continue语句
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cleanLine)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 移除注释和字符串内容
     */
    private function removeCommentsAndStrings($line)
    {
        // 移除单行注释
        $line = preg_replace('/\/\/.*$/', '', $line);
        
        // 移除多行注释
        $line = preg_replace('/\/\*.*?\*\//', '', $line);
        
        // 移除字符串内容（简化处理）
        $line = preg_replace('/"[^"]*"/', '""', $line);
        $line = preg_replace("/'[^']*'/", "''", $line);
        
        return $line;
    }
    
    /**
     * 移除注释
     */
    private function removeComments($line)
    {
        // 移除单行注释
        $line = preg_replace('/\/\/.*$/', '', $line);
        
        // 移除多行注释
        $line = preg_replace('/\/\*.*?\*\//', '', $line);
        
        return $line;
    }
    
    /**
     * 判断是否是注释行
     */
    private function isCommentLine($line)
    {
        $trimmed = ltrim($line);
        return strpos($trimmed, '//') === 0 || 
               strpos($trimmed, '/*') === 0 ||
               strpos($trimmed, '*') === 0;
    }
    
    /**
     * 记录修复详情
     */
    private function logFixDetails($filePath, $lineNumber, $removedLines)
    {
        echo "   位置: 第{$lineNumber}行后\n";
        echo "   移除: " . implode('; ', array_map('trim', $removedLines)) . "\n";
    }
    
    /**
     * 打印处理摘要
     */
    private function printSummary()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "Throw-Return冗余修复完成\n";
        echo str_repeat("=", 50) . "\n";
        echo "扫描文件数: " . $this->stats['files_scanned'] . "\n";
        echo "发现问题数: " . $this->stats['issues_found'] . "\n";
        echo "修复问题数: " . $this->stats['issues_fixed'] . "\n";
        echo "错误数量: " . $this->stats['errors'] . "\n";
        echo str_repeat("=", 50) . "\n";
        
        if ($this->stats['errors'] > 0) {
            echo "⚠️  存在处理错误，请检查上述错误信息\n";
        } elseif ($this->stats['issues_found'] > 0) {
            echo "✅ 成功修复所有发现的问题\n";
        } else {
            echo "ℹ️  未发现Throw-Return冗余问题\n";
        }
    }
}

// 主程序执行
try {
    $targetDir = isset($argv[1]) ? $argv[1] : './FLEA';
    $fixer = new ThrowReturnRedundancyFixer($targetDir);
    $fixer->execute();
} catch (Exception $e) {
    echo "❌ 程序执行出错: " . $e->getMessage() . "\n";
    exit(1);
}
?>