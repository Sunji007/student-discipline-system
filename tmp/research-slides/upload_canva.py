from pathlib import Path
import requests
base = Path(__file__).resolve().parent
url_path = base / 'canva-upload-url.txt'
url = url_path.read_text(encoding='utf-8').strip()
pdf = base.parents[1] / 'output/pdf/student-discipline-presentation-sdlc.pdf'
with pdf.open('rb') as data:
    response = requests.post(url, data=data, headers={'Content-Type': 'application/octet-stream'}, timeout=180, allow_redirects=False)
print('HTTP', response.status_code)
print(response.text)
(base / 'canva-upload-result.json').write_text(response.text, encoding='utf-8')
url_path.unlink()
