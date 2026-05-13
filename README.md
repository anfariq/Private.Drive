Markdown
# ☁️ Private.Drive (Backend)

Private.Drive adalah sebuah proyek eksperimental arsitektur *backend* yang memanfaatkan **Telegram** sebagai media *cloud storage* alternatif. Dibangun menggunakan framework **Laravel**, sistem ini bekerja dengan cara menerima file, lalu menggunakan Telegram Bot API untuk meneruskan dan menyimpan file tersebut ke dalam sebuah Telegram Channel yang telah ditentukan.

> **⚠️ Security Notice:** Repositori ini hanya berisi struktur kode *backend*. Semua *core links*, kredensial, dan API Token asli telah diubah/disamarkan demi menjaga keamanan *environment* produksi.

## 🚀 Fitur Utama

- **Telegram Storage Integration:** Menggunakan Telegram Bot API untuk mengirim dan mengarsipkan dokumen/file ke dalam Telegram Channel.
- **Laravel Architecture:** Dibangun dengan standar arsitektur MVC dari Laravel untuk memastikan kode yang rapi dan mudah di-*maintain*.
- **Secure File Handling:** Memproses *file* yang diunggah sebelum diteruskan ke *endpoint* Telegram.

## 🛠️ Teknologi yang Digunakan

- **Framework:** Laravel (PHP)
- **API:** Telegram Bot API
- **HTTP Client:** Guzzle HTTP / Laravel HTTP Client

## ⚙️ Cara Instalasi (Local Setup)

1. Clone repository ini:
   ```bash
   git clone [https://github.com/anfariq/Private.Drive.git](https://github.com/anfariq/Private.Drive.git)
   cd Private.Drive
Install semua dependencies menggunakan Composer:

Bash
composer install

3. Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   
Generate application key Laravel:

Bash
php artisan key:generate

5. Konfigurasi kredensial Telegram Bot Anda di dalam file `.env`:
   ```env
   TELEGRAM_BOT_TOKEN=your_bot_token_here
   TELEGRAM_CHANNEL_ID=your_channel_id_here
   
(Pastikan bot Anda sudah dijadikan Administrator di dalam Channel tersebut agar bisa mengirim pesan/file).
6. Jalankan development server:

Bash
php artisan serve

💡 Konsep Kerja
Sistem backend menerima request upload file (misalnya dari client-side atau Postman).

Controller di Laravel menangkap file tersebut.

Laravel melakukan HTTP Request ke API Telegram (sendDocument, sendPhoto, dll) menggunakan TELEGRAM_BOT_TOKEN.

File akan terkirim dan tersimpan dengan aman di dalam channel Telegram yang didefinisikan pada TELEGRAM_CHANNEL_ID.
