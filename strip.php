<?php
// Function to strip all whitespace from a file
function strip_whitespace($inputFile, $outputFile) {
    // Check if the input file exists
    if (!file_exists($inputFile)) {
        die("Input file does not exist.");
    }

    // Read the content of the input file
    $content = file_get_contents($inputFile);

    // Strip all whitespace characters
    $strippedContent = preg_replace('/\s+/', '', $content);

    // Write the stripped content to the output file
    file_put_contents($outputFile, $strippedContent);

    echo "Whitespace stripped and content saved to $outputFile";
}

// Define the input and output file paths
$inputFile = __DIR__ . '/build/sitemap.xml';
$outputFile = __DIR__ . '/build/sitemap.xml';

// Call the function to strip whitespace
strip_whitespace($inputFile, $outputFile);
