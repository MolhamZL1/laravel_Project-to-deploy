<?php

/**
 * Direct string replacement approach - simpler and more reliable
 * Finds Schema::table( and wraps it with if (Schema::hasTable(...))
 */

$migrationsPath = __DIR__ . '/database/migrations';
$files = glob($migrationsPath . '/*.php');

$fixed = 0;
$skipped = 0;

echo "🔧 Fixing migrations with direct replacement...\n\n";

foreach ($files as $file) {
    if (strpos($file, '.backup') !== false) {
        continue;
    }
    
    $filename = basename($file);
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Skip if already has safety checks
    if (strpos($content, 'Schema::hasTable') !== false) {
        $skipped++;
        continue;
    }
    
    // Only process files that use Schema::table
    if (strpos($content, 'Schema::table(') === false) {
        continue;
    }
    
    // Extract table name
    preg_match_all("/Schema::table\(['\"]([^'\"]+)['\"]/", $content, $matches);
    if (empty($matches[1])) {
        continue;
    }
    
    $tableName = $matches[1][0]; // Use first table found
    $modified = false;
    
    // Fix up() method - find Schema::table call and wrap it
    // Pattern: Schema::table('table_name', function...
    $pattern = "/(\s+)(Schema::table\(['\"]{$tableName}['\"],\s*function\s*\([^)]+\)\s*\{[^}]*?\}\);)/s";
    
    if (preg_match($pattern, $content, $upMatch)) {
        $indent = $upMatch[1];
        $tableCall = $upMatch[2];
        
        // Wrap with if check
        $wrapped = $indent . "if (Schema::hasTable('{$tableName}')) {\n";
        $wrapped .= $indent . "    " . ltrim($tableCall) . "\n";
        $wrapped .= $indent . "}";
        
        $content = str_replace($upMatch[0], $wrapped, $content);
        $modified = true;
    }
    
    // Fix down() method
    if (preg_match($pattern, $content, $downMatch)) {
        $indent = $downMatch[1];
        $tableCall = $downMatch[2];
        
        // Wrap with if check
        $wrapped = $indent . "if (Schema::hasTable('{$tableName}')) {\n";
        $wrapped .= $indent . "    " . ltrim($tableCall) . "\n";
        $wrapped .= $indent . "}";
        
        $content = str_replace($downMatch[0], $wrapped, $content);
        $modified = true;
    }
    
    // Validate and save
    if ($modified && $content !== $originalContent) {
        $tempFile = tempnam(sys_get_temp_dir(), 'migration_check');
        file_put_contents($tempFile, $content);
        $output = [];
        $return = 0;
        exec("php -l $tempFile 2>&1", $output, $return);
        unlink($tempFile);
        
        if ($return === 0) {
            if (!file_exists($file . '.backup')) {
                file_put_contents($file . '.backup', $originalContent);
            }
            file_put_contents($file, $content);
            $fixed++;
            if ($fixed <= 20 || $fixed % 20 == 0) {
                echo "✓ Fixed: {$filename}\n";
            }
        }
    }
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "📊 Summary:\n";
echo "  ✓ Fixed: {$fixed} migrations\n";
echo "  ⊘ Skipped (already safe): {$skipped} migrations\n";
echo "═══════════════════════════════════════════════════════\n";

if ($fixed > 0) {
    echo "\n✅ Fixed {$fixed} migrations!\n";
}

