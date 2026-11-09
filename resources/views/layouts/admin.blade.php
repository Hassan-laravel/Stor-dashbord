<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Affiliate Admin Panel</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- تنسيقات لوحة التحكم (Custom CSS) -->
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* تنسيق الشريط الجانبي المطابق للصورة */
        .sidebar {
            width: 260px;
            background-color: #1e222d;
            /* لون داكن */
            color: #fff;
            flex-shrink: 0;
            transition: all 0.3s;
        }

        .sidebar .brand {
            padding: 20px;
            font-size: 1.25rem;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid #2d323e;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: #9ba1ab;
            text-decoration: none;
            transition: 0.2s;
        }

        .sidebar-menu li a i {
            width: 25px;
            font-size: 1.1rem;
        }

        .sidebar-menu li a:hover {
            color: #fff;
            background-color: #262b36;
        }

        /* تنسيق العنصر النشط */
        .sidebar-menu li a.active {
            color: #fff;
            background-color: #2b3b52;
            border-left: 4px solid #3b82f6;
        }

        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .top-header {
            background-color: #fff;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    <div class="wrapper">
        <!-- الشريط الجانبي (Sidebar) -->
        <aside class="sidebar">
            <a href="#" class="brand">
                <i class="fas fa-chart-line me-2"></i> Affiliate Admin
            </a>

            <ul class="sidebar-menu">
                <li>
                    <a href="#">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.stores.index') }}"
                        class="{{ request()->routeIs('admin.stores.*') ? 'active' : '' }}">
                        <i class="fas fa-store"></i> Stores
                    </a>
                </li>
                <li>
                    <!-- تم وضع كلاس active هنا لتمييز صفحة التصنيفات كما في صورتك -->
                    <a href="{{ route('admin.categories.index') }}"
                        class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.products.index') }}"
                        class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i class="fas fa-box"></i> Products
                    </a>
                    <a href="{{ route('admin.bol-products.index') }}"
                        class="list-group-item list-group-item-action bg-transparent border-0 {{ request()->routeIs('admin.bol-products.*') ? 'active fw-bold' : '' }}">
                        <i class="fas fa-box-open text-primary me-2"></i> Bol.com Products
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.post-categories.index') }}">
                        <i class="fas fa-tags"></i> Post Categories
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.posts.index') }}">
                        <i class="fas fa-file-alt"></i> Posts & Pages
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.settings.edit') }}" class="nav-link">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>Settings</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.social-media-links.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-share-alt"></i>
                        <p>Social Media</p>
                    </a>
                </li>
            </ul>
        </aside>

        <!-- المحتوى الرئيسي (Main Content) -->
        <main class="main-content">
            <!-- الهيدر العلوي -->
            <header class="top-header">
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('admin.profile.edit') }}">Profile</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </header>

            <!-- منطقة حقن المحتوى (Views) -->
            <div class="content p-4">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- منطقة حقن السكريبتات الخاصة بكل صفحة -->
    @yield('scripts')
</body>

</html>
