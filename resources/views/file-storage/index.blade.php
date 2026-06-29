@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto" x-data="fileManager()" x-init="init()">
    <!-- Header with Breadcrumbs & Action Buttons -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <!-- Breadcrumbs -->
            <div class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                <button @click="navigateTo(null)" class="hover:text-brand-500 font-medium transition-colors">File Manager</button>
                <template x-for="bc in breadcrumbs" :key="bc.id">
                    <div class="flex items-center space-x-2">
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <button @click="navigateTo(bc.id)" class="hover:text-brand-500 font-medium transition-colors" x-text="bc.name"></button>
                    </div>
                </template>
            </div>
            <h1 class="text-2xl font-bold text-dark-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
                <span x-text="currentFolderName">All Documents</span>
            </h1>
        </div>

        <div class="flex items-center space-x-3">
            <button @click="openNewFolderModal()" class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-200 text-dark-700 text-sm font-semibold rounded-xl hover:bg-gray-50 hover:text-dark-900 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                </svg>
                New Folder
            </button>
            <label class="inline-flex items-center px-4 py-2.5 bg-brand-500 text-white text-sm font-semibold rounded-xl hover:bg-brand-600 cursor-pointer transition-all shadow-md shadow-brand-500/10">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Upload Files
                <input type="file" multiple class="hidden" @change="handleFileSelect($event)">
            </label>
        </div>
    </div>

    <!-- Drag & Drop Uploader Banner -->
    <div 
        class="border-2 border-dashed rounded-2xl p-8 mb-8 text-center transition-all cursor-pointer relative"
        :class="isDragging ? 'border-brand-500 bg-brand-50/50 scale-[0.99]' : 'border-gray-200 hover:border-gray-300 bg-white'"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop($event)"
    >
        <!-- Upload Progress indicator overlay -->
        <div x-show="isUploading" class="absolute inset-0 bg-white/95 rounded-2xl flex flex-col items-center justify-center p-6 z-10" x-cloak>
            <div class="w-full max-w-xs bg-gray-100 rounded-full h-2 mb-3 overflow-hidden">
                <div class="bg-brand-500 h-full transition-all duration-300" :style="'width: ' + uploadProgress + '%'"></div>
            </div>
            <p class="text-sm font-semibold text-dark-800" x-text="'Uploading assets... ' + uploadProgress + '%'"></p>
        </div>

        <div class="flex flex-col items-center justify-center">
            <div class="p-3 bg-brand-50 text-brand-500 rounded-full mb-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
            </div>
            <p class="text-base font-semibold text-dark-800">Drag & Drop files here, or click to upload</p>
            <p class="text-xs text-gray-500 mt-1">Accepts document templates, brochures, contracts, images up to 20MB</p>
        </div>
    </div>

    <!-- Files & Folders Container -->
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden min-h-[300px] relative">
        
        <!-- Loading Overlay -->
        <div x-show="isLoading" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-30 flex items-center justify-center" x-cloak>
            <svg class="animate-spin h-8 w-8 text-brand-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <!-- Empty State -->
        <div x-show="folders.length === 0 && files.length === 0" class="p-16 text-center" x-cloak>
            <div class="inline-flex p-4 bg-gray-50 rounded-full text-gray-400 mb-4">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v4.586a1 1 0 01-.293.707l-2.829 2.828a1 1 0 01-.707.293H7.828a1 1 0 01-.707-.293l-2.828-2.828A1 1 0 014 13.586V13"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-dark-800 mb-1">This directory is empty</h3>
            <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6">Create a folder or drop files in the zone above to start organizing files.</p>
            <button @click="openNewFolderModal()" class="inline-flex items-center px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl transition-all">
                Create a Folder
            </button>
        </div>

        <!-- Grid view -->
        <div x-show="folders.length > 0 || files.length > 0" class="p-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            
            <!-- Folders Loop -->
            <template x-for="folder in folders" :key="'folder-' + folder.id">
                <div class="relative group border border-gray-100 bg-gray-50/50 hover:bg-white hover:border-gray-200 rounded-xl p-4 transition-all cursor-pointer shadow-sm hover:shadow-md flex flex-col justify-between"
                     @click.self="navigateTo(folder.id)">
                    
                    <!-- Actions Dropdown -->
                    <div class="absolute top-2 right-2" x-data="{ open: false }">
                        <button @click="open = !open" class="p-1 hover:bg-gray-100 rounded text-gray-400 hover:text-dark-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-32 bg-white border border-gray-100 rounded-lg shadow-lg z-20 py-1" x-cloak>
                            <button @click="openRenameModal('folder', folder.id, folder.name)" class="w-full text-left px-3 py-1.5 text-xs text-dark-700 hover:bg-gray-50 flex items-center">
                                Rename
                            </button>
                            <button @click="deleteItem('folder', folder.id)" class="w-full text-left px-3 py-1.5 text-xs text-rose-600 hover:bg-rose-50 flex items-center">
                                Delete
                            </button>
                        </div>
                    </div>

                    <!-- Folder Content -->
                    <div class="flex flex-col items-center text-center mt-2" @click="navigateTo(folder.id)">
                        <span class="p-2.5 bg-amber-50 text-amber-500 rounded-xl mb-3">
                            <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4l2 2h4a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"></path>
                            </svg>
                        </span>
                        <span class="text-sm font-semibold text-dark-800 truncate w-full px-1" :title="folder.name" x-text="folder.name"></span>
                    </div>
                </div>
            </template>

            <!-- Files Loop -->
            <template x-for="file in files" :key="'file-' + file.id">
                <div class="relative group border border-gray-100 hover:border-gray-200 rounded-xl p-4 transition-all shadow-sm hover:shadow-md flex flex-col justify-between bg-white">
                    
                    <!-- Actions Dropdown -->
                    <div class="absolute top-2 right-2" x-data="{ open: false }">
                        <button @click="open = !open" class="p-1 hover:bg-gray-100 rounded text-gray-400 hover:text-dark-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-32 bg-white border border-gray-100 rounded-lg shadow-lg z-20 py-1" x-cloak>
                            <a :href="'/file-storage/files/' + file.id + '/download'" class="w-full text-left px-3 py-1.5 text-xs text-dark-700 hover:bg-gray-50 flex items-center">
                                Download
                            </a>
                            <template x-if="['pdf', 'png', 'jpg', 'jpeg', 'gif', 'txt'].includes(file.extension)">
                                <a :href="'/file-storage/files/' + file.id + '/preview'" target="_blank" class="w-full text-left px-3 py-1.5 text-xs text-dark-700 hover:bg-gray-50 flex items-center">
                                    Preview
                                </a>
                            </template>
                            <button @click="openRenameModal('file', file.id, file.original_name)" class="w-full text-left px-3 py-1.5 text-xs text-dark-700 hover:bg-gray-50 flex items-center">
                                Rename
                            </button>
                            <button @click="deleteItem('file', file.id)" class="w-full text-left px-3 py-1.5 text-xs text-rose-600 hover:bg-rose-50 flex items-center">
                                Delete
                            </button>
                        </div>
                    </div>

                    <!-- File Content -->
                    <div class="flex flex-col items-center text-center mt-2">
                        <span class="p-2.5 rounded-xl mb-3" :class="getFileBg(file.extension)">
                            <!-- Image Extension Custom Icon -->
                            <template x-if="['png','jpg','jpeg','gif'].includes(file.extension)">
                                <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </template>
                            <!-- PDF Custom Icon -->
                            <template x-if="file.extension === 'pdf'">
                                <svg class="w-10 h-10 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </template>
                            <!-- General Custom Icon -->
                            <template x-if="!['png','jpg','jpeg','gif','pdf'].includes(file.extension)">
                                <svg class="w-10 h-10 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </template>
                        </span>
                        <span class="text-sm font-semibold text-dark-800 truncate w-full px-1" :title="file.original_name" x-text="file.original_name"></span>
                        <span class="text-[10px] text-gray-400 mt-0.5" x-text="formatSize(file.size)"></span>
                    </div>
                </div>
            </template>

        </div>
    </div>

    <!-- Modals -->
    <!-- 1. New Folder Modal -->
    <div x-show="showNewFolderModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-dark-900/40 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-gray-100" @click.away="showNewFolderModal = false">
            <h3 class="text-lg font-bold text-dark-900 mb-4">Create New Folder</h3>
            <input type="text" x-model="newFolderName" placeholder="Folder Name" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none mb-4" @keyup.enter="submitNewFolder()">
            <div class="flex justify-end space-x-3">
                <button @click="showNewFolderModal = false" class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-dark-700 text-sm font-semibold rounded-xl">Cancel</button>
                <button @click="submitNewFolder()" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl">Create</button>
            </div>
        </div>
    </div>

    <!-- 2. Rename Modal -->
    <div x-show="showRenameModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-dark-900/40 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-gray-100" @click.away="showRenameModal = false">
            <h3 class="text-lg font-bold text-dark-900 mb-4">Rename Item</h3>
            <input type="text" x-model="renameItemName" placeholder="New Name" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none mb-4" @keyup.enter="submitRename()">
            <div class="flex justify-end space-x-3">
                <button @click="showRenameModal = false" class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-dark-700 text-sm font-semibold rounded-xl">Cancel</button>
                <button @click="submitRename()" class="px-4 py-2 bg-brand-500 hover:bg-brand-600 text-white text-sm font-semibold rounded-xl">Save</button>
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div class="fixed bottom-4 right-4 z-50" x-show="toastMessage !== ''" x-cloak>
        <div :class="toastType === 'success' ? 'bg-emerald-600' : 'bg-rose-600'" class="text-white px-5 py-3 rounded-xl shadow-xl flex items-center space-x-3 transition-opacity">
            <span class="text-sm font-semibold" x-text="toastMessage"></span>
        </div>
    </div>

</div>

@push('scripts')
<script>
function fileManager() {
    return {
        folders: [],
        files: [],
        breadcrumbs: [],
        currentFolderId: null,
        currentFolderName: 'All Documents',
        
        showNewFolderModal: false,
        newFolderName: '',
        showRenameModal: false,
        renameItemId: null,
        renameItemType: 'file',
        renameItemName: '',
        
        isDragging: false,
        isUploading: false,
        isLoading: false,
        uploadProgress: 0,
        toastMessage: '',
        toastType: 'success',

        init() {
            // Initial data load from blade compile parameters
            this.folders = @json($folders);
            this.files = @json($files);
            this.breadcrumbs = @json($breadcrumbs);
            this.currentFolderId = @json($currentFolder ? $currentFolder->id : null);
            this.currentFolderName = @json($currentFolder ? $currentFolder->name : 'All Documents');
        },

        showToast(msg, type = 'success') {
            this.toastMessage = msg;
            this.toastType = type;
            setTimeout(() => {
                this.toastMessage = '';
            }, 4000);
        },

        getFileBg(ext) {
            ext = ext.toLowerCase();
            if (['png', 'jpg', 'jpeg', 'gif'].includes(ext)) return 'bg-emerald-50';
            if (ext === 'pdf') return 'bg-rose-50';
            return 'bg-indigo-50';
        },

        formatSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        },

        async navigateTo(folderId) {
            this.isLoading = true;
            const url = folderId ? `/file-storage/${folderId}` : '/file-storage';
            
            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const data = await response.json();
                
                this.folders = data.folders;
                this.files = data.files;
                this.breadcrumbs = data.breadcrumbs;
                this.currentFolderId = data.currentFolder ? data.currentFolder.id : null;
                this.currentFolderName = data.currentFolder ? data.currentFolder.name : 'All Documents';
                
                // Push status to history so back button works nicely
                window.history.pushState({folderId: this.currentFolderId}, '', url);
            } catch (e) {
                this.showToast('Failed to load directory.', 'error');
            } finally {
                this.isLoading = false;
            }
        },

        openNewFolderModal() {
            this.newFolderName = '';
            this.showNewFolderModal = true;
        },

        async submitNewFolder() {
            if (!this.newFolderName.trim()) return;

            try {
                const response = await fetch('{{ route("file-storage.folders.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        name: this.newFolderName,
                        parent_id: this.currentFolderId
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.showNewFolderModal = false;
                    // Add folder directly to UI array
                    this.folders.push(data.folder);
                    this.folders.sort((a, b) => a.name.localeCompare(b.name));
                    this.showToast('Folder created successfully!');
                } else {
                    this.showToast(data.message || 'Error creating folder.', 'error');
                }
            } catch (e) {
                this.showToast('Something went wrong.', 'error');
            }
        },

        openRenameModal(type, id, currentName) {
            this.renameItemType = type;
            this.renameItemId = id;
            this.renameItemName = currentName;
            this.showRenameModal = true;
        },

        async submitRename() {
            if (!this.renameItemName.trim()) return;

            const url = this.renameItemType === 'folder' 
                ? `/file-storage/folders/${this.renameItemId}/rename`
                : `/file-storage/files/${this.renameItemId}/rename`;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        name: this.renameItemName
                    })
                });

                const data = await response.json();
                if (data.success) {
                    this.showRenameModal = false;
                    
                    // Update model array directly
                    if (this.renameItemType === 'folder') {
                        const idx = this.folders.findIndex(f => f.id === this.renameItemId);
                        if (idx !== -1) {
                            this.folders[idx].name = data.folder.name;
                            this.folders.sort((a, b) => a.name.localeCompare(b.name));
                        }
                    } else {
                        const idx = this.files.findIndex(f => f.id === this.renameItemId);
                        if (idx !== -1) {
                            this.files[idx].original_name = data.file.original_name;
                            this.files[idx].name = data.file.name;
                            this.files.sort((a, b) => a.original_name.localeCompare(b.original_name));
                        }
                    }
                    this.showToast('Item renamed successfully!');
                } else {
                    this.showToast('Error renaming item.', 'error');
                }
            } catch (e) {
                this.showToast('Something went wrong.', 'error');
            }
        },

        async deleteItem(type, id) {
            if (!confirm(`Are you sure you want to delete this ${type}? This action cannot be undone.`)) return;

            const url = type === 'folder'
                ? `/file-storage/folders/${id}`
                : `/file-storage/files/${id}`;

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    if (type === 'folder') {
                        this.folders = this.folders.filter(f => f.id !== id);
                    } else {
                        this.files = this.files.filter(f => f.id !== id);
                    }
                    this.showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} deleted successfully.`);
                } else {
                    this.showToast(`Error deleting ${type}.`, 'error');
                }
            } catch (e) {
                this.showToast('Something went wrong.', 'error');
            }
        },

        handleDrop(e) {
            this.isDragging = false;
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.uploadMultipleFiles(files);
            }
        },

        handleFileSelect(e) {
            const files = e.target.files;
            if (files.length > 0) {
                this.uploadMultipleFiles(files);
            }
        },

        async uploadMultipleFiles(files) {
            this.isUploading = true;
            this.uploadProgress = 0;

            const total = files.length;
            let uploaded = 0;

            for (let i = 0; i < total; i++) {
                const formData = new FormData();
                formData.append('file', files[i]);
                if (this.currentFolderId) {
                    formData.append('folder_id', this.currentFolderId);
                }

                try {
                    const response = await fetch('{{ route("file-storage.files.store") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    const data = await response.json();
                    if (data.success) {
                        uploaded++;
                        this.uploadProgress = Math.round((uploaded / total) * 100);
                        // Add newly uploaded file directly to DOM array
                        this.files.push(data.file);
                        this.files.sort((a, b) => a.original_name.localeCompare(b.original_name));
                    } else {
                        this.showToast(`Failed uploading ${files[i].name}.`, 'error');
                    }
                } catch (err) {
                    this.showToast(`Error uploading ${files[i].name}.`, 'error');
                }
            }

            this.isUploading = false;
            if (uploaded > 0) {
                this.showToast(`Uploaded ${uploaded} file(s) successfully!`);
            }
        }
    }
}
</script>
@endpush
@endsection
