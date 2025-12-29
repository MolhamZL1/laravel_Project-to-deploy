<?php

/**
 * Comprehensive fix for ALL migrations that use Schema::table() without safety checks
 * This script will add Schema::hasTable() and Schema::hasColumn() checks to all migrations
 */

$migrationsPath = __DIR__ . '/database/migrations';
$files = glob($migrationsPath . '/*.php');

$fixed = 0;
$skipped = 0;
$errors = [];

echo "🔧 Fixing ALL migrations without safety checks...\n\n";

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
    
    // Only process files that use Schema::table (not Schema::create)
    if (strpos($content, 'Schema::table(') === false) {
        continue;
    }
    
    // Extract table name from Schema::table('table_name', ...)
    if (!preg_match("/Schema::table\(['\"]([^'\"]+)['\"]/", $content, $tableMatch)) {
        continue;
    }
    
    $tableName = $tableMatch[1];
    $modified = false;
    
    // Fix up() method - wrap Schema::table with hasTable check
    if (preg_match('/(public\s+function\s+up\(\)\s*\{[^}]*?)(Schema::table\([\'"]' . preg_quote($tableName, '/') . '[\'"][^}]+?\})([^}]*?\})/s', $content, $upMatches)) {
        $beforeUp = $upMatches[1];
        $tableCall = $upMatches[2];
        $afterUp = $upMatches[3];
        
        // Extract the closure content
        if (preg_match("/function\s*\([^)]*Blueprint[^)]*\)\s*\{([^}]+)\}/s", $tableCall, $funcMatch)) {
            $columnOps = trim($funcMatch[1]);
            
            // Extract column names being added
            $columnNames = [];
            if (preg_match_all("/->(string|integer|boolean|float|decimal|text|date|timestamp|unsignedBigInteger|bigInteger|foreignId|tinyInteger|longText|dateTime|char)\(['\"]?([^'\"),\s]+)['\"]?/", $columnOps, $colMatches)) {
                $columnNames = array_unique($colMatches[2]);
            }
            
            // Check for change() operations
            $hasChange = strpos($columnOps, '->change()') !== false;
            
            // Build safe up() method
            $safeUp = "        if (Schema::hasTable('{$tableName}')) {\n";
            $safeUp .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
            
            if (!empty($columnNames) && !$hasChange) {
                // Multiple columns - check each one
                $lines = explode("\n", $columnOps);
                $processedLines = [];
                
                foreach ($lines as $line) {
                    $line = rtrim($line);
                    if (empty($line) || preg_match('/^\s*\/\//', $line)) {
                        $processedLines[] = "                " . $line;
                        continue;
                    }
                    
                    // Check if this line contains a column definition
                    $foundColumn = false;
                    foreach ($columnNames as $colName) {
                        if (strpos($line, $colName) !== false && preg_match("/->(string|integer|boolean|float|decimal|text|date|timestamp|unsignedBigInteger|bigInteger|foreignId|tinyInteger|longText|dateTime|char)\(/", $line)) {
                            $processedLines[] = "                if (!Schema::hasColumn('{$tableName}', '{$colName}')) {";
                            $processedLines[] = "                    " . ltrim($line);
                            $processedLines[] = "                }";
                            $foundColumn = true;
                            break;
                        }
                    }
                    
                    if (!$foundColumn) {
                        $processedLines[] = "                " . $line;
                    }
                }
                
                $safeUp .= implode("\n", $processedLines) . "\n";
            } elseif ($hasChange) {
                // For change() operations, check if column exists
                foreach ($columnNames as $colName) {
                    $safeUp .= "                if (Schema::hasColumn('{$tableName}', '{$colName}')) {\n";
                    // Find the line with this column
                    $lines = explode("\n", $columnOps);
                    foreach ($lines as $line) {
                        if (strpos($line, $colName) !== false) {
                            $safeUp .= "                    " . trim($line) . "\n";
                            break;
                        }
                    }
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
    
    // Fix down() method
    if (preg_match('/(public\s+function\s+down\(\)\s*\{[^}]*?)(Schema::table\([\'"]' . preg_quote($tableName, '/') . '[\'"][^}]+?\})([^}]*?\})/s', $content, $downMatches)) {
        $beforeDown = $downMatches[1];
        $tableCallDown = $downMatches[2];
        $afterDown = $downMatches[3];
        
        // Extract dropColumn operations
        preg_match_all("/->dropColumn\(\[?['\"]([^'\"]+)['\"]\]?\)/", $tableCallDown, $dropMatches);
        $dropColumns = !empty($dropMatches[1]) ? $dropMatches[1] : [];
        
        // Check if it's just a comment
        if (preg_match("/\/\/.*$/", $tableCallDown) || trim($tableCallDown) === '') {
            $safeDown = "        if (Schema::hasTable('{$tableName}')) {\n";
            $safeDown .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
            $safeDown .= "                //\n";
            $safeDown .= "            });\n";
            $safeDown .= "        }";
        } elseif (!empty($dropColumns)) {
            $safeDown = "        if (Schema::hasTable('{$tableName}')) {\n";
            $safeDown .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
            
            foreach ($dropColumns as $dropCol) {
                $safeDown .= "                if (Schema::hasColumn('{$tableName}', '{$dropCol}')) {\n";
                $safeDown .= "                    \$table->dropColumn(['{$dropCol}']);\n";
                $safeDown .= "                }\n";
            }
            
            $safeDown .= "            });\n";
            $safeDown .= "        }";
        } else {
            // Extract any operations
            if (preg_match("/function\s*\([^)]*Blueprint[^)]*\)\s*\{([^}]+)\}/s", $tableCallDown, $funcMatchDown)) {
                $downOps = trim($funcMatchDown[1]);
                $safeDown = "        if (Schema::hasTable('{$tableName}')) {\n";
                $safeDown .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
                $safeDown .= "                " . $downOps . "\n";
                $safeDown .= "            });\n";
                $safeDown .= "        }";
            } else {
                $safeDown = "        if (Schema::hasTable('{$tableName}')) {\n";
                $safeDown .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
                $safeDown .= "                //\n";
                $safeDown .= "            });\n";
                $safeDown .= "        }";
            }
        }
        
        $newDownMethod = $beforeDown . $safeDown . $afterDown;
        $content = str_replace($downMatches[0], $newDownMethod, $content);
        $modified = true;
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
            // Create backup
            if (!file_exists($file . '.backup')) {
                file_put_contents($file . '.backup', $originalContent);
            }
            file_put_contents($file, $content);
            $fixed++;
            echo "✓ Fixed: {$filename}\n";
        } else {
            $errors[] = $filename . ': ' . implode(' ', array_slice($output, 0, 2));
        }
    }
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "📊 Summary:\n";
echo "  ✓ Fixed: {$fixed} migrations\n";
echo "  ⊘ Skipped (already safe): {$skipped} migrations\n";
echo "  ❌ Errors: " . count($errors) . " migrations\n";
echo "═══════════════════════════════════════════════════════\n";

if (!empty($errors) && count($errors) <= 20) {
    echo "\n⚠️  Migrations with errors (need manual fix):\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
} elseif (!empty($errors)) {
    echo "\n⚠️  " . count($errors) . " migrations had errors (showing first 10):\n";
    foreach (array_slice($errors, 0, 10) as $error) {
        echo "  - $error\n";
    }
}

if ($fixed > 0) {
    echo "\n✅ Backups created with .backup extension\n";
    echo "📝 Review changes, test migrations, then remove backups if everything works\n";
}

