<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class PostController extends Controller
{
    //cargamos el middleware para no usar en el idex y show
    //Esto sirve para no pedir Authorization y excluir las funciones que quiera no quiera usar
    public function __construct()
    {
        $this->middleware('apiAuth', ['except'=>[
            'index','show','getImage','getPostByCategory','getPostsByUser']]);
    }

    //Para listar todos los post
    public function index(){
        $posts = Post::all()->load('category');//load para mostar los datos de la category y no solo el id, si no todo
        return response()->json([
            'code' => 200,
            'status' =>'success',
            'posts' => $posts
        ]);
    }
    //Para listar un unico registros
    public function show($id){
        $post = Post::find($id)->load('category');

        if(is_object($post)){
            return response()->json([
                'code' => 200,
                'status' =>'success',
                'post' => $post
            ]);
        }else{
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'Post no encontrado'
            ], 404);
        }
    }
    //Para crear un nuevo post
    public function store(Request $request){
       //recoger los datos del post
       $json =$request->input('json',null);
       $params= json_decode($json);
       $params_array = json_decode($json, true);

       //validar los datos
       if(!empty($params_array)){
            //cpnseguir usuario identificado por token
            $user= $this->getIdentity($request);

            //validar los datos
            $validate = Validator::make($params_array,[
                'titulo' => 'required',
                'contenido' => 'required',
                'id_cat' => 'required',
                'imagen'=>'required',
            ]);

            //comprobar que no hay fallos en las valudaciones
            if($validate->fails()){
                return response()->json([
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validate->errors()
                ], 400);
            }else{
                //guardar el post
                $post = new Post();
                $post->id_use = $user->sub;
                $post->id_cat = $params->id_cat;
                $post->titulo = $params->titulo;
                $post->contenido = $params->contenido;
                $post->imagen = $params->imagen;
                $post->save();

                return response()->json([
                    'code' => 200,
                    'status' =>'success',
                    'post' => $post,
                ], 200);
            }
        }else{
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha enviado ningún post',
            ], 400);

        }
    }

    //Actualizar un post
    public function update(Request $request, $id){
        // Obtener los datos del post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        //METODOS PARA VALIDAR EL USUARIO POR EL TOKEN
        //PARA VERIFICAR QUE ES EL USUARIO Y QUE LE PERTENECE ESE POST
        //Error en caso de que el id_use no tiene permiso
        $post = Post::find($id);
        if (is_object($post)) {
            if ($post->id_use!= $this->getIdentity($request)->sub) {
                return response()->json([
                    'code' => 403,
                    'status' => 'error',
                    'message' => 'No tiene permisos para actualizar este post',
                ], 403);
            }
        } else {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Error en los datos enviados',
                ], 404);
        }
        // Validar los datos
        if (!empty($params_array)) {
            $validate = Validator::make($params_array, [
                'titulo' => 'required',
                'contenido' => 'required',
                'imagen' => 'required',
                'id_cat' => 'required',
            ]);

            // Quitar los campos que no voy a utilizar
            unset($params_array['id']);
            unset($params_array['id_use']);
            unset($params_array['created_at']);

            //Conseguir usuar identificado
            $user=$this->getIdentity($request);

            if ($validate->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => 'error',
                    'message' => 'Error de validación',
                    'errors' => $validate->errors()
                ], 400);
            } else {
                $where=[
                    'id'=>$id,
                    'id_use'=>$user->sub
                ];
                $post = Post::updateOrCreate($where, $params_array);
                if (is_object($post)) {
                    // Guardar los datos anteriores (si necesitas comparar)
                    $beforeUpdate = $post->toArray();

                    // Actualizar el modelo
                    $post->titulo = $params_array['titulo'];
                    $post->contenido = $params_array['contenido'];
                    $post->imagen = $params_array['imagen'];
                    $post->id_cat = $params_array['id_cat'];
                    $post->save();

                    // Obtener los cambios realizados
                    $changes = $post->getChanges();

                    return response()->json([
                        'code' => 200,
                        'status' => 'success',
                        'post' => $post,
                        'changes' => $changes,
                    ], 200);
                } else {
                    return response()->json([
                        'code' => 404,
                        'status' => 'error',
                        'message' => 'Post no encontrado'
                    ], 404);
                }
            }
        } else {
            return response()->json([
                'code' => 400,
                'status' => 'error',
                'message' => 'No se ha enviado ningún post para actualizar',
            ], 400);
        }
    }

    //Eliminar un registro
    public function destroy($id, Request $request){
        //conseguir usuario identificado por token
        $user= $this->getIdentity($request);

        //conseguir la registro
        //METODOS PARA VERIFICAR QUE LE PERTENECE ESE POST y que el id_user
        //CONSIDA CON EL TOKEN DEL USUARIO
        $post = Post::where('id',$id)->where('id_use',$user->sub)->first();

        //validar la registro
        if(!empty($post)){
            //Eliminar el registro
            $post->delete();

        //Devolver algo
            $data=[
                'code'=>200,
                'status'=>'success',
                'message'=>'Post eliminado correctamente',
                'post'=>$post
            ];
        }else{
            $data=[
                'code'=>404,
                'status'=>'error',
                'message'=>'Post no encontrado',
            ];
        }
        //borrar la registro

        return response()->json($data, $data['code']);
    }
    //NETODO PARA VERIFICAR QUE EL USUARIO ESTA LOGUEADO
    private function getIdentity($request){
        $jwtAuth =new JwtAuth();
        $token = $request->header('Authorization', null);
        $user= $jwtAuth->checkToken($token,true);
        return $user;
    }

    //Metodo para subir IMAGENES
    //se debe CREAR la RUTA
    public function upload(Request $request){
        //recoger la imagen
        $image=$request->file('file0');

        //validar la imagen
        $validate= Validator::make($request->all(),[
            'file0'=>'required|image|mimes:gif,jpg,png,jpeg'
        ]);
        //guardar la imagen
        if(!$image || $validate->fails()){
            $data=[
                'code'=>400,
                'status'=>'error',
                'message'=> 'Error al subir la imagen'
            ];
        }else{
            $image_name=time().$image->getClientOriginalName();
            Storage::disk('images')->put($image_name, File::get($image));
            $data=[
                'code'=>200,
                'status'=>'success',
                'message'=>'Imagen subida correctamente',
                'image'=>$image_name
                ];
        }
        //devolver datos
        return response()->json($data,$data['code']);
    }
        //Metodo para traer la imagen
        public function getImage($filename){
            $isset = Storage::disk('images')->exists($filename);

            if($isset){
                $file = Storage::disk('images')->get($filename);
                return new  Response($file,200);
            }else{
                $data = array(
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'La imagen no existe',
                );
                return response()->json($data, $data['code']);
            }
        }

    //Obtener los posts por categoría
    public function getPostsByCategory($id){
        $posts = Post::where('id_cat', $id)->get();//->orderBy('id', 'DESC')->get();

        if(is_object($posts)){
            return response()->json([
                'code' => 200,
                'status' =>'success',
                'posts' => $posts
            ]);
        }else{
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'No hay posts para esta categoría',
            ]);
        }
    }
    //obtener los posts por usuario
    public function getPostsByUser($id){
        $posts = Post::where('id_use', $id)->get();//->orderBy('id', 'DESC')->get();

        if(is_object($posts)){
            return response()->json([
                'code' => 200,
                'status' =>'success',
                'posts' => $posts
            ]);
        }else{
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'No hay posts para este usuario',
            ]);
        }
    }

}
