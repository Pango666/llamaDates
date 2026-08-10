from pathlib import Path
import pdfplumber

source = Path(r"C:\Users\Pangod97\Desktop\Alcances.pdf")
target = Path(r"C:\laragon\www\llamaDates\tmp\qa-audit\alcances-text.txt")

parts = []
with pdfplumber.open(source) as pdf:
    parts.append(f"PAGES {len(pdf.pages)}")
    for index, page in enumerate(pdf.pages, start=1):
        parts.append(f"--- PAGE {index} ---\n{page.extract_text() or ''}")

target.write_text("\n".join(parts), encoding="utf-8")
