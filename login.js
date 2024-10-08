var url = "login.php"; /* Ruta de archivo php */
var Inventario = new Vue({
    el:'#Login', /* Nombre de contenedor en vista */
    data:{
        usuario: "",
        contraseña: ""
    },
    methods:{
        
        //Pasar datos a BD/login
        onSubmit:function(){          
            //Obtener datos del formulario
            this.usuario = $("#usuario").val();
            this.contraseña = $("#contraseña").val();
            //Enviar datos por axios a la URL
            axios.post(url, {
                usuario: this.usuario,
                contraseña: this.contraseña,
            }).then(response =>{
                if (response.data) {
                    // Si los datos son correctos, redirigir al usuario a la página correspondiente
                    if (response.data['rol'] == 'Administrador') {
                        window.location = 'inicio'; //Ruta amigable
                    } else if (response.data['rol'] == 'Socio') {
                        window.location = 'inicioSocio'; //Ruta amigable
                    } else if (response.data['rol'] == 'Chofer') { 
                        window.location = 'inicioChofer'; //Ruta amigable
                    }
                } else {
                    // Si los datos son incorrectos, mostrar un mensaje de error
                    Swal.fire({
                        title: 'Datos Incorrectos!',
                        icon: 'error',
                    });
                }
                //Poner las variables en blanco
                this.usuario = "";
                this.contraseña = "";
            }).catch(error => {
                console.error(error.response.data);
            });
        }
        
    }
});