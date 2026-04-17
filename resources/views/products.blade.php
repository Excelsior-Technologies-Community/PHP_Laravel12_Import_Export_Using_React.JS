<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>

    <!-- CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- React -->
    @viteReactRefresh
    @vite('resources/js/Product.jsx')

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom Style -->
    <style>
        body {
            background: #f4f6f9;
        }

        .main-card {
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        .header-box {
            background: linear-gradient(135deg, #4e73df, #1cc88a);
            color: white;
            border-radius: 12px;
            padding: 20px;
        }

        .btn-custom {
            border-radius: 8px;
            padding: 6px 14px;
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        input[type="file"] {
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <div class="container py-5">

        <!-- HEADER -->
        <div class="header-box mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0 fw-bold">📦 Product Management</h3>
                <small>Manage your products easily</small>
            </div>
        </div>

        <!-- SUCCESS MESSAGE -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                {{ session('success') }}
                <button class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- IMPORT EXPORT CARD -->
        <div class="card main-card p-4 mb-4">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">

                <h5 class="fw-semibold mb-0">📊 Import / Export</h5>

                <div class="d-flex flex-wrap gap-2">

                    <!-- EXPORT -->
                    <a href="{{ route('products.export') }}" class="btn btn-success btn-custom">
                        <i class="bi bi-download"></i> Export
                    </a>

                    <!-- IMPORT -->
                    <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data"
                        class="d-flex gap-2">
                        @csrf

                        <input type="file" name="file" accept=".xlsx,.csv" class="form-control" required>

                        <button class="btn btn-primary btn-custom">
                            <i class="bi bi-upload"></i> Import
                        </button>
                    </form>

                </div>
            </div>

        </div>

        <!-- REACT TABLE CARD -->
        <div class="card main-card p-4">
            <h5 class="fw-semibold mb-3">📋 Product List</h5>

            <div id="app"></div>
        </div>

    </div>

    <!-- PASS DATA -->
    <script>
        window.productsData = @json($products ?? []);
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>