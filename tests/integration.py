import http.cookiejar
import urllib.request
import urllib.parse
import urllib.error
import re
import json
import datetime
import time
import sys
from pathlib import Path

BASE = sys.argv[1] if len(sys.argv) > 1 else 'http://localhost/lepestok-practice/'
results = []

class Client:
    def __init__(self):
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))

    def request(self, path, data=None):
        body = urllib.parse.urlencode(data).encode() if data is not None else None
        try:
            response = self.opener.open(BASE + path, data=body, timeout=20)
        except urllib.error.HTTPError as error:
            response = error
        return response.status, response.read().decode('utf-8'), response.url

    def post(self, page, data):
        _, html, _ = self.request('index.php?page=' + page)
        token = re.search(r'name="csrf" value="([a-f0-9]+)"', html).group(1)
        return self.request('index.php?page=' + page, dict(data, csrf=token))

def check(name, condition):
    results.append({'test': name, 'passed': bool(condition)})
    print(('PASS ' if condition else 'FAIL ') + name)
    if not condition:
        raise AssertionError(name)

def run():
    anon = Client()
    status, html, _ = anon.request('index.php')
    check('Homepage with four products', status == 200 and html.count('data-product-category=') == 4)
    check('Guest API rejected', anon.request('api.php?resource=orders')[0] == 401)
    check('Unknown page 404', anon.request('index.php?page=missing')[0] == 404)
    check('Config protected by Apache', anon.request('config.php')[0] == 403)
    check('SQL protected by Apache', anon.request('sql/schema.sql')[0] in [403, 404])
    check('CSRF rejected', anon.request('index.php?page=register', {'action':'register'})[0] == 403)
    stamp = str(int(time.time()))
    login = 'qa' + stamp
    account = dict(action='register', login=login, password='PracticeTest2026!', full_name='Тестовый Покупатель', phone='8(999)111-22-33', email='qa@example.test')
    status, html, url = anon.post('register', dict(account, login='bad', phone='123', email='broken'))
    check('Server validation rejects invalid registration', 'aria-invalid="true"' in html and 'page=register' in url)
    status, html, url = anon.post('register', account)
    check('Registration succeeds', 'page=login' in url and 'Аккаунт создан' in html)
    check('Login availability detects duplicate', not json.loads(anon.request('api.php?resource=login-available&login='+login)[1])['available'])
    _, html, url = anon.post('register', account)
    check('Duplicate login rejected', 'Этот логин уже занят' in html)
    _, html, _ = anon.post('login',dict(action='login',login=login,password='wrongpass'))
    check('Wrong password rejected', 'Неверный логин или пароль' in html)
    _, html, url = anon.post('login',dict(action='login',login=login,password=account['password']))
    check('Customer login succeeds', 'page=account' in url)
    check('Customer forbidden from admin', anon.request('index.php?page=admin')[0] == 403)
    date=(datetime.datetime.now(datetime.timezone(datetime.timedelta(hours=3))).date()+datetime.timedelta(days=2)).isoformat()
    order=dict(action='order',bouquet_id=1,quantity=2,delivery_date=date,delivery_slot='13:00–16:00',address='Москва, Тестовая улица, дом 1',recipient='Тестовый Получатель',recipient_phone='8(999)111-22-33',payment_method='card',note='<script>alert(1)</script>',unit_price='1',user_id='1',status_id='4')
    _, html, url = anon.post('order',dict(order,quantity=0,delivery_date='2020-01-01'))
    check('Invalid order rejected', 'Проверьте введённые данные' in html and 'page=order' in url)
    _, html, url = anon.post('order',order)
    check('Order created and redirected', 'page=account' in url and 'оформлен' in html)
    check('XSS escaped', '&lt;script&gt;alert(1)&lt;/script&gt;' in html and '<script>alert(1)</script>' not in html)
    rows=json.loads(anon.request('api.php?resource=orders')[1])['orders']
    oid=rows[0]['id']
    check('Server ignores forged price, owner and status', float(rows[0]['unit_price'])==3290 and rows[0]['status']=='Новый' and len(rows)==1)
    other=Client()
    other.post('login',dict(action='login',login='demouser',password='FlowerDemo2026!'))
    others=json.loads(other.request('api.php?resource=orders')[1])['orders']
    check('Orders isolated by owner', all(row['id']!=oid for row in others))
    admin=Client()
    _, _, url=admin.post('login',dict(action='login',login='adminmark',password='FlowerAdmin2026!'))
    check('Admin login succeeds', 'page=admin' in url)
    _, html, _=admin.post('admin',dict(action='status',order_id=oid,status_id=4))
    check('Illegal status transition rejected', 'Переход недоступен' in html)
    for target in [2,3,4]:
        _, html, _=admin.post('admin',dict(action='status',order_id=oid,status_id=target))
        check('Status transition to '+str(target), 'Статус заказа №'+str(oid)+' обновлён' in html)
    _, html, _=anon.request('index.php?page=account')
    check('Customer sees final status and history', 'История статусов' in html and 'Выполнен' in html and 'В доставке' in html)
    rows=json.loads(admin.request('api.php?resource=orders&status=4')[1])['orders']
    check('API status filter', bool(rows) and all(row['status']=='Выполнен' for row in rows))
    _, html, _=admin.request('index.php?page=admin&q='+str(oid))
    check('Search by order number', 'Найдено заказов: 1' in html)
    _, html, _=admin.request('index.php?page=admin&p=2')
    check('Pagination', 'aria-current="page">2</a>' in html)
    anon.post('account',dict(action='logout'))
    check('Logout clears access', anon.request('api.php?resource=orders')[0]==401)

if __name__ == '__main__':
    try:
        run()
    finally:
        target=Path(__file__).resolve().parents[1]/'docs'/'test-results.json'
        target.write_text(json.dumps({'checked_at':datetime.datetime.now().isoformat(),'base':BASE,'tests':results},ensure_ascii=False,indent=2),encoding='utf-8')
