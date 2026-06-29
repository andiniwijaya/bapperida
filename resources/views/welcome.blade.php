<x-layouts::guest :title="'Selamat Datang'">
    <section
        class="landing-hero"
        aria-labelledby="landing-title"
        style="--landing-bg-image: url('{{ asset('assets/images/background.png') }}')"
    >
        <div class="landing-hero__background" role="img" aria-label="Latar belakang institusi"></div>
        <div class="landing-hero__overlay" aria-hidden="true"></div>

        <div class="landing-hero__inner">
            <div class="landing-hero__main">
                <div class="landing-hero__copy">
                    <h1 id="landing-title" class="landing-hero__title">
                        Sistem Registrasi Penomoran dan Arsip Surat
                    </h1>
                    <p class="landing-hero__subtitle">
                        Badan Perencanaan Pembangunan Riset dan Inovasi Daerah Kabupaten Bandung
                    </p>
                    <p class="landing-hero__description">
                        Platform Terintegrasi untuk Pengelolaan Penomoran Surat, Arsip Surat Masuk dan Surat Keluar,
                        serta Pelaporan Administrasi Perkantoran BAPPERIDA Kabupaten Bandung.
                        Sistem ini dirancang untuk Meningkatkan Efisiensi, Akurasi Data, dan Transparansi Proses
                        Administrasi Surat.
                    </p>
                </div>
                </div>

            <blockquote class="landing-hero__quote">
                <p class="landing-hero__quote-text">
                    &ldquo;Melayani dengan Profesional, Mengelola Surat dengan Presisi.&rdquo;
                </p>
                <footer class="landing-hero__quote-footer">
                    BAPPERIDA Kabupaten Bandung
                </footer>
            </blockquote>
        </div>
    </section>
</x-layouts::guest>
