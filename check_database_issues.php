<?php

/**
 * Comprehensive check for database-related issues that could crash Railway deployment
 */

$issues = [];
$migrationsPath = __DIR__ . '/database/migrations';
$seedsPath = __DIR__ . '/database/seeds';
$factoriesPath = __DIR__ . '/database/factories';

echo "🔍 Checking database folder for Railway deployment issues...\n\n";

// Check migrations
echo "📋 Checking Migrations...\n";
$migrationFiles = glob($migrationsPath . '/*.php');
$unsafeMigrations = [];
$syntaxErrors = [];

foreach ($migrationFiles as $file) {
    if (strpos($file, '.backup') !== false) {
        continue;
    }
    
    $filename = basename($file);
    $content = file_get_contents($file);
    
    // Check for Schema::table without safety checks
    if (strpos($content, 'Schema::table(') !== false && strpos($content, 'Schema::hasTable') === false) {
        $unsafeMigrations[] = $filename;
    }
    
    // Check for syntax errors
    $output = [];
    $return = 0;
    exec("php -l $file 2>&1", $output, $return);
    if ($return !== 0) {
        $syntaxErrors[] = $filename . ': ' . implode(' ', $output);
    }
    
    // Check for common errors in down() method
    if (preg_match('/Schema::dropIfExists\([\'"]\w+[\'"]\)/', $content) && strpos($content, 'Schema::table') !== false) {
        $issues[] = "⚠️  $filename: Uses dropIfExists() for columns (should use dropColumn())";
    }
}

if (!empty($unsafeMigrations)) {
    echo "  ⚠️  Found " . count($unsafeMigrations) . " migrations without safety checks\n";
    if (count($unsafeMigrations) <= 10) {
        foreach ($unsafeMigrations as $migration) {
            echo "     - $migration\n";
        }
    } else {
        echo "     (Showing first 10)\n";
        foreach (array_slice($unsafeMigrations, 0, 10) as $migration) {
            echo "     - $migration\n";
        }
        echo "     ... and " . (count($unsafeMigrations) - 10) . " more\n";
    }
} else {
    echo "  ✅ All migrations have safety checks\n";
}

if (!empty($syntaxErrors)) {
    echo "  ❌ Found " . count($syntaxErrors) . " migrations with syntax errors:\n";
    foreach ($syntaxErrors as $error) {
        echo "     - $error\n";
    }
} else {
    echo "  ✅ No syntax errors found\n";
}

// Check seeders
echo "\n🌱 Checking Seeders...\n";
$seederFiles = glob($seedsPath . '/*.php');
$unsafeSeeders = [];

foreach ($seederFiles as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    
    // Check for DB::table()->insert without safety checks
    if (strpos($content, 'DB::table(') !== false && strpos($content, 'Schema::hasTable') === false) {
        $unsafeSeeders[] = $filename;
    }
}

if (!empty($unsafeSeeders)) {
    echo "  ⚠️  Found " . count($unsafeSeeders) . " seeders without safety checks:\n";
    foreach ($unsafeSeeders as $seeder) {
        echo "     - $seeder\n";
    }
} else {
    echo "  ✅ All seeders have safety checks\n";
}

// Check factories
echo "\n🏭 Checking Factories...\n";
$factoryFiles = glob($factoriesPath . '/*.php');
$oldFactorySyntax = [];

foreach ($factoryFiles as $file) {
    $filename = basename($file);
    $content = file_get_contents($file);
    
    // Check for old Laravel factory syntax
    if (strpos($content, '$factory->define') !== false) {
        $oldFactorySyntax[] = $filename;
    }
}

if (!empty($oldFactorySyntax)) {
    echo "  ⚠️  Found " . count($oldFactorySyntax) . " factories using old syntax (Laravel 8+):\n";
    foreach ($oldFactorySyntax as $factory) {
        echo "     - $factory\n";
    }
    echo "  ℹ️  This won't crash deployment but may not work with newer Laravel versions\n";
} else {
    echo "  ✅ Factories use correct syntax\n";
}

// Check SQL files
echo "\n📄 Checking SQL Files...\n";
$sqlFiles = glob($migrationsPath . '/*.sql');
if (!empty($sqlFiles)) {
    echo "  ⚠️  Found " . count($sqlFiles) . " SQL files in migrations folder:\n";
    foreach ($sqlFiles as $file) {
        echo "     - " . basename($file) . "\n";
    }
    echo "  ℹ️  SQL files won't run automatically - they need to be converted to migrations or run manually\n";
} else {
    echo "  ✅ No SQL files found\n";
}

// Summary
echo "\n═══════════════════════════════════════════════════════\n";
echo "📊 Summary:\n";
echo "  Migrations without safety checks: " . count($unsafeMigrations) . "\n";
echo "  Migrations with syntax errors: " . count($syntaxErrors) . "\n";
echo "  Seeders without safety checks: " . count($unsafeSeeders) . "\n";
echo "  Factories with old syntax: " . count($oldFactorySyntax) . "\n";
echo "  SQL files found: " . count($sqlFiles) . "\n";
echo "═══════════════════════════════════════════════════════\n";

if (!empty($issues)) {
    echo "\n⚠️  Additional Issues Found:\n";
    foreach ($issues as $issue) {
        echo "  $issue\n";
    }
}

if (count($unsafeMigrations) > 0 || count($syntaxErrors) > 0 || count($unsafeSeeders) > 0) {
    echo "\n❌ Issues found that could cause Railway deployment to crash!\n";
    echo "   Run fix scripts or fix manually before deploying.\n";
} else {
    echo "\n✅ No critical issues found! Database folder is ready for Railway deployment.\n";
}

