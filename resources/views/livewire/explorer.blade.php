<div>
    <div
        x-data="{ isUploading: false, progress: 0 }"
        x-on:livewire-upload-start="isUploading = true"
        x-on:livewire-upload-finish="isUploading = false; @this.uploadFiles()"
        x-on:livewire-upload-error="isUploading = false"
        x-on:livewire-upload-progress="progress = $event.detail.progress"
    >
        <x-app-layout>
            <x-slot name="header">
                <!--<h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Explorador de archivos
                </h2>-->
                <div class="text-gray-600">
                    <span wire:click="open" class="cursor-pointer">Inicio</span>
                    @if ($folder)
                        @foreach ($folder->breadcrumbs() as $key => $name)
                            <span wire:click="open('{{ $key }}')" class="{{ $loop->last ? 'font-bold' : '' }} cursor-pointer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4 -mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                {{ $name }}
                            </span>
                        @endforeach
                    @endif
                </div>
            </x-slot>

            @if ($showNewFolderModal)
                <x-modal
                    title="Nueva carpeta"
                    okLabel="Crear carpeta"
                    onOk="createFolder"
                    onKo="closeNewFolderModal"
                >
                    <div>
                        <label for="name" class="text-sm font-bold text-gray-900 block mb-2">Nombre de la carpeta</label>
                        <x-j-input model="newFolderName" />
                    </div>
                </x-modal>
            @endif

            @if ($showRenameModal)
                <x-modal
                    title="Renombrar"
                    okLabel="Renombrar"
                    onOk="rename"
                    onKo="closeRenameModal"
                >
                    <div>
                        <label for="name" class="text-sm font-bold text-gray-900 block mb-2">Nuevo nombre</label>
                        <x-j-input model="renameModalNewName" id="renameModalNewName" autofocus />
                        <script>
                            const input = document.getElementById('renameModalNewName');
                            input.setSelectionRange(0, input.value.length);
                        </script>
                    </div>
                </x-modal>
            @endif

            @if ($showPermissionsModal)
                <x-modal
                    title="Permisos"
                    okLabel="Guardar"
                    onOk="updatePermissions"
                    onKo="closePermissionsModal"
                >
                    <div>
                        <div>Leer</div>
                        <div>
                            <select wire:model="permissionsModalValues.read" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                @foreach (jcloud()->permissions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($permissionsModalValues['read'] == jcloud()->some()->value)
                            <div class="mt-2">
                                @foreach (jcloud()->users() as $id => $name)
                                    <input wire:model="permissionsModalValues.read_users" type="checkbox" value="{{ $id }}" id="read_user_{{ $id }}" />
                                    <label for="read_user_{{ $id }}" class="mr-2">{{ $name }}</label>
                                @endforeach
                            </div>
                        @endif
                        <div class="mt-5">Escribir</div>
                        <div>
                            <select wire:model="permissionsModalValues.write" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                @foreach (jcloud()->permissions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($permissionsModalValues['write'] == jcloud()->some()->value)
                            <div class="mt-2">
                                @foreach (jcloud()->users() as $id => $name)
                                    <input wire:model="permissionsModalValues.write_users" type="checkbox" value="{{ $id }}" id="write_user_{{ $id }}" />
                                    <label for="write_user_{{ $id }}" class="mr-2">{{ $name }}</label>
                                @endforeach
                            </div>
                        @endif
                        @if ($permissionsModalIsFolder)
                            <div class="mt-5">Subir archivos</div>
                            <div>
                                <select wire:model="permissionsModalValues.upload" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                    @foreach (jcloud()->permissions() as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($permissionsModalValues['upload'] == jcloud()->some()->value)
                                <div class="mt-2">
                                    @foreach (jcloud()->users() as $id => $name)
                                        <input wire:model="permissionsModalValues.upload_users" type="checkbox" value="{{ $id }}" id="upload_user_{{ $id }}" />
                                        <label for="upload_user_{{ $id }}" class="mr-2">{{ $name }}</label>
                                    @endforeach
                                </div>
                            @endif
                            <small class="block mt-2">Conceda este permiso para que alguien pueda subir archivos a una carpeta aunque no tenga permisos de escritura.</small>
                        @endif
                    </div>
                </x-modal>
            @endif

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <div class="flex gap-2 items-center mb-6">
                                <x-j-button wire:click="{{ $this->isAnySelected() ? 'unselectAll' : 'selectAll' }}" look="soft" style="padding-left: 7px; padding-right: 7px;">
                                    @if ($this->isAnySelected())
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </x-j-button>

                                @if ($this->isAnySelected())
                                    <x-j-button wire:click="download" look="soft">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        Descargar ({{ $this->getSelectedCount() }})
                                    </x-j-button>

                                    <x-j-button wire:click="deleteSelected" look="danger">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Borrar ({{ $this->getSelectedCount() }})
                                    </x-j-button>
                                @endif

                                <div class="flex gap-1 bg-gray-100 rounded">
                                    <div class="inline-flex items-center py-1 pl-2 pr-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </div>
                                    <div wire:click="$set('sortBy', 'name')" class="inline-flex gap-2 items-center py-1 px-2 rounded cursor-pointer hover:bg-blue-600 hover:text-white {{ $sortBy == 'name' ? 'font-bold text-white bg-blue-600' : '' }}">
                                        Nombre
                                    </div>
                                    <div wire:click="$set('sortBy', 'date')" class="inline-flex gap-2 items-center py-1 px-2 rounded cursor-pointer hover:bg-blue-600 hover:text-white {{ $sortBy == 'date' ? 'font-bold text-white bg-blue-600' : '' }}">
                                        Fecha
                                    </div>
                                </div>

                                <!--<div class="flex gap-1 bg-gray-100 rounded">
                                    <div wire:click="$set('view', 'list')" class="inline-flex gap-2 items-center py-1 px-2 rounded cursor-pointer hover:bg-blue-600 hover:text-white {{ $view == 'list' ? 'font-bold text-white bg-blue-600' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                        </svg>
                                    </div>
                                    <div wire:click="$set('view', 'grid')" class="inline-flex gap-2 items-center py-1 px-2 rounded cursor-pointer hover:bg-blue-600 hover:text-white {{ $view == 'grid' ? 'font-bold text-white bg-blue-600' : '' }}">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                        </svg>
                                    </div>
                                </div>-->

                                <div class="flex gap-1 bg-gray-100 rounded">
                                    <div wire:click="toggleTrashed" class="inline-flex gap-2 items-center py-1 px-2 rounded cursor-pointer hover:bg-blue-600 hover:text-white {{ $withTrashed ? 'font-bold text-white bg-blue-600' : '' }}">
                                        @if ($withTrashed)
                                            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                            Ocultar borrados
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Mostrar borrados
                                        @endif
                                    </div>
                                </div>

                                <div class="ml-auto inline-flex gap-2 items-center">
                                    @if ($folder && ($folder->canWrite() || $folder->canUpload()))
                                        <x-j-button id="bt-upload-files">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                            </svg>
                                            Subir archivos

                                        </x-j-button>
                                        <script>
                                            const resumable = new Resumable({
                                                chunkSize: 10 * 1024 * 1024, // 10 MB
                                                simultaneousUploads: 1,
                                                testChunks: false,
                                                throttleProgressCallbacks: 1,
                                                target: '{{ route('upload') }}',
                                                query: {
                                                    _token : '{{ csrf_token() }}', // CSRF token
                                                    folderId: '{{ $folderId }}',
                                                },
                                            });
                                            resumable.assignBrowse(document.getElementById('bt-upload-files'));
                                            resumable.on('fileAdded', function(file, event){
                                                @this.addToQueue(file.fileName);
                                                resumable.upload();
                                                console.log(file);
                                            });
                                            resumable.on('fileSuccess', function(file, event){
                                                @this.emitTo('explorer', 'file-uploaded');
                                                @this.removeFromQueue(file.fileName);
                                            });
                                            resumable.on('fileError', function(file, event){
                                                @this.removeFromQueue(file.fileName);
                                                alert('Ocurrió un error subiendo el archivo ' + file.fileName);
                                            });
                                        </script>
                                    @endif

                                    <!-- <input wire:model="uploadedFiles" type="file" multiple class="absolute inset-0 opacity-0" /> -->

                                    @if (($folder && $folder->canWrite()) || ! $folder)
                                        <x-j-button wire:click="openNewFolderModal" wire:key="new-folder-button" onclick="console.log('hola?')">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="inline h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                            </svg>
                                            Nueva carpeta
                                        </x-j-button>
                                    @endif

                                    <span wire:click="$refresh" class="ml-2 cursor-pointer">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            @foreach ($this->uploadQueue as $fileName)
                                <div wire:key="{{ md5($fileName) }}" class="flex items-center gap-2 bg-yellow-50 border border-dotted border-yellow-400 rounded my-2 px-3 py-2">
                                    <div class="flex flex-col justify-center mr-1">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </div>
                                    <div class="flex flex-col font-bold">
                                        <div>{{ $fileName }}</div>
                                    </div>
                                </div>
                            @endforeach

                            @foreach ($this->list() as $file)
                                <div wire:key="{{ $file->key }}" class="flex items-center gap-2 {{ $this->isSelected($file->key) ? 'border border-blue-300 border-blue-300 bg-blue-100' : 'bg-gray-50 border border-gray-100 hover:border-blue-100 hover:bg-blue-50' }} {{ $file->trashed() ? 'border-red-200 bg-red-50' : '' }} rounded my-2 px-3 py-2">
                                    <div wire:click="toggleSelect('{{ $file->key }}')" class="flex flex-col justify-center mr-1">
                                        <input type="checkbox" {{ $this->isSelected($file->key) ? 'checked' : '' }} class="bg-gray-50 border-gray-300 focus:ring-3 focus:ring-yellow-300 h-5 w-5 rounded">
                                    </div>
                                    <div>
                                        @if ($file->isFile())
                                            @if ($file->isImage() && $file->is_uploaded)
                                                <a href="{{ $file->url }}" target="_blank">
                                                    <img src="{{ $file->getThumbnailUrl(40) }}" style="width: 40px;" />
                                                </a>
                                            @else
                                                <div class="w-8 h-8 mx-1 border-2 border-yellow-400 text-yellow-400 rounded-lg">
                                                    <div class="pt-1.5 text-xs font-extrabold text-center">{{ mb_strtoupper($file->extension) }}</div>
                                                </div>
                                            @endif
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 {{ $this->isSelected($file->key) ? 'text-blue-600' : 'text-yellow-400' }}" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1H8a3 3 0 00-3 3v1.5a1.5 1.5 0 01-3 0V6z" clip-rule="evenodd" />
                                                <path d="M6 12a2 2 0 012-2h8a2 2 0 012 2v2a2 2 0 01-2 2H2h2a2 2 0 002-2v-2z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div
                                        class="flex flex-col font-bold {{ $file->trashed() ? '' : 'cursor-pointer' }}"
                                        wire:click="open('{{ $file->key }}')"
                                    >
                                        <div>{{ $file->name }}</div>
                                        <div class="-mt-1 text-sm text-gray-600">
                                            {{ $file->human_size ? $file->human_size . ' - ' : '' }}{{ $file->created_at->format('d/m/y H:i') }} - {{ $file->owner->name }}
                                        </div>
                                    </div>
                                    <div class="ml-auto">
                                        @if ($file->trashed())
                                            @if ($file->canWrite())
                                                <span wire:click="restore('{{ $file->key }}')" class="cursor-pointer text-green-600" title="Restaurar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                                                    </svg>
                                                </span>
                                            @endif
                                        @else
                                            <span wire:click="open('{{ $file->key }}')" class="mr-2 cursor-pointer" title="Descargar">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                            </svg>
                                        </span>

                                            @if ($file->canWrite())
                                                <span wire:click="openRenameModal('{{ $file->key }}')" class="mr-2 cursor-pointer" title="Renombrar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </span>
                                            @endif

                                            @if ($file->isFolder() && $file->isRootFolder() && $file->canEditPermissions())
                                                <span wire:click="openPermissionsModal('{{ $file->key }}')" class="mr-2 cursor-pointer" title="Permisos">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                </span>
                                            @endif
                                            @if ($file->isFile())
                                                <span onclick="shareFileUrl('{{ $this->getShareUrl($file->key) }}')" class="mr-2 cursor-pointer" title="Compartir">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                                    </svg>
                                                </span>
                                            @endif

                                            @if ($file->canWrite())
                                                <span wire:click="delete('{{ $file->key }}')" class="cursor-pointer text-red-600" title="Borrar">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    <!--<div class="hidden">
                                        <x-dropdown >
                                            <x-slot name="trigger">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="cursor-pointer h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
                                                </svg>
                                            </x-slot>

                                            <x-slot name="content">
                                                <x-dropdown-link wire:click="open('{{ $file->key }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                    </svg>
                                                    Descargar
                                                </x-dropdown-link>
                                                <x-dropdown-link wire:click="openPermissionsModal('{{ $file->key }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                    </svg>
                                                    Permisos
                                                </x-dropdown-link>
                                                <x-dropdown-link wire:click="delete('{{ $file->key }}')">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="inline-block h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Eliminar
                                                </x-dropdown-link>
                                            </x-slot>
                                        </x-dropdown>
                                    </div>-->
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </x-app-layout>

        <div id="notification" class="fixed bottom-5 right-5 mb-4" style="display: none;">
            <div class="flex max-w-sm w-full bg-white shadow-md rounded-lg overflow-hidden mx-auto">
                <div class="w-2 bg-green-600">
                </div>
                <div class="w-full flex justify-between items-start px-2 py-2">
                    <div class="flex flex-col ml-2">
                        <label id="notification-title" class="text-lg text-gray-800 font-bold">Enlace copiado</label>
                        <p id="notification-text" class="mt-1 text-gray-500"></p>
                    </div>
                    <!--<a href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </a>-->
                </div>
            </div>
        </div>

        <div wire:loading.delay.longer style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999999;">
            <div style="position: absolute; top: 50%; left: 50%; margin-left: -8px; margin-top: -8px;">
                <div>
                    <div style="border-top-color:transparent"
                         class="w-16 h-16 border-4 border-yellow-300 border-solid rounded-full animate-spin"></div>
                </div>
            </div>
        </div>

        <script wire:key="explorer-page-scripts">
            function shareFileUrl(url) {
                copyText(url);
                notify('Enlace copiado', 'Se ha copiado al portapaeles el enlace de descarga del archivo.');
            }

            function copyText(text) {
                navigator.clipboard.writeText(text);
            }

            function notify(title, text) {
                document.getElementById('notification-title').textContent = title;
                document.getElementById('notification-text').textContent = text;
                document.getElementById('notification').style.display = 'block';
                setTimeout(() => {
                    document.getElementById('notification').style.display = 'none';
                }, 7000);
            }
        </script>
    </div>
</div>
