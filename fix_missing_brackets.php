<?php

/**
 * Fix missing closing brackets in migrations
 * Looks for Schema::table closures that are missing });
 */

$migrationsPath = __DIR__ . '/database/migrations';
$files = glob($migrationsPath . '/*.php');

$fixed = 0;
$errors = [];

echo "Checking for missing brackets in migrations...\n\n";

foreach ($files as $file) {
    if (strpos($file, '.backup') !== false) {
        continue;
    }
    
    $filename = basename($file);
    $content = file_get_contents($file);
    $originalContent = $content;
    $modified = false;
    
    // Fix: Schema::table(...) { ... } should end with });
    // Pattern: function (Blueprint $table) { ... } followed by just } instead of });
    
    // Check for missing closing ); after Schema::table closures
    // Look for: Schema::table(...) { ... } followed by } without );
    $lines = explode("\n", $content);
    $newLines = [];
    $inTable = false;
    $braceCount = 0;
    $parenCount = 0;
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        
        // Detect start of Schema::table
        if (preg_match('/Schema::table\(/', $line)) {
            $inTable = true;
            $braceCount = substr_count($line, '{') - substr_count($line, '}');
            $parenCount = substr_count($line, '(') - substr_count($line, ')');
        } else if ($inTable) {
            $braceCount += substr_count($line, '{') - substr_count($line, '}');
            $parenCount += substr_count($line, '(') - substr_count($line, ')');
            
            // If we're closing the function and braces are balanced but missing );
            if ($braceCount === 0 && $parenCount === 0 && $inTable) {
                // Check if this line is just a closing brace
                if (preg_match('/^\s*\}\s*$/', $line)) {
                    // Check if next line doesn't have });
                    $nextLine = isset($lines[$i + 1]) ? $lines[$i + 1] : '';
                    if (!preg_match('/^\s*\}\);\s*$/', $nextLine)) {
                        // Replace } with });
                        $line = preg_replace('/^\s*\}\s*$/', '            });', $line);
                        $modified = true;
                    }
                }
                $inTable = false;
            }
        }
        
        $newLines[] = $line;
    }
    
    if ($modified) {
        $content = implode("\n", $newLines);
    }
    
    // Also fix Schema::dropIfExists for columns (should be $table->dropColumn)
    if (preg_match('/Schema::dropIfExists\([\'"](\w+)[\'"]\)/', $content) && strpos($content, 'Schema::table') !== false) {
        $content = preg_replace(
            '/Schema::dropIfExists\([\'"](\w+)[\'"]\)/',
            '\$table->dropColumn([\'$1\'])',
            $content
        );
        $modified = true;
    }
    
    // Validate syntax
    if ($modified) {
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
echo "Fixed {$fixed} migrations.\n";

if (!empty($errors)) {
    echo "\n⚠️  Errors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

