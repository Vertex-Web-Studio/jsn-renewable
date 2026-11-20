import zipfile
import xml.etree.ElementTree as ET
import os

pptx_path = "C:/Users/PC/Downloads/JSN Renewable Energy Pvt Ltd Profile-Final.pptx"
if os.path.exists(pptx_path):
    print(f"Reading PPTX: {pptx_path}")
    with zipfile.ZipFile(pptx_path, 'r') as z:
        slides = [f for f in z.namelist() if f.startswith('ppt/slides/slide') and f.endswith('.xml')]
        slides.sort(key=lambda x: int(''.join(filter(str.isdigit, x))))
        for s in slides:
            print(f"\n==================== {s} ====================")
            xml_content = z.read(s)
            root = ET.fromstring(xml_content)
            texts = [node.text for node in root.iter() if node.text and node.text.strip()]
            print(" ".join(texts))

pdf_path = "C:/Users/PC/Downloads/ppt.pdf"
if os.path.exists(pdf_path):
    print(f"\nReading PDF raw text strings from: {pdf_path}")
    with open(pdf_path, 'rb') as f:
        content = f.read()
        import re
        # Find readable text sequences inside PDF
        text_matches = re.findall(b'\((.*?)\)', content)
        clean_texts = [m.decode('latin1', errors='ignore') for m in text_matches if len(m) > 3]
        print("\n".join(clean_texts[:100]))
