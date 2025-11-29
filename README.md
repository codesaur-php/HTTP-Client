# 🦖 codesaur/http-client  
PHP 8.2 эсвэл түүнээс дээш хувилбарт зориулсан, HTTP хүсэлт илгээх болон MIME имэйл боловсруулах/илгээх энгийн хөнгөн жинтэй, объект хандалтат http-client компонент.

---

## ✨ Онцлох боломжууд

- ✔ **CurlClient** — cURL дээр суурилсан уян хатан HTTP клиент  
- ✔ **JSONClient** — JSON өгөгдөлтэй REST API-тэй ажиллахад тохиромжтой  
- ✔ **Mail** — HTML + Text + олон хавсралттай MIME имэйл илгээгч  
- ✔ UTF-8 бүрэн дэмжлэг (нэрс, файлын нэр, гарчиг г.м.)  
- ✔ PSR-4 autoload  
- ✔ Хөнгөн, хурдан, ямар ч фрэймворк дээр эсвэл дангаар ашиглаж болно  
- ✔ Зөвхөн `ext-curl`, `ext-json` байхад л болно  

---

## 📦 Суурилуулалт

```bash
composer require codesaur/http-client
```

---

# 📡 1. CurlClient — Ерөнхий HTTP клиент

```php
use codesaur\Http\Client\CurlClient;

$curl = new CurlClient();

$response = $curl->request(
    'https://httpbin.org/get',
    'GET'
);

echo $response;
```

---

# 🧩 2. JSONClient — JSON API-тэй ажиллах

### GET хүсэлт

```php
use codesaur\Http\Client\JSONClient;

$client = new JSONClient();

$response = $client->get(
    'https://httpbin.org/get',
    ['hello' => 'world']
);

print_r($response);
```

### POST хүсэлт

```php
$response = $client->post(
    'https://httpbin.org/post',
    ['test' => 'codesaur']
);

echo $response['json']['test']; // codesaur
```

### Алдаа буцаах бүтэц

```json
{
  "error": { "code": 123, "message": "Алдаа үүссэн байна..." }
}
```

---

# ✉ 3. Mail — MIME HTML + Хавсралттай имэйл клиент

### Энгийн HTML имэйл илгээх

```php
use codesaur\Http\Client\Mail;

$mail = new Mail();

$mail->targetTo('user@example.com', 'Хэрэглэгч');
$mail->setFrom('no-reply@example.com', 'codesaur');
$mail->setSubject('Сайн байна уу?');
$mail->setMessage('<h1>Hello!</h1><p>Тест имэйл.</p>');

$mail->sendMail();
```

### Хавсралт нэмэх

```php
$mail->addFileAttachment(__DIR__ . '/file.pdf');
$mail->addUrlAttachment('https://example.com/logo.png');
$mail->addContentAttachment("Hello world", "note.txt");
```

### Олон хүлээн авагч

```php
$mail->addRecipient('a@example.com', 'Хүн А');
$mail->addCCRecipient('b@example.com', 'Хүн Б');
$mail->addBCCRecipient('c@example.com', 'Хүн С');
```

---

# 📂 Файлын бүтэц

```
example/
 ├── index.php
 ├── index_mail.php
 └── *.jpg
src/
 ├── CurlClient.php
 ├── JSONClient.php
 └── Mail.php
composer.json
LICENSE
README.md
```

---

# 📄 Лиценз

Энэ төсөл MIT лицензтэй.

---

# 👨‍💻 Хөгжүүлэгч

Narankhuu  
📧 codesaur@gmail.com  
📱 +976 99000287  
🌐 https://github.com/codesaur  
