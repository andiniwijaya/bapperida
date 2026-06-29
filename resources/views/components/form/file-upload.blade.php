@props([
    'id' => null,
    'name' => null,
    'accept' => null,
    'multiple' => false,
    'disabled' => false,
    'error' => null,
    'required' => false,
    'label' => null,
    'hint' => null,
    'maxSize' => '10 MB',
    'class' => '',
])

@php
    $controlId = $id ?? $name;
@endphp

<div
    class="w-full"
    x-data="{
        dragover: false,
        files: [],
        uploadProgress: {},
        uploadTimers: {},
        handleFiles(fileList) {
            this.files = Array.from(fileList);
            const input = this.$refs.fileInput;
            if (input && fileList.length) {
                const dataTransfer = new DataTransfer();
                Array.from(fileList).forEach((file) => dataTransfer.items.add(file));
                input.files = dataTransfer.files;
            }

            this.files.forEach((file) => {
                if (!this.uploadProgress[file.name]) {
                    this.uploadProgress[file.name] = {
                        percent: 0,
                        status: 'Siap diunggah',
                    };
                }
            });
        },
        removeFile(name) {
            this.files = this.files.filter((file) => file.name !== name);
            delete this.uploadProgress[name];
            if (this.uploadTimers[name]) {
                clearInterval(this.uploadTimers[name]);
                delete this.uploadTimers[name];
            }
            const input = this.$refs.fileInput;
            if (input) {
                input.value = '';
            }
        },
        formatFileSize(bytes) {
            if (bytes === 0) {
                return '0 Byte';
            }
            const k = 1024;
            const sizes = ['Byte', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return `${Math.round((bytes / Math.pow(k, i)) * 100) / 100} ${sizes[i]}`;
        },
        startUploadSimulation() {
            this.files.forEach((file) => {
                this.uploadProgress[file.name] = {
                    percent: 0,
                    status: 'Mengunggah...',
                };

                if (this.uploadTimers[file.name]) {
                    clearInterval(this.uploadTimers[file.name]);
                }

                this.uploadTimers[file.name] = setInterval(() => {
                    const current = this.uploadProgress[file.name]?.percent ?? 0;

                    if (current >= 90) {
                        clearInterval(this.uploadTimers[file.name]);
                        delete this.uploadTimers[file.name];

                        return;
                    }

                    this.uploadProgress[file.name].percent = current + 10;
                }, 180);
            });
        },
        init() {
            const form = this.$el.closest('form');

            if (!form) {
                return;
            }

            form.addEventListener('submit', () => {
                if (this.files.length) {
                    this.startUploadSimulation();
                }
            });
        },
    }"
    x-init="init()"
>
    @if ($label)
        <label for="{{ $controlId }}" class="ds-label">
            {{ $label }}
            @if ($required)
                <span class="ds-field-required" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div
        @dragover.prevent="dragover = true"
        @dragleave.prevent="dragover = false"
        @drop.prevent="dragover = false; handleFiles($event.dataTransfer.files)"
        :class="{ 'is-dragover': dragover }"
        class="ds-file-upload-zone p-6 text-center {{ $error ? 'is-error' : '' }} {{ $class }}"
    >
        <div class="flex flex-col items-center justify-center">
            <svg class="ds-file-upload-icon mb-2 h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>

            <p class="ds-body text-sm font-medium">
                Seret file ke sini atau
                <label for="{{ $controlId }}" class="ds-file-upload-link cursor-pointer">
                    pilih file
                </label>
            </p>

            @if ($hint)
                <p class="ds-field-hint mt-1">{{ $hint }}</p>
            @endif

            <p class="ds-caption mt-2">Ukuran maksimal: {{ $maxSize }}@if($accept) · Format: {{ strtoupper(str_replace(['application/', '.', '*'], '', $accept)) }}@endif</p>
        </div>
    </div>

    <input
        type="file"
        x-ref="fileInput"
        id="{{ $controlId }}"
        name="{{ $name }}{{ $multiple ? '[]' : '' }}"
        accept="{{ $accept }}"
        @disabled($disabled)
        @required($required)
        @if ($multiple) multiple @endif
        @change="handleFiles($event.target.files)"
        class="hidden"
        {{ $attributes }}
    />

    <div class="mt-4 space-y-2" x-show="files.length > 0" x-cloak>
        <template x-for="file in files" :key="file.name">
            <div class="ds-file-upload-item p-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex min-w-0 items-center">
                        <svg class="ds-file-upload-icon mr-2 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <div class="min-w-0">
                            <p class="ds-body truncate text-sm font-medium" x-text="file.name"></p>
                            <p class="ds-caption" x-text="formatFileSize(file.size)"></p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        <span class="ds-caption text-xs" x-text="uploadProgress[file.name]?.status ?? 'Siap diunggah'"></span>
                        <span class="ds-caption text-xs font-medium" x-text="`${uploadProgress[file.name]?.percent ?? 0}%`"></span>
                        <button
                            type="button"
                            @click="removeFile(file.name)"
                            class="ds-file-upload-icon transition-colors hover:text-maroon-500"
                            aria-label="Hapus file"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="ds-file-upload-progress mt-3" x-show="uploadProgress[file.name]" x-cloak>
                    <div class="ds-file-upload-progress__track" aria-hidden="true">
                        <div
                            class="ds-file-upload-progress__bar"
                            :style="`width: ${uploadProgress[file.name]?.percent ?? 0}%`"
                        ></div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    @if ($error)
        <p id="{{ $controlId }}-error" role="alert" class="ds-field-error mt-2">{{ $error }}</p>
    @endif
</div>
