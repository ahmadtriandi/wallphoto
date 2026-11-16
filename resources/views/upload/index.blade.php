@extends('layouts.app')

@section('title', 'Upload Foto')

@section('content')

<div class="max-w-md mx-auto" x-data="uploadForm()" x-cloak>

    <div class="text-center mb-8">
        <h1 class="text-2xl font-semibold text-gray-800">Upload Foto</h1>
        <p class="text-gray-500 text-sm mt-1">Foto akan langsung tampil di gallery</p>
    </div>

    {{-- ══════════════════════════════════════════
         FORM UPLOAD
    ══════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

        {{-- Preview foto --}}
        <div
            class="relative mb-5 rounded-xl overflow-hidden bg-gray-50 border-2 border-dashed border-gray-200 cursor-pointer transition-colors hover:border-indigo-300 hover:bg-indigo-50"
            :class="preview ? 'border-solid border-indigo-200' : ''"
            @click="!preview && $refs.fileInput.click()"
            style="min-height: 220px;"
        >
            {{-- Placeholder sebelum ada foto --}}
            <div x-show="!preview" class="flex flex-col items-center justify-center h-full py-12 text-gray-400">
                <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-sm font-medium">Tap untuk pilih foto</p>
                <p class="text-xs mt-1">atau ambil dari kamera</p>
            </div>

            {{-- Preview foto dipilih --}}
            <div x-show="preview" class="relative">
                <img :src="preview" alt="Preview" class="w-full rounded-xl object-contain max-h-72">
                {{-- Tombol ganti foto --}}
                <button
                    type="button"
                    @click.stop="resetFoto()"
                    class="absolute top-2 right-2 bg-black/50 hover:bg-black/70 text-white rounded-full p-1.5 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                {{-- Info ukuran --}}
                <span x-show="fileSize" class="absolute bottom-2 left-2 bg-black/50 text-white text-xs px-2 py-0.5 rounded-full"
                      x-text="fileSize"></span>
            </div>
        </div>

        {{-- Input file (hidden) --}}
        <input
            type="file"
            x-ref="fileInput"
            accept="image/*"
            capture="environment"
            class="hidden"
            @change="onFileChange($event)"
        >

        {{-- Tombol ambil dari kamera / galeri --}}
        <div x-show="!preview" class="flex gap-2 mb-5">
            <button type="button"
                @click="openCamera()"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                </svg>
                Kamera
            </button>
            <button type="button"
                @click="$refs.fileInput.click()"
                class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Galeri
            </button>
        </div>

        {{-- Input caption --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Caption</label>
            <textarea
                x-model="caption"
                placeholder="Tulis caption untuk foto ini..."
                rows="3"
                maxlength="255"
                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition-colors"
                :class="errors.caption ? 'border-red-300 focus:ring-red-200' : ''"
            ></textarea>
            <div class="flex justify-between mt-1">
                <p x-show="errors.caption" class="text-red-500 text-xs" x-text="errors.caption"></p>
                <p class="text-xs text-gray-400 ml-auto" x-text="`${caption.length}/255`"></p>
            </div>
        </div>

        {{-- Input nama (opsional) --}}
        <div class="mb-5">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Nama kamu <span class="text-gray-400 font-normal">(opsional)</span>
            </label>
            <input
                type="text"
                x-model="uploader"
                placeholder="Siapa yang upload?"
                maxlength="100"
                class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 transition-colors"
            >
        </div>

        {{-- Error umum --}}
        <div x-show="errors.general" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
            <p class="text-red-600 text-sm" x-text="errors.general"></p>
        </div>

        {{-- Tombol upload --}}
        <button
            type="button"
            @click="submit()"
            :disabled="uploading || !foto || !caption.trim()"
            class="w-full py-3.5 rounded-xl font-medium text-sm transition-all duration-200
                   bg-indigo-600 text-white hover:bg-indigo-700 active:scale-95
                   disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100"
        >
            {{-- Loading state --}}
            <span x-show="uploading" class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                <span x-text="uploadProgress ? `Mengupload... ${uploadProgress}%` : 'Menyiapkan...'"></span>
            </span>
            {{-- Default state --}}
            <span x-show="!uploading">
                <span x-show="!foto">Pilih foto dulu</span>
                <span x-show="foto && !caption.trim()">Isi caption dulu</span>
                <span x-show="foto && caption.trim()">Upload Foto</span>
            </span>
        </button>
    </div>

    {{-- ══════════════════════════════════════════
         SUCCESS STATE
    ══════════════════════════════════════════ --}}
    <div
        x-show="success"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="mt-4 bg-green-50 border border-green-200 rounded-2xl p-5 text-center"
        style="display: none;"
    >
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-green-800 font-medium">Foto berhasil diupload!</p>
        <p class="text-green-600 text-sm mt-1">Foto kamu sudah tampil di gallery</p>
        <button
            @click="resetAll()"
            class="mt-4 px-5 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors"
        >
            Upload foto lagi
        </button>
    </div>

</div>

@endsection

@push('scripts')
<script>
function uploadForm() {
    return {
        foto: null,
        preview: null,
        fileSize: '',
        caption: '',
        uploader: '',
        uploading: false,
        uploadProgress: 0,
        success: false,
        errors: {},

        onFileChange(event) {
            const file = event.target.files[0];
            if (!file) return;

            this.foto = file;
            this.errors = {};

            // Format ukuran file
            const kb = file.size / 1024;
            this.fileSize = kb < 1024 ? `${Math.round(kb)} KB` : `${(kb/1024).toFixed(1)} MB`;

            // Buat preview
            const reader = new FileReader();
            reader.onload = (e) => { this.preview = e.target.result; };
            reader.readAsDataURL(file);
        },

        openCamera() {
            // Buka kamera langsung (khusus mobile)
            const input = this.$refs.fileInput;
            input.setAttribute('capture', 'environment');
            input.click();
        },

        resetFoto() {
            this.foto = null;
            this.preview = null;
            this.fileSize = '';
            this.$refs.fileInput.value = '';
        },

        resetAll() {
            this.resetFoto();
            this.caption = '';
            this.uploader = '';
            this.success = false;
            this.errors = {};
            this.uploadProgress = 0;
        },

        async submit() {
            // Validasi client-side
            this.errors = {};
            if (!this.foto) { this.errors.general = 'Pilih foto terlebih dahulu.'; return; }
            if (!this.caption.trim()) { this.errors.caption = 'Caption tidak boleh kosong.'; return; }

            this.uploading = true;
            this.uploadProgress = 0;

            try {
                const formData = new FormData();
                formData.append('foto', this.foto);
                formData.append('caption', this.caption.trim());
                formData.append('uploader', this.uploader.trim() || 'Anonim');
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                // XMLHttpRequest untuk progress tracking
                const result = await new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();

                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                        }
                    });

                    xhr.addEventListener('load', () => {
                        try {
                            resolve(JSON.parse(xhr.responseText));
                        } catch {
                            reject(new Error('Response tidak valid'));
                        }
                    });

//                     xhr.addEventListener('load', () => {
//     try {
//         resolve(JSON.parse(xhr.responseText));
//     } catch {
//         console.error('Raw response:', xhr.responseText); // ← tambah ini
//         console.error('Status:', xhr.status);
//         reject(new Error('Response tidak valid: ' + xhr.status));
//     }
// });

                    xhr.addEventListener('error', () => reject(new Error('Koneksi gagal')));

                    xhr.open('POST', '/upload');
                    xhr.send(formData);
                });

                if (result.success) {
                    this.success = true;
                    this.uploading = false;
                } else {
                    this.errors.general = result.message || 'Upload gagal.';
                    this.uploading = false;
                }

            } catch (err) {
                this.errors.general = 'Terjadi kesalahan. Periksa koneksi internet.';
                this.uploading = false;
                console.error('Upload error:', err);
            }
        }
    };
}
</script>
@endpush
