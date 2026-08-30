import os
import re
import glob

def process_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    # Avoid replacing indigo in getStatusColor functions to preserve semantic status colors
    def repl(m):
        # check if it's inside getStatusColor context roughly by looking at the line
        return m.group(0)

    lines = content.split('\n')
    new_lines = []
    in_status_block = False
    
    for line in lines:
        if 'getStatusColor' in line:
            in_status_block = True
            
        if in_status_block and '}' in line and 'return' not in line and 'switch' not in line:
            # roughly ending block
            in_status_block = False
            
        if not in_status_block or ('getStatusColor' not in line and 'case' not in line):
            # Replace indigo with brand
            line = re.sub(r'\bindigo-(\d+)\b', r'brand-\1', line)
            
        new_lines.append(line)
        
        if '};' in line and in_status_block:
            in_status_block = False

    new_content = '\n'.join(new_lines)
    
    # Let's also update the logo in AuthenticatedLayout.vue
    if 'AuthenticatedLayout.vue' in filepath:
        # replace the text 'Dev Command Center' or any SVG logo with the image
        logo_html = '<img src="/logo.png" alt="Cleon Innovations" class="w-8 h-8 object-contain" />'
        new_content = re.sub(r'<div class="w-8 h-8 rounded-lg bg-indigo-600.*?</svg>\s*</div>', logo_html, new_content, flags=re.DOTALL)
        new_content = re.sub(r'<div class="w-8 h-8 rounded-lg bg-brand-600.*?</svg>\s*</div>', logo_html, new_content, flags=re.DOTALL)
        # Or if it's just the 'C' logo:
        new_content = re.sub(r'<svg class="w-6 h-6 text-white".*?</svg>', logo_html, new_content, flags=re.DOTALL)

    if content != new_content:
        with open(filepath, 'w') as f:
            f.write(new_content)
        print(f"Updated {filepath}")

# Process all Vue files
vue_files = glob.glob('resources/js/**/*.vue', recursive=True)
for f in vue_files:
    process_file(f)
