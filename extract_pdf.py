import os

pdf_path = "C:/Users/PC/Downloads/ppt.pdf"
if os.path.exists(pdf_path):
    try:
        import pypdf
        reader = pypdf.PdfReader(pdf_path)
        print(f"=== Total PDF Pages: {len(reader.pages)} ===")
        for i, page in enumerate(reader.pages):
            print(f"--- PAGE {i+1} ---")
            print(page.extract_text())
    except Exception as e:
        print("pypdf error:", e)
        try:
            import fitz # PyMuPDF
            doc = fitz.open(pdf_path)
            for i, page in enumerate(doc):
                print(f"--- PAGE {i+1} ---")
                print(page.get_text())
        except Exception as e2:
            print("PyMuPDF error:", e2)

pptx_path = "C:/Users/PC/Downloads/JSN Renewable Energy Pvt Ltd Profile-Final.pptx"
if os.path.exists(pptx_path):
    try:
        from pptx import Presentation
        prs = Presentation(pptx_path)
        print(f"=== Total PPTX Slides: {len(prs.slides)} ===")
        for i, slide in enumerate(prs.slides):
            print(f"--- SLIDE {i+1} ---")
            for shape in slide.shapes:
                if shape.has_text_frame:
                    print(shape.text)
    except Exception as e:
        print("PPTX error:", e)
