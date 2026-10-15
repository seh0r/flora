from pathlib import Path
from integration import Client

folder = Path(__file__).resolve().parents[1] / 'tmp' / 'html'
folder.mkdir(parents=True, exist_ok=True)
client = Client()
for page in ['home', 'login', 'register']:
    (folder / (page + '.html')).write_text(client.request('index.php?page=' + page)[1], encoding='utf-8')
client.post('login', dict(action='login', login='demouser', password='FlowerDemo2026!'))
for page in ['account', 'order']:
    (folder / (page + '.html')).write_text(client.request('index.php?page=' + page)[1], encoding='utf-8')
admin = Client()
admin.post('login', dict(action='login', login='adminmark', password='FlowerAdmin2026!'))
(folder / 'admin.html').write_text(admin.request('index.php?page=admin')[1], encoding='utf-8')
