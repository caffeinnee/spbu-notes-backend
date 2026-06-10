# Laravel Api Dummy
Laravel Api Dummy

### Kebutuhan Sistem
- PHP >= 8.2
- Composer 
- OS Linux Ubuntu versi terbaru (disarankan)
- Web server Nginx atau Apache (disarankan)
- Database MySQL atau SQLite (disarankan)

### Instalasi
1. Clone repositori `git clone https://github.com/mikhsanw/laravel-api-dummy.git`
2. Masuk ke folder project dengan `cd laravel-api-dummy`
3. Jalankan `composer install`
4. Salin file environment dengan `copy .env.example .env`
5. Jalankan `php artisan key:generate`
6. Jalankan `php artisan migrate`
7. Jalankan `php artisan storage:link`
8. Jalankan `php artisan serve --host=0.0.0.0 --port=8000`
9. Buka `http://localhost:8000` di browser

### Contoh Uji Coba API
Base URL:

`http://127.0.0.1:8000/api`

Sebelum menguji API, pastikan server Laravel sudah berjalan:

`php artisan serve`

#### 1. Registrasi User
```bash
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "username": "demouser",
    "email": "demo@example.com",
    "password": "password",
    "password_confirmation": "password",
    "hp": "08123456789"
  }'
```

#### 2. Login User
```bash
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "demo@example.com",
    "password": "password"
  }'
```

Simpan token dari response login, lalu gunakan token tersebut untuk endpoint yang dilindungi di bawah ini:

`Authorization: Bearer YOUR_TOKEN`

#### 3. Ambil Profil
```bash
curl -X GET http://127.0.0.1:8000/api/profile \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

#### 4. Buat Post
```bash
curl -X POST http://127.0.0.1:8000/api/posts \
  -H "Accept: application/json" \
  -F "title=Post Pertama" \
  -F "content=Ini isi post pertama"
```
Upload gambar opsional:
```bash
curl -X POST http://127.0.0.1:8000/api/posts \
  -H "Accept: application/json" \
  -F "title=Post Dengan Gambar" \
  -F "content=Contoh post dengan upload file" \
  -F "image=@/path/to/image.jpg"
```

#### 5. Ambil Daftar Post
```bash
curl -X GET http://127.0.0.1:8000/api/posts \
  -H "Accept: application/json"
```

#### 6. Ambil Detail Post
```bash
curl -X GET http://127.0.0.1:8000/api/posts/1 \
  -H "Accept: application/json"
```

#### 7. Ubah Post
```bash
curl -X PUT http://127.0.0.1:8000/api/posts/1 \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Post Pertama Updated",
    "content": "Konten post sudah diperbarui"
  }'
```

#### 8. Hapus Post
```bash
curl -X DELETE http://127.0.0.1:8000/api/posts/1 \
  -H "Accept: application/json"
```

#### 9. Logout User
```bash
curl -X POST http://127.0.0.1:8000/api/logout \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```
