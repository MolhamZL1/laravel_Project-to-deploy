<?php

/**
 * Fix syntax errors in migrations - missing closing braces and parentheses
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
    
    // Fix missing closing braces for Schema::table closures
    // Pattern: Schema::table(...) { ... } should have });
    if (preg_match('/Schema::table\([^)]+\)\s*function\s*\([^)]+\)\s*\{[^}]*\n\s*\}\s*$/m', $content)) {
        // Check if closing brace is missing
        $lines = explode("\n", $content);
        $fixedContent = [];
        $inTable = false;
        $braceCount = 0;
        
        foreach ($lines as $i => $line) {
            if (preg_match('/Schema::table\(/', $line)) {
                $inTable = true;
                $braceCount = 0;
            }
            
            if ($inTable) {
                $braceCount += substr_count($line, '{');
                $braceCount -= substr_count($line, '}');
                
                // If we're closing the function but missing the Schema::table closing
                if ($braceCount === 0 && $inTable && preg_match('/^\s*\}\s*$/', $line) && !preg_match('/\}\);\s*$/', $line)) {
                    // Check next line
                    if (isset($lines[$i + 1]) && !preg_match('/^\s*\}\);\s*$/', $lines[$i + 1])) {
                        $fixedContent[] = $line;
                        $fixedContent[] = '        });';
                        $inTable = false;
                        continue;
                    }
                }
            }
            
            $fixedContent[] = $line;
        }
        
        $content = implode("\n", $fixedContent);
    }
    
    // Fix: }); should be }); not just }
    $content = preg_replace(
        '/(Schema::table\([^)]+\)\s*function\s*\([^)]+\)\s*\{[^}]*)\n\s*\}\s*\n\s*\}\s*$/m',
        '$1' . "\n            });\n        }",
        $content
    );
    
    // Fix double arrows
    $content = str_replace('$table->->', '$table->', $content);
    
    // Fix extra indentation at start of methods
    $content = preg_replace('/^\s{16}if \(Schema::hasTable/', '        if (Schema::hasTable', $content);
    
    // Validate syntax
    $tempFile = tempnam(sys_get_temp_dir(), 'migration_check');
    file_put_contents($tempFile, $content);
    $output = [];
    $return = 0;
    exec("php -l $tempFile 2>&1", $output, $return);
    unlink($tempFile);
    
    if ($return === 0 && $content !== $originalContent) {
        file_put_contents($file, $content);
        $fixed++;
        echo "✓ Fixed: " . basename($file) . "\n";
    } elseif ($return !== 0) {
        // Try to restore from backup
        $backupFile = $file . '.backup';
        if (file_exists($backupFile)) {
            file_put_contents($file, file_get_contents($backupFile));
            echo "⚠ Restored from backup: " . basename($file) . "\n";
        }
    }
}

echo "\nFixed {$fixed} migrations.\n";

