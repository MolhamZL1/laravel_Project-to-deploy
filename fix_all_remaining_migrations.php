<?php

/**
 * Fix ALL remaining migrations that don't have safety checks
 * This is a comprehensive fix for Railway deployment
 */

$migrationsPath = __DIR__ . '/database/migrations';
$files = glob($migrationsPath . '/*.php');

$fixed = 0;
$skipped = 0;
$errors = [];

echo "Fixing all remaining migrations...\n\n";

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
    if (!preg_match("/Schema::table\(['\"]([^'\"]+)['\"]/", $content, $tableMatch)) {
        continue;
    }
    
    $tableName = $tableMatch[1];
    $modified = false;
    
    // Fix up() method
    $upPattern = '/(public\s+function\s+up\(\)\s*\{[^}]*?)(Schema::table\([\'"]' . preg_quote($tableName, '/') . '[\'"][^}]+?\})([^}]*?\})/s';
    
    if (preg_match($upPattern, $content, $upMatches)) {
        $beforeUp = $upMatches[1];
        $tableCall = $upMatches[2];
        $afterUp = $upMatches[3];
        
        // Extract column operations
        if (preg_match("/function\s*\([^)]*Blueprint[^)]*\)\s*\{([^}]+)\}/s", $tableCall, $funcMatch)) {
            $columnOps = trim($funcMatch[1]);
            
            // Check if it's a change() operation
            $isChange = strpos($columnOps, '->change()') !== false;
            $isDrop = strpos($columnOps, '->dropColumn') !== false;
            
            // Extract all column names
            $columnNames = [];
            if (preg_match_all("/->(string|integer|boolean|float|decimal|text|date|timestamp|unsignedBigInteger|bigInteger|foreignId|tinyInteger|longText)\(['\"]?([^'\"),\s]+)['\"]?/", $columnOps, $colMatches)) {
                $columnNames = $colMatches[2];
            } elseif (preg_match_all("/->dropColumn\(\[?['\"]([^'\"]+)['\"]\]?\)/", $columnOps, $dropMatches)) {
                $columnNames = $dropMatches[1];
            }
            
            // Build safe up() method
            $safeUp = "        if (Schema::hasTable('{$tableName}')) {\n";
            $safeUp .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
            
            if (!empty($columnNames) && !$isChange && !$isDrop) {
                // Adding new columns - check each one
                foreach ($columnNames as $colName) {
                    $safeUp .= "                if (!Schema::hasColumn('{$tableName}', '{$colName}')) {\n";
                    // Extract the specific column operation
                    if (preg_match("/->(string|integer|boolean|float|decimal|text|date|timestamp|unsignedBigInteger|bigInteger|foreignId|tinyInteger|longText)\(['\"]?{$colName}['\"]?[^;]*;/", $columnOps, $colOpMatch)) {
                        $safeUp .= "                    \$table->" . $colOpMatch[0] . "\n";
                    }
                    $safeUp .= "                }\n";
                }
            } elseif (!empty($columnNames) && $isChange) {
                // Changing existing columns
                foreach ($columnNames as $colName) {
                    $safeUp .= "                if (Schema::hasColumn('{$tableName}', '{$colName}')) {\n";
                    if (preg_match("/->(string|integer|boolean|float|decimal|text|date|timestamp|unsignedBigInteger|bigInteger|foreignId|tinyInteger|longText)\(['\"]?{$colName}['\"]?[^;]*->change\(\);/", $columnOps, $colOpMatch)) {
                        $safeUp .= "                    \$table->" . $colOpMatch[0] . "\n";
                    }
                    $safeUp .= "                }\n";
                }
            } elseif ($isDrop && !empty($columnNames)) {
                // Dropping columns
                foreach ($columnNames as $colName) {
                    $safeUp .= "                if (Schema::hasColumn('{$tableName}', '{$colName}')) {\n";
                    $safeUp .= "                    \$table->dropColumn(['{$colName}']);\n";
                    $safeUp .= "                }\n";
                }
            } else {
                // Fallback - just wrap with table check
                $safeUp .= "                " . $columnOps . "\n";
            }
            
            $safeUp .= "            });\n";
            $safeUp .= "        }";
            
            $newUpMethod = $beforeUp . $safeUp . $afterUp;
            $content = str_replace($upMatches[0], $newUpMethod, $content);
            $modified = true;
        }
    }
    
    // Fix down() method - fix dropIfExists errors
    if (preg_match('/Schema::dropIfExists\([\'"](\w+)[\'"]\)/', $content) && strpos($content, 'Schema::table') !== false) {
        // Replace dropIfExists with proper dropColumn
        $content = preg_replace(
            '/Schema::dropIfExists\([\'"](\w+)[\'"]\)/',
            '\$table->dropColumn([\'$1\'])',
            $content
        );
        $modified = true;
    }
    
    // Fix down() method - add safety checks
    $downPattern = '/(public\s+function\s+down\(\)\s*\{[^}]*?)(Schema::table\([\'"]' . preg_quote($tableName, '/') . '[\'"][^}]+?\})([^}]*?\})/s';
    
    if (preg_match($downPattern, $content, $downMatches)) {
        $beforeDown = $downMatches[1];
        $tableCallDown = $downMatches[2];
        $afterDown = $downMatches[3];
        
        // Extract dropColumn operations
        if (preg_match_all("/->dropColumn\(\[?['\"]([^'\"]+)['\"]\]?\)/", $tableCallDown, $dropMatches)) {
            $dropColumns = $dropMatches[1];
            
            $safeDown = "        if (Schema::hasTable('{$tableName}')) {\n";
            $safeDown .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
            
            foreach ($dropColumns as $dropCol) {
                $safeDown .= "                if (Schema::hasColumn('{$tableName}', '{$dropCol}')) {\n";
                $safeDown .= "                    \$table->dropColumn(['{$dropCol}']);\n";
                $safeDown .= "                }\n";
            }
            
            $safeDown .= "            });\n";
            $safeDown .= "        }";
            
            $newDownMethod = $beforeDown . $safeDown . $afterDown;
            $content = str_replace($downMatches[0], $newDownMethod, $content);
            $modified = true;
        } elseif (trim($tableCallDown) === '' || strpos($tableCallDown, '//') !== false) {
            // Empty down() method
            $safeDown = "        if (Schema::hasTable('{$tableName}')) {\n";
            $safeDown .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
            $safeDown .= "                //\n";
            $safeDown .= "            });\n";
            $safeDown .= "        }";
            
            $newDownMethod = $beforeDown . $safeDown . $afterDown;
            $content = str_replace($downMatches[0], $newDownMethod, $content);
            $modified = true;
        }
    }
    
    // Only write if content changed
    if ($modified && $content !== $originalContent) {
        // Validate syntax
        $tempFile = tempnam(sys_get_temp_dir(), 'migration_check');
        file_put_contents($tempFile, $content);
        $output = [];
        $return = 0;
        exec("php -l $tempFile 2>&1", $output, $return);
        unlink($tempFile);
        
        if ($return === 0) {
            file_put_contents($file . '.backup', $originalContent);
            file_put_contents($file, $content);
            $fixed++;
            echo "✓ Fixed: {$filename}\n";
        } else {
            $errors[] = $filename . ': ' . implode(' ', $output);
        }
    }
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "Summary:\n";
echo "  ✓ Fixed: {$fixed} migrations\n";
echo "  ⊘ Skipped (already safe): {$skipped} migrations\n";
echo "  ❌ Errors: " . count($errors) . " migrations\n";
echo "═══════════════════════════════════════════════════════\n";

if (!empty($errors)) {
    echo "\n⚠️  Migrations with errors (need manual fix):\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

if ($fixed > 0) {
    echo "\n⚠️  Backups created with .backup extension\n";
    echo "📝 Review changes, test migrations, then remove backups\n";
}

