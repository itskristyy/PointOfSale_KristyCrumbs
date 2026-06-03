# 🥐🍰🍔 ✦ KristyCrumbs POS ✦
> *｢ From kitchen to cashier, seamlessly ｣*

![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-8892BF?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-F29111?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-v5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-brightgreen?style=for-the-badge)

Bilingual technical documentation for **KristyCrumbs POS System**.
*Dokumentasi teknis dwibahasa untuk **Sistem POS KristyCrumbs**.*

---

## 1. Tinjauan Proyek / Project Overview

### **Bahasa Indonesia**
**KristyCrumbs POS** adalah aplikasi manajemen restoran dan Point of Sale (POS) berbasis web yang dirancang untuk merampingkan alur operasional bisnis makanan dan minuman (F&B). Aplikasi ini mempermudah pencatatan transaksi pelanggan, pemrosesan antrean memasak di dapur, pengelolaan stok menu, pengaturan meja makan, serta penyusunan laporan omzet untuk pemilik usaha.

Sistem ini didesain dengan antarmuka web modern bernuansa hangat (*cream & warm brown*) menggunakan **Bootstrap 5** dan **Boxicons**, memberikan tampilan premium yang responsif serta nyaman digunakan baik di perangkat desktop maupun tablet.

Aplikasi membagi hak akses pengguna secara ketat ke dalam **4 Peran Utama (Roles)**:
1. **Admin**: Bertanggung jawab penuh terhadap manajemen sistem. Admin dapat mengelola menu makanan/minuman, mengelola akun staf/user, mengatur nomor meja pelanggan, membatalkan transaksi yang bermasalah (dengan pemulihan stok otomatis), serta melihat ringkasan seluruh laporan transaksi.
2. **Kasir**: Melayani pemesanan langsung dari pelanggan. Kasir dilengkapi dengan fitur *interactive shopping cart* dinamis untuk menambah/mengurangi kuantitas menu, menambahkan catatan khusus per pesanan (contoh: *"tanpa es"*, *"tidak pedas"*), memilih metode pembayaran (Tunai, QRIS, Transfer, Kartu), menghitung kembalian, serta mencetak struk belanja pelanggan secara instan.
3. **Dapur**: Memantau antrean pesanan makanan/minuman yang harus disiapkan secara real-time. Halaman dapur dilengkapi dengan penanda status meja yang intuitif (Merah = Terisi, Hijau = Kosong), opsi cetak struk dapur khusus koki, dan tombol konfirmasi "Siap Saji" untuk menyelesaikan antrean memasak.
4. **Owner (Pemilik)**: Memantau performa keuangan bisnis. Owner dapat memantau pendapatan harian secara langsung, menggunakan filter laporan penjualan harian atau mingguan secara spesifik, serta mencetak dokumen laporan pendapatan.

**Teknologi & Dependensi Utama:**
- **Core Engine**: PHP (Versi kompatibilitas 7.4 s/d 8.3+)
- **Database**: MySQL / MariaDB (Interaksi database menggunakan ekstensi `mysqli`)
- **Tampilan (Frontend)**: HTML5, CSS (Vanilla CSS & Custom Bootstrap 5.3.3 Styling), JavaScript (ES6+ Asynchronous Fetch API), Boxicons v2.1.4
- **Keamanan**: PHP Session (`session_start()`), enkripsi password kuat menggunakan `password_hash()` dan `password_verify()`, serta pencegahan celah keamanan SQL Injection dengan menggunakan Prepared Statements di semua query sensitif.

---

### **English**
**KristyCrumbs POS** is a web-based restaurant management and Point of Sale (POS) application designed to streamline the operational workflow of Food and Beverage (F&B) businesses. The application simplifies customer order recording, kitchen cooking queue processing, menu stock management, dining table assignments, and revenue reporting for the business owner.

The system features a warm-toned, modern web interface (*cream & warm brown*) utilizing **Bootstrap 5** and **Boxicons**, delivering a premium, highly responsive user experience optimized for both desktop and tablet screens.

Access control is strictly divided into **4 Core User Roles**:
1. **Admin**: Holds full administrative privileges. Admins can manage food/beverage menus, create or edit user accounts, register dining tables, cancel problematic orders (which automatically restores item stock), and view comprehensive transaction summaries.
2. **Kasir (Cashier)**: Handles direct customer sales. Cashiers are equipped with an interactive, dynamic shopping cart to adjust quantities, write custom notes per item (e.g. *"no ice"*, *"less spicy"*), select payment methods (Cash, QRIS, Transfer, Card), calculate change, and print customer bills instantly.
3. **Dapur (Kitchen)**: Monitors active cooking queues for incoming food and beverage orders in real-time. The kitchen screen features intuitive visual table status markers (Red = Occupied, Green = Vacant), print-friendly kitchen slips for chefs, and a "Ready to Serve" button to resolve active queues.
4. **Owner**: Observes overall business performance. The owner can check daily revenues at a glance, filter sales history by specific days or weeks, and print out financial reports for business bookkeeping.

**Key Technology Stack & Dependencies:**
- **Core Engine**: PHP (Compatible with versions 7.4 up to 8.3+)
- **Database**: MySQL / MariaDB (Database interactions managed via the native `mysqli` extension)
- **Frontend Presentation**: HTML5, CSS (Vanilla CSS & Custom Bootstrap 5.3.3 Styling), JavaScript (ES6+ Asynchronous Fetch API), Boxicons v2.1.4
- **Security & Protection**: PHP Sessions (`session_start()`), secure password encryption using `password_hash()` and `password_verify()`, and SQL Injection protection utilizing Prepared Statements on all sensitive database queries.

---

## 2. Instalasi & Setup / Installation & Setup

### **Bahasa Indonesia**

#### **Persyaratan Sistem:**
- **Web Server**: Apache atau Nginx (Sangat direkomendasikan memakai bundel local server seperti **Laragon** atau **XAMPP**).
- **Versi PHP**: PHP 7.4, 8.0, 8.1, 8.2, atau versi lebih tinggi.
- **Database**: MySQL 5.7+ atau MariaDB 10.3+.
- **Web Browser**: Versi terbaru dari Google Chrome, Microsoft Edge, Mozilla Firefox, atau Safari.

#### **Langkah-Langkah Instalasi:**
1. **Kloning Proyek ke Web Root Server Anda:**
   Letakkan berkas proyek di dalam folder root server lokal Anda (misal `C:\laragon\www\` untuk Laragon, atau `C:\xampp\htdocs\` untuk XAMPP).
   ```bash
   # Masuk ke direktori web root Anda lewat terminal, lalu jalankan:
   git clone https://github.com/Kristyyyvy/nnnn.git
   ```

2. **Import Skema Database MySQL:**
   - Aktifkan MySQL/MariaDB server Anda lewat aplikasi Laragon/XAMPP.
   - Buka program manajemen database pilihan Anda (misalnya **phpMyAdmin**, **HeidiSQL**, atau **DBeaver**).
   - Buat database baru dengan nama `db_resto`.
   - Pilih database `db_resto`, lalu pilih menu **Import** dan unggah berkas skema `db_resto (3).sql` yang tersedia pada direktori utama proyek ini.

3. **Konfigurasi Sambungan Database:**
   - Buka file `function/connect.php` menggunakan teks editor Anda (VS Code, Notepad++, dll.).
   - Sesuaikan nilai variabel sambungan database di bawah ini agar cocok dengan konfigurasi server MySQL lokal Anda:
     ```php
     $server = "localhost";   // Host database lokal Anda
     $username = "root";      // Username database Anda
     $password = "";          // Password database Anda (kosongkan jika bawaan XAMPP/Laragon)
     $database = "db_resto";  // Nama database restoran Anda
     ```

4. **Menjalankan Aplikasi POS:**
   - **Laragon**: Jika menggunakan Laragon dengan mode virtual hosts, buka browser dan akses `http://nnnn.test/` atau `http://localhost/nnnn/`.
   - **XAMPP**: Buka browser dan akses alamat `http://localhost/nnnn/`.

5. **Masuk Menggunakan Akun Demo Bawaan:**
   Untuk mempermudah pengujian, kami telah menyediakan tombol *quick demo accounts fill-in* langsung di halaman login (`index.php`). Cukup klik salah satu chip peran di bawah form, lalu klik **Masuk**:
   
   | Peran / Role | Username | Password Bawaan |
   | --- | --- | --- |
   | **Owner** | `Owner` | `user_owner` |
   | **Admin** | `Admin` | `user_admin` |
   | **Kasir** | `Kasir` | `user_kasir` |
   | **Dapur** | `Dapur` | `user_dapur` |

---

### **English**

#### **System Requirements:**
- **Web Server**: Apache or Nginx (Highly recommended to utilize local server suites like **Laragon** or **XAMPP**).
- **PHP Version**: PHP 7.4, 8.0, 8.1, 8.2, or higher.
- **Database**: MySQL 5.7+ or MariaDB 10.3+.
- **Web Browser**: Modern releases of Google Chrome, Microsoft Edge, Mozilla Firefox, or Safari.

#### **Installation Steps:**
1. **Clone the Project to Your Server's Web Root:**
   Move the project files inside your local server's web root folder (e.g. `C:\laragon\www\` for Laragon, or `C:\xampp\htdocs\` for XAMPP).
   ```bash
   # Navigate to your web root directory via terminal, then execute:
   git clone https://github.com/Kristyyyvy/nnnn.git
   ```

2. **Import the MySQL Database Schema:**
   - Launch your local MySQL/MariaDB server via Laragon/XAMPP.
   - Open your preferred database management application (such as **phpMyAdmin**, **HeidiSQL**, or **DBeaver**).
   - Create a brand new database named `db_resto`.
   - Select the newly created database `db_resto`, head over to the **Import** tab, and upload the schema file `db_resto (3).sql` located in the root of the project folder.

3. **Configure Database Connection:**
   - Open the file `function/connect.php` using your code editor (VS Code, Notepad++, etc.).
   - Edit the database connection variables below to match your local MySQL server configurations:
     ```php
     $server = "localhost";   // Your local database host
     $username = "root";      // Your database username
     $password = "";          // Your database password (leave empty if using default XAMPP/Laragon)
     $database = "db_resto";  // Your database name
     ```

4. **Access the POS Web Application:**
   - **Laragon**: If virtual hosts are enabled, open your browser and navigate to `http://nnnn.test/` or `http://localhost/nnnn/`.
   - **XAMPP**: Open your browser and navigate to `http://localhost/nnnn/`.

5. **Log In Using Pre-Seeded Demo Accounts:**
   To facilitate immediate testing, we have implemented interactive *quick demo accounts fill-in* chips on the login page (`index.php`). Simply click any role chip beneath the login form to auto-populate credentials, and click **Masuk**:
   
   | Role | Username | Default Password |
   | --- | --- | --- |
   | **Owner** | `Owner` | `user_owner` |
   | **Admin** | `Admin` | `user_admin` |
   | **Kasir (Cashier)** | `Kasir` | `user_kasir` |
   | **Dapur (Kitchen)** | `Dapur` | `user_dapur` |

---

## 3. Cara Penggunaan / Usage

### **Bahasa Indonesia**

#### **Alur Operasional Aplikasi:**
1. **Langkah Awal (Oleh Admin)**:
   - Masuk sebagai `Admin`. Akses tab **Kelola Meja** untuk mendaftarkan nomor meja makan yang tersedia di restoran Anda.
   - Buka tab **Kelola Menu** untuk mengisi daftar menu makanan dan minuman beserta harga jual serta stok awal yang tersedia.
   - Akses tab **Kelola User** untuk menambahkan kredensial staf kasir dan tim dapur baru.
2. **Proses Pemesanan & Pembayaran (Oleh Kasir)**:
   - Masuk sebagai `Kasir`. Pilih menu pesanan pelanggan dengan menekan tombol `+` atau `−` untuk menyesuaikan kuantitas.
   - Tulis instruksi tambahan pada kolom **Catatan** di bawah item belanja jika pelanggan memiliki permintaan khusus (misal: *"sedikit es"*).
   - Masukkan nama pelanggan dan pilih nomor meja makan yang ditempati pelanggan.
   - Pilih metode bayar (**Tunai / QRIS / Transfer / Kartu**), masukkan nominal uang bayar pelanggan, lalu klik tombol **Proses & Bayar**.
   - Struk thermal printer belanja pelanggan (`print_invoice.php`) akan terbuka otomatis pada tab browser baru, siap dicetak atau disimpan.
3. **Penyajian Makanan (Oleh Dapur)**:
   - Masuk sebagai `Dapur`. Tim dapur akan melihat kartu antrean pesanan yang masuk secara real-time.
   - Koki dapat mencetak slip dapur dengan menekan tombol **Struk Dapur**.
   - Setelah makanan siap disajikan ke meja pelanggan, koki menekan tombol **Siap Saji**. Status meja makan otomatis terbarui, dan antrean memasak tersebut akan dibersihkan.
4. **Pemantauan Bisnis (Oleh Owner)**:
   - Masuk sebagai `Owner`. Dasbor owner menyajikan informasi perolehan omzet hari ini secara akurat.
   - Gunakan filter laporan fleksibel berdasarkan rentang hari atau minggu tertentu, lalu tekan **Tampilkan**. Owner juga dapat mencetak laporan fisik dengan menekan **Cetak Laporan**.

#### **Integrasi API Pemrosesan Checkout Transaksi:**
Aplikasi POS ini menggunakan modul JavaScript asinkron `fetch` untuk memproses checkout transaksi kasir. Data dikirimkan dalam objek `FormData` menuju file pemroses transaksi `checkout_kasir.php` untuk meminimalkan waktu tunggu kasir di toko:
```javascript
// Cuplikan pengiriman pesanan asinkron dari kasir.php
async function prosesCheckout() {
  const fd = new FormData();
  fd.append('nama_pelanggan', nama);
  fd.append('no_meja', meja);
  fd.append('bayar', bayar);
  fd.append('metode_bayar', document.getElementById('metode-bayar').value);
  fd.append('cart', JSON.stringify(items)); // Array objek keranjang belanja

  const res = await fetch('function/function_pesanan/checkout_kasir.php', {
    method: 'POST',
    body: fd
  });
  const data = await res.json();

  if (data.ok) {
    alert(`Transaksi Berhasil!\nTotal: Rp ${data.total}\nKembalian: Rp ${data.kembalian}`);
    window.open('print_invoice.php?id=' + data.id_pesanan, '_blank');
    location.reload();
  }
}
```

---

### **English**

#### **Core System Operations Flow:**
1. **Initial Operations Setup (By Admin)**:
   - Log in as `Admin`. Navigate to the **Kelola Meja** tab to register available physical dining table numbers.
   - Go to the **Kelola Menu** tab to populate food and beverage lists, including individual pricing and available inventory stock.
   - Use the **Kelola User** tab to configure new credentials for cashiers and kitchen operators.
2. **Order Intake & Transaction Settlement (By Cashier)**:
   - Log in as `Kasir`. Select customer orders from the menu grid by clicking the `+` or `−` buttons to adjust item quantities.
   - Write down specific requirements inside the **Catatan** input beneath each selected item in the cart (e.g. *"no sugar"*, *"less ice"*).
   - Enter the customer's name and choose their assigned dining table from the dropdown.
   - Select the transaction payment method (**Tunai / QRIS / Transfer / Kartu**), input the customer's cash payment, and click **Proses & Bayar**.
   - A print-friendly thermal billing invoice (`print_invoice.php`) will open automatically in a new browser tab, ready to be printed or saved.
3. **Food Preparation (By Kitchen)**:
   - Log in as `Dapur`. The kitchen queue interface displays incoming orders in real-time.
   - Kitchen staff can print out specific cooking slips by clicking the **Struk Dapur** button.
   - Once the dishes are ready to serve, the chef clicks **Siap Saji**. The system automatically updates the assigned table status, and clears the cooked order from the queue.
4. **Analytics & Performance Tracking (By Owner)**:
   - Log in as `Owner`. The owner dashboard presents real-time revenue breakdowns for today.
   - Utilize dates or weekly filters to display precise sales ranges, and trigger the web browser print system via the **Cetak Laporan** button.

#### **Asynchronous Checkout API Integration:**
The POS application leverages asynchronous JavaScript `fetch` calls to process cashier transactions. Data payload is packed inside a `FormData` object and posted to `checkout_kasir.php`, eliminating page refreshes and speeding up customer checkouts:
```javascript
// Sample transaction checkout submit from kasir.php
async function prosesCheckout() {
  const fd = new FormData();
  fd.append('nama_pelanggan', nama);
  fd.append('no_meja', meja);
  fd.append('bayar', bayar);
  fd.append('metode_bayar', document.getElementById('metode-bayar').value);
  fd.append('cart', JSON.stringify(items)); // Array of shopping cart items

  const res = await fetch('function/function_pesanan/checkout_kasir.php', {
    method: 'POST',
    body: fd
  });
  const data = await res.json();

  if (data.ok) {
    alert(`Success!\nTotal: Rp ${data.total}\nChange: Rp ${data.kembalian}`);
    window.open('print_invoice.php?id=' + data.id_pesanan, '_blank');
    location.reload();
  }
}
```

---

## 4. Struktur Folder / Folder Structure

### **Bahasa Indonesia**
Berikut merupakan bagan pohon direktori proyek KristyCrumbs POS yang menjelaskan struktur berkas dan kegunaan fungsional masing-masing komponen:

```
nnnn/
│
├── function/                           # Mengelompokkan seluruh berkas logika bisnis PHP
│   ├── auth.php                        # Mengatur session dan validasi hak akses berdasar peran (roles)
│   ├── connect.php                     # Mengatur parameter koneksi database lokal dengan ekstensi mysqli
│   ├── login.php                       # Memproses otentikasi login pengguna dan mengalihkan ke halaman yang sesuai
│   ├── logout.php                      # Menghancurkan session pengguna yang aktif dan mengarahkan kembali ke login
│   │
│   ├── function_meja/                  # Berkas logika manipulasi data meja makan
│   │   ├── add_meja.php                # Memvalidasi dan menyimpan nomor meja makan baru
│   │   └── delete_meja.php             # Menghapus nomor meja makan tertentu dari database
│   │
│   ├── function_menu/                  # Berkas logika manipulasi menu makanan & minuman
│   │   ├── add_menu.php                # Menyimpan menu baru beserta kategori, harga, dan jumlah stok
│   │   ├── delete_menu.php             # Menghapus item menu tertentu dari sistem database
│   │   └── update_menu.php             # Memproses pembaruan data menu (harga, nama, stok baru)
│   │
│   ├── function_pesanan/               # Berkas logika pemrosesan transaksi dan dapur
│   │   ├── cart_action.php             # Menyediakan fungsi tambahan manipulasi keranjang belanja belanja
│   │   ├── checkout_kasir.php          # Membuka transaksi database, memotong stok, menyimpan data pesanan
│   │   ├── confirm_payment.php         # Mengubah status pembayaran pesanan pelanggan
│   │   ├── delete_pesanan.php          # Membatalkan pesanan (mengembalikan stok & mengosongkan status meja)
│   │   └── finish_order.php            # Menandai masakan selesai diproses dapur ("Siap Saji")
│   │
│   └── function_user/                  # Berkas logika manajemen akun staf restoran
│       ├── add_user.php                # Menambahkan user baru dan mengenkripsi password dengan aman
│       ├── delete_user.php             # Menghapus user staf dari sistem database
│       └── update_user.php             # Memperbarui data pengguna, termasuk perubahan role dan update password
│
├── _layout.php                         # Template pembuat kerangka dasar atas web, navigasi sidebar, & css
├── _layout_end.php                     # Template penutup kerangka web, tag HTML, & inisiasi Bootstrap JS
├── admin.php                           # Halaman kendali admin untuk mengelola menu, pengguna, meja, & laporan
├── dapur.php                           # Layar pantau tim koki dapur untuk melihat pesanan yang sedang diproses
├── dapur_struk.php                     # Slip pesanan ringkas yang dikhususkan untuk dibaca tim masak dapur
├── db_resto (3).sql                    # Skema database MySQL awal restoran, siap di-import
├── edit_menu.php                       # Tampilan halaman formulir untuk mengubah detail menu makanan/minuman
├── edit_user.php                       # Tampilan halaman formulir untuk mengubah data user/akun staf
├── index.php                           # Halaman login utama restoran dengan fitur chip demo otomatis
├── kasir.php                           # Antarmuka utama kasir untuk melakukan pemesanan (cart) & pembayaran
├── owner.php                           # Dasbor owner untuk memantau omzet & laporan berkala harian/mingguan
├── owner_report.php                    # Layar khusus owner untuk menyusun rentang laporan penjualan harian/mingguan
├── print_invoice.php                   # Layar pratinjau struk belanja transaksi yang siap cetak format printer kasir
└── README.md                           # Dokumentasi teknis sistem POS KristyCrumbs (berkas ini)
└── README.md                           # Dokumentasi teknis sistem POS KristyCrumbs (berkas ini)
```

---

### **English**
The following directory tree showcases the file structural layout of the KristyCrumbs POS project and defines the functional purposes of each key script:

```
nnnn/
│
├── function/                           # Houses all primary backend PHP business logic scripts
│   ├── auth.php                        # Manages active sessions and checks role-based access permissions
│   ├── connect.php                     # Sets up local MySQL connection parameters via the mysqli extension
│   ├── login.php                       # Evaluates user credentials and redirects users to corresponding portals
│   ├── logout.php                      # Clears active user session arrays and returns users to login
│   │
│   ├── function_meja/                  # Data manipulation scripts for restaurant dining tables
│   │   ├── add_meja.php                # Validates and registers a new dining table into the system
│   │   └── delete_meja.php             # Deletes a specific table registration from the database
│   │
│   ├── function_menu/                  # Data manipulation scripts for food & beverage menus
│   │   ├── add_menu.php                # Saves new menu items detailing pricing, category, and initial stock
│   │   ├── delete_menu.php             # Removes a specific menu item permanently from database storage
│   │   └── update_menu.php             # Saves modified food/beverage data (title, prices, replenished stock)
│   │
│   ├── function_pesanan/               # Transactional checkout and kitchen processing scripts
│   │   ├── cart_action.php             # Supplies auxiliary functionalities for shopping cart manipulations
│   │   ├── checkout_kasir.php          # Executes SQL transactions, reduces item inventory, writes new orders
│   │   ├── confirm_payment.php         # Confirms customer payment transaction settlement
│   │   ├── delete_pesanan.php          # Cancels orders (restores menu stock & clears dining table occupancy)
│   │   └── finish_order.php            # Updates order statuses to "Ready to Serve" once prepared by chefs
│   │
│   └── function_user/                  # Staff profile credentials database manipulation scripts
│       ├── add_user.php                # Inserts a new user profile and safely hashes their password
│       ├── delete_user.php             # Deletes a staff profile credential from database storage
│       └── update_user.php             # Edits staff credentials, user role classifications, and updates passwords
│
├── _layout.php                         # HTML header shell, sidebar navigation panels, and CSS styling sheets
├── _layout_end.php                     # Closes HTML body tags and binds global Bootstrap JS bundles
├── admin.php                           # Admin portal featuring tabs for menus, users, tables, and reports
├── dapur.php                           # Cooking queue display for kitchen chefs to track active prep queues
├── dapur_struk.php                     # Minimalist print-ready cooking slip designed specifically for chefs
├── db_resto (3).sql                    # Primary pre-populated SQL database schema file for instant deployment
├── edit_menu.php                       # Input form interface layout to modify menu details
├── edit_user.php                       # Input form interface layout to edit existing staff user accounts
├── index.php                           # Entry login page integrated with clickable automated demo account chips
├── kasir.php                           # Primary point-of-sale interface featuring dynamic shopping carts
├── owner.php                           # Owner portal listing daily gross revenues and periodic reports
├── owner_report.php                    # Dedicated filter layout to extract and print daily/weekly financial reports
├── print_invoice.php                   # Formatted receipt template optimized for standard thermal checkout printers
└── README.md                           # Tech stack documentation file for the KristyCrumbs POS (this file)
```

---

## 5. Variabel Lingkungan / Environment Variables

### **Bahasa Indonesia**
Meskipun aplikasi POS berbasis PHP native ini menyimpan konfigurasi database secara terpusat di dalam berkas `function/connect.php` agar dapat langsung dijalankan pada server lokal, developer disarankan untuk bermigrasi ke berkas `.env` saat akan memindahkan sistem ini ke server produksi. Cara ini lebih aman untuk mencegah kebocoran kredensial sensitif.

Berikut adalah tabel deskripsi dari variabel konfigurasi koneksi database yang digunakan dalam berkas `function/connect.php`:

| Nama Variabel Koneksi | Nilai Bawaan Lokal | Deskripsi Fungsional |
| --- | --- | --- |
| `$server` | `localhost` | Alamat host server database MySQL Anda (misal `127.0.0.1` atau `localhost`). |
| `$username` | `root` | Username otentikasi database MySQL (bawaan XAMPP & Laragon adalah `root`). |
| `$password` | ` ` *(kosong)* | Kata sandi otentikasi database MySQL (bawaan XAMPP & Laragon adalah string kosong). |
| `$database` | `db_resto` | Nama basis data restoran yang diakses untuk menyimpan data transaksi & stok menu. |

#### **Contoh Konfigurasi Modern (`.env.example`):**
Apabila struktur proyek ini dimigrasikan untuk menggunakan library pembaca `.env` standard (misal `vlucas/phpdotenv`), format konfigurasi file `.env` di bawah ini sangat direkomendasikan untuk digunakan:

```ini
# --- APLIKASI UTAMA / MAIN APP CONFIG ---
APP_NAME="KristyCrumbs POS"
APP_ENV=development
APP_URL=http://localhost/nnnn

# --- SAMBUNGAN DATABASE RESTORAN / RESTAURANT DATABASE CONNECTION ---
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=db_resto
DB_USERNAME=root
DB_PASSWORD=**** # Ganti tanda bintang dengan password database Anda di produksi

# --- LAIN-LAIN / EXTRA OPTIONS ---
DEFAULT_TIMEZONE="Asia/Jakarta"
```

---

### **English**
Although this native PHP POS application stores its database configurations directly within the `function/connect.php` script for instant local server deployments, developers are highly encouraged to migrate towards a `.env` schema when transitioning this application onto production servers. This approach ensures sensitive backend secrets are kept out of revision control.

Below is a breakdown table of database connection variables defined inside the `function/connect.php` configuration script:

| Connection Variable Name | Default Local Value | Functional Description |
| --- | --- | --- |
| `$server` | `localhost` | Host address of your local MySQL database server (e.g. `127.0.0.1` or `localhost`). |
| `$username` | `root` | Authenticating username for your MySQL server (default for XAMPP & Laragon is `root`). |
| `$password` | ` ` *(empty string)* | Password for your local MySQL server (default for XAMPP & Laragon is an empty string). |
| `$database` | `db_resto` | Target restaurant database name holding transactional files & inventory tables. |

#### **Modern Environment Template (`.env.example`):**
If you choose to refactor this native project structure to integrate a standard `.env` parser package (like `vlucas/phpdotenv`), the following configuration template layout is highly recommended:

```ini
# --- APLIKASI UTAMA / MAIN APP CONFIG ---
APP_NAME="KristyCrumbs POS"
APP_ENV=development
APP_URL=http://localhost/nnnn

# --- SAMBUNGAN DATABASE RESTORAN / RESTAURANT DATABASE CONNECTION ---
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=db_resto
DB_USERNAME=root
DB_PASSWORD=**** # Replace these wildcards with your real production database password

# --- LAIN-LAIN / EXTRA OPTIONS ---
DEFAULT_TIMEZONE="Asia/Jakarta"
```
