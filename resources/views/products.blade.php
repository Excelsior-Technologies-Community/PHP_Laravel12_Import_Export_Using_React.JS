    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Laravel React Import/Export</title>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Vite React -->
        @viteReactRefresh
        @vite('resources/js/Product.jsx')

        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </head>

    <body>
        <div class="container mt-5">

            <!-- Import/Export Section -->
            <div class="d-flex flex-column flex-md-row justify-content-end align-items-center mb-4 gap-2">

                <!-- Export Button -->
                <a href="{{ route('products.export') }}" class="btn btn-success d-flex align-items-center justify-content-center gap-1" style="height: 42px;">
                    <i class="bi bi-download"></i> Export Excel
                </a>

                <!-- Import Form -->
                <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                    @csrf
                    <input type="file" name="file" accept=".xlsx,.csv" class="form-control" style="height: 42px;">
                    <button type="submit" class="btn btn-primary d-flex align-items-center justify-content-center gap-1" style="height: 42px;">
                        <i class="bi bi-upload"></i> Import Excel
                    </button>
                </form>
            </div>


            <!-- React App Mount Point -->
            <div id="app"></div>
        </div>

        <script>
            // Pass products data to React
            window.productsData = @json($products ?? []);
        </script>

        <!-- Optional Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>

    </html>