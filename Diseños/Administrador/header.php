<?php
    /* Validar sesion, si no existe sesión activa con rol Administrador redirigir al login, sino se mantiene en la página del rol Administrador */
    session_start();
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'Administrador') {
      header('Location: login');
      exit();
    }
    
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="jquery-3.6.1.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="Complementos/sweetAlert/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="Complementos/sweetAlert/sweetalert2.min.css">
    <title>Cooperativa de Transporte Monterrey</title>
    <!-- Esilos para los menús -->
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: column;
            background-color: #FFFFF9;
            overflow-x: hidden; /* Evita el desplazamiento horizontal */
        }
        .sidebar {
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            background-color: #F8F0DA;  /* #f8f9fa */
            padding: 20px;
            overflow-y: auto;
            z-index: 1020; /* Asegura que esté por encima del contenido principal */
        }
        .sidebar .user-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar .user-info img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
        }
        .bottom-nav {
            display: none;
            width: 100%;
            position: fixed;
            bottom: 0;
            left: 0;
            background-color: #F8F0DA;
            padding: 10px 0;
            border-top: 1px solid #ddd;
            z-index: 1030; /* Asegura que esté por encima del contenido principal */
        }
        .bottom-nav a, .bottom-nav .dropdown {
            display: block;
            text-align: center;
            flex-grow: 1;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
            overflow-y: auto;
        }
        @media (max-width: 767px) {
            .sidebar {
                display: none;
            }
            .bottom-nav {
                display: flex;
                justify-content: space-around;
            }
            .content {
                margin-left: 0;
                margin-bottom: 60px; /* Deja espacio para la bottom-nav */
            }
        }
    </style>
</head>

<body>

    <div class="container-fluid"> <!-- Contenedor principal -->
            <div class="row flex-nowrap"> <!-- Inicio de fila -->
                <div class="sidebar"> <!-- Menú lateral para vista en ordenador y dispositivos con pantallas anchas -->
                    <div class="user-info"> <!-- Información de usuario, foto y nombre -->
                        <img src="<?php echo $_SESSION['imagen']; ?>" alt="User Photo" id="sidebar-image">
                        <h3><?php echo $_SESSION['nombreUsuario']; ?></h3>
                        <a href="usuarioAdmin" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Perfil"><i class="bi bi-person-circle"></i></a> <!-- Botón para ver perfil de usuario -->
                        <a href="logout.php" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Salir"><i class="bi bi-box-arrow-right"></i></a> <!-- Botón para deslogearse -->
                    </div>
                    <hr> <!-- Barra divisora de sección -->
                    <nav class="nav flex-column flex-grow-1"> <!-- Links a diferentes páginas del sistema -->
                        <a class="nav-link active" href="inicio"><i class="fs-4 bi-house"></i> Inicio</a>
                        <a class="nav-link" href="personal"><i class="fs-4 bi-people"></i> Gestión Personal</a>
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#submenu1" aria-expanded="false">
                            <i class="fs-4 bi-bus-front"></i> Buses
                        </a>
                        <div class="collapse" id="submenu1">
                            <a class="nav-link ms-3" href="buses">Gestión de Buses</a>
                            <a class="nav-link ms-3" href="ordenBus">Orden de Buses</a>
                            <a class="nav-link ms-3" href="ordenRanchera">Orden de Rancheras</a>
                        </div>
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="collapse" data-bs-target="#submenu2" aria-expanded="false">
                            <i class="fs-4 bi-geo-alt"></i> Rutas
                        </a>
                        <div class="collapse" id="submenu2">
                            <a class="nav-link ms-3" href="asignaciones">Asignación de Rutas</a>
                            <a class="nav-link ms-3" href="rutas">Gestión de Rutas</a>
                        </div>
                        <a class="nav-link active" href="reportes"><i class="fs-4 bi-file-bar-graph"></i> Reportes</a>
                        <div class="footer mt-auto"> <!-- Footer de menú con manual de usuario -->
                            <hr> <!-- Barra divisora de sección -->
                            <a href="https://docs.google.com/viewer?url=https://www.monterreylc.com/uploads/Manual_Usuario.pdf" target="_blank" class="nav-link"><i class="fs-5 bi-journal"></i> Manual de Usuario</a>
                        </div>
                    </nav>
                </div>
                <div class="bottom-nav"> <!-- Menú inferior para vista de moviles -->
                    <a href="inicio" class="nav-link">
                        <i class="fs-4 bi-house"></i><br>
                        <label class="form-label">Inicio</label>
                    </a>
                    <a href="personal" class="nav-link">
                        <i class="fs-4 bi-people"></i><br>
                        <label class="form-label">Personal</label>
                    </a>
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="bottomNavDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fs-4 bi-bus-front"></i><br>
                            <label class="form-label">Buses</label>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="bottomNavDropdown">
                            <li><a class="dropdown-item" href="buses">Gestión de Buses</a></li>
                            <li><a class="dropdown-item" href="ordenBus">Orden de Buses</a></li>
                            <li><a class="dropdown-item" href="ordenRanchera">Orden de Rancheras</a></li>
                        </ul>
                    </div>
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="bottomNavDropdown2" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fs-4 bi-geo-alt"></i><br>
                            <label class="form-label">Rutas</label>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="bottomNavDropdown2">
                            <li><a class="dropdown-item" href="asignaciones">Asignación de Rutas</a></li>
                            <li><a class="dropdown-item" href="rutas">Gestión de Rutas</a></li>
                        </ul>
                    </div>
                    <a href="reportes" class="nav-link">
                        <i class="fs-4 bi-file-bar-graph"></i><br>
                        <label class="form-label">Reportes</label>
                        
                    </a>
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="bottomNavDropdown3" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fs-4 bi-person-circle"></i><br>
                            <label class="form-label">Perfil</label>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="bottomNavDropdown3">
                            <li><a class="dropdown-item" href="usuarioAdmin">Perfil</a></li>
                            <li><a class="dropdown-item" href="https://docs.google.com/viewer?url=https://www.monterreylc.com/uploads/Manual_Usuario.pdf" target="_blank">Manual de Usuario</a></li>
                            <li><a class="dropdown-item" href="logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            <!-- Inicio de sección principal y cuerpo de la página -->
            <main class="col ps-md-2 pt-2 content">