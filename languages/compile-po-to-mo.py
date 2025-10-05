#!/usr/bin/env python3
"""
PO to MO Compiler for WordPress
Converts PO translation files to MO binary format
"""

import struct
import os
import sys

def parse_po_file(po_path):
    """Parse PO file and extract translations"""
    translations = {}
    
    with open(po_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Simple parser for msgid/msgstr pairs
    lines = content.split('\n')
    i = 0
    
    while i < len(lines):
        line = lines[i].strip()
        
        # Skip comments and empty lines
        if not line or line.startswith('#'):
            i += 1
            continue
        
        # Look for msgid
        if line.startswith('msgid'):
            # Extract msgid
            msgid = line[6:].strip(' "')
            i += 1
            
            # Continue reading multi-line msgid
            while i < len(lines) and lines[i].strip().startswith('"'):
                msgid += lines[i].strip(' "')
                i += 1
            
            # Look for msgstr
            if i < len(lines) and lines[i].strip().startswith('msgstr'):
                msgstr = lines[i].strip()[7:].strip(' "')
                i += 1
                
                # Continue reading multi-line msgstr
                while i < len(lines) and lines[i].strip().startswith('"'):
                    msgstr += lines[i].strip(' "')
                    i += 1
                
                # Unescape strings
                msgid = msgid.replace('\\n', '\n').replace('\\t', '\t').replace('\\"', '"')
                msgstr = msgstr.replace('\\n', '\n').replace('\\t', '\t').replace('\\"', '"')
                
                # Add to translations (skip empty msgid and msgstr)
                if msgid and msgstr:
                    translations[msgid] = msgstr
        else:
            i += 1
    
    return translations

def write_mo_file(mo_path, translations):
    """Write translations to MO file"""
    
    # MO file format constants
    MAGIC = 0x950412de  # Little endian magic number
    VERSION = 0
    
    # Prepare strings
    keys = sorted(translations.keys())
    ids = b''.join(k.encode('utf-8') + b'\x00' for k in keys)
    strs = b''.join(translations[k].encode('utf-8') + b'\x00' for k in keys)
    
    # Calculate offsets
    key_start = 7 * 4 + 16 * len(keys)  # Header + offset table
    val_start = key_start + len(ids)
    
    # Build offset tables
    key_offsets = []
    str_offsets = []
    
    ids_offset = 0
    strs_offset = 0
    
    for key in keys:
        key_len = len(key.encode('utf-8'))
        str_len = len(translations[key].encode('utf-8'))
        
        key_offsets.append((key_len, key_start + ids_offset))
        str_offsets.append((str_len, val_start + strs_offset))
        
        ids_offset += key_len + 1
        strs_offset += str_len + 1
    
    # Build MO file
    mo = struct.pack(
        '<7I',  # Little endian, 7 unsigned integers
        MAGIC,              # Magic number
        VERSION,            # Version
        len(keys),          # Number of strings
        28,                 # Offset of original string table
        28 + len(keys) * 8, # Offset of translated string table
        0,                  # Hash table size
        0                   # Hash table offset
    )
    
    # Add key offset table
    for length, offset in key_offsets:
        mo += struct.pack('<2I', length, offset)
    
    # Add string offset table
    for length, offset in str_offsets:
        mo += struct.pack('<2I', length, offset)
    
    # Add strings
    mo += ids + strs
    
    # Write file
    with open(mo_path, 'wb') as f:
        f.write(mo)

def main():
    print('========================================')
    print('PO to MO Compiler for Azure AI Chatbot')
    print('========================================\n')
    
    # Get script directory
    script_dir = os.path.dirname(os.path.abspath(__file__))
    
    # Files to compile
    files = [
        'azure-ai-chatbot-ko_KR.po',
        'azure-ai-chatbot-en_US.po'
    ]
    
    compiled = 0
    
    for po_file in files:
        po_path = os.path.join(script_dir, po_file)
        mo_path = po_path.replace('.po', '.mo')
        
        if not os.path.exists(po_path):
            print(f'⏭️  Skipping: {po_file} (not found)')
            continue
        
        print(f'📝 Compiling: {po_file}')
        
        try:
            translations = parse_po_file(po_path)
            write_mo_file(mo_path, translations)
            
            size = os.path.getsize(mo_path)
            mo_name = os.path.basename(mo_path)
            print(f'✅ Created: {mo_name} ({size:,} bytes)')
            print(f'   Translations: {len(translations)}\n')
            
            compiled += 1
        except Exception as e:
            print(f'❌ Error: {str(e)}\n')
    
    print('========================================')
    print(f'Compilation completed!')
    print(f'Total compiled: {compiled} file(s)')
    print('========================================')
    
    return 0 if compiled > 0 else 1

if __name__ == '__main__':
    sys.exit(main())
