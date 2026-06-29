<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-6">
        <x-input
            id="letter_number"
            name="letter_number"
            label="Nomor Surat"
            placeholder="Masukkan nomor surat..."
            :autofocus="$autofocus ?? false"
            required
        />
    </div>
    <div class="col-span-12 lg:col-span-6">
        <x-input id="sent_date" name="sent_date" type="date" label="Tanggal Surat" hint="Format tanggal mengikuti locale perangkat." required />
    </div>
    <div class="col-span-12 lg:col-span-6">
        <x-input id="received_date" name="received_date" type="date" label="Tanggal Diterima" required />
    </div>
    <div class="col-span-12 lg:col-span-6">
        <x-input id="disposition_date" name="disposition_date" type="date" label="Tanggal Disposisi" />
    </div>
    <div class="col-span-12 lg:col-span-6">
        <x-input id="sender" name="sender" label="Pengirim" placeholder="Masukkan nama pengirim..." required />
    </div>
    <div class="col-span-12 lg:col-span-6">
        <x-select id="department_id" name="department_id" label="Bidang" placeholder="Pilih bidang..." searchable required />
    </div>
    <div class="col-span-12 lg:col-span-6">
        <x-select
            id="disposition_department_id"
            name="disposition_department_id"
            label="Bidang Disposisi"
            placeholder="Pilih bidang disposisi..."
            searchable
            clearable
            tooltip="Bidang yang menerima disposisi surat masuk."
        />
    </div>
    <div class="col-span-12 lg:col-span-6">
        <x-input id="subject" name="subject" label="Perihal" placeholder="Masukkan perihal surat..." required />
    </div>
    <div class="col-span-12 lg:col-span-6">
        <x-input id="agenda_name" name="agenda_name" label="Nama Agenda" placeholder="Masukkan nama agenda..." />
    </div>
    <div class="col-span-12">
        <x-textarea id="summary" name="summary" label="Isi Ringkas" placeholder="Masukkan isi ringkas surat..." rows="4" />
    </div>
    <div class="col-span-12 lg:col-span-6">
        <x-select id="letter_attribute" name="letter_attribute" label="Jenis Surat" placeholder="Pilih jenis surat..." required />
    </div>
    <div class="col-span-12 lg:col-span-6">
        <x-input id="attachment" name="attachment" label="Lampiran" placeholder="Masukkan keterangan lampiran..." />
    </div>
    <div class="col-span-12 lg:col-span-6">
        <x-form.file-upload
            id="file"
            name="file"
            label="Unggah File PDF"
            accept="application/pdf,.pdf"
            hint="Format PDF. Ukuran maksimal 10 MB."
            max-size="10 MB"
        />
    </div>
    <div class="col-span-12 lg:col-span-6">
        <x-select id="status" name="status" label="Status" placeholder="Pilih status..." required />
    </div>
    <div class="col-span-12">
        <x-textarea id="notes" name="notes" label="Catatan" placeholder="Masukkan catatan tambahan..." rows="4" />
    </div>
</div>
