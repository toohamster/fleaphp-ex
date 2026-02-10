#!/usr/bin/env php
<?php

/**
 * 安全的PHP访问控制符批量添加脚本
 * 
 * 安全准则：
 * 1. 只处理类级别的方法和属性声明
 * 2. 绝不在方法体内添加访问控制符
 * 3. 精确识别类结构边界
 * 4. 保留原有代码逻辑不变
 */

class SafeAccessControlProcessor {
    
    private $basePath;
    private $stats = [
        'files_processed' => 0,
        'methods_updated' => 0,
        'properties_updated' => 0,
        'errors' => 0
    ];
    
    public function __construct($basePath) {
        $this->basePath = $basePath;
    }
    
    /**
     * 安全地批量处理所有PHP文件
     */
    public function processAllFiles() {
        echo "开始安全批量处理访问控制符...\n";
        
        // 获取所有需要处理的PHP文件
        $phpFiles = $this->getPhpFiles();
        echo "找到 " . count($phpFiles) . " 个PHP文件需要处理\n";
        
        foreach ($phpFiles as $filePath) {
            try {
                $this->processSingleFile($filePath);
            } catch (Exception $e) {
                echo "处理文件出错 {$filePath}: " . $e->getMessage() . "\n";
                $this->stats['errors']++;
            }
        }
        
        $this->printSummary();
    }
    
    /**
     * 获取需要处理的PHP文件列表
     */
    private function getPhpFiles() {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->basePath)
        );
        
        $phpFiles = [];
        foreach ($iterator as $file) {
            $pathname = $file->getPathname();
            if ($file->getExtension() === 'php' && 
                strpos($pathname, '3rd/') === false && 
                strpos($pathname, 'FLEA/') !== false) {
                $phpFiles[] = $pathname;
            }
        }
        
        return $phpFiles;
    }
    
    /**
     * 安全处理单个文件
     */
    private function processSingleFile($filePath) {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // 使用正则表达式安全地添加访问控制符
        $content = $this->addAccessModifiersSafely($content);
        
        // 只有内容发生变化时才保存
        if ($content !== $originalContent) {
            file_put_contents($filePath, $content);
            $this->stats['files_processed']++;
            echo "✓ 处理完成: " . basename($filePath) . "\n";
        }
    }
    
    /**
     * 安全地添加访问控制符（只处理类级别声明）
     */
    private function addAccessModifiersSafely($content) {
        // 处理类方法声明（不包括构造函数，因为我们已经处理过了）
        $content = $this->processMethodDeclarations($content);
        
        // 处理类属性声明
        $content = $this->processPropertyDeclarations($content);
        
        return $content;
    }
    
    /**
     * 处理方法声明
     */
    private function processMethodDeclarations($content) {
        // 匹配类中的方法声明（排除已经处理过的构造函数）
        $patterns = [
            // 匹配普通方法声明
            '/^(\s*)function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/m' => function($matches) {
                $indent = $matches[1];
                $methodName = $matches[2];
                
                // 跳过已经处理过的方法和构造函数
                if (strpos($methodName, '__') === 0) {
                    return $matches[0]; // 保持原样
                }
                
                // 确定访问级别
                $accessModifier = $this->determineMethodAccessLevel($methodName);
                $this->stats['methods_updated']++;
                
                return $indent . $accessModifier . ' function ' . $methodName . '(';
            },
            
            // 匹配引用参数的方法
            '/^(\s*)function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(\s*&\s*\$/m' => function($matches) {
                $fullMatch = $matches[0];
                $indent = $matches[1];
                $methodName = $matches[2];
                
                // 跳过已经处理过的方法
                if (strpos($methodName, '__') === 0) {
                    return $fullMatch;
                }
                
                $accessModifier = $this->determineMethodAccessLevel($methodName);
                $this->stats['methods_updated']++;
                
                // 重建匹配的字符串
                return $indent . $accessModifier . ' function ' . $methodName . ' (& $';
            }
        ];
        
        foreach ($patterns as $pattern => $callback) {
            $content = preg_replace_callback($pattern, $callback, $content);
        }
        
        return $content;
    }
    
    /**
     * 处理属性声明
     */
    private function processPropertyDeclarations($content) {
        // 匹配类属性声明（在类中但在方法外）
        $pattern = '/^(\s*)(\$[a-zA-Z_][a-zA-Z0-9_]*)\s*=/m';
        
        $content = preg_replace_callback($pattern, function($matches) {
            $indent = $matches[1];
            $propertyName = $matches[2];
            
            // 检查是否已经有访问控制符
            $lineStart = strrpos(substr($content, 0, strpos($content, $matches[0])), "\n") + 1;
            $line = substr($content, $lineStart, strpos($content, "\n", $lineStart) - $lineStart);
            
            // 如果行中已经包含访问控制符，则跳过
            if (preg_match('/\b(public|protected|private|var)\b/', $line)) {
                return $matches[0];
            }
            
            $accessModifier = $this->determinePropertyAccessLevel($propertyName);
            $this->stats['properties_updated']++;
            
            return $indent . $accessModifier . ' ' . $propertyName . ' =';
        }, $content);
        
        return $content;
    }
    
    /**
     * 确定方法访问级别
     */
    private function determineMethodAccessLevel($methodName) {
        // 魔术方法通常是public
        $magicMethods = ['__get', '__set', '__call', '__toString', '__invoke', '__destruct', '__isset', '__unset'];
        if (in_array($methodName, $magicMethods)) {
            return 'public';
        }
        
        // 以下划线开头的方法通常是protected
        if (strpos($methodName, '_') === 0) {
            return 'protected';
        }
        
        // 公共API方法
        return 'public';
    }
    
    /**
     * 确定属性访问级别
     */
    private function determinePropertyAccessLevel($propertyName) {
        // 以下划线开头的属性通常是protected
        if (strpos($propertyName, '$_') === 0) {
            return 'protected';
        }
        
        // 双下划线开头的属性是private
        if (strpos($propertyName, '$__') === 0) {
            return 'private';
        }
        
        // 公共属性
        return 'public';
    }
    
    /**
     * 打印处理摘要
     */
    private function printSummary() {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "访问控制符批量处理完成\n";
        echo str_repeat("=", 50) . "\n";
        echo "处理文件数: " . $this->stats['files_processed'] . "\n";
        echo "更新方法数: " . $this->stats['methods_updated'] . "\n";
        echo "更新属性数: " . $this->stats['properties_updated'] . "\n";
        echo "错误数量: " . $this->stats['errors'] . "\n";
        echo str_repeat("=", 50) . "\n";
        
        if ($this->stats['errors'] > 0) {
            echo "⚠️  存在处理错误，请检查上述错误信息\n";
        } else {
            echo "✅ 所有文件处理完成，无错误\n";
        }
    }
}

// 执行批量处理
try {
    $processor = new SafeAccessControlProcessor(__DIR__ . '/FLEA');
    $processor->processAllFiles();
} catch (Exception $e) {
    echo "批量处理过程中发生致命错误: " . $e->getMessage() . "\n";
    exit(1);
}