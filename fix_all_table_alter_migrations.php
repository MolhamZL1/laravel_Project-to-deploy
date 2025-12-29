<?php

/**
 * Fix ALL migrations that use Schema::table() without safety checks
 * This will add Schema::hasTable() and Schema::hasColumn() checks
 */

$migrationsPath = __DIR__ . '/database/migrations';
$files = glob($migrationsPath . '/*.php');

$fixed = 0;
$skipped = 0;
$errors = [];

echo "Fixing all migrations that alter tables without safety checks...\n\n";

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
    
    // Fix up() method
    $upPattern = '/(public\s+function\s+up\(\)\s*\{[^}]*?)(Schema::table\([\'"]' . preg_quote($tableName, '/') . '[\'"][^}]+?\})([^}]*?\})/s';
    
    if (preg_match($upPattern, $content, $upMatches)) {
        $beforeUp = $upMatches[1];
        $tableCall = $upMatches[2];
        $afterUp = $upMatches[3];
        
        // Extract column operations from the closure
        if (preg_match("/function\s*\([^)]*Blueprint[^)]*\)\s*\{([^}]+)\}/s", $tableCall, $funcMatch)) {
            $columnOps = trim($funcMatch[1]);
            
            // Extract column names being added/modified
            $columnNames = [];
            if (preg_match_all("/->(string|integer|boolean|float|decimal|text|date|timestamp|unsignedBigInteger|bigInteger|foreignId|tinyInteger|longText|dateTime)\(['\"]?([^'\"),\s]+)['\"]?/", $columnOps, $colMatches)) {
                $columnNames = $colMatches[2];
            }
            
            // Build safe up() method
            $safeUp = "        if (Schema::hasTable('{$tableName}')) {\n";
            $safeUp .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
            
            if (!empty($columnNames)) {
                // Multiple columns - wrap each with check
                $lines = explode("\n", $columnOps);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line) || strpos($line, '//') === 0) {
                        $safeUp .= "                " . $line . "\n";
                        continue;
                    }
                    
                    // Check if this line defines a column
                    foreach ($columnNames as $colName) {
                        if (strpos($line, $colName) !== false) {
                            $safeUp .= "                if (!Schema::hasColumn('{$tableName}', '{$colName}')) {\n";
                            $safeUp .= "                    " . $line . "\n";
                            $safeUp .= "                }\n";
                            continue 2;
                        }
                    }
                    
                    // Not a column definition, just add it
                    $safeUp .= "                " . $line . "\n";
                }
            } else {
                // No column names found, just wrap with table check
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
    $downPattern = '/(public\s+function\s+down\(\)\s*\{[^}]*?)(Schema::table\([\'"]' . preg_quote($tableName, '/') . '[\'"][^}]+?\})([^}]*?\})/s';
    
    if (preg_match($downPattern, $content, $downMatches)) {
        $beforeDown = $downMatches[1];
        $tableCallDown = $downMatches[2];
        $afterDown = $downMatches[3];
        
        // Extract dropColumn operations
        preg_match_all("/->dropColumn\(\[?['\"]([^'\"]+)['\"]\]?\)/", $tableCallDown, $dropMatches);
        $dropColumns = !empty($dropMatches[1]) ? $dropMatches[1] : [];
        
        // Check if it's just a comment
        if (preg_match("/\/\/.*$/", $tableCallDown)) {
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
            // Empty or unknown, just add table check
            $safeDown = "        if (Schema::hasTable('{$tableName}')) {\n";
            $safeDown .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
            if (preg_match("/function\s*\([^)]*Blueprint[^)]*\)\s*\{([^}]+)\}/s", $tableCallDown, $funcMatchDown)) {
                $safeDown .= "                " . trim($funcMatchDown[1]) . "\n";
            } else {
                $safeDown .= "                //\n";
            }
            $safeDown .= "            });\n";
            $safeDown .= "        }";
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

