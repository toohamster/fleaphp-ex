#!/usr/bin/env php
<?php

/**
 * PHP访问控制符分析和添加脚本（修正版）
 * 
 * 正确的处理范围：
 * 1. 类属性声明（类级别变量）
 * 2. 类方法声明（不包括方法内部的局部变量）
 * 
 * 分析原则：
 * - 公共API方法 -> public
 * - 内部使用方法 -> protected  
 * - 私有实现细节 -> private
 * - 需要外部访问的属性 -> public
 * - 内部使用的属性 -> protected
 * - 真正私有的属性 -> private
 */

class AccessControlAnalyzer {
    
    private $basePath;
    private $stats = [
        'methods_updated' => 0,
        'properties_updated' => 0,
        'files_processed' => 0
    ];
    
    public function __construct($basePath) {
        $this->basePath = $basePath;
    }
    
    /**
     * 分析并添加访问控制符
     */
    public function analyzeAndAddAccessModifiers() {
        echo "开始分析和添加访问控制符...\n";
        
        // 查找所有PHP文件（排除第三方库）
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
        
        echo "找到 " . count($phpFiles) . " 个PHP文件需要处理\n";
        
        foreach ($phpFiles as $filePath) {
            $this->processFile($filePath);
        }
        
        $this->printSummary();
    }
    
    /**
     * 处理单个文件
     */
    private function processFile($filePath) {
        $content = file_get_contents($filePath);
        $originalContent = $content;
        
        // 解析PHP文件结构
        $tokens = token_get_all($content);
        $newContent = '';
        $inClass = false;
        $inMethod = false;
        $braceLevel = 0;
        $i = 0;
        
        while ($i < count($tokens)) {
            $token = $tokens[$i];
            
            if (is_array($token)) {
                $tokenType = $token[0];
                $tokenValue = $token[1];
                
                // 检测类开始
                if ($tokenType === T_CLASS) {
                    $inClass = true;
                    $newContent .= $tokenValue;
                    $i++;
                    continue;
                }
                
                // 检测方法开始
                if ($inClass && $tokenType === T_FUNCTION) {
                    $inMethod = true;
                    // 查找方法名
                    $j = $i + 1;
                    while ($j < count($tokens) && is_array($tokens[$j]) && $tokens[$j][0] !== T_STRING) {
                        $j++;
                    }
                    if ($j < count($tokens) && is_array($tokens[$j])) {
                        $methodName = $tokens[$j][1];
                        // 添加访问控制符
                        $accessModifier = $this->determineMethodAccessLevel($methodName);
                        $newContent .= $accessModifier . ' ';
                        $this->stats['methods_updated']++;
                    }
                    $newContent .= $tokenValue;
                    $i++;
                    continue;
                }
                
                // 检测变量声明（在类中但不在方法内）
                if ($inClass && !$inMethod && $tokenType === T_VARIABLE) {
                    // 检查前面是否有访问控制符
                    $hasModifier = $this->hasAccessModifierBefore($tokens, $i);
                    if (!$hasModifier) {
                        $accessModifier = $this->determinePropertyAccessLevel($tokenValue);
                        $newContent .= $accessModifier . ' ';
                        $this->stats['properties_updated']++;
                    }
                    $newContent .= $tokenValue;
                    $i++;
                    continue;
                }
                
                $newContent .= $tokenValue;
            } else {
                // 处理符号
                $char = $token;
                $newContent .= $char;
                
                if ($char === '{') {
                    $braceLevel++;
                    if ($inClass && $braceLevel === 1) {
                        // 类的主体开始
                    }
                } elseif ($char === '}') {
                    $braceLevel--;
                    if ($braceLevel === 0) {
                        $inClass = false;
                        $inMethod = false;
                    } elseif ($inMethod && $braceLevel === 1) {
                        $inMethod = false;
                    }
                }
            }
            $i++;
        }
        
        // 如果有变化，保存文件
        if ($newContent !== $originalContent) {
            file_put_contents($filePath, $newContent);
            $this->stats['files_processed']++;
            echo "处理文件: " . basename($filePath) . "\n";
        }
    }
    
    /**
     * 检查变量声明前是否有访问控制符
     */
    private function hasAccessModifierBefore($tokens, $currentIndex) {
        // 向前查找最近的非空白token
        for ($i = $currentIndex - 1; $i >= 0; $i--) {
            $token = $tokens[$i];
            if (is_array($token)) {
                $tokenType = $token[0];
                $tokenValue = $token[1];
                
                // 跳过空白和注释
                if ($tokenType === T_WHITESPACE || $tokenType === T_COMMENT || $tokenType === T_DOC_COMMENT) {
                    continue;
                }
                
                // 检查是否是访问控制符
                if (in_array($tokenValue, ['public', 'protected', 'private', 'var', 'static'])) {
                    return true;
                }
                
                // 如果遇到分号、花括号或其他语句结束符，说明前面没有访问控制符
                if (in_array($tokenValue, [';', '{', '}', '(', ')', ','])) {
                    return false;
                }
                
                // 如果遇到其他关键字，也说明前面没有访问控制符
                return false;
            } else {
                // 符号处理
                if (in_array($token, [';', '{', '}', '(', ')', ','])) {
                    return false;
                }
            }
        }
        return false;
    }
    
    /**
     * 确定方法的访问级别
     */
    private function determineMethodAccessLevel($methodName) {
        // 构造函数始终是public
        if ($methodName === '__construct') {
            return 'public';
        }
        
        // 魔术方法通常是public
        $magicMethods = ['__get', '__set', '__call', '__toString', '__invoke', '__destruct'];
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
     * 确定属性的访问级别
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
        echo "\n=== 访问控制符添加完成 ===\n";
        echo "处理文件数: " . $this->stats['files_processed'] . "\n";
        echo "更新方法数: " . $this->stats['methods_updated'] . "\n";
        echo "更新属性数: " . $this->stats['properties_updated'] . "\n";
        echo "========================\n";
    }
}

// 执行分析
$analyzer = new AccessControlAnalyzer(__DIR__ . '/FLEA');
$analyzer->analyzeAndAddAccessModifiers();
