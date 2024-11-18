<?php
namespace App\Helpers;

use App\Models\User as ModelsUser;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\DB;
use App\User;

class JwtAuth{
    public $key;
    public function __construct(){
        $this->key = 'clave_secreta_999';
    }

    public function signup($email, $password,$getToken=null){
        //buscar si existe el usuario
        $user=ModelsUser::where([
            'email'=>$email,
            'password'=>$password
        ])->first();
    //comprobar si son correctas
            $signup=false;
            if(is_object($user)){
                $signup=true;
            }
    //generar el token con los datos del usuario identificado
            if($signup){
                $token=array(
                    'sub' => $user->id,
                    'email' =>$user->email,
                    'name'=>$user->name,
                    'username'=>$user->username,
                    'iat' => time(),
                    'exp'=> time()+ (7*24*60*60), //desdpues de una semana el token se caducaria
                );
                $jwt=JWT::encode($token,$this->key,'HS256');
                $decode = JWT::decode($jwt, new Key($this->key, 'HS256'));

                if(is_null($getToken)){
                    $data= $jwt;
                }else{
                    $data=$decode;
                }
            }else{
                $data=array(
                    'status'=>'error',
                    'message'=>'Login incorrecto'
                );
            }
        return $data;

    }
    public function checkToken($jwt, $getIdentity=false){
        $auth =false;
        try{
            $decode=JWT::decode($jwt,new Key($this->key,'HS256'));
        }catch(\UnexpectedValueException $e){
            $auth=false;
        }catch(\DomainException $e){
            $auth=false;
        }
        if(!empty($decode) && is_object($decode) && isset($decode->sub)){
            $auth=true;
        }else{
            $auth=false;
        }
        if($getIdentity){
            return $decode;
        }
        return $auth;
    }

}
