<div class="app-crud-form-card">
    <div class="app-crud-form-card__header">
        <div>
            <h2 class="app-crud-form-card__title">Data Registrasi Penomoran</h2>
            <p class="app-crud-form-card__description">Lengkapi informasi registrasi nomor surat.</p>
        </div>
    </div>

    <div class="app-crud-form-card__body">
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 md:col-span-4">
                <x-input
                    id="index_code"
                    name="index_code"
                    label="Kode Indeks"
                    placeholder="Masukkan kode indeks..."
                    autofocus
                    required
                />
            </div>
            <div class="col-span-12 md:col-span-4">
                <x-input
                    id="letter_code"
                    name="letter_code"
                    label="Kode Surat"
                    placeholder="Masukkan kode surat..."
                    required
                />
            </div>
            <div class="col-span-12 md:col-span-4">
                <x-select id="department_id" name="department_id" label="Bidang" placeholder="Pilih bidang..." searchable required />
            </div>
            <div class="col-span-12">
                <x-input
                    id="letter_number_preview"
                    label="Preview Nomor Surat"
                    placeholder="Nomor surat dibuat otomatis..."
                    readonly
                    hint="Nomor surat dibuat otomatis berdasarkan kode dan urutan."
                />
            </div>
            <div class="col-span-12 md:col-span-4">
                <x-input
                    id="year"
                    name="year"
                    type="number"
                    label="Tahun"
                    placeholder="Masukkan tahun..."
                    min="2000"
                    max="2100"
                    :value="now()->year"
                    required
                />
            </div>
            <div class="col-span-12 md:col-span-4">
                <x-input
                    id="sequence_number"
                    name="sequence_number"
                    type="number"
                    label="Nomor Urut"
                    placeholder="Masukkan nomor urut..."
                    min="1"
                    step="1"
                    tooltip="Nomor urut unik per tahun. Ditampilkan tiga digit pada nomor surat (001, 002, dst.)."
                    required
                />
            </div>
            <div class="col-span-12 md:col-span-4">
                <x-select id="letter_type" name="letter_type" label="Jenis Surat" placeholder="Pilih jenis surat..." required />
            </div>
            <div class="col-span-12 md:col-span-4">
                <x-input
                    id="letter_date"
                    name="letter_date"
                    type="date"
                    label="Tanggal Surat"
                    :value="now()->format('Y-m-d')"
                    required
                />
            </div>
            <div class="col-span-12">
                <x-input id="subject" name="subject" label="Perihal" placeholder="Masukkan perihal surat..." required />
            </div>
            <div class="col-span-12">
                <x-textarea id="summary" name="summary" label="Isi Ringkas" placeholder="Masukkan isi ringkas..." rows="4" />
            </div>
            <div class="col-span-12">
                <x-input id="recipient" name="recipient" label="Kepada" placeholder="Masukkan tujuan surat..." />
            </div>
            <div class="col-span-12 md:col-span-6">
                <x-input id="attachment" name="attachment" label="Lampiran" placeholder="Masukkan keterangan lampiran..." />
            </div>
            <div class="col-span-12 md:col-span-6">
                <x-input id="notes" name="notes" label="Catatan" placeholder="Masukkan catatan..." />
            </div>
        </div>
    </div>

    <div class="app-crud-form-card__footer">
        <a href="{{ route('letter-number-registrations.index') }}" class="ds-btn ds-btn--secondary">
            Batal
        </a>
        <button type="submit" class="ds-btn ds-btn--primary">
            Simpan
        </button>
    </div>
</div>
