# ☁️ Private.Drive (Backend)

Private.Drive adalah sebuah proyek backend berbasis **Laravel** yang memanfaatkan **Telegram** sebagai media **cloud storage alternatif**. Sistem ini memungkinkan pengguna untuk mengunggah, mengelola, membagikan, dan mencadangkan file menggunakan Telegram Bot API sebagai infrastruktur penyimpanan.

Setiap file yang diunggah akan dikirim ke Telegram Channel melalui bot, kemudian metadata file disimpan di database untuk mendukung fitur seperti manajemen grup, pembagian akses, backup file, dan monitoring kapasitas penyimpanan pengguna.

Dengan pendekatan ini, Telegram berfungsi sebagai storage backend yang praktis, cepat, stabil, dan minim biaya operasional.

> ⚠️ **Security Notice**
>
> Repository ini hanya berisi source code backend. Seluruh kredensial sensitif seperti API Token, Channel ID, URL internal, dan konfigurasi produksi telah dihapus atau disamarkan untuk menjaga keamanan environment produksi.

---

## 🚀 Fitur Utama

### 📦 Telegram Storage Integration
Menggunakan Telegram Bot API (`sendDocument`, `sendPhoto`, dan endpoint lainnya) untuk menyimpan file ke Telegram Channel.

### 👤 Authentication & Authorization
Menggunakan Laravel Sanctum untuk autentikasi API berbasis token.

### 🛡️ User Verification System
Middleware `verified.user` memastikan hanya pengguna yang telah diverifikasi yang dapat mengakses fitur utama.

### 👥 Group Collaboration
Mendukung pembuatan grup publik, penambahan anggota, dan berbagi file dalam grup.

### 📂 Private & Group File Storage
File dapat disimpan secara pribadi maupun dibagikan ke dalam grup.

### ♻️ Backup to Private Storage
File grup dapat dicadangkan ke penyimpanan pribadi pengguna.

### 📊 Storage Quota Monitoring
Menampilkan penggunaan storage dan batas kapasitas masing-masing pengguna.

### 🔐 Role-Based Access Control
Admin memiliki akses khusus untuk verifikasi user, pembuatan akun, dan pengaturan kapasitas storage.

### ⚡ RESTful API Ready
Dapat digunakan sebagai backend untuk aplikasi web, mobile, maupun desktop.

---

## 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi |
|--------:|-----------|
| Framework | PHP Laravel |
| Authentication | Laravel Sanctum |
| API Integration | Telegram Bot API |
| HTTP Client | Laravel HTTP Client / Guzzle |
| Database | MySQL / PostgreSQL |
| Storage Backend | Telegram Channel |
| Authorization | Laravel Gates & Policies |
| Environment Management | `.env` Configuration |

---

## 📁 Struktur Proyek

```text
Private.Drive/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthenticatedSessionController.php
│   │   │   ├── AdminController.php
│   │   │   └── FileController.php
│   ├── Models/
│   ├── Policies/
│   └── Services/
├── routes/
│   └── api.php
├── database/
├── storage/
├── config/
├── .env.example
└── README.md
```

---

## ⚙️ Instalasi Lokal

### 1. Clone Repository

```bash
git clone https://github.com/anfariq/Private.Drive.git
cd Private.Drive
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Salin File Environment

```bash
cp .env.example .env
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Konfigurasi Database

Sesuaikan konfigurasi database pada file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=private_drive
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Konfigurasi Telegram Bot

```env
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHANNEL_ID=your_channel_id
```

> Pastikan bot telah ditambahkan sebagai **Administrator** di channel tujuan.

### 7. Jalankan Migrasi Database

```bash
php artisan migrate
```

### 8. Jalankan Development Server

```bash
php artisan serve
```

Server akan berjalan pada:

```text
http://127.0.0.1:8000
```

---

## 🤖 Cara Membuat Telegram Bot

1. Buka https://t.me/BotFather
2. Jalankan `/newbot`
3. Ikuti instruksi pembuatan bot
4. Simpan token yang diberikan
5. Tambahkan bot ke channel Telegram sebagai Administrator
6. Gunakan `channel_id` channel tersebut pada file `.env`

---

# 🔐 Authentication

## Login

```http
POST /api/login
```

### Request Body

```json
{
  "email": "user@example.com",
  "password": "password"
}
```

### Response

```json
{
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxx"
}
```

---

# 📡 API Endpoints

Semua endpoint berikut memerlukan autentikasi menggunakan Bearer Token serta status user yang telah diverifikasi.

---

## 👥 Group Management

| Method | Endpoint | Deskripsi |
|------:|----------|-----------|
| GET | `/api/groups` | Menampilkan seluruh grup |
| POST | `/api/public/groups` | Membuat grup publik |
| DELETE | `/api/groups/{group}` | Menghapus grup |
| GET | `/api/groups/{group}/files` | Menampilkan file dalam grup |
| POST | `/api/groups/{group}/members` | Menambahkan anggota ke grup |

---

## 📂 File Management

| Method | Endpoint | Deskripsi |
|------:|----------|-----------|
| GET | `/api/files` | Menampilkan seluruh file milik user |
| POST | `/api/files/upload/private` | Upload file ke storage pribadi |
| POST | `/api/files/upload/group/{groupId}` | Upload file ke grup |
| GET | `/api/files/download/{file}` | Download file |
| POST | `/api/files/{file}/backup` | Backup file grup ke private storage |
| DELETE | `/api/files/{file}` | Menghapus file |

---

## 📊 User Storage Information

| Method | Endpoint | Deskripsi |
|------:|----------|-----------|
| GET | `/api/user-storage` | Menampilkan penggunaan dan limit storage user |

### Contoh Response

```json
{
  "user_id": 1,
  "name": "John Doe",
  "used": 512.35,
  "limit": 20480,
  "role": "user"
}
```

> `used` dinyatakan dalam MB.  
> `limit` dinyatakan dalam MB (misalnya 20 GB = 20480 MB).

---

## 👑 Admin Endpoints

Endpoint berikut hanya dapat diakses oleh pengguna dengan role `admin`.

| Method | Endpoint | Deskripsi |
|------:|----------|-----------|
| PUT | `/api/admin/users/{id}/verify` | Verifikasi / batal verifikasi user |
| GET | `/api/admin/users` | Menampilkan daftar user |
| POST | `/api/admin/users` | Membuat user baru |
| PUT | `/api/admin/users/{id}/storage` | Mengubah limit storage user |

---

## 🔑 Header Authorization

```http
Authorization: Bearer YOUR_ACCESS_TOKEN
Accept: application/json
```

---

## 💡 Arsitektur Sistem

```text
Client Application
        │
        ▼
Laravel REST API
        │
        ▼
Authentication (Sanctum)
        │
        ▼
Controllers
        │
        ▼
Telegram Storage Service
        │
        ▼
Telegram Bot API
        │
        ▼
Telegram Channel
        │
        ▼
Database Metadata
```

---

## 📌 Keunggulan Sistem

- Tidak membutuhkan layanan cloud storage berbayar.
- Memanfaatkan infrastruktur Telegram yang stabil.
- Mendukung private storage dan group sharing.
- Tersedia fitur backup antar storage.
- Memiliki sistem kuota penyimpanan.
- Mendukung role-based access control.
- REST API siap diintegrasikan ke frontend apa pun.

---

## ⚠️ Keterbatasan

- Bergantung pada kebijakan Telegram.
- Ukuran file mengikuti limit Telegram Bot API.
- Membutuhkan bot administrator di channel.
- Tidak ditujukan untuk enterprise-scale tanpa optimasi tambahan.

---

## 🔒 Best Practices Keamanan

- Simpan token hanya di `.env`.
- Terapkan rate limiting.
- Validasi tipe dan ukuran file.
- Gunakan HTTPS pada production.
- Batasi akses endpoint dengan middleware dan policy.
- Pertimbangkan enkripsi file untuk data sensitif.

---

## 🗺️ Roadmap Pengembangan

- [ ] End-to-end file encryption
- [ ] Versioning file
- [ ] File preview
- [ ] Audit logs
- [ ] Search & tagging
- [ ] Signed URL access
- [ ] Notification system
- [ ] Scheduled cleanup

---

## 📄 Lisensi

Proyek ini menggunakan lisensi MIT.

---

## 👨‍💻 Author

**Andicha Fariq Putra Pratama**  
Chief Technology Officer & Software Engineer

- GitHub: https://github.com/anfariq
- LinkedIn: https://www.linkedin.com/in/andifariq

---

## ⭐ Dukungan

Jika proyek ini bermanfaat, berikan ⭐ pada repository berikut:

https://github.com/anfariq/Private.Drive
