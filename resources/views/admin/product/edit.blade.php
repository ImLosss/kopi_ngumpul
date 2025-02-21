@extends('layouts.admin-layout')

@section('title')
    - Add Product
@endsection

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('home') }}">Home</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('stock') }}">Products</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Edit Product</li>
        </ol>
        <h5 class="font-weight-bolder mb-0">Product</h5>
    </nav>
@stop

@section('content')
<div class="card">
    <div class="card-header pb-0 px-3">
        <h5 class="mb-0">{{ __('Edit Product') }}</h5>
    </div>
    <div class="card-body pt-4 p-3">
        <form action="{{ route('product.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group has-validation">
                        <label for="name" class="form-control-label">{{ __('Nama Product') }}</label>
                        <input class="form-control @error('name') border border-danger rounded-3 @enderror" type="text" placeholder="Name" name="name" value="{{ $product->name }}" autofocus>
                        @error('name')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group has-validation">
                        <label for="category_id" class="form-control-label">{{ __('Category') }}</label>
                        <select name="category_id" class="form-control @error('category_id') border border-danger rounded-3 @enderror">
                            <option value="" selected disabled>- Pilih Kategori -</option>
                            @forelse ($categories as $item)
                                <option value="{{ $item->id }}" {{ $product->category_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @empty
                                <option>Kategori Belum diatur</option>
                            @endforelse
                        </select>
                        @error('category_id')
                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        
            <!-- Ingredients Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <label class="form-control-label">{{ __('Bahan-bahan') }}</label>
                    <div id="ingredient-container">
                        @if(!empty($product->stocks))
                            @foreach($product->stocks as $key => $value)
                                <div class="row ingredient-row mb-3">
                                    <div class="col-md-5">
                                        <select name="ingredients[]" class="form-control ingredient-select">
                                            <option value="" disabled>- Pilih Bahan -</option>
                                            @foreach($ingredients as $ingredient)
                                                <option value="{{ $ingredient->id }}" 
                                                    {{ $value->id == $ingredient->id ? 'selected' : '' }}>
                                                    {{ $ingredient->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('ingredients.'.$key)
                                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="col-md-5">
                                        <input type="number" name="quantities[]" class="form-control" 
                                               placeholder="Jumlah (gram/ml)" value="{{ $value->pivot->gram_ml }}" min="1">
                                        @error('quantities.'.$key)
                                        <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-ingredient" {{ $key == 0 ? 'disabled' : '' }}>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    
                    <button type="button" class="btn btn-success mt-2" id="add-ingredient">
                        Tambah Bahan
                    </button>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="submit" class="btn bg-gradient-dark btn-md mt-4 mb-4">{{ 'Edit Product' }}</button>
            </div>
</div>

@endsection

@section('script')
    <script>
        function formatNumberInput() {
            // Ambil nilai input dan hapus semua karakter non-digit
            let modalView = Number($('#modalView').val().replace(/\D/g, ''));
            let hargaView = Number($('#hargaView').val().replace(/\D/g, ''));

            $('#modal').val(modalView)
            $('#modalView').val(modalView.toLocaleString('id-ID'));
            $('#harga').val(hargaView)
            $('#hargaView').val(hargaView.toLocaleString('id-ID'));
        }
    </script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('ingredient-container');
        const addButton = document.getElementById('add-ingredient');
        let ingredientOptions = {!! json_encode($ingredients->pluck('name', 'id')) !!};
    
        // Fungsi untuk update opsi select
        function updateSelectOptions() {
            const allSelects = container.querySelectorAll('.ingredient-select');
            const usedValues = new Set();
            
            allSelects.forEach(select => {
                if(select.value) usedValues.add(select.value);
            });

            // Update status tombol tambah
            const totalIngredients = Object.keys(ingredientOptions).length;
            addButton.disabled = usedValues.size >= totalIngredients || allSelects.length >= totalIngredients;
    
            allSelects.forEach(select => {
                const currentValue = select.value;
                select.innerHTML = '<option value="" selected disabled>- Pilih Bahan -</option>';
                
                Object.entries(ingredientOptions).forEach(([id, name]) => {
                    const option = document.createElement('option');
                    option.value = id;
                    option.textContent = name;
                    
                    if(id === currentValue) {
                        option.selected = true;
                    } else if(usedValues.has(id) && id !== currentValue) {
                        option.disabled = true;
                    }
                    
                    select.appendChild(option);
                });
            });
        }
    
        // Tambah baris baru
        addButton.addEventListener('click', function() {
            const newRow = container.querySelector('.ingredient-row').cloneNode(true);
            newRow.querySelector('input').value = '';
            newRow.querySelector('select').selectedIndex = 0;
            newRow.querySelector('.remove-ingredient').disabled = false;
            container.appendChild(newRow);
            updateSelectOptions();
        });
    
        // Hapus baris
        container.addEventListener('click', function(e) {
            if(e.target.classList.contains('remove-ingredient')) {
                if(container.querySelectorAll('.ingredient-row').length > 1) {
                    e.target.closest('.ingredient-row').remove();
                    updateSelectOptions();
                }
            }
        });
    
        // Update opsi saat ada perubahan select
        container.addEventListener('change', function(e) {
            if(e.target.classList.contains('ingredient-select')) {
                updateSelectOptions();
            }
        });
    
        // Inisialisasi pertama kali
        updateSelectOptions();
    });
    </script>
@endsection
