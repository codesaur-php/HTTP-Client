# 🦖 codesaur/http-client  
PHP 8.2 эсвэл түүнээс дээш хувилбарт зориулсан, HTTP хүсэлт илгээх болон MIME имэйл боловсруулах/илгээх энгийн хөнгөн жинтэй, объект хандалтат http-client компонент.

---

## ✨ Онцлох боломжууд

- ✔ **CurlClient** - cURL дээр суурилсан уян хатан HTTP клиент  
- ✔ **JSONClient** - JSON өгөгдөлтэй REST API-тэй ажиллахад тохиромжтой  
- ✔ **Mail** - HTML + Text + олон хавсралттай MIME имэйл илгээгч  
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

# 📡 1. CurlClient - Ерөнхий HTTP клиент

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

# 🧩 2. JSONClient - JSON API-тэй ажиллах

**Анхаар:** JSONClient нь `CODESAUR_APP_ENV` environment variable-аас хамааруулан SSL verify-ийг тохируулна:
- `development` орчинд SSL verify унтраалттай (хөгжүүлэлтэд тохиромжтой)
- `production` эсвэл бусад орчинд SSL verify идэвхтэй (аюулгүй)

```bash
# .env файл эсвэл environment variable
CODESAUR_APP_ENV=development  # эсвэл production
```

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

# ✉ 3. Mail - MIME HTML + Хавсралттай имэйл клиент

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
tests/
 ├── CurlClientTest.php
 ├── JSONClientTest.php
 └── MailTest.php
composer.json
phpunit.xml
LICENSE
README.md
```

---

# 🧪 Тест ажиллуулах

Энэ төсөлд PHPUnit ашиглан unit тестүүд багтсан байна.

### Тест суурилуулах

```bash
composer install
```

### Тест ажиллуулах

#### 🪟 Windows (PowerShell / Command Prompt)

```powershell
# Бүх тест ажиллуулах
vendor\bin\phpunit.bat

# Тодорхой тест файл ажиллуулах
vendor\bin\phpunit.bat tests\CurlClientTest.php
vendor\bin\phpunit.bat tests\JSONClientTest.php
vendor\bin\phpunit.bat tests\MailTest.php

# Дэлгэрэнгүй мэдээлэлтэй ажиллуулах
vendor\bin\phpunit.bat --testdox
```

#### 🐧 Linux / 🍎 macOS (Bash / Zsh)

```bash
# Бүх тест ажиллуулах
vendor/bin/phpunit

# Тодорхой тест файл ажиллуулах
vendor/bin/phpunit tests/CurlClientTest.php
vendor/bin/phpunit tests/JSONClientTest.php
vendor/bin/phpunit tests/MailTest.php

# Дэлгэрэнгүй мэдээлэлтэй ажиллуулах
vendor/bin/phpunit --testdox
```

#### 🔧 Аль ч OS дээр (Composer Script)

```bash
# Composer script ашиглан (аль ч OS дээр ажиллана)
composer test

# Тодорхой тест файл (Windows дээр)
composer test -- tests\CurlClientTest.php

# Тодорхой тест файл (Linux/macOS дээр)
composer test -- tests/CurlClientTest.php
```

**Анхаар:** Composer script нь аль ч OS дээр ижилхэн ажиллана, учир нь Composer нь OS-оос хамаарахгүйгээр зөв командыг сонгоно.

### Тестийн бүтэц

- **CurlClientTest** - CurlClient классын GET, POST, PUT, DELETE хүсэлтүүд, алдааны боловсруулалт
- **JSONClientTest** - JSONClient классын JSON encode/decode, алдааны боловсруулалт
- **MailTest** - Mail классын хүлээн авагч, хавсралт, валидаци шалгалтууд

---

# 📄 Лиценз

Энэ төсөл MIT лицензтэй.

---

# 👨‍💻 Хөгжүүлэгч

Narankhuu  
📧 codesaur@gmail.com  
📱 +976 99000287  
🌐 https://github.com/codesaur  
