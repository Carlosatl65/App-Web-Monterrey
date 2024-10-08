var url = "BD/Administrador/inicioConsultas.php"; /* Ruta de archivo php */
var Inventario = new Vue({
    el:'#Tarjetas', /* Nombre de contenedor en vista */
    data:{
        Usuarios: [],
        Buses: [],
        Rutas: [],
    },
    methods:{
        
        //Listar valores de tarjetas de inicio

        //Valores de usuarios
        listarUsuario:function(){
            axios.post(url, {opcion:"listarUsuario"}).then(response =>{
                this.Usuarios = response.data;
            });
        },

        //Valores de unidades de trasnporte
        listarBus:function(){
            axios.post(url, {opcion:"listarBus"}).then(response =>{
                this.Buses = response.data;
            });
        },

        //Valores de rutas
        listarRutas:function(){
            axios.post(url, {opcion:"listarRutas"}).then(response =>{
                this.Rutas = response.data;
            });
        },
        
    },
    created: function(){
        this.listarUsuario();
        this.listarBus();
        this.listarRutas();
    }
});