<?php

/**
 * Simple and reliable fix for migrations - just wraps Schema::table with hasTable check
 * This is safer than trying to parse and modify column operations
 */

$migrationsPath = __DIR__ . '/database/migrations';
$files = glob($migrationsPath . '/*.php');

$fixed = 0;
$skipped = 0;
$errors = [];

echo "🔧 Fixing migrations with simple wrapper approach...\n\n";

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
    
    // Extract table name
    if (!preg_match("/Schema::table\(['\"]([^'\"]+)['\"]/", $content, $tableMatch)) {
        continue;
    }
    
    $tableName = $tableMatch[1];
    $modified = false;
    
    // Simple approach: Just wrap Schema::table with if (Schema::hasTable(...))
    // For up() method
    $upPattern = '/(public\s+function\s+up\(\)\s*\{[^}]*?)(Schema::table\([\'"]' . preg_quote($tableName, '/') . '[\'"][^}]+?\})([^}]*?\})/s';
    
    if (preg_match($upPattern, $content, $upMatches)) {
        $beforeUp = $upMatches[1];
        $tableCall = $upMatches[2];
        $afterUp = $upMatches[3];
        
        // Simple wrap - just add the if check around the existing Schema::table call
        $safeUp = "        if (Schema::hasTable('{$tableName}')) {\n";
        $safeUp .= "            " . $tableCall . "\n";
        $safeUp .= "        }";
        
        $newUpMethod = $beforeUp . $safeUp . $afterUp;
        $content = str_replace($upMatches[0], $newUpMethod, $content);
        $modified = true;
    }
    
    // For down() method
    $downPattern = '/(public\s+function\s+down\(\)\s*\{[^}]*?)(Schema::table\([\'"]' . preg_quote($tableName, '/') . '[\'"][^}]+?\})([^}]*?\})/s';
    
    if (preg_match($downPattern, $content, $downMatches)) {
        $beforeDown = $downMatches[1];
        $tableCallDown = $downMatches[2];
        $afterDown = $downMatches[3];
        
        // Simple wrap
        $safeDown = "        if (Schema::hasTable('{$tableName}')) {\n";
        $safeDown .= "            " . $tableCallDown . "\n";
        $safeDown .= "        }";
        
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
            if ($fixed % 10 == 0) {
                echo "✓ Fixed {$fixed} migrations...\n";
            }
        } else {
            $errors[] = $filename;
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
    echo "\n⚠️  Migrations with errors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
} elseif (!empty($errors)) {
    echo "\n⚠️  " . count($errors) . " migrations had errors\n";
}

if ($fixed > 0) {
    echo "\n✅ Fixed {$fixed} migrations with safety checks!\n";
    echo "📝 Backups created with .backup extension\n";
}

