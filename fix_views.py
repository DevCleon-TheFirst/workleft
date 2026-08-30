import os
import re
import glob

def process_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    # Skip if already processed
    if 'min-h-full' in content and 'space-y-6' in content and '<template #header>' not in content:
        return

    # Replace <template #header> block
    header_pattern = re.compile(r'<template #header>([\s\S]*?)</template>', re.MULTILINE)
    
    def repl_header(match):
        inner = match.group(1)
        # Remove the first inner div wrapper for flex if it exists
        inner = re.sub(r'<div class="flex items-center justify-between">\s*', '', inner, count=1)
        # Since we removed the opening div, we should remove its corresponding closing div.
        # This is hacky with regex. Let's just wrap it instead of removing the inner div.
        inner = match.group(1)
        return f"""        <div class="p-6 space-y-6 min-h-full">
            <div class="pb-4 border-b border-gray-800">
{inner}
            </div>
            <div>"""

    new_content = header_pattern.sub(repl_header, content)

    # Replace `<div class="py-12">`
    new_content = new_content.replace('<div class="py-12">', '<div class="py-2">')

    # Replace styles
    new_content = new_content.replace('bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg', 'bg-gray-800 border border-gray-700 sm:rounded-md')
    new_content = new_content.replace('text-gray-900 dark:text-gray-100', 'text-gray-100')
    new_content = new_content.replace('text-white', 'text-gray-100')
    new_content = new_content.replace('ring-1 ring-white/5', 'border border-gray-700')
    new_content = new_content.replace('rounded-xl bg-gray-800 p-6 shadow-sm', 'rounded-md bg-gray-800 p-5')
    
    # Close the divs we opened
    if '<template #header>' in content:
        new_content = new_content.replace('</AuthenticatedLayout>', '            </div>\n        </div>\n    </AuthenticatedLayout>')

    with open(filepath, 'w') as f:
        f.write(new_content)
    print(f"Processed {filepath}")

vue_files = glob.glob('resources/js/Pages/**/*.vue', recursive=True)
for f in vue_files:
    if 'Auth' not in f and 'Profile' not in f and 'Dashboard' not in f and 'Welcome' not in f:
        process_file(f)
