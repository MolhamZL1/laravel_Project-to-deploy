<?php

/**
 * Fix migrations that incorrectly use Schema::dropIfExists() for columns
 * Should use $table->dropColumn() instead
 */

$migrationsPath = __DIR__ . '/database/migrations';
$files = glob($migrationsPath . '/*.php');

$fixed = 0;

foreach ($files as $file) {
    if (strpos($file, '.backup') !== false) {
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Fix: Schema::dropIfExists('column_name') should be $table->dropColumn(['column_name'])
    if (preg_match('/Schema::dropIfExists\([\'"](\w+)[\'"]\)/', $content)) {
        // Replace all instances
        $content = preg_replace(
            '/Schema::dropIfExists\([\'"](\w+)[\'"]\)/',
            '\$table->dropColumn([\'$1\'])',
            $content
        );
        
        // Now add safety checks if not present
        if (strpos($content, 'Schema::hasTable') === false && preg_match("/Schema::table\(['\"]([^'\"]+)['\"]/", $content, $tableMatch)) {
            $tableName = $tableMatch[1];
            
            // Fix up() method
            $upPattern = '/(public\s+function\s+up\(\)\s*\{[^}]*?)(Schema::table\([\'"]' . preg_quote($tableName, '/') . '[\'"][^}]+?\})([^}]*?\})/s';
            if (preg_match($upPattern, $content, $upMatches)) {
                $beforeUp = $upMatches[1];
                $tableCall = $upMatches[2];
                $afterUp = $upMatches[3];
                
                if (preg_match("/function\s*\([^)]*Blueprint[^)]*\)\s*\{([^}]+)\}/s", $tableCall, $funcMatch)) {
                    $columnOps = trim($funcMatch[1]);
                    
                    // Extract column names
                    preg_match_all("/->(string|integer|boolean|float|decimal|text|date|timestamp|unsignedBigInteger|bigInteger|foreignId|tinyInteger|longText)\(['\"]?([^'\"),\s]+)['\"]?/", $columnOps, $colMatches);
                    $columnNames = !empty($colMatches[2]) ? $colMatches[2] : [];
                    
                    $safeUp = "        if (Schema::hasTable('{$tableName}')) {\n";
                    $safeUp .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
                    
                    if (!empty($columnNames)) {
                        foreach ($columnNames as $colName) {
                            $safeUp .= "                if (!Schema::hasColumn('{$tableName}', '{$colName}')) {\n";
                            if (preg_match("/->(string|integer|boolean|float|decimal|text|date|timestamp|unsignedBigInteger|bigInteger|foreignId|tinyInteger|longText)\(['\"]?{$colName}['\"]?[^;]*;/", $columnOps, $colOpMatch)) {
                                $safeUp .= "                    \$table->" . trim($colOpMatch[0]) . "\n";
                            }
                            $safeUp .= "                }\n";
                        }
                    } else {
                        $safeUp .= "                " . $columnOps . "\n";
                    }
                    
                    $safeUp .= "            });\n";
                    $safeUp .= "        }";
                    
                    $content = str_replace($upMatches[0], $beforeUp . $safeUp . $afterUp, $content);
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
                
                $safeDown = "        if (Schema::hasTable('{$tableName}')) {\n";
                $safeDown .= "            Schema::table('{$tableName}', function (Blueprint \$table) {\n";
                
                if (!empty($dropColumns)) {
                    foreach ($dropColumns as $dropCol) {
                        $safeDown .= "                if (Schema::hasColumn('{$tableName}', '{$dropCol}')) {\n";
                        $safeDown .= "                    \$table->dropColumn(['{$dropCol}']);\n";
                        $safeDown .= "                }\n";
                    }
                } else {
                    $safeDown .= "                //\n";
                }
                
                $safeDown .= "            });\n";
                $safeDown .= "        }";
                
                $content = str_replace($downMatches[0], $beforeDown . $safeDown . $afterDown, $content);
            }
        }
        
        if ($content !== $originalContent) {
            file_put_contents($file . '.backup', $originalContent);
            file_put_contents($file, $content);
            $fixed++;
            echo "✓ Fixed: " . basename($file) . "\n";
        }
    }
}

echo "\nFixed {$fixed} migrations with dropIfExists errors.\n";

