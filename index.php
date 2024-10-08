<?php
    /* Validar sesion, si existe sesión activa redirigir al inicio del rol activo, sino se mantiene en la página de login */
    session_start();
    if (isset($_SESSION['rol'])) {
      switch ($_SESSION['rol']){
        case 'Administrador':
            header('Location: inicio');
            break;
        case 'Socio':
            header('Location: inicioSocio');
            break;
        case 'Chofer':
            header('Location: inicioChofer');
            break;
      }
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
</head>
<body>
    
    <div class="container" id="Login"> <!-- Contenedor principal con id identificada en Vue -->
        <div class="row justify-content-center">
            <div class="col-lg-4 col-sm-6 col-10 my-5">
                <div class="card shadow-lg bg-white rounded">
                    <img src="uploads/Logo Cooperativa.jpg" alt="Company Logo" width="100" class="card-img-top">
                    <div class="card-body">
                        <!-- Formulario Login con prevención de método submit -->
                        <form @submit.prevent="onSubmit">
                            <h1 class="text-center fw-bolder">LOGIN</h1><hr>
                            <div class="mb-3">
                                <label for="username" class="form-label fw-light">Correo Usuario</label>
                                <input type="text" class="form-control" id="usuario">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label fw-light">Contraseña</label>
                                <input type="password" class="form-control" id="contraseña">
                            </div>
                            <button class="btn btn-primary w-100">Ingresar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts de Vue, Axios, Bootstrap y Archivo Javascript -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.7.13/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.1.3/axios.js" integrity="sha512-xjzDqCmpabFznyCZ92vM1F0gg8ExgSukopZQOCcVbObLyJSmZAkaB9wzOCeSClearljJcjRh67cGDp2uv4diLg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="login.js"></script>
</body>
</html>