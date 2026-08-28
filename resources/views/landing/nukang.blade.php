<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1" />
    <title>Nukang - Cari Tukang Profesional Jadi Lebih Mudah</title>
    <meta name="description"
        content="Temukan tukang terpercaya untuk renovasi rumah, listrik, plumbing, AC, pengecatan, dan puluhan layanan lainnya." />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('assets/landing/css/style.css') }}">
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar" id="navbar">
        <div class="container navbar-container">
            <a href="#" class="logo">Nu<span>kang</span></a>

            <div class="nav-menu" id="nav-menu">
                <ul class="nav-links">
                    <li><a href="#beranda">Beranda</a></li>
                    <li><a href="#layanan">Layanan</a></li>
                    <li><a href="#promo">Promo</a></li>
                    <li><a href="#tentang">Tentang</a></li>
                    <li><a href="#kontak">Kontak</a></li>
                </ul>
                <div class="nav-actions">
                    <a href="{{ route('login') }}" class="btn btn-outline btn-small">Masuk</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-small">Daftar</a>
                </div>
            </div>

            <button class="hamburger" id="hamburger" aria-label="Menu">
                <span class="bar"></span>
                <span class="bar"></span>
                <span class="bar"></span>
            </button>
        </div>
    </nav>

    <!-- HERO -->
    <header id="beranda" class="hero">
        <div class="hero-bg-gradient"></div>
        <div class="container hero-container">
            <div class="hero-content reveal">
                <h1>Cari Tukang Profesional Jadi Lebih Mudah</h1>
                <p>Temukan tukang terpercaya untuk renovasi rumah, listrik, plumbing, AC, pengecatan, dan puluhan
                    layanan lainnya.</p>
                <div class="hero-cta">
                    <a href="#layanan" class="btn btn-primary btn-large">Cari Jasa</a>
                    <a href="#mitra" class="btn btn-outline btn-large btn-bg-white">Daftar Jadi Mitra</a>
                </div>
            </div>
            <div class="hero-illustration reveal" style="--delay: 0.2s;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 500 500" width="100%" height="100%">
                    <path fill="#fef3c7"
                        d="M380.2,143.5c34.8,32.7,64.2,74.5,66.8,119.8c2.6,45.3-21.6,94.2-56.1,126.8c-34.5,32.6-79.3,49-123.4,56.8 c-44.1,7.8-87.5,7-124-10.3c-36.5-17.3-66-51-82.6-89.9c-16.6-38.9-20.2-83-10.2-123.1c10-40.1,33.5-76.2,64.6-104 C146.4,91.8,185,72.4,226.7,64c41.7-8.4,86.6-5.8,124.9,13.4C389.9,96.6,419.8,131.7,380.2,143.5z" />
                    <path fill="#D28219"
                        d="M250,150 c-40,0-75,30-85,70 l-15,100 c-5,25,10,40,30,40 h140 c20,0,35-15,30-40 l-15-100 C325,180,290,150,250,150z" />
                    <path fill="#111827" d="M180,360 v90 h30 v-90 H180z M290,360 v90 h30 v-90 H290z" />
                    <circle cx="250" cy="140" r="45" fill="#fcd34d" />
                    <path fill="#fbbf24"
                        d="M200,125 c0-40,100-40,100,0 h15 c5,0,5,10,0,10 h-130 c-5,0-5-10,0-10 H200z" />
                    <path fill="#D28219" d="M150,250 l-30,40 c-10,15-5,35,10,45 c15,10,35,5,45-10 l25-35" />
                    <path fill="#D28219" d="M350,250 l30,40 c10,15,5,35-10,45 c-15,10-35,5-45-10 l-25-35" />
                    <path fill="#9ca3af"
                        d="M120,310 l-20-20 c-5-5-5-15,0-20 l10-10 c5-5,15-5,20,0 l20,20 l-10,10 l-10-10 l-10,10 Z" />
                    <rect x="200" y="280" width="100" height="30" rx="5" fill="#4b5563" />
                    <rect x="220" y="270" width="20" height="15" rx="2" fill="#9ca3af" />
                    <rect x="260" y="270" width="20" height="15" rx="2" fill="#9ca3af" />
                </svg>
            </div>
        </div>
    </header>

    <!-- SEARCH -->
    <section class="search-section">
        <div class="container reveal">
            <div class="search-card">
                <div class="search-input-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="#6B7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.3-4.3"></path>
                    </svg>
                    <input type="text" placeholder="Cari layanan atau tukang..." />
                </div>
                <button class="btn btn-primary search-btn">Cari</button>
            </div>
        </div>
    </section>

    <!-- KATEGORI -->
    <section id="layanan" class="categories-section">
        <div class="container">
            <div class="section-title reveal">
                <h2>Kategori Layanan</h2>
                <p>Berbagai macam layanan untuk segala masalah di rumah Anda</p>
            </div>
            <div class="categories-grid">
                <a href="#" class="category-card reveal" style="--delay: 0.1s">
                    <div class="category-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                        </svg>
                    </div>
                    <h3>Listrik</h3>
                </a>
                <a href="#" class="category-card reveal" style="--delay: 0.15s">
                    <div class="category-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                            <polyline points="9 22 9 12 15 12 15 22" />
                        </svg>
                    </div>
                    <h3>Perbaikan Rumah</h3>
                </a>
                <a href="#" class="category-card reveal" style="--delay: 0.2s">
                    <div class="category-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z" />
                        </svg>
                    </div>
                    <h3>Plumbing</h3>
                </a>
                <a href="#" class="category-card reveal" style="--delay: 0.25s">
                    <div class="category-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 2-1.5 3" />
                            <path d="m12 22 1.5-3" />
                            <path d="M22 12h-3" />
                            <path d="M5 12H2" />
                            <path d="m19.07 4.93-1.5 2.6" />
                            <path d="m6.43 16.47-1.5 2.6" />
                            <path d="m4.93 4.93 2.6 1.5" />
                            <path d="m16.47 16.43 2.6 1.5" />
                        </svg>
                    </div>
                    <h3>AC & Kulkas</h3>
                </a>
                <a href="#" class="category-card reveal" style="--delay: 0.3s">
                    <div class="category-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m18.5 4.5-9 9a2 2 0 0 0-2 2v2h2a2 2 0 0 0 2-2l9-9" />
                            <path d="M15 8l1-1" />
                            <path d="M8.5 15.5l1-1" />
                            <path d="M4.5 19.5v2a1 1 0 0 0 1 1h2" />
                        </svg>
                    </div>
                    <h3>Cat Rumah</h3>
                </a>
                <a href="#" class="category-card reveal" style="--delay: 0.35s">
                    <div class="category-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="16" height="20" x="4" y="2" rx="2" ry="2" />
                            <path d="M9 22v-4h6v4" />
                            <path d="M8 6h.01" />
                            <path d="M16 6h.01" />
                            <path d="M12 6h.01" />
                            <path d="M12 10h.01" />
                            <path d="M12 14h.01" />
                            <path d="M16 10h.01" />
                            <path d="M16 14h.01" />
                            <path d="M8 10h.01" />
                            <path d="M8 14h.01" />
                        </svg>
                    </div>
                    <h3>Bangunan</h3>
                </a>
                <a href="#" class="category-card reveal" style="--delay: 0.4s">
                    <div class="category-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m2 10 10-8 10 8" />
                            <path d="M4 10v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V10" />
                            <path d="M12 15v7" />
                        </svg>
                    </div>
                    <h3>Atap</h3>
                </a>
                <a href="#" class="category-card reveal" style="--delay: 0.45s">
                    <div class="category-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2" />
                            <path d="M3 12h18" />
                            <path d="M12 3v18" />
                        </svg>
                    </div>
                    <h3>Keramik</h3>
                </a>
                <a href="#" class="category-card reveal" style="--delay: 0.5s">
                    <div class="category-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 9V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v2" />
                            <path d="M2 13h20" />
                            <path d="M22 13v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6" />
                            <path d="M6 13v8" />
                            <path d="M18 13v8" />
                        </svg>
                    </div>
                    <h3>Interior</h3>
                </a>
                <a href="#" class="category-card reveal" style="--delay: 0.55s">
                    <div class="category-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 13h18" />
                            <path d="M12 3v10" />
                            <path d="m4 13 1 7h14l1-7" />
                        </svg>
                    </div>
                    <h3>Cleaning</h3>
                </a>
                <a href="#" class="category-card reveal" style="--delay: 0.6s">
                    <div class="category-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z" />
                            <path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12" />
                        </svg>
                    </div>
                    <h3>Taman</h3>
                </a>
                <a href="#" class="category-card all-categories reveal" style="--delay: 0.65s">
                    <div class="category-icon bg-primary text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="7" height="7" x="3" y="3" rx="1" />
                            <rect width="7" height="7" x="14" y="3" rx="1" />
                            <rect width="7" height="7" x="14" y="14" rx="1" />
                            <rect width="7" height="7" x="3" y="14" rx="1" />
                        </svg>
                    </div>
                    <h3>Semua Kategori</h3>
                </a>
            </div>
        </div>
    </section>

    <!-- REKOMENDASI -->
    <section class="recommendations-section">
        <div class="container">
            <div class="section-title reveal">
                <h2>Rekomendasi Jasa</h2>
                <p>Layanan terpopuler dengan rating terbaik minggu ini</p>
            </div>
            <div class="services-list">
                <div class="service-item reveal" style="--delay: 0.1s">
                    <div class="service-image img-gradient-1"></div>
                    <div class="service-content">
                        <span class="service-category">Listrik</span>
                        <h4>Instalasi Listrik Premium</h4>
                        <div class="service-provider">
                            <div class="provider-avatar">B</div>
                            <span class="provider-name">Budi Santoso</span>
                            <span class="service-rating"><svg fill="#fbbf24" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg> 4.9</span>
                        </div>
                        <div class="service-footer">
                            <div class="service-price">Mulai <span>Rp 150.000</span></div>
                            <button class="btn btn-outline btn-small">Lihat Detail</button>
                        </div>
                    </div>
                </div>
                <div class="service-item reveal" style="--delay: 0.2s">
                    <div class="service-image img-gradient-2"></div>
                    <div class="service-content">
                        <span class="service-category">AC & Kulkas</span>
                        <h4>Servis AC Berkala</h4>
                        <div class="service-provider">
                            <div class="provider-avatar">A</div>
                            <span class="provider-name">Agus Teknik</span>
                            <span class="service-rating"><svg fill="#fbbf24" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg> 4.8</span>
                        </div>
                        <div class="service-footer">
                            <div class="service-price">Mulai <span>Rp 75.000</span></div>
                            <button class="btn btn-outline btn-small">Lihat Detail</button>
                        </div>
                    </div>
                </div>
                <div class="service-item reveal" style="--delay: 0.3s">
                    <div class="service-image img-gradient-3"></div>
                    <div class="service-content">
                        <span class="service-category">Plumbing</span>
                        <h4>Perbaikan Pipa Bocor</h4>
                        <div class="service-provider">
                            <div class="provider-avatar">H</div>
                            <span class="provider-name">Hadi Saluran</span>
                            <span class="service-rating"><svg fill="#fbbf24" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg> 4.7</span>
                        </div>
                        <div class="service-footer">
                            <div class="service-price">Mulai <span>Rp 120.000</span></div>
                            <button class="btn btn-outline btn-small">Lihat Detail</button>
                        </div>
                    </div>
                </div>
                <div class="service-item reveal" style="--delay: 0.4s">
                    <div class="service-image img-gradient-4"></div>
                    <div class="service-content">
                        <span class="service-category">Cat Rumah</span>
                        <h4>Pengecatan Interior & Eksterior</h4>
                        <div class="service-provider">
                            <div class="provider-avatar">D</div>
                            <span class="provider-name">Dedi Warna</span>
                            <span class="service-rating"><svg fill="#fbbf24" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg> 4.9</span>
                        </div>
                        <div class="service-footer">
                            <div class="service-price">Mulai <span>Rp 45.000 /m²</span></div>
                            <button class="btn btn-outline btn-small">Lihat Detail</button>
                        </div>
                    </div>
                </div>
                <div class="service-item reveal" style="--delay: 0.5s">
                    <div class="service-image img-gradient-5"></div>
                    <div class="service-content">
                        <span class="service-category">Atap</span>
                        <h4>Perbaikan Atap Bocor</h4>
                        <div class="service-provider">
                            <div class="provider-avatar">S</div>
                            <span class="provider-name">Surya Atap</span>
                            <span class="service-rating"><svg fill="#fbbf24" viewBox="0 0 20 20">
                                    <path
                                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg> 4.8</span>
                        </div>
                        <div class="service-footer">
                            <div class="service-price">Mulai <span>Rp 200.000</span></div>
                            <button class="btn btn-outline btn-small">Lihat Detail</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CARA KERJA -->
    <section class="how-it-works bg-gray">
        <div class="container">
            <div class="section-title reveal">
                <h2>Cara Kerja Nukang</h2>
                <p>4 Langkah mudah mendapatkan solusi masalah rumah Anda</p>
            </div>
            <div class="steps-container reveal" style="--delay: 0.2s">
                <div class="step-item">
                    <div class="step-icon">
                        <span class="step-number">1</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.3-4.3"></path>
                        </svg>
                    </div>
                    <h4>Cari Layanan</h4>
                    <p>Temukan layanan atau tukang yang sesuai dengan kebutuhan Anda</p>
                </div>
                <div class="step-item">
                    <div class="step-icon">
                        <span class="step-number">2</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <polyline points="16 11 18 13 22 9" />
                        </svg>
                    </div>
                    <h4>Pilih Tukang</h4>
                    <p>Pilih tukang terverifikasi berdasarkan rating dan ulasan</p>
                </div>
                <div class="step-item">
                    <div class="step-icon">
                        <span class="step-number">3</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                    </div>
                    <h4>Survey Lokasi</h4>
                    <p>Tukang akan melakukan survey untuk estimasi biaya pasti</p>
                </div>
                <div class="step-item">
                    <div class="step-icon">
                        <span class="step-number">4</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22l-1.5-5.5L5 15l5.5-1.5L12 8l1.5 5.5L19 15l-5.5 1.5z" />
                            <path d="M22 2l-2.5 8L12 12l7.5-2L22 2z" />
                        </svg>
                    </div>
                    <h4>Pekerjaan Selesai</h4>
                    <p>Pekerjaan beres, bayar dengan aman, dan berikan ulasan</p>
                </div>
            </div>
        </div>
    </section>

    <!-- PROMO SLIDER -->
    <section id="promo" class="promo-section">
        <div class="container reveal">
            <div class="slider" id="promoSlider">
                <div class="slide slide-1 active">
                    <div class="slide-content">
                        <h2>Diskon 50% untuk pelanggan baru!</h2>
                        <p>Gunakan kode NUKANG50 untuk transaksi pertamamu.</p>
                        <button class="btn btn-dark">Klaim Promo</button>
                    </div>
                </div>
                <div class="slide slide-2">
                    <div class="slide-content text-white">
                        <h2>Cashback Rp 50.000 untuk transaksi pertama</h2>
                        <p>Nikmati kemudahan layanan tukang profesional tanpa repot.</p>
                        <button class="btn btn-primary">Gunakan Sekarang</button>
                    </div>
                </div>
                <div class="slide slide-3">
                    <div class="slide-content">
                        <h2>Gratis Survey untuk area Jabodetabek</h2>
                        <p>Jangan ragu, konsultasikan masalah rumahmu tanpa biaya awal.</p>
                        <button class="btn btn-dark">Pesan Survey</button>
                    </div>
                </div>
                <div class="slide slide-4">
                    <div class="slide-content text-white">
                        <h2>Voucher Rp 75.000 khusus pengguna baru!</h2>
                        <p>Layanan berkualitas dengan harga terbaik dari tukang pilihan.</p>
                        <button class="btn btn-primary">Ambil Voucher</button>
                    </div>
                </div>
            </div>
            <div class="slider-dots" id="sliderDots">
                <span class="dot active" data-index="0"></span>
                <span class="dot" data-index="1"></span>
                <span class="dot" data-index="2"></span>
                <span class="dot" data-index="3"></span>
            </div>
        </div>
    </section>

    <!-- KENAPA MEMILIH NUKANG -->
    <section id="tentang" class="features-section">
        <div class="container">
            <div class="section-title reveal">
                <h2>Kenapa Memilih Nukang</h2>
                <p>Kami memastikan setiap layanan dikerjakan oleh profesional</p>
            </div>
            <div class="features-grid">
                <div class="feature-card reveal" style="--delay: 0.1s">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                    </div>
                    <h4>Tukang Terverifikasi</h4>
                    <p>Identitas dan keahlian telah divalidasi dengan ketat</p>
                </div>
                <div class="feature-card reveal" style="--delay: 0.2s">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z" />
                            <line x1="7" y1="7" x2="7.01" y2="7" />
                        </svg>
                    </div>
                    <h4>Harga Transparan</h4>
                    <p>Tidak ada biaya tersembunyi, semua jelas di awal</p>
                </div>
                <div class="feature-card reveal" style="--delay: 0.3s">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1" />
                            <path d="M9 14h6" />
                            <path d="M9 10h6" />
                        </svg>
                    </div>
                    <h4>Survey Sebelum Pekerjaan</h4>
                    <p>Mencegah salah harga dan salah pengerjaan</p>
                </div>
                <div class="feature-card reveal" style="--delay: 0.4s">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                        </svg>
                    </div>
                    <h4>Chat Langsung</h4>
                    <p>Komunikasi mudah melalui aplikasi tanpa ribet</p>
                </div>
                <div class="feature-card reveal" style="--delay: 0.5s">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                    </div>
                    <h4>Pembayaran Aman</h4>
                    <p>Uang ditahan sistem sampai pekerjaan selesai</p>
                </div>
                <div class="feature-card reveal" style="--delay: 0.6s">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20V10" />
                            <path d="M18 20V4" />
                            <path d="M6 20v-4" />
                        </svg>
                    </div>
                    <h4>Progress Realtime</h4>
                    <p>Pantau perkembangan pekerjaan langsung di aplikasi</p>
                </div>
            </div>
        </div>
    </section>

    <!-- STATISTIK -->
    <section class="stats-section bg-dark">
        <div class="container">
            <div class="stats-grid" id="stats">
                <div class="stat-item reveal" style="--delay: 0.1s">
                    <div class="stat-number" data-target="10000">0</div>
                    <p>Pelanggan</p>
                </div>
                <div class="stat-item reveal" style="--delay: 0.2s">
                    <div class="stat-number" data-target="3500">0</div>
                    <p>Mitra Tukang</p>
                </div>
                <div class="stat-item reveal" style="--delay: 0.3s">
                    <div class="stat-number" data-target="36">0</div>
                    <p>Kategori Layanan</p>
                </div>
                <div class="stat-item reveal" style="--delay: 0.4s">
                    <div class="stat-number" data-target="4.9" data-is-decimal="true">0</div>
                    <p>Rating Aplikasi</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA MITRA -->
    <section id="mitra" class="cta-mitra">
        <div class="container reveal">
            <div class="cta-mitra-content">
                <h2>Jadilah Mitra Nukang</h2>
                <p>Bergabung dengan ribuan tukang profesional dan dapatkan pelanggan lebih banyak.</p>
                <button class="btn btn-white btn-large">Daftar Sekarang</button>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer id="kontak" class="footer">
        <div class="container">
            <div class="footer-grid reveal">
                <div class="footer-col brand-col">
                    <a href="#" class="logo footer-logo">Nu<span>kang</span></a>
                    <p class="tagline">Cari tukang profesional jadi lebih mudah, aman, dan bergaransi.</p>
                    <div class="social-links">
                        <a href="#" aria-label="Instagram"><svg xmlns="http://www.w3.org/2000/svg"
                                width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                                <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                            </svg></a>
                        <a href="#" aria-label="Facebook"><svg xmlns="http://www.w3.org/2000/svg"
                                width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                            </svg></a>
                        <a href="#" aria-label="Twitter"><svg xmlns="http://www.w3.org/2000/svg"
                                width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                    d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z" />
                            </svg></a>
                        <a href="#" aria-label="YouTube"><svg xmlns="http://www.w3.org/2000/svg"
                                width="20" height="20" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2">
                                <path
                                    d="M22.54 6.42a2.78 2.78 0 0 0-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 0 0-1.94 2A29 29 0 0 0 1 11.75a29 29 0 0 0 .46 5.33 2.78 2.78 0 0 0 1.94 2c1.72.46 8.6.46 8.6.46s6.88 0 8.6-.46a2.78 2.78 0 0 0 1.94-2 29 29 0 0 0 .46-5.33 29 29 0 0 0-.46-5.33z" />
                                <polygon points="9.75 15.02 15.5 11.75 9.75 8.48 9.75 15.02" />
                            </svg></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Perusahaan</h4>
                    <ul>
                        <li><a href="#">Tentang Kami</a></li>
                        <li><a href="#">Karir</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Press</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Layanan</h4>
                    <ul>
                        <li><a href="#">Listrik</a></li>
                        <li><a href="#">Plumbing</a></li>
                        <li><a href="#">AC & Kulkas</a></li>
                        <li><a href="#">Cat Rumah</a></li>
                        <li><a href="#">Semua Layanan</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Bantuan</h4>
                    <ul>
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Kontak</a></li>
                        <li><a href="#">Kebijakan Privasi</a></li>
                        <li><a href="#">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Nukang. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script type="module" src="{{ asset('assets/landing/js/script.js') }}"></script>
</body>

</html>
