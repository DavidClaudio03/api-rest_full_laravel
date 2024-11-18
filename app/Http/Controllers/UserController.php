<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User; // Asegúrate de que el modelo User está importado

use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
//use Illuminate\Support\Facades\Response;
use Illuminate\Http\Response;


class UserController extends Controller
{
    public function pruebas(Request $request){
        return "Accion de pruebas UserController";
    }

    public function register(Request $request) {
        // Recoger los datos del usuario por post
        $json = $request->input('json', null);
        $params = json_decode($json); // saca un objeto
        $params_array = json_decode($json, true); // saca un array

        // Validar datos
        $validate = Validator::make($params_array, [
            'name' => 'required|alpha',
            'surname' => 'required|alpha',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El usuario no se ha creado',
                'errors' => $validate->errors()
            );
        }else{

            // Validación pasada correctamente

            // Cifrar la contraseña
            $pwd = hash('sha256',$params->password);

            // Crear usuario
            $user = new User();
            $user->name = $params_array['name'];
            $user->surname = $params_array['surname'];
            $user->email = $params_array['email'];
            $user->password = $pwd;
            $user->description = isset($params_array['description']) ? $params_array['description'] : ''; // Añadir este campo
            $user->image = isset($params_array['image']) ? $params_array['image'] : 'default.jpg';
            // Guardar el usuario
            $user->save();

            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El usuario se ha creado',
            );
        }

        return response()->json($data, $data['code']);
    }
    public function login(Request $request) {
        $JwtAuth = new \App\Helpers\JwtAuth();

        // Obtener el JSON directamente del body del request
        $params = json_decode($request->getContent());
        $params_array = json_decode($request->getContent(), true);

        // Validar los datos
        $validate = Validator::make($params_array, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $validate->errors()
            ], 400);
        }

        // Cifrar la password
        $pwd = hash('sha256', $params->password);

        // Devolver token
        $signup = $JwtAuth->signup($params->email, $pwd);

        if ($signup == 'error') {  // Asumiendo que tu JwtAuth devuelve 'error' cuando falla
            return response()->json([
                'status' => 'error',
                'message' => 'Login incorrecto'
            ], 401);
        }

        if (!empty($params->gettoken)) {
            $signup = $JwtAuth->signup($params->email, $pwd, true);
        }

        return response()->json($signup, 200);
    }

    public function update(Request $request) {
        $token = $request->header('Authorization') ?? $request->input('Authorization');
        if (!$token) {
            return response()->json(['message' => 'Token not provided'], 400);
        }

        // Si el token tiene el prefijo "Bearer ", elimínalo
        $token = str_replace('Bearer ', '', $token);

        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //recoger los datos por post
        $json = $request->input('json', null);
        $params_array = json_decode($json, true); // saca un array

        if($checkToken && !empty($params_array)){
            //sacra el usuario
            $user = $jwtAuth->checkToken($token,true);
            // Validar datos
            $validate = Validator::make($params_array, [
                'name' => 'required|alpha',
                'surname' => 'required|alpha',
                'email' => 'required|email|unique:users,'.$user->sub //para que no se pueda cambiar el email
            ]);
            //quitar campos que no quiero cambiar
            unset($params_array['id']);
            unset($params_array['role']);
            unset($params_array['password']);
            unset($params_array['created_at']);
            unset($params_array['remember_token']);

            //actualizar el user en bdd
            $user_update =User::where('id',$user->sub)->update($params_array);

            //devolver el array con los resultados
            $data =array(
                'code' => 200,
                'status' =>'success',
                'message' => 'Usuario actualizado',
                'changed' =>$params_array
            );
        }else{
            $data = array(
                'code' => 400,
                'status' =>'error',
                'message' => 'Login no identificado',
            );
        }
        return response()->json($data, $data['code']);
    }

    public function upload(Request $request){
        //recoger los datos de la petión
        $image = $request->file('file0');

        //VALIDAR QUE SOLO SEA UN IMAGEN
        $validate = Validator::make($request->all(),[
            'file0'=> 'required|image|mimes:jpg,jpeg,png,gif'
        ]);
        //guardad imagen
        if(!$image || $validate->fails()){
            $data = array(
                'code' => 400,
                'status' => 'error',
                'message' => 'Error al subir la imagen',
            );
        }else{
            $image_name = time() . '_' . $image->getClientOriginalName();//para que sea un imgen que no se vuelva repetir
            Storage::disk('users')->put($image_name, File::get($image));

            $data = array(
                'code' => 200,
                'status' =>'success',
                'message' => 'Imagen subida correctamente',
                'image_name' => $image_name,
            );
        }
        return response()->json($data, $data['code']);
    }

    //Metodo para traer la imagen
    public function getImage($filename){
        $isset = Storage::disk('users')->exists($filename);

        if($isset){
            $file = Storage::disk('users')->get($filename);
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

    //Obtener los datos del usuario
    public function detail($id){
        $user = User::find($id);

        if(!empty($user)){
            $data = array(
                'code' => 200,
                'status' =>'success',
                'user' => $user
            );
        }else{
            $data = array(
                'code' => 404,
                'status' => 'error',
                'message' => 'El usuario no existe',
            );
        }
        return response()->json($data, $data['code']);
    }
}
