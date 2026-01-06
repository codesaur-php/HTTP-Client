# 🦖 codesaur/http-client  

HTTP хүсэлт илгээх болон MIME имэйл боловсруулах/илгээх энгийн хөнгөн жинтэй, объект хандалтат http-client компонент.

---

## ✨ Онцлох боломжууд

- ✔ **CurlClient** - cURL дээр суурилсан уян хатан HTTP клиент  
- ✔ **JSONClient** - JSON өгөгдөлтэй REST API-тэй ажиллахад тохиромжтой  
- ✔ **Mail** - HTML + Text + олон хавсралттай MIME имэйл илгээгч  
- ✔ UTF-8 бүрэн дэмжлэг (нэрс, файлын нэр, гарчиг г.м.)  
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

## 🧪 Тест ажиллуулах

### Composer Test Command-ууд

```bash
# Бүх тест ажиллуулах (Unit + Integration тестүүд)
composer test

# Зөвхөн Unit тестүүд ажиллуулах
composer test:unit

# Зөвхөн Integration тестүүд ажиллуулах
composer test:integration

# Unit болон Integration тестүүдийг дараалан ажиллуулах
composer test:all

# HTML coverage report үүсгэх (coverage/ directory дотор)
composer test:coverage
```

### Тестүүдийн мэдээлэл

- **Unit Tests**: CurlClient, JSONClient, Mail классуудын тест
- **Integration Tests**: Бодит API-тай ажиллах integration тестүүд
- **EndToEndTest**: Бүх компонентуудыг хамтдаа ашиглах end-to-end тест

### PHPUnit шууд ашиглах

Composer command-уудын оронд PHPUnit-ийг шууд ажиллуулж болно:

```bash
# Бүх тест ажиллуулах
vendor/bin/phpunit

# Зөвхөн Unit тестүүд
vendor/bin/phpunit --testsuite Unit

# Зөвхөн Integration тестүүд
vendor/bin/phpunit --testsuite Integration

# Coverage report (Clover XML формат)
vendor/bin/phpunit --coverage-clover coverage/clover.xml

# Coverage report (HTML формат)
vendor/bin/phpunit --coverage-html coverage
```

**Windows хэрэглэгчид:** `vendor/bin/phpunit`-ийг `vendor\bin\phpunit.bat`-аар солино уу

---

# 🔄 CI/CD Pipeline

Энэ төсөл нь GitHub Actions ашиглан CI/CD pipeline-тэй:

- ✅ **Автомат тест** - Push эсвэл Pull Request үед тест ажиллуулна
- ✅ **Олон PHP хувилбар** - PHP 8.2, 8.3 дээр шалгана
- ✅ **Олон OS** - Ubuntu болон Windows дээр шалгана
- ✅ **Code Coverage** - Pull Request үед coverage мэдээлэл үүсгэнэ
- ✅ **Security Check** - Composer audit ажиллуулна
- ✅ **Code Linting** - PHP syntax шалгана

CI/CD pipeline-ийн дэлгэрэнгүй мэдээлэл: [.github/workflows/ci.yml](.github/workflows/ci.yml)

---

# 📄 Лиценз

Энэ төсөл MIT лицензтэй.

---

# 👨‍💻 Зохиогч

Narankhuu  
https://github.com/codesaur  
