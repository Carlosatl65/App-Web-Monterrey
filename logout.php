<!-- Destruye la sesión activa y redirige al login -->
<?php
    session_start();
    session_destroy();
    header("Location: login");
?>