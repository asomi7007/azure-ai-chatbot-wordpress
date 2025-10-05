#!/usr/bin/env php
<?php
/**
 * PO to MO Compiler
 * Converts PO translation files to MO binary format for WordPress
 */

if (php_sapi_name() !== 'cli') {
    die('This script must be run from the command line.');
}

class POtoMOCompiler {
    private $po_file;
    private $mo_file;
    
    public function __construct($po_file, $mo_file = null) {
        $this->po_file = $po_file;
        $this->mo_file = $mo_file ?: str_replace('.po', '.mo', $po_file);
    }
    
    public function compile() {
        if (!file_exists($this->po_file)) {
            throw new Exception("PO file not found: {$this->po_file}");
        }
        
        $translations = $this->parse_po_file();
        $this->write_mo_file($translations);
        
        return $this->mo_file;
    }
    
    private function parse_po_file() {
        $content = file_get_contents($this->po_file);
        $translations = [];
        
        // Parse PO file
        preg_match_all('/msgid\s+"(.*)"\s+msgstr\s+"(.*)"/Us', $content, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $msgid = $this->unescape_string($match[1]);
            $msgstr = $this->unescape_string($match[2]);
            
            // Skip empty msgid (header) and empty translations
            if ($msgid !== '' && $msgstr !== '') {
                $translations[$msgid] = $msgstr;
            }
        }
        
        return $translations;
    }
    
    private function unescape_string($string) {
        $string = str_replace(['\\"', '\\n', '\\t', '\\r'], ['"', "\n", "\t", "\r"], $string);
        return $string;
    }
    
    private function write_mo_file($translations) {
        $count = count($translations);
        
        // MO file format (GNU gettext)
        $magic = 0x950412de; // Little endian
        $revision = 0;
        
        $ids = '';
        $strings = '';
        $id_offsets = [];
        $str_offsets = [];
        
        foreach ($translations as $id => $str) {
            $id_offsets[] = [strlen($ids), strlen($id)];
            $ids .= $id . "\0";
            
            $str_offsets[] = [strlen($strings), strlen($str)];
            $strings .= $str . "\0";
        }
        
        // Calculate offsets
        $key_start = 28;
        $value_start = $key_start + ($count * 8);
        $id_start = $value_start + ($count * 8);
        $str_start = $id_start + strlen($ids);
        
        // Build binary data
        $mo = pack('Iiiiiii',
            $magic,          // Magic number
            $revision,       // File format revision
            $count,          // Number of strings
            $key_start,      // Offset of table with original strings
            $value_start,    // Offset of table with translated strings
            0,               // Size of hashing table
            0                // Offset of hashing table
        );
        
        // Write original string offsets
        foreach ($id_offsets as $offset) {
            $mo .= pack('ii', $offset[1], $id_start + $offset[0]);
        }
        
        // Write translated string offsets
        foreach ($str_offsets as $offset) {
            $mo .= pack('ii', $offset[1], $str_start + $offset[0]);
        }
        
        // Write original strings
        $mo .= $ids;
        
        // Write translated strings
        $mo .= $strings;
        
        // Write to file
        if (file_put_contents($this->mo_file, $mo) === false) {
            throw new Exception("Failed to write MO file: {$this->mo_file}");
        }
        
        return true;
    }
}

// Main execution
try {
    $languages_dir = __DIR__;
    $files = [
        'azure-ai-chatbot-ko_KR.po',
        'azure-ai-chatbot-en_US.po'
    ];
    
    echo "========================================\n";
    echo "PO to MO Compiler for Azure AI Chatbot\n";
    echo "========================================\n\n";
    
    $compiled = 0;
    foreach ($files as $file) {
        $po_path = $languages_dir . '/' . $file;
        $mo_path = str_replace('.po', '.mo', $po_path);
        
        if (!file_exists($po_path)) {
            echo "⏭️  Skipping: $file (not found)\n";
            continue;
        }
        
        echo "📝 Compiling: $file\n";
        
        try {
            $compiler = new POtoMOCompiler($po_path, $mo_path);
            $result = $compiler->compile();
            
            $size = filesize($mo_path);
            echo "✅ Created: " . basename($result) . " (" . number_format($size) . " bytes)\n";
            $compiled++;
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    echo "========================================\n";
    echo "Compilation completed!\n";
    echo "Total compiled: $compiled file(s)\n";
    echo "========================================\n";
    
    exit(0);
    
} catch (Exception $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
