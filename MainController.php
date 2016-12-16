<?php

namespace App\Http\Controllers;

use App\Client;
use App\Scope;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class MainController extends Controller
{
    function welcome()
    {
        return View("welcome");
    }

    function authenticate()
    {
        try
        {

            $client_secret=$_GET["clientsecret"];
            $client_id=$_GET["clientid"];
            $email=$_GET["email"];
            $password=$_GET["password"];

            $hasScope=false;
            $scopeExist=true;

            if($_GET["scope"]!=null)
            {
                $scope=$_GET["scope"];
                $scopes=explode(" ",$scope);
                $hasScope=true;
            }

            $myClient=Client::where("client_id",$client_id)->where("client_secret",$client_secret)->get()->count();
            if($myClient>0)
            {
                if($hasScope)
                {
                    foreach ($scopes as $myscope)
                    {
                        $myScope=Scope::where("name",$myscope)->where("client_id",$client_id)->get()->count();
                        if(($myScope>0)==false)
                        {
                            $scopeExist=false;
                        }
                    }
                    if($scopeExist)
                    {
                        $myUser=User::where("email",$email)->where("password",$password)->get()->count();

                        if($myUser>0)
                        {
                            $token= Hash::make($client_id."-".$client_secret."-".$scope."-".$email."-".$password);

                            return \Response::json(array('Access_Token' => $token, 'state' => 'true'));
                            // User was authenticated and client was also authorized!
                        }
                        else
                        {
                            return \Response::json(['result' => 'Invalid_User'],400);
                            // Invalid User
                        }
                    }
                    else
                    {
                        return \Response::json(['result' => 'Invalid_Scope'],400);
                        // Invalid Scope
                    }
                }
                else
                {

                }

            }
            else
            {
                return \Response::json(['result' => 'Invalid_Client'],400);
                // Invalid_Client
            }
        }
        catch(\Exception $ex)
        {
            return \Response::json(['result' => $ex->getMessage()],400);
            // Missing Parametres
        }
    }
}
