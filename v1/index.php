<?php

require_once '../include/DbHandler.php';
require_once '../include/PassHash.php';
require '.././libs/Slim/Slim.php';
//require '.././libs/Upload/Storage/FileSystem.php';
//require '.././libs/Upload/File.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;


//define('PRINC_PATH_IMAGES', '/home/zerosdev/public_html/mctapi.zerosdev.com/Zmercato.Api/images');
define('PRINC_PATH_IMAGES', 'C:/wamp/www/Zmercato.Api/images');

/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route)
{
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['Authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key
        if (!$db->check_api_key($api_key)) {
            // api key is not present in users table
            $response["error"] = true;
            $response["message"] = "Access Denied. Invalid Api key";
            echoResponse(401, $response);
            $app->stop();
        } else {
            //global $user_id;
            // get user primary key id
            //$user_id = $db->get_user_id_by_api_key($api_key);
        }
    } else {
        // api key is missing in header
        $response["error"] = true;
        $response["message"] = "Api key is misssing";
        echoResponse(400, $response);
        $app->stop();
    }
}

//VERSION

$app->get('/check_version_final', function () use ($app) {

    global $user_id;

    $db = new DbHandler();
    $response = array();

    $response["new_function"] = array();

    $tmp = "Cambios en el diseño de algunas interfaces";
    $tmp1 = "Corregidos errores en algunos dispositivos";
    $tmp1 = "Integración con Facebook";

    array_push($response["new_function"], $tmp);
    array_push($response["new_function"], $tmp1);

    $version = "1.0";
    $response["version"] = $version;

    echoResponse(200, $response);

});

$app->post('/check_version', function () use ($app) {

    verifyRequiredParams(array('v'));
    global $user_id;
    $response = array();
    $db = new DbHandler();


    //Rrcibo los datos
    $v = $app->request()->post('v');

    $current_version = "1.0.6";

    if ($v == $current_version) {

        $response["new_version"] = false;

    } else {

        $response["new_version"] = true;

    }

    echoResponse(200, $response);

});


//CODE
$app->post('/verifcode_mercato', function () use ($app) {


    verifyRequiredParams(array('body', 'mac'));
    global $user_id;
    $response = array();
    $db = new DbHandler();


    //Rrcibo los datos
    $code = $app->request()->post('body');
    $mac = $app->request()->post('mac');


    //obtengo todos los codigos

    $all_codes = $db->get_all_codes();


    while ($code1 = $all_codes->fetch_assoc()) {


        //VOY COMPROBANDO SI ES VALIDO EL CODIGO

        if (PassHash::check_password($code1["body"], $code)) {

            //existe, chequeo que no este usado

            $result_used = $db->check_is_used($code1["body"], 0);

            if ($result_used) {

                //no esta usado, procedemos

                //Recibo los datos del code, cant dias y tipo de servicio
                $data = $db->get_data_code($code1["body"]);

                if ($data != null) {

                    //obtengo los datos del code y proceso
                    $response["error"] = false;
                    $response["error_sended"] = false;

                    $response["id_code"] = $data["id_code"];
                    $response["type"] = $data["type"];
                    $response["days"] = $data["days"];
                    $response["message"] = "Código aceptado";


                    //Reviso que si a sido enviado para el dato de enviado
                    $result_sended = $db->check_is_sended($code1["body"], 1);

                    if ($result_sended) {

                        //ya fue accedido y no usado, lo notifico

                        $response["error_sended"] = true;

                        $time = $db->get_time_is_sended($code1["body"]);

                        if ($time != null) {

                            $response["time_sended"] = $time;

                        }


                    }


                    //marco las banderas sended

                    $db->update_sended_code(1, $code1["body"]);
                    $db->update_mac_sended($mac, $code1["body"]);
                    $db->update_timestap_sended_code($code1["body"]);

                    echoResponse(200, $response);


                } else {

                    //no puedo acceder a los datos del code, notifico el error

                    $response["error"] = true;
                    $response["message"] = "Ha ocurrido un error, por favor contactar con el equipo de Mercato";
                    echoResponse(200, $response);

                }

            } else {

                $response["error"] = true;
                $response["message"] = "El código ya ha sido usado";

                echoResponse(200, $response);

            }


        }


    }

    $response["error"] = true;
    $response["message"] = "El código no es correcto";


    echoResponse(200, $response);

});


// CODE PREM

$app->post('/get_arts_choice_prem', function () use ($app) {

    verifyRequiredParams(array('id_user'));
    global $user_id;
    $response = array();
    $db = new DbHandler();
    $id_user = $app->request()->post('id_user');


    $result = $db->get_arts_for_choice_prem($id_user, 0);

    $response["error"] = false;
    $response["arts"] = array();

    // looping through result and preparing tasks array

    while ($art = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["id_art"] = $art["id_art"];
        $tmp["created_at"] = $art["created_at"];
        $tmp["price"] = $art["price"];
        $tmp["title"] = $art["title"];
        $tmp["body"] = $art["body"];
        $tmp["img_p"] = $art["img_p"];
        $tmp["des"] = $art["des"];
        $tmp["vis"] = $art["vis"];
        $tmp["coments"] = $art["coments"];
        $tmp["share"] = $art["share"];
        $tmp["prior"] = $art["prior"];
        $tmp["coin"] = $art["coin"];
        $tmp["id_dep"] = $art["id_dep"];
        $tmp["id_cat"] = $art["id_cat"];
        $tmp["id_prov"] = $art["id_prov"];
        $tmp["id_user"] = $art["id_user"];

        array_push($response["arts"], $tmp);
    }

    echoResponse(200, $response);
});

$app->post('/addpremuser_code', function () use ($app) {
    // check for required params

    verifyRequiredParams(array('id_user', 'id_art', 'id_code', 'mac'));

    global $user_id;
    $db = new DbHandler();

    $response = array();
    $id_user = $app->request()->post('id_user');
    $id_art = $app->request()->post('id_art');
    $id_code = $app->request()->post('id_code');
    $mac = $app->request()->post('mac');

    $result_first_step = $db->check_code_first_step($id_code, 1, 0);


    //chequeo que el code se envio y no se ha usado

    if ($result_first_step) {

        //adiciono el premium

        //primero obtengo la cantidad de dias para fijar la fecha de entrada y fecha de salida

        $result_second_step = $db->get_data_second_step($id_code);

        if ($result_second_step != NULL) {

            $days = $result_second_step["days"];

            $days1 = (int)$days + 1;

            $diffDays = new DateInterval('P' . $days1 . 'D');

            $date_begin = new DateTime('now');

            $date_end = new DateTime('now');

            $date_end->add($diffDays);


            $d_b = $date_begin->format('Y-m-d H:i:s');
            $d_e = $date_end->format('Y-m-d H:i:s');


            $prem_id = $db->add_prem_user($d_b, $d_e, $days, 1, $id_art, $id_user);

            if ($prem_id != NULL) {

                $response["error"] = false;
                $response["message"] = "Se realizó la asignación premium correctamente";
                $response["prem_id"] = $prem_id;

                //realizar transaccion para validar la venta
                //cambiar boolean_used, mac_used, time_used, is_user, id_art, sold


                $db->update_datos_venta_premium(1, $mac, 1, $id_art, $id_user, $id_code);


                $db->update_is_prem_art(1, $id_art);


            } else {

                $response["error"] = true;
                $response["message"] = "Error al insertar el premium";

            }

        } else {

            $response["error"] = true;
            $response["message"] = "Datos no disponibles";
        }


    } else {

        $response["error"] = true;
        $response["message"] = "El code no esta disponible";
    }

    echoResponse(201, $response);

});
// CODE TOP

$app->post('/addtopuser_code', function () use ($app) {
    // check for required params

    verifyRequiredParams(array('id_user', 'id_art', 'id_code', 'mac'));

    global $user_id;
    $db = new DbHandler();

    $response = array();
    $id_user = $app->request()->post('id_user');
    $id_art = $app->request()->post('id_art');
    $id_code = $app->request()->post('id_code');
    $mac = $app->request()->post('mac');


    $result_first_step = $db->check_code_first_step($id_code, 1, 0);

    //chequeo que el code se envio y no se ha usado

    if ($result_first_step) {

        //adiciono el premium

        //primero obtengo la cantidad de dias para fijar la fecha de entrada y fecha de salida

        $result_second_step = $db->get_data_second_step($id_code);

        if ($result_second_step != NULL) {

            $days = $result_second_step["days"];

            $days1 = (int)$days + 1;

            $diffDays = new DateInterval('P' . $days1 . 'D');

            $date_begin = new DateTime('now');

            $date_end = new DateTime('now');

            $date_end->add($diffDays);


            $d_b = $date_begin->format('Y-m-d H:i:s');
            $d_e = $date_end->format('Y-m-d H:i:s');


            $top_id = $db->add_top_user($d_b, $d_e, $days, 1, $id_art, $id_user);

            if ($top_id != NULL) {

                $response["error"] = false;
                $response["message"] = "Se realizó la asignación top correctamente";
                $response["top_id"] = $top_id;

                //realizar transaccion para validar la venta
                //cambiar boolean_used, mac_used, time_used, is_user, id_art, sold


                $db->update_datos_venta_top(1, $mac, 1, $id_art, $id_user, $id_code);


                $db->update_is_top_art(1, $id_art);


            } else {

                $response["error"] = true;
                $response["message"] = "Error al insertar el top";

            }

        } else {

            $response["error"] = true;
            $response["message"] = "Datos no disponibles";
        }


    } else {

        $response["error"] = true;
        $response["message"] = "El code no esta disponible";
    }

    echoResponse(201, $response);

});

$app->post('/get_arts_choice_top', function () use ($app) {


    verifyRequiredParams(array('id_user'));
    global $user_id;
    $response = array();
    $db = new DbHandler();
    $id_user = $app->request()->post('id_user');

    $result = $db->get_arts_for_choice_top($id_user, 0);

    $response["error"] = false;
    $response["arts"] = array();


    while ($art = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["id_art"] = $art["id_art"];
        $tmp["created_at"] = $art["created_at"];
        $tmp["price"] = $art["price"];
        $tmp["title"] = $art["title"];
        $tmp["body"] = $art["body"];
        $tmp["img_p"] = $art["img_p"];
        $tmp["des"] = $art["des"];
        $tmp["vis"] = $art["vis"];
        $tmp["coments"] = $art["coments"];
        $tmp["share"] = $art["share"];
        $tmp["prior"] = $art["prior"];
        $tmp["coin"] = $art["coin"];
        $tmp["id_dep"] = $art["id_dep"];
        $tmp["id_cat"] = $art["id_cat"];
        $tmp["id_prov"] = $art["id_prov"];
        $tmp["id_user"] = $art["id_user"];

        array_push($response["arts"], $tmp);
    }

    echoResponse(200, $response);

});


//CODE TOP


//ADD

$app->post('/registermov', function () use ($app) {
    // check for required params


    verifyRequiredParams(array('name', 'mov', 'pass', 'sex', 'img', 'id_prov'));
    // reading post params
    $name = $app->request()->post('name');
    $mov = $app->request()->post('mov');
    $password = $app->request()->post('pass');
    $sex = $app->request()->post('sex');
    $img = $app->request()->post('img');
    $id_prov = $app->request()->post('id_prov');
    $response = array();

    // validating email address
    $db = new DbHandler();

    if ($sex == "F") {
        $path = "wom1";
    } else {
        $path = "man1";
    }

    //$img, $name, $mov, $password, $sex, $prov

    $res = $db->add_user_mov($path, $name, $mov, $password, $sex, $id_prov);

    if ($res != NULL) {

        $user = $db->get_user_by_id_para_devolver_register($res);

        $response["error"] = false;
        $response['id_user'] = $user['id_user'];
        $response['api_key'] = $user['api_key'];
        $response['img'] = $path;
        $response['img_port'] = $user['img_port'];
        $response['name'] = $user['name'];
        $response['mov'] = $user['mov'];
        $response['email'] = $user['email'];
        $response['sex'] = $user['sex'];
        $response['id_prov'] = $user['id_prov'];
        $response["message"] = "Te has registrado con éxito";

        if (strlen($img) > 5) {

            $id_user = $user['id_user'];

            $path = PRINC_PATH_IMAGES . "/profiles/user_$id_user.jpg";

            $path_pent = "/profiles/user_$id_user.jpg";

            if (file_put_contents($path, base64_decode($img))) {

                if ($db->update_img_user($user['id_user'], $path_pent)) {

                    $response['bool_img'] = false;
                    $response['img'] = $path_pent;

                } else {

                    $response['bool_img'] = true;

                }

            } else {

                $response['bool_img'] = true;

            }

        } else {

            if ($img != "empty") {

                $result = $db->update_img_user($user['id_user'], $img);

                if ($result) {

                    $response['img'] = $img;

                }

            } else {

                if ($sex == "F") {
                    $path1 = "wom1";
                } else {
                    $path1 = "man1";
                }

                $result = $db->update_img_user($user['id_user'], $path1);

                if ($result) {

                    $response['img'] = $path1;

                }

            }


        }

    } else {
        $response["error"] = true;
        $response["message"] = "Un error ha ocurrido mientras te registrabas. Intenta más tarde.";
    }

    // echo json response
    echoResponse(201, $response);

});

$app->post('/registeremail', function () use ($app) {

    // check for required params
    verifyRequiredParams(array('name', 'email', 'pass', 'sex', 'img', 'id_prov'));
    // reading post params
    $name = $app->request()->post('name');
    $email = $app->request()->post('email');
    $password = $app->request()->post('pass');
    $sex = $app->request()->post('sex');
    $img = $app->request()->post('img');
    $id_prov = $app->request()->post('id_prov');
    $response = array();
    // validating email address
    $db = new DbHandler();

    if ($sex == "F") {
        $path = "wom1";
    } else {
        $path = "man1";
    }

    $res = $db->add_user_email($path, $name, $email, $password, $sex, $id_prov);

    if ($res != NULL) {

        $user = $db->get_user_by_id_para_devolver_register($res);

        $response["error"] = false;
        $response['id_user'] = $user['id_user'];
        $response['api_key'] = $user['api_key'];
        $response['img'] = $path;
        $response['img_port'] = $user['img_port'];
        $response['name'] = $user['name'];
        $response['email'] = $user['email'];
        $response['sex'] = $user['sex'];
        $response['mov'] = $user['mov'];
        $response['id_prov'] = $user['id_prov'];

        $response["message"] = "Te has registrado con éxito";

        if (strlen($img) > 5) {

            $id_user = $user['id_user'];

            $path = PRINC_PATH_IMAGES . "/profiles/user_$id_user.jpg";

            $path_pent = "/profiles/user_$id_user.jpg";

            if (file_put_contents($path, base64_decode($img))) {

                if ($db->update_img_user($user['id_user'], $path_pent)) {
                    $response['bool_img'] = false;
                    $response['img'] = $path_pent;

                } else {

                    $response['bool_img'] = true;

                }

            } else {

                $response['bool_img'] = true;
            }

        } else {

            if ($img != "empty") {

                $result = $db->update_img_user($user['id_user'], $img);

                if ($result) {

                    $response['img'] = $img;

                }

            } else {

                if ($sex == "F") {
                    $path1 = "wom1";
                } else {
                    $path1 = "man1";
                }

                $result = $db->update_img_user($user['id_user'], $path1);

                if ($result) {

                    $response['img'] = $path1;

                }

            }
        }

    } else {

        $response["error"] = true;
        $response["message"] = "Ha ocurrido un error de red. Intenta más tarde";

    }

    // echo json response
    echoResponse(201, $response);


});

function get_type_register_facebook($email, $mov)
{

    if ($email != "Sin email" && $mov != "Sin móvil") {

        //login con ambos
        return 1;

    }

    if ($email != "Sin email" && $mov == "Sin móvil") {

        //login con email
        return 2;

    }

    if ($email == "Sin email" && $mov != "Sin móvil") {

        //login con mov
        return 3;

    }

    return 4;

}

$app->post('/verifb_profile_with_email', function () use ($app) {

    verifyRequiredParams(array('fb_id', 'email'));
    global $user_id;
    $response = array();
    $db = new DbHandler();

    $fb_id = $app->request()->post('fb_id');
    $email = $app->request()->post('email');

    $result_fb_id = $db->check_fb_id_user($fb_id);

    if ($result_fb_id) {

        //ya tiene perfil en facebook, iniciamos sesion

        $user = $db->get_user_by_id_para_devolver_login_fb($fb_id);

        if ($user != NULL) {

            $response["mode"] = 0;
            $response['id_user'] = $user['id_user'];
            $response['api_key'] = $user['api_key'];
            $response['img'] = $user['img'];
            $response['img_port'] = $user['img_port'];
            $response['name'] = $user['name'];
            $response['mov'] = $user['mov'];
            $response['email'] = $user['email'];
            $response['sex'] = $user['sex'];
            $response['id_prov'] = $user['id_prov'];

            $response['error_obtain_data'] = false;

            if ($user['sex'] == "F") {

                $response["message"] = "Bienvenida de vuelta, " . $user['name'];

            } else {

                $response["message"] = "Bienvenido de vuelta, " . $user['name'];

            }

        } else {

            // unknown error occurred, indicarle que se loguee por la via normal
            $response['mode'] = 3;
            $response['message'] = 'Ha ocurrido un error de red. Intenta mas tarde';

        }


    } else {

        //aqui verifico el email para si esta registrado con ese email sencillamente agregar fb_id y mantener
        //la misma cuenta

        if ($email != "Sin email") {

            //verifico si ese email existe, para si existe agregar fb_id y mantener ese perfil

            $result_email = $db->check_email_usuario($email);

            if ($result_email) {

                //existe el email, actualizamos el perfil con los nuevos datos de facebook
                $response["mode"] = 1;

            } else {

                //no existe el email, procedemos a registro
                $response["mode"] = 2;
            }

        } else {

            //tampoco tiene email, envio para proceso de registro
            $response["mode"] = 2;

        }

    }


    echoResponse(200, $response);

});

$app->post('/registerfb', function () use ($app) {

    // check for required params
    verifyRequiredParams(array('fb_id', 'name', 'email', 'sex', 'img',
        'id_prov','mov'));
    // reading post params
    $fb_id = $app->request()->post("fb_id");
    $name = $app->request()->post('name');
    $email = $app->request()->post('email');
    $mov = $app->request()->post('mov');
    $sex = $app->request()->post('sex');
    $img = $app->request()->post('img');
    $id_prov = $app->request()->post('id_prov');
    $response = array();
    // validating email address
    $db = new DbHandler();

    if ($sex == "F") {
        $path = "wom1";
    } else {
        $path = "man1";
    }


    $bool_update = true;

    switch (get_type_register_facebook($email, $mov)) {

        case 1:
            //llegaron ambos
            //verificar que el movil ya existe, en caso positivo solo actualizar
            $res = $db->check_mov_usuario(gestionar_mov($mov));

            if ($res) {
                //existe el mismo usuario

                $bool_update = $db->update_profile_to_fb_with_mov($fb_id, $name, gestionar_mov($mov),$email);

            } else {

                //no existe el movil, agregamos
                $res = $db->add_user_fb_mov_email($fb_id, $path, $name, gestionar_mov($mov), $email, $sex, $id_prov);

            }
            break;
        case 2:
            //llego solo el email
            $res = $db->add_user_fb_email($fb_id, $path, $name, $email, $sex, $id_prov);
            break;

        case 3:
            //llego solo el movil

            //verificar que el movil ya existe, en caso positivo solo actualizar
            $res = $db->check_mov_usuario(gestionar_mov($mov));
            if ($res) {
                //existe el mismo usuario

                $bool_update = $db->update_profile_to_fb_with_mov1($fb_id, $name, gestionar_mov($mov));

            } else {
                //no existe el movil, agregar usuario
                $res = $db->add_user_fb_mov($fb_id, $path, $name, gestionar_mov($mov), $sex, $id_prov);
            }

            break;

        case 4:
            // ninguna de las anteriores combinaciones, pensar q hacer
            break;

    }

    if ($res != NULL && $bool_update) {

        $user = $db->get_user_by_FB_id_para_devolver_register($fb_id);

        $response["error"] = false;
        $response['id_user'] = $user['id_user'];
        $response['api_key'] = $user['api_key'];
        $response['img'] = $path;
        $response['img_port'] = $user['img_port'];
        $response['name'] = $user['name'];
        $response['email'] = $user['email'];
        $response['sex'] = $user['sex'];
        $response['mov'] = $user['mov'];
        $response['id_prov'] = $user['id_prov'];

        $response["message"] = "Te has registrado con éxito";


        $id_user = $user['id_user'];

        $path = PRINC_PATH_IMAGES . "/profiles/user_$id_user.jpg";

        $path_pent = "/profiles/user_$id_user.jpg";

        if (file_put_contents($path, base64_decode($img))) {

            $db->update_img_user($user['id_user'], $path_pent);
            $response['bool_img'] = false;
            $response['img'] = $path_pent;


        } else {

            $response['bool_img'] = true;
        }


    } else {

        $response["error"] = true;
        $response["message"] = "Ha ocurrido un error de red. Intenta más tarde";

    }

    // echo json response
    echoResponse(200, $response);


});

$app->post('/update_profile_with_fb', function () use ($app) {

    // check for required params
    verifyRequiredParams(array('fb_id', 'name', 'email', 'img'));
    // reading post params
    $fb_id = $app->request()->post("fb_id");
    $name = $app->request()->post('name');
    $email = $app->request()->post('email');
    $img = $app->request()->post('img');

    $response = array();
    // validating email address
    $db = new DbHandler();

    $res = $db->update_profile_to_fb_with_email($fb_id, $name, $email);

    if ($res) {

        $user = $db->get_user_by_email_id_para_devolver_register($email);

        $response["error"] = false;
        $response['id_user'] = $user['id_user'];
        $response['api_key'] = $user['api_key'];
        $response['img'] = $user["img"];
        $response['img_port'] = $user['img_port'];
        $response['name'] = $user['name'];
        $response['email'] = $user['email'];
        $response['sex'] = $user['sex'];
        $response['mov'] = $user['mov'];
        $response['id_prov'] = $user['id_prov'];

        $response["message"] = "Te has registrado con éxito";


        //actualizo la iamgen con la del perfil de facebook

        $id_user = $user['id_user'];

        $path = PRINC_PATH_IMAGES . "/profiles/user_$id_user.jpg";

        $path_pent = "/profiles/user_$id_user.jpg";

        if (file_put_contents($path, base64_decode($img))) {

            $db->update_img_user($user['id_user'], $path_pent);
            $response['img'] = $path_pent;
        }

    } else {

        $response["error"] = true;
        $response["message"] = "Ha ocurrido un error de red. Intenta más tarde";

    }

    // echo json response
    echoResponse(200, $response);

});

$app->post('/loginfb', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('mov', 'pass'));
    // reading post params
    $mov = $app->request()->post('mov');
    $password = $app->request()->post('pass');
    $response = array();

    $db = new DbHandler();
    // check for correct email and password


    if ($db->check_login_mov(gestionar_mov($mov), $password)) {
        // get the user by email
        $user = $db->get_user_by_id_para_devolver_login_mov(gestionar_mov($mov));

        if ($user != NULL) {

            $response["error"] = false;
            $response['id_user'] = $user['id_user'];
            $response['api_key'] = $user['api_key'];
            $response['img'] = $user['img'];
            $response['img_port'] = $user['img_port'];
            $response['name'] = $user['name'];
            $response['mov'] = $user['mov'];
            $response['email'] = $user['email'];
            $response['sex'] = $user['sex'];
            $response['id_prov'] = $user['id_prov'];


            if ($user['sex'] == "F") {

                $response["message"] = "Bienvenida de vuelta, " . $user['name'];

            } else {

                $response["message"] = "Bienvenido de vuelta, " . $user['name'];

            }

        } else {

            // unknown error occurred
            $response['error'] = true;
            $response['message'] = 'Ha ocurrido un error de red. Intenta mas tarde';

        }

    } else {

        // user credentials are wrong
        $response['error'] = true;
        $response['message'] = 'datos';

    }

    echoResponse(200, $response);

});

$app->post('/loginemail', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('email', 'pass'));
    // reading post params
    $email = $app->request()->post('email');
    $password = $app->request()->post('pass');
    $response = array();

    $db = new DbHandler();

    // check for correct email and password

    if ($db->check_login_email($email, $password)) {

        // get the user by email

        $user = $db->get_user_by_id_para_devolver_login_email($email);

        if ($user != NULL) {
            $response["error"] = false;
            $response['id_user'] = $user['id_user'];
            $response['api_key'] = $user['api_key'];
            $response['img'] = $user['img'];
            $response['img_port'] = $user['img_port'];
            $response['name'] = $user['name'];
            $response['mov'] = $user['mov'];
            $response['email'] = $user['email'];
            $response['sex'] = $user['sex'];
            $response['id_prov'] = $user['id_prov'];
            $response["message"] = "Has iniciado sesión con éxito";

        } else {
            // unknown error occurred
            $response['error'] = true;
            $response['message'] = 'Ha ocurrido un error de red. Intenta mas tarde';
        }

    } else {
        // user credentials are wrong
        $response['error'] = true;
        $response['message'] = 'datos';
    }

    echoResponse(200, $response);
});

$app->post('/loginmov', function () use ($app) {
    // check for required params
    verifyRequiredParams(array('mov', 'pass'));
    // reading post params
    $mov = $app->request()->post('mov');
    $password = $app->request()->post('pass');
    $response = array();

    $db = new DbHandler();
    // check for correct email and password


    if ($db->check_login_mov(gestionar_mov($mov), $password)) {
        // get the user by email
        $user = $db->get_user_by_id_para_devolver_login_mov(gestionar_mov($mov));

        if ($user != NULL) {

            $response["error"] = false;
            $response['id_user'] = $user['id_user'];
            $response['api_key'] = $user['api_key'];
            $response['img'] = $user['img'];
            $response['img_port'] = $user['img_port'];
            $response['name'] = $user['name'];
            $response['mov'] = $user['mov'];
            $response['email'] = $user['email'];
            $response['sex'] = $user['sex'];
            $response['id_prov'] = $user['id_prov'];


            if ($user['sex'] == "F") {

                $response["message"] = "Bienvenida de vuelta, " . $user['name'];

            } else {

                $response["message"] = "Bienvenido de vuelta, " . $user['name'];

            }

        } else {

            // unknown error occurred
            $response['error'] = true;
            $response['message'] = 'Ha ocurrido un error de red. Intenta mas tarde';

        }

    } else {

        // user credentials are wrong
        $response['error'] = true;
        $response['message'] = 'datos';

    }

    echoResponse(200, $response);

});

$app->post('/addart', function () use ($app) {

    // check for required params

    verifyRequiredParams(array('img_p', 'title', 'price', 'body', 'coin', 'id_dep', 'id_cat', 'id_prov', 'id_user'));
    $response = array();
    $img = $app->request()->post('img_p');
    $title = $app->request()->post('title');
    $price = $app->request()->post('price');
    $body = $app->request()->post('body');
    $id_dep = $app->request()->post('id_dep');
    $id_cat = $app->request()->post('id_cat');
    $id_prov = $app->request()->post('id_prov');
    $coin = $app->request()->post('coin');
    $id_user = $app->request()->post('id_user');

    global $user_id;
    $db = new DbHandler();


    $old_name = time();


    $path = PRINC_PATH_IMAGES . "/arts/First/$old_name.jpg";

    $path_to_store = "/arts/First/$old_name.jpg";

    $art_id = NULL;

    if (file_put_contents($path, base64_decode($img))) {

        $art_id = $db->add_art($path_to_store, $title, $price, $body, $coin, $id_dep, $id_cat, $id_prov, $id_user);

        if ($art_id != NULL) {

            $db->add_cant_arts_user($id_user);

            $new_name = "IMG_P_" . $art_id;

            $oldDirectory = PRINC_PATH_IMAGES . "/arts/First/$old_name.jpg";
            $newDirectory = PRINC_PATH_IMAGES . "/arts/First/$new_name.jpg";

            $result_rename = rename($oldDirectory, $newDirectory);

            if ($result_rename) {

                $new_path = "/arts/First/$new_name.jpg";
                $db->update_img_p_by_id_art($new_path, $art_id);

            }


            $response["error"] = false;
            $response["message"] = "Artículo creado correctamente.";
            $response["id_art"] = $art_id;
            echoResponse(200, $response);

        } else {

            $response["error"] = true;
            $response["message"] = "Error al insertar el artículo. Intenta mas tarde";
            echoResponse(200, $response);

        }

    } else {

        $response["error"] = true;
        $response["message"] = "Error al insertar la imagen del artículo. Intenta mas tarde";
        echoResponse(200, $response);

    }


    clean_title($art_id, $title);


});

$app->post('/addimgs', function () use ($app) {

    verifyRequiredParams(array('img2', 'img3', 'img4', 'id_art', 'id_dep'));
    $image2 = $app->request()->post('img2');
    $image3 = $app->request()->post('img3');
    $image4 = $app->request()->post('img4');
    $id_art = $app->request()->post('id_art');
    $id_dep = $app->request()->post('id_dep');

    // echo $image;
    $db = new DbHandler();


    $dir = PRINC_PATH_IMAGES . "/arts/";

    $dir_to_store = "/arts/";

    $response = array();

    $name_folder = get_name_folder($id_dep);

    $response["error2"] = false;
    $response["error3"] = false;
    $response["error4"] = false;

    if ($image2 != "empty") {
        $posc = "2";
        $name = $id_art . "_" . $posc;
        $path = $dir . "$name_folder/$name.jpg";
        $path_to_store = $dir_to_store . "$name_folder/$name.jpg";
        if (file_put_contents($path, base64_decode($image2))) {

            $result = $db->add_img($path_to_store, $id_art);

            if ($result != null) {
                $response["error2"] = false;
            } else {
                $response["error2"] = true;
            }


        } else {

            $response["error2"] = true;

        }


        if ($image3 != "empty") {
            $posc = "3";
            $name = $id_art . "_" . $posc;
            $path = $dir . "$name_folder/$name.jpg";
            $path_to_store = $dir_to_store . "$name_folder/$name.jpg";
            if (file_put_contents($path, base64_decode($image3))) {

                $result = $db->add_img($path_to_store, $id_art);

                if ($result != null) {

                    $response["error3"] = false;
                } else {
                    $response["error3"] = true;
                }

            } else {

                $response["error3"] = true;

            }

            if ($image4 != "empty") {
                $posc = "4";
                $name = $id_art . "_" . $posc;
                $path = $dir . "$name_folder/$name.jpg";
                $path_to_store = $dir_to_store . "$name_folder/$name.jpg";
                if (file_put_contents($path, base64_decode($image4))) {

                    $result = $db->add_img($path_to_store, $id_art);

                    if ($result != null) {

                        $response["error4"] = false;
                    } else {
                        $response["error4"] = true;
                    }
                } else {

                    $response["error4"] = true;

                }
            }
        }
    }


    echoResponse(200, $response);

});

$app->post('/addcoment', function () use ($app) {
    // check for required params

    verifyRequiredParams(array('body', 'id_user', 'id_art'));

    $db = new DbHandler();


    $response = array();
    $descp = $app->request()->post('body');
    $id_user = $app->request()->post('id_user');
    $id_art = $app->request()->post('id_art');


    global $user_id;

    $comment_id = $db->add_coment($descp, $id_user, $id_art);


    if ($comment_id != NULL) {

        $db->add_coment_est($id_art);

        $response["error"] = false;
        $response["message"] = "Comentario agregado con éxito";
        $response["id_comment"] = $comment_id;
        echoResponse(201, $response);

    } else {

        $response["error"] = true;
        $response["message"] = "Ocurrió un error al añadir el comentario. Intenta mas tarde.";
        echoResponse(200, $response);

    }
});

$app->post('/addsugest', function () use ($app) {
    // check for required params

    verifyRequiredParams(array('body', 'type', 'so', 'device', 'email', 'id_user'));

    $db = new DbHandler();


    $response = array();
    $body = $app->request()->post('body');
    $type = $app->request()->post('type');
    $so = $app->request()->post('so');
    $device = $app->request()->post('device');
    $email = $app->request()->post('email');
    $id_user = $app->request()->post('id_user');

    global $user_id;

    $sugest_id = $db->add_sugest($body, $type, $so, $device, $email, $id_user);


    if ($sugest_id != NULL) {

        $response["error"] = false;

        echoResponse(201, $response);

    } else {

        $response["error"] = true;
        echoResponse(200, $response);

    }
});

$app->post('/addrep', function () use ($app) {
    // check for required params

    verifyRequiredParams(array('type', 'id_user', 'id_art'));

    $response = array();
    $type = $app->request()->post('type');
    $id_user = $app->request()->post('id_user');
    $id_art = $app->request()->post('id_art');

    global $user_id;
    $db = new DbHandler();

    // creating new task
    $is_rep = $db->check_rep($id_user, $id_art);

    if ($is_rep) {

        $response["error"] = true;
        $response["message"] = "¡Ya has reportado este artículo!";
        echoResponse(200, $response);

    } else {

        $reporte_id = $db->add_rep($type, $id_user, $id_art);

        if ($reporte_id != NULL) {

            $response["error"] = false;
            $response["message"] = "¡Gracias por tu aporte! Es muy importante para la comunidad Mercato";
            $response["reporte_id"] = $reporte_id;
            echoResponse(201, $response);

        } else {

            $response["error"] = true;
            $response["message"] = "Ha ocurrido un error al reportar el artículo. Intenta más tarde";
            echoResponse(200, $response);

        }
    }

});

$app->post('/adddes', function () use ($app) {


    verifyRequiredParams(array('id_user', 'id_art'));

    $response = array();

    $id_user = $app->request()->post('id_user');
    $id_art = $app->request()->post('id_art');

    global $user_id;
    $db = new DbHandler();


    $exist = $db->check_deseo($id_user, $id_art);

    if ($exist) {

        $response["error"] = false;
        $response["message"] = "¡Se ha añadido a su lista de deseos!";

    } else {

        $lista_id = $db->add_deseo_a_lista($id_user, $id_art);

        if ($lista_id != NULL) {

            $db->add_deseo_est($id_art);

            $response["error"] = false;
            $response["message"] = "¡Se ha añadido a su lista de deseos!";

        } else {

            $response["error"] = true;
            $response["message"] = "Error en la base de datos";

        }

    }

    echoResponse(200, $response);

});


//GET

$app->post('/getport_by_prov', function () use ($app) {

    verifyRequiredParams(array('id_prov', 'page'));
    global $user_id;
    $response = array();
    $db = new DbHandler();
    //numero de filas por pagina
    $rows_per_page = 20;

    $id_prov = $app->request()->post("id_prov");

    //pagina actual
    $current_page = (int)$app->request()->post("page");

    //numero total de recursos
    //$num_rows = $db->get_count_for_dep($dep);
    $num_rows = $db->get_count_arts_prem_by_prov1($id_prov, 1);


    //cantidad de paginas basadas en el total de recursos
    $total_pages = ceil($num_rows / $rows_per_page);

    //rango de resultados basados en la pagina actual (los listados comienzan en zero)
    $offset = (int)($current_page) * $rows_per_page;


    //obteniendo los articulos
    $ids = $db->get_prem_for_port($id_prov, 1, $offset, $rows_per_page);

    $response["error"] = false;
    $response["total_pages"] = $total_pages;
    $response["total_rows"] = $num_rows;
    $response["port_arts"] = array();


    while ($art = $ids->fetch_assoc()) {

        if ($art != NULL) {

            $tmp = array();
            $tmp["id_art"] = $art["id_art"];
            $tmp["created_at"] = $art["created_at"];
            $tmp["price"] = $art["price"];
            $tmp["title"] = $art["title"];
            $tmp["body"] = $art["body"];
            $tmp["img_p"] = $art["img_p"];
            $tmp["des"] = $art["des"];
            $tmp["vis"] = $art["vis"];
            $tmp["coments"] = $art["coments"];
            $tmp["prior"] = $art["prior"];
            $tmp["coin"] = $art["coin"];
            $tmp["id_dep"] = $art["id_dep"];
            $tmp["id_cat"] = $art["id_cat"];
            $tmp["id_prov"] = $art["id_prov"];
            $tmp["id_user"] = $art["id_user"];

            array_push($response["port_arts"], $tmp);

        }
    }


    echoResponse(200, $response);


});

$app->post('/gettienda_by_cat', function () use ($app) {

    verifyRequiredParams(array('id_dep', 'id_cat', 'id_prov', 'page'));
    global $user_id;
    $response = array();
    $db = new DbHandler();
    $id_dep = $app->request()->post('id_dep');
    $id_cat = $app->request()->post('id_cat');
    $id_prov = $app->request()->post('id_prov');

    //numero de filas por pagina
    $rows_per_page = 20;

    //pagina actual
    $current_page = (int)$app->request()->post("page");

    //numero total de recursos
    //$num_rows = $db->get_count_for_dep($dep);
    $num_rows = $db->get_count_for_cat($id_dep, $id_cat, $id_prov);

    //cantidad de paginas basadas en el total de recursos
    $total_pages = ceil($num_rows / $rows_per_page);

    //rango de resultados basados en la pagina actual (los listados comienzan en zero)

    $offset = (int)($current_page) * $rows_per_page;

    $result = $db->get_cat($id_dep, $id_cat, $id_prov, $offset, $rows_per_page);

    $response["error"] = false;
    $response["total_pages"] = $total_pages;
    $response["num_rows"] = $num_rows;
    $response["arts"] = array();

    // looping through result and preparing tasks array

    while ($art = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["id_art"] = $art["id_art"];
        $tmp["created_at"] = $art["created_at"];
        $tmp["price"] = $art["price"];
        $tmp["title"] = $art["title"];
        $tmp["body"] = $art["body"];
        $tmp["img_p"] = $art["img_p"];
        $tmp["des"] = $art["des"];
        $tmp["vis"] = $art["vis"];
        $tmp["coments"] = $art["coments"];
        $tmp["prior"] = $art["prior"];
        $tmp["coin"] = $art["coin"];
        $tmp["is_prem"] = $art["is_prem"];
        $tmp["is_top"] = $art["is_top"];
        $tmp["id_dep"] = $art["id_dep"];
        $tmp["id_cat"] = $art["id_cat"];
        $tmp["id_prov"] = $art["id_prov"];
        $tmp["id_user"] = $art["id_user"];

        array_push($response["arts"], $tmp);
    }

    echoResponse(200, $response);


});

$app->post('/getart_des', function () use ($app) {

    verifyRequiredParams(array('page', 'id_user'));

    $response = array();
    $db = new DbHandler();

    $rows_per_page = 20;


    $current_page = (int)$app->request()->post("page");
    $id_user = $app->request()->post("id_user");

    $num_rows = $db->get_count_for_list_des($id_user);

    $response["num_rows"] = $num_rows;

    //cantidad de paginas basadas en el total de recursos
    $total_pages = ceil($num_rows / $rows_per_page);

    //rango de resultados basados en la pagina actual (los listados comienzan en zero)
    $offset = (int)($current_page) * $rows_per_page;


    $response["error"] = false;
    $response["total_pages"] = $total_pages;
    $response["total_rows"] = $num_rows;
    $response["arts_des"] = array();


    //OBTENGO EN RESULT EL ARRAY CON LOS IDS

    $ids_dates = $db->get_ids_anuncios_deseados_by_id_user($id_user, $offset, $rows_per_page);

    $cant = (count($ids_dates));

    $response["count_ids"] = $cant;

    if ($cant > 0) {

        while ($aux = $ids_dates->fetch_assoc()) {

            $art = $db->get_art_by_id($aux["id_art"]);

            if ($art != NULL) {

                $tmp = array();
                $tmp["id_art"] = $art["id_art"];
                $tmp["created_at"] = $art["created_at"];
                $tmp["price"] = $art["price"];
                $tmp["title"] = $art["title"];
                $tmp["body"] = $art["body"];
                $tmp["img_p"] = $art["img_p"];
                $tmp["des"] = $art["des"];
                $tmp["vis"] = $art["vis"];
                $tmp["coments"] = $art["coments"];
                $tmp["prior"] = $art["prior"];
                $tmp["coin"] = $art["coin"];
                $tmp["id_dep"] = $art["id_dep"];
                $tmp["id_cat"] = $art["id_cat"];
                $tmp["id_prov"] = $art["id_prov"];
                $tmp["id_user"] = $art["id_user"];
                $tmp["date_deseo"] = $aux["created_at"];

                array_push($response["arts_des"], $tmp);

            } else {

                $response["message"] = "art es null";
            }

        }

        echoResponse(200, $response);


    } else {

        $response["error"] = true;
        $response["message"] = "Lista de deseo vacia para este usuario";
        echoResponse(404, $response);
    }

});

$app->post('/getarts_user_contact', function () use ($app) {

    verifyRequiredParams(array('id_user', 'id_art', 'page'));

    $response = array();
    $db = new DbHandler();
    $id_user = $app->request()->post("id_user");
    $id_art = $app->request()->post("id_art");

    //numero de filas por pagina
    $rows_per_page = 15;

    //pagina actual
    $current_page = (int)$app->request()->post("page");

    //numero total de recursos

    $num_rows = (int)$db->get_count_arts_user_contact($id_user) - 1;

    //cantidad de paginas basadas en el total de recursos
    $total_pages = ceil($num_rows / $rows_per_page);

    //rango de resultados basados en la pagina actual (los listados comienzan en zero)
    // $offset = (int)($current_page - 1) * $rows_per_page;

    $offset = (int)($current_page) * $rows_per_page;

    $result = $db->get_all_anuncios_user_contact($id_user, $offset, $rows_per_page);

    $response["error"] = false;
    $response["total_pages"] = $total_pages;
    $response["num_rows"] = $num_rows;
    $response["arts"] = array();


    if (count($result) > 0) {

        while ($art = $result->fetch_assoc()) {

            if ($art["id_art"] != $id_art) {

                $tmp = array();
                $tmp["id_art"] = $art["id_art"];
                $tmp["created_at"] = $art["created_at"];
                $tmp["price"] = $art["price"];
                $tmp["title"] = $art["title"];
                $tmp["body"] = $art["body"];
                $tmp["img_p"] = $art["img_p"];
                $tmp["des"] = $art["des"];
                $tmp["vis"] = $art["vis"];
                $tmp["coments"] = $art["coments"];
                $tmp["prior"] = $art["prior"];
                $tmp["coin"] = $art["coin"];
                $tmp["id_dep"] = $art["id_dep"];
                $tmp["id_cat"] = $art["id_cat"];
                $tmp["id_prov"] = $art["id_prov"];
                $tmp["id_user"] = $art["id_user"];

                array_push($response["arts"], $tmp);


            }
        }
        echoResponse(200, $response);

    } else {

        $response["error"] = true;
        $response["message"] = "Este usuario no tiene mas anuncios";
        echoResponse(404, $response);

    }
});

$app->post('/getdata_vista_art', function () use ($app) {


    verifyRequiredParams(array('id_art', 'id_user'));

    global $user_id;
    $response = array();
    $db = new DbHandler();

    $id_art = $app->request()->post('id_art');
    $id_user = $app->request()->post('id_user');
    $id_current_user = $app->request()->post('id_current_user');

    $result = $db->get_coments_by_id_anuncio($id_art);

    $error_coment = false;
    $error_user = true;

    $response["coments"] = array();

    if ($result != NULL) {

        while ($coment = $result->fetch_assoc()) {

            $tmp = array();
            $tmp["id_coment"] = $coment["id_coment"];
            $tmp["body"] = $coment["body"];
            $tmp["created_at"] = $coment["created_at"];
            $tmp["id_user"] = $coment["id_user"];
            $tmp["id_art"] = $coment["id_art"];
            $tmp["name_user"] = $db->get_name_user_for_coment($coment["id_user"]);
            $tmp["img_user"] = $db->get_img_perfil_for_coment($coment["id_user"]);


            array_push($response["coments"], $tmp);

        }

    } else {

        $error_coment = true;

    }


    $result = $db->get_user_by_id($id_user);

    if ($result != NULL) {


        $response["id_user"] = $result["id_user"];
        $response["name"] = $result["name"];
        $response["img"] = $result["img"];
        $response["img_port"] = $result["img_port"];
        $response["mov"] = $result["mov"];
        $response["email"] = $result["email"];
        $response["id_prov"] = $result["id_prov"];
        $response["cant_art"] = $result["cant_art"];


        $error_user = false;

    } else {

        $error_user = true;

    }

    $es_deseo = $db->check_deseo($id_current_user, $id_art);

    $db->add_visita($id_art);

    if ($es_deseo == true) {

        $response["is_des"] = true;
        //$response["message"] = "no deseado";


    } else {

        $response["is_des"] = false;
        //$response["message"] = "deseado";

    }


    if (!$error_coment && !$error_user) {

        $response["error"] = false;

    } else {

        $response["error"] = true;

    }

    echoResponse(200, $response);

});


//MIS ARTICULOS

$app->post('/get_mis_arts_user_pub', function () use ($app) {

    verifyRequiredParams(array('id_user', 'page'));
    $response = array();
    $db = new DbHandler();
    $id_user = $app->request()->post("id_user");


    //numero de filas por pagina
    $rows_per_page = 15;

    //pagina actual
    $current_page = (int)$app->request()->post("page");

    //numero total de recursos
    $num_rows = $db->get_count_for_mis_arts_pub($id_user, 0);

    //cantidad de paginas basadas en el total de recursos
    $total_pages = ceil($num_rows / $rows_per_page);


    $offset = (int)($current_page) * $rows_per_page;


    $result = $db->get_all_anuncios_user_for_mis_art_pub($id_user, 0, $offset, $rows_per_page);

    $response["error"] = false;
    $response["total_pages"] = $total_pages;
    $response["num_rows"] = $num_rows;
    $response["arts_pub"] = array();

    if (count($result) > 0) {

        while ($art = $result->fetch_assoc()) {

            $tmp = array();
            $tmp["id_art"] = $art["id_art"];
            $tmp["created_at"] = $art["created_at"];
            $tmp["price"] = $art["price"];
            $tmp["title"] = $art["title"];
            $tmp["body"] = $art["body"];
            $tmp["img_p"] = $art["img_p"];
            $tmp["des"] = $art["des"];
            $tmp["vis"] = $art["vis"];
            $tmp["coments"] = $art["coments"];
            $tmp["prior"] = $art["prior"];
            $tmp["coin"] = $art["coin"];
            $tmp["id_dep"] = $art["id_dep"];
            $tmp["id_cat"] = $art["id_cat"];
            $tmp["id_prov"] = $art["id_prov"];
            $tmp["id_user"] = $art["id_user"];

            array_push($response["arts_pub"], $tmp);

        }

        echoResponse(200, $response);

    } else {

        $response["error"] = true;
        $response["message"] = "Este usuario no tiene mas anuncios";
        echoResponse(404, $response);

    }
});

$app->post('/get_mis_arts_user_prem', function () use ($app) {


    verifyRequiredParams(array('id_user', 'page'));
    global $user_id;
    $response = array();
    $db = new DbHandler();

    $id_user = $app->request()->post("id_user");

    //numero de filas por pagina
    $rows_per_page = 15;

    //pagina actual
    $current_page = (int)$app->request()->post("page");

    //numero total de recursos
    $num_rows = $db->get_count_for_mis_arts_prem($id_user, 1);

    //cantidad de paginas basadas en el total de recursos
    $total_pages = ceil($num_rows / $rows_per_page);

    //rango de resultados basados en la pagina actual (los listados comienzan en zero)
    $offset = (int)($current_page) * $rows_per_page;

    $response["error"] = false;
    $response["arts_prem"] = array();
    $response["total_pages"] = $total_pages;
    $response["num_rows"] = $num_rows;

    $ids = $db->get_prem_for_my_arts_prem($id_user, 1, $offset, $rows_per_page);


    while ($aux = $ids->fetch_assoc()) {


        $date1 = new DateTime("now");
        $date2 = new DateTime($aux["date_end"]);

        $diff = $date1->diff($date2);

        $rest_days = (int)$diff->days;

        $aux1 = $date2 < $date1;

        $art = $db->get_art_by_id($aux["id_art"]);

        if ($art != NULL) {
            $tmp = array();
            $tmp["id_art"] = $art["id_art"];
            $tmp["created_at"] = $art["created_at"];
            $tmp["price"] = $art["price"];
            $tmp["title"] = $art["title"];
            $tmp["body"] = $art["body"];
            $tmp["img_p"] = $art["img_p"];
            $tmp["des"] = $art["des"];
            $tmp["vis"] = $art["vis"];
            $tmp["coments"] = $art["coments"];
            $tmp["prior"] = $art["prior"];
            $tmp["coin"] = $art["coin"];
            $tmp["id_dep"] = $art["id_dep"];
            $tmp["id_cat"] = $art["id_cat"];
            $tmp["id_prov"] = $art["id_prov"];
            $tmp["id_user"] = $art["id_user"];

            $tmp["date_begin"] = $aux["date_begin"];
            $tmp["date_end"] = $aux["date_end"];
            $tmp["cant_days"] = $aux["cant_days"];
            $tmp["rest_days"] = $rest_days;
            $tmp["is_ven"] = $aux1;

            array_push($response["arts_prem"], $tmp);
        }


    }

    echoResponse(200, $response);

});

$app->post('/get_mis_arts_user_top', function () use ($app) {


    verifyRequiredParams(array('id_user', 'page'));
    global $user_id;
    $response = array();
    $db = new DbHandler();

    $id_user = $app->request()->post("id_user");

    //numero de filas por pagina
    $rows_per_page = 15;

    //pagina actual
    $current_page = (int)$app->request()->post("page");

    //numero total de recursos
    $num_rows = $db->get_count_for_mis_arts_top($id_user, 1);

    //cantidad de paginas basadas en el total de recursos
    $total_pages = ceil($num_rows / $rows_per_page);

    $offset = (int)($current_page) * $rows_per_page;


    $response["error"] = false;
    $response["arts_top"] = array();
    $response["total_pages"] = $total_pages;
    $response["num_rows"] = $num_rows;

    $ids = $db->get_top_for_my_arts_top($id_user, 1, $offset, $rows_per_page);

    while ($aux = $ids->fetch_assoc()) {


        $date1 = new DateTime("now");
        $date2 = new DateTime($aux["date_end"]);

        $diff = $date1->diff($date2);

        $rest_days = $diff->days;

        $aux1 = $date2 < $date1;

        $art = $db->get_art_by_id($aux["id_art"]);

        if ($art != NULL) {

            $tmp = array();

            $tmp["id_art"] = $art["id_art"];
            $tmp["created_at"] = $art["created_at"];
            $tmp["price"] = $art["price"];
            $tmp["title"] = $art["title"];
            $tmp["body"] = $art["body"];
            $tmp["img_p"] = $art["img_p"];
            $tmp["des"] = $art["des"];
            $tmp["vis"] = $art["vis"];
            $tmp["coments"] = $art["coments"];
            $tmp["prior"] = $art["prior"];
            $tmp["coin"] = $art["coin"];
            $tmp["id_dep"] = $art["id_dep"];
            $tmp["id_cat"] = $art["id_cat"];
            $tmp["id_prov"] = $art["id_prov"];
            $tmp["id_user"] = $art["id_user"];
            $tmp["date_begin"] = $aux["date_begin"];
            $tmp["date_end"] = $aux["date_end"];
            $tmp["cant_days"] = $aux["cant_days"];
            $tmp["rest_days"] = $rest_days;
            $tmp["is_ven"] = $aux1;

            array_push($response["arts_top"], $tmp);

        }


    }

    echoResponse(200, $response);

});

$app->post('/getpudint_art', function () use ($app) {

    verifyRequiredParams(array('id_art', 'id_dep', 'id_cat', 'id_prov'));

    $response = array();
    $db = new DbHandler();
    $id_art_app = $app->request()->post("id_art");
    $id_dep = $app->request()->post("id_dep");
    $id_cat = $app->request()->post("id_cat");
    $id_prov = $app->request()->post("id_prov");

    $result = $db->get_arts_pud_int_art_for_dep($id_dep, $id_cat, $id_prov, 1);

    $response["error"] = false;
    $response["arts"] = array();

    if (count($result) > 0) {

        while ($art = $result->fetch_assoc()) {

            if ($art["id_art"] != $id_art_app) {

                $tmp = array();
                $tmp["id_art"] = $art["id_art"];
                $tmp["created_at"] = $art["created_at"];
                $tmp["price"] = $art["price"];
                $tmp["title"] = $art["title"];
                $tmp["body"] = $art["body"];
                $tmp["img_p"] = $art["img_p"];
                $tmp["des"] = $art["des"];
                $tmp["vis"] = $art["vis"];
                $tmp["coments"] = $art["coments"];
                $tmp["prior"] = $art["prior"];
                $tmp["coin"] = $art["coin"];
                $tmp["id_dep"] = $art["id_dep"];
                $tmp["id_cat"] = $art["id_cat"];
                $tmp["id_prov"] = $art["id_prov"];
                $tmp["id_user"] = $art["id_user"];

                array_push($response["arts"], $tmp);

            }
        }

    } else {

        $response["error"] = true;
        $response["message"] = "No hay artículos";


    }

    echoResponse(200, $response);

});

$app->post('/getpudint_user_contact', function () use ($app) {

    verifyRequiredParams(array('id_user', 'id_art', 'id_dep', 'id_prov'));
    $response = array();
    $db = new DbHandler();

    $id_user = $app->request()->post("id_user");
    $id_art = $app->request()->post("id_art");
    $id_dep = $app->request()->post("id_dep");
    $id_prov = $app->request()->post("id_prov");

    $result = $db->get_arts_pud_int_art_for_dep_user_contact($id_dep, $id_prov, 1, $id_user, $id_art);

    $response["error"] = false;
    $response["arts"] = array();

    if (count($result) > 0) {

        while ($art = $result->fetch_assoc()) {
            $tmp = array();

            $tmp["id_art"] = $art["id_art"];
            $tmp["created_at"] = $art["created_at"];
            $tmp["price"] = $art["price"];
            $tmp["title"] = $art["title"];
            $tmp["body"] = $art["body"];
            $tmp["img_p"] = $art["img_p"];
            $tmp["des"] = $art["des"];
            $tmp["vis"] = $art["vis"];
            $tmp["coments"] = $art["coments"];
            $tmp["prior"] = $art["prior"];
            $tmp["coin"] = $art["coin"];
            $tmp["id_dep"] = $art["id_dep"];
            $tmp["id_cat"] = $art["id_cat"];
            $tmp["id_prov"] = $art["id_prov"];
            $tmp["id_user"] = $art["id_user"];
            array_push($response["arts"], $tmp);

        }


    } else {

        $response["error"] = true;
        $response["message"] = "No hay artículos";

    }

    echoResponse(200, $response);

});

$app->post('/getuser', function () use ($app) {


    verifyRequiredParams(array('id_user'));

    $response = array();
    $db = new DbHandler();

    $id_user = $app->request()->post('id_user');

    $result = $db->get_user_by_id($id_user);

    if ($result != NULL) {

        $response["error"] = false;
        $response["id_user"] = $result["id_user"];
        $response["name"] = $result["name"];
        $response["img"] = $result["img"];
        $response["img_port"] = $result["img_port"];
        $response["mov"] = $result["mov"];
        $response["email"] = $result["email"];
        $response["id_prov"] = $result["id_prov"];
        $response["cant_art"] = $result["cant_art"];


        echoResponse(200, $response);

    } else {
        $response["error"] = true;
        $response["message"] = "Error de red";
        echoResponse(404, $response);
    }
});

$app->post('/getrutas', function () use ($app) {

    verifyRequiredParams(array('id_art'));

    $response = array();
    $db = new DbHandler();

    $id = $app->request()->post('id_art');

    $result = $db->get_paths_by_id_art($id);

    $response["error"] = false;
    $response["paths"] = array();

    // looping through result and preparing tasks array

    while ($foto = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["path"] = $foto["path"];
        array_push($response["paths"], $tmp);
    }

    echoResponse(200, $response);

});

$app->post('/getcoments', function () use ($app) {


    verifyRequiredParams(array('id_art'));

    global $user_id;
    $response = array();
    $db = new DbHandler();

    $id_art = $app->request()->post('id_art');

    $result = $db->get_coments_by_id_anuncio($id_art);

    $response["error"] = false;
    $response["coments"] = array();


    while ($coment = $result->fetch_assoc()) {

        $tmp = array();
        $tmp["id_coment"] = $coment["id_coment"];
        $tmp["body"] = $coment["body"];
        $tmp["created_at"] = $coment["created_at"];
        $tmp["id_user"] = $coment["id_user"];
        $tmp["id_art"] = $coment["id_art"];
        $tmp["name_user"] = $db->get_name_user_for_coment($coment["id_user"]);
        $tmp["img_user"] = $db->get_img_perfil_for_coment($coment["id_user"]);


        array_push($response["coments"], $tmp);

    }

    echoResponse(200, $response);

});

$app->post('/getusersdes_art', function () use ($app) {


    verifyRequiredParams(array('id_art'));

    global $user_id;
    $response = array();
    $db = new DbHandler();

    $id_art = $app->request()->post('id_art');

    // fetching all user tasks
    $result = $db->get_ids_users_for_des_by_id_art($id_art);


    if ($result != NULL) {

        $response["error"] = false;
        $response["users"] = array();


        while ($users = $result->fetch_assoc()) {

            $user = $db->get_two_values_user_by_id($users["id_user"]);

            if ($user != NULL) {
                $tmp = array();
                $tmp["img"] = $user["img"];
                $tmp["name"] = $user["name"];

                array_push($response["users"], $tmp);
            }

        }

    } else {

        $response["error"] = true;

    }


    echoResponse(200, $response);

});


//SET

$app->post('/set_img_port', function () use ($app) {


    // check for required params
    verifyRequiredParams(array('img_port', 'id_user'));

    $response = array();
    $img = $app->request()->post('img_port');
    $id_user = $app->request()->post('id_user');

    global $user_id;
    $db = new DbHandler();

    $name = "IMG_PORT_" . $id_user;


    $path = PRINC_PATH_IMAGES . "/portadas/$name.jpg";

    $path_to_store = "/portadas/$name.jpg";

    if (file_put_contents($path, base64_decode($img))) {

        $result = $db->update_img_portada($path_to_store, $id_user);

        $response["error"] = false;
        $response["path"] = $path_to_store;
        $response["message"] = 'La imagen de portada se cambió correctamente';


    } else {

        $response["error"] = true;
        $response["message"] = 'Ha ocurrido un error, intenta más tarde';

    }

    echoResponse(200, $response);

});

$app->post('/setimg_p', function () use ($app) {


    verifyRequiredParams(array('id_user', 'img'));

    $id_user = $app->request()->post('id_user');
    $image = $app->request()->post('img');

    $db = new DbHandler();
    $response = array();

    if (strlen($image) < 5) {

        //ESTA USANDO AVATAR

        $result = $db->update_img_user($id_user, $image);


        $response["error"] = false;
        $response["message"] = "La foto de perfil se cambió correctamente";
        $response["ruta"] = $image;

        $db->update_updated_at_user($id_user);


    } else {

        //ESTA USANDO IMAGEN


        $path = PRINC_PATH_IMAGES . "/profiles/user_$id_user.jpg";

        $path_pent = "/profiles/user_$id_user.jpg";

        if (file_put_contents($path, base64_decode($image))) {

            $result = $db->update_img_user($id_user, $path_pent);

            $response["error"] = false;
            $response["message"] = "La foto de perfil se cambió correctamente";
            $response["ruta"] = $path_pent;

            $db->update_updated_at_user($id_user);


        } else {

            $response["error"] = true;
            $response["message"] = "Error de red al cambiar la foto. Inténtalo más tarde...";

        }

    }

    echoResponse(201, $response);

});

$app->put('/editnewpass', function () use ($app) {

    $db = new DbHandler();
    $response = array();
    $id_user = $app->request()->put('id_user');
    $old_pass = $app->request()->put('old_pass');
    $new_pass = $app->request()->put('new_pass');

    $result = $db->update_password($id_user, $old_pass, $new_pass);

    if ($result) {

        $response["error"] = false;

        $db->update_updated_at_user($id_user);


    } else {

        $response["error"] = true;

    }

    echoResponse(200, $response);

});

$app->post('/add_newpass', function () use ($app) {

    $db = new DbHandler();
    $response = array();
    $id_user = $app->request()->post('id_user');
    $new_pass = $app->request()->post('new_pass');

    $result = $db->add_new_pass($new_pass, $id_user);

    if ($result) {

        $response["error"] = false;

        $db->update_updated_at_user($id_user);


    } else {

        $response["error"] = true;

    }

    echoResponse(200, $response);

});

$app->put('/setsex', function () use ($app) {

    $db = new DbHandler();
    $response = array();
    $id_usuario = $app->request()->put('id_user');
    $sexo = $app->request()->put('sex');


    $result = $db->update_sexo($id_usuario, $sexo);

    if ($result) {
        $response["error"] = false;
        $response["sex"] = $sexo;
    } else {
        $response["error"] = true;

    }
    echoResponse(200, $response);
});

$app->put('/setart', function () use ($app) {

    // check for required params
    verifyRequiredParams(array('id_art', 'coin', 'price', 'title', 'body', 'id_dep', 'id_cat', 'id_prov'));

    //global $user_id;
    $id_art = $app->request()->put('id_art');
    $coin = $app->request()->put('coin');
    $price = $app->request()->put('price');
    $title = $app->request()->put('title');
    $body = $app->request()->put('body');
    $id_dep = $app->request()->put('id_dep');
    $id_cat = $app->request()->put('id_cat');
    $id_prov = $app->request()->put('id_prov');

    $db = new DbHandler();
    $response = array();

    $result = $db->update_art($id_art, $coin, $price, $title, $body, $id_dep, $id_cat, $id_prov);

    if ($result) {

        $response["error"] = false;
        $response["message"] = "¡ Tu anuncio ha sido editado con éxito !";

    } else {

        // task failed to update
        $response["error"] = true;
        $response["message"] = "Error actualizando el anuncio. Intenta más tarde";

    }

    echoResponse(200, $response);


});

$app->post('/editimgs_prem_top', function () use ($app) {

    verifyRequiredParams(array('img2', 'img3', 'img4', 'id_dep', 'id_art', 'flag2', 'flag3', 'flag4'));

    $image2 = $app->request()->post('img2');
    $image3 = $app->request()->post('img3');
    $image4 = $app->request()->post('img4');

    $id_art = $app->request()->post('id_art');
    $dep = $app->request()->post('id_dep');

    $flag_img2 = $app->request()->post('flag2');
    $flag_img3 = $app->request()->post('flag3');
    $flag_img4 = $app->request()->post('flag4');

    $db = new DbHandler();
    $response = array();

    $name_folder = get_name_folder($dep);

    $dir_para_disco = PRINC_PATH_IMAGES . "/arts/";

    $dir_para_bd = "/arts/";

    switch ($flag_img2) {
        case 'e':
            //editando imagen 2
            $posc = "2";
            $name = $id_art . "_" . $posc;
            $path = $dir_para_disco . $name_folder . "/$name.jpg";
            if (file_put_contents($path, base64_decode($image2))) {
                //$db->update_imagen($path, $posc, $id_art);
                $response["error2"] = false;
            } else {
                $response["error2"] = true;
            }
            break;
        case 'a':
            //agregando imagen 2
            $posc = "2";
            $name = $id_art . "_" . $posc;
            $path = $dir_para_disco . $name_folder . "/$name.jpg";
            $path_to_store = $dir_para_bd . $name_folder . "/$name.jpg";
            if (file_put_contents($path, base64_decode($image2))) {

                $res = $db->add_img($path_to_store, $id_art);

                if ($res != NULL) {
                    $response["error2"] = false;
                } else {
                    $response["error2"] = true;
                }
            } else {
                $response["error2"] = true;
            }
            break;
        case 'd':
            $response["error2"] = delete_imagen_de_disco_y_de_database($id_art, $image2);
            break;
        case 'n':
            $response["error2"] = false;
            break;

    }

    switch ($flag_img3) {
        case 'e':
            //editando imagen 3
            $posc = "3";
            $name = $id_art . "_" . $posc;
            $path = $dir_para_disco . $name_folder . "/$name.jpg";
            if (file_put_contents($path, base64_decode($image3))) {
                //$db->update_imagen($path, $posc, $id_art);
                $response["error3"] = false;
            } else {
                $response["error3"] = true;
            }
            break;
        case 'a':
            //agregando imagen 3
            $posc = "3";
            $name = $id_art . "_" . $posc;
            $path = $dir_para_disco . $name_folder . "/$name.jpg";
            $path_to_store = $dir_para_bd . $name_folder . "/$name.jpg";
            if (file_put_contents($path, base64_decode($image3))) {

                $res = $db->add_img($path_to_store, $id_art);

                if ($res != NULL) {
                    $response["error3"] = false;
                } else {
                    $response["error3"] = true;
                }
            } else {
                $response["error3"] = true;
            }
            break;
        case 'd':
            $response["error3"] = delete_imagen_de_disco_y_de_database($id_art, $image3);
            break;
        case 'n':
            $response["error3"] = false;
            break;

    }

    switch ($flag_img4) {
        case 'e':
            //editando imagen 4
            $posc = "4";
            $name = $id_art . "_" . $posc;
            $path = $dir_para_disco . $name_folder . "/$name.jpg";
            if (file_put_contents($path, base64_decode($image4))) {
                //$db->update_imagen($path, $posc, $id_art);
                $response["error4"] = false;
            } else {
                $response["error4"] = true;
            }
            break;
        case 'a':
            //agregando imagen 4
            $posc = "4";
            $name = $id_art . "_" . $posc;
            $path = $dir_para_disco . $name_folder . "/$name.jpg";
            $path_to_store = $dir_para_bd . $name_folder . "/$name.jpg";
            if (file_put_contents($path, base64_decode($image4))) {

                $res = $db->add_img($path_to_store, $id_art);

                if ($res != NULL) {
                    $response["error4"] = false;
                } else {
                    $response["error4"] = true;
                }
            } else {
                $response["error4"] = true;
            }
            break;
        case 'd':
            $response["error4"] = delete_imagen_de_disco_y_de_database($id_art, $image4);
            break;
        case 'n':
            $response["error4"] = false;
            break;

    }

    echoResponse(200, $response);

});

function delete_imagen_de_disco_y_de_database($id_art, $path)
{


    $dir_para_disco = PRINC_PATH_IMAGES . $path;

    $flag_disco = unlink(realpath($dir_para_disco));


    $db = new DbHandler();

    $flag_db = $db->delete_imagen($id_art, $path);


    return !$flag_disco || !$flag_db;


}

$app->put('/setart_prem_top', function () use ($app) {
    // check for required params

    verifyRequiredParams(array('id_art', 'img', 'coin', 'price', 'title', 'body', 'id_dep', 'id_cat', 'id_prov'));

    //global $user_id;
    $id_art = $app->request()->put('id_art');
    $img = $app->request()->put('img');
    $coin = $app->request()->put('coin');
    $price = $app->request()->put('price');
    $title = $app->request()->put('title');
    $body = $app->request()->put('body');
    $id_dep = $app->request()->put('id_dep');
    $id_cat = $app->request()->put('id_cat');
    $id_prov = $app->request()->put('id_prov');

    $db = new DbHandler();
    $response = array();

    if ($img != "empty") {

        $nombre = "IMG_P_" . $id_art;


        $path = PRINC_PATH_IMAGES . "/arts/First/$nombre.jpg";

        $path_to_store = "/arts/First/$nombre.jpg";

        if (file_put_contents($path, base64_decode($img))) {

            $result = $db->update_art_with_img($id_art, $path_to_store, $coin, $price, $title, $body, $id_dep,
                $id_cat, $id_prov);

            if ($result) {

                //elimino los comentarios ya que cambio la imagen principal
                $db->delete_coments_after_edit_img_p($id_art);
                $db->update_cant_coments_art($id_art);

                $response["error"] = false;
                $response["message"] = "¡Tu artículo premium ha sido editado con éxito!";

            } else {

                // task failed to update
                $response["error"] = false;
                $response["message"] = "La imagen se actualizó con éxito";
            }

            echoResponse(200, $response);


        } else {

            $response["error"] = true;
            $response["message"] = "Ha ocurrido un error, intenta más tarde";

        }

    } else {

        $result = $db->update_art($id_art, $coin, $price, $title, $body, $id_dep, $id_cat, $id_prov);

        if ($result) {

            $response["error"] = false;
            $response["message"] = "¡Tu anuncio ha sido editado con éxito!";

        } else {

            // task failed to update
            $response["error"] = true;
            $response["message"] = "Error actualizando el anuncio. Intenta más tarde";

        }

        echoResponse(200, $response);

    }

});

$app->put('/setprof', function () use ($app) {

    verifyRequiredParams(array('id_user', 'name', 'email', 'mov', 'id_prov'));

    $id_user = $app->request()->put('id_user');
    $name = $app->request()->put('name');
    $email = $app->request()->put('email');
    $mov = $app->request()->put('mov');
    $id_prov = $app->request()->put('id_prov');

    $db = new DbHandler();
    $response["error"] = false;

    $check_mov = false;

    if ($mov != "Sin móvil" && $mov != "null") {

        $check_mov = $db->check_mov_para_edit(gestionar_mov($mov), $id_user);

    }

    $check_email = false;

    if ($email != "Sin email" && $email != "null") {

        $check_email = $db->check_email_para_edit($email, $id_user);

    }

    if (!$check_mov && !$check_email) {

        if ($mov != "Sin móvil" && $mov != "null") {

            $update_result = $db->update_profile($id_user, $name, $email, gestionar_mov($mov), $id_prov);

        } else {


            $update_result = $db->update_profile($id_user, $name, $email, $mov, $id_prov);

        }

        if ($update_result) {

            $result = $db->get_user_by_id_for_edit($id_user);

            if ($result != NULL) {

                $response["name"] = $result["name"];
                $response["mov"] = $result["mov"];
                $response["email"] = $result["email"];
                $response["id_prov"] = $result["id_prov"];
                $response["error"] = false;

                $db->update_updated_at_user($id_user);

                $response["message"] = "¡Tus datos han sido actualizados correctamente!";

                echoResponse(200, $response);

            } else {

                $response["error"] = true;
                $response["message"] = "Ha ocurrido un error de red. Intenta más tarde";
                echoResponse(200, $response);

            }

        } else {

            $response["error"] = true;
            $response["message"] = "No se actualizaron los datos. No has ingresado los datos correctos.";
            echoResponse(200, $response);


        }


    } else {


        $response["error"] = true;

        if ($check_email) {

            $response["message"] = "Este email ya está asociado a una cuenta";

        }

        if ($check_mov) {

            $response["message"] = "Este número de móvil ya está asociado a una cuenta";

        }

        echoResponse(200, $response);

    }

});

$app->put('/setprof_new_mode', function () use ($app) {

    verifyRequiredParams(array('id_user', 'name', 'email', 'mov', 'id_prov'));

    $id_user = $app->request()->put('id_user');
    $name = $app->request()->put('name');
    $email = $app->request()->put('email');
    $mov = $app->request()->put('mov');
    $id_prov = $app->request()->put('id_prov');


    $db = new DbHandler();
    //$response["error"] = false;

    $response["error_name"] = 0;
    $response["error_email"] = 0;
    $response["error_mov"] = 0;
    $response["error_prov"] = 0;


    if ($name != "1") {
        $response["error_name"] = $db->update_name($id_user, $name);
    }

    if ($email != "1") {

        //actualizar email
        //verificar q el email no exista
        $aux = $db->check_email_usuario($email);
        if ($aux) {
            $response["error_email"] = 2;
        } else {
            $response["error_email"] = $db->update_email($id_user, $email);
        }


    }

    if ($mov != "1") {

        //actualizar mov
        //verificar q el mov no exista
        $aux = $db->check_mov_usuario(gestionar_mov($mov));
        if ($aux) {
            $response["error_mov"] = 2;
        } else {
            $response["error_mov"] = $db->update_mov($id_user, gestionar_mov($mov));
        }
    }

    if ($id_prov != "99") {
        $response["error_prov"] = $db->update_id_prov($id_user, $id_prov);
        //actualizar prov
    }

    $result = $db->get_user_by_id_for_edit($id_user);

    if ($result != NULL) {

        $response["name"] = $result["name"];
        $response["mov"] = $result["mov"];
        $response["email"] = $result["email"];
        $response["id_prov"] = $result["id_prov"];
        $response["error"] = false;

        $db->update_updated_at_user($id_user);

        //$response["message"] = "¡Tus datos han sido actualizados correctamente!";

        echoResponse(200, $response);

    } else {

        $response["error"] = true;
        //$response["message"] = "Ha ocurrido un error de red. Intenta más tarde";
        echoResponse(200, $response);

    }

    echoResponse(200, $response);

});


//SEARCH
$app->post('/srchprice', function () use ($app) {

    verifyRequiredParams(array('id_dep', 'id_cat', 'word', 'desde', 'hasta', 'id_prov'));
    $response = array();
    $db = new DbHandler();
    $id_dep = $app->request()->post('id_dep');
    $id_cat = $app->request()->post('id_cat');
    $word = $app->request()->post('word');
    $desde = $app->request()->post('desde');
    $hasta = $app->request()->post('hasta');
    $id_prov = $app->request()->post('id_prov');

    //numero de filas por pagina
    $rows_per_page = 20;

    //pagina actual
    $current_page = (int)$app->request()->post("page");

    //numero total de recursos
    //$num_rows = $db->get_count_for_dep($dep);
    $num_rows = $db->get_count_cat_search_price($id_dep, $id_cat, $id_prov, $word, $desde, $hasta);

    //cantidad de paginas basadas en el total de recursos
    $total_pages = ceil($num_rows / $rows_per_page);

    //rango de resultados basados en la pagina actual (los listados comienzan en zero)
    $offset = (int)($current_page) * $rows_per_page;


    $result = $db->get_dep_con_precio_for_page($id_dep, $id_cat, $id_prov,
        $word, $desde, $hasta, $offset, $rows_per_page);

    $response["total_rows"] = $num_rows;
    $response["total_pages"] = $total_pages;
    $response["error"] = false;
    $response["arts"] = array();

    while ($art = $result->fetch_assoc()) {

        $tmp = array();
        $tmp["id_art"] = $art["id_art"];
        $tmp["created_at"] = $art["created_at"];
        $tmp["price"] = $art["price"];
        $tmp["title"] = $art["title"];
        $tmp["body"] = $art["body"];
        $tmp["img_p"] = $art["img_p"];
        $tmp["des"] = $art["des"];
        $tmp["vis"] = $art["vis"];
        $tmp["coments"] = $art["coments"];
        $tmp["prior"] = $art["prior"];
        $tmp["coin"] = $art["coin"];
        $tmp["is_prem"] = $art["is_prem"];
        $tmp["is_top"] = $art["is_top"];
        $tmp["id_dep"] = $art["id_dep"];
        $tmp["id_cat"] = $art["id_cat"];
        $tmp["id_prov"] = $art["id_prov"];
        $tmp["id_user"] = $art["id_user"];

        array_push($response["arts"], $tmp);

    }

    echoResponse(200, $response);

});

$app->post('/srchnoprice', function () use ($app) {

    verifyRequiredParams(array('id_dep', 'id_cat', 'word', 'id_prov'));
    $response = array();
    $db = new DbHandler();
    $id_dep = $app->request()->post('id_dep');
    $id_cat = $app->request()->post('id_cat');
    $word = $app->request()->post('word');
    $id_prov = $app->request()->post('id_prov');


    //BUSCAR EN DEP SIN PRECIOS
    //numero de filas por pagina

    $rows_per_page = 20;

    //pagina actual
    $current_page = (int)$app->request()->post("page");

    //numero total de recursos
    //$num_rows = $db->get_count_for_dep($dep);
    $num_rows = $db->get_count_search_no_price($id_dep, $id_cat, $id_prov, $word);

    //cantidad de paginas basadas en el total de recursos
    $total_pages = ceil($num_rows / $rows_per_page);

    //rango de resultados basados en la pagina actual (los listados comienzan en zero)
    $offset = (int)($current_page) * $rows_per_page;

    $result = $db->get_search_no_price_for_page($id_dep, $id_cat, $id_prov, $word, $offset, $rows_per_page);


    $response["total_rows"] = $num_rows;
    $response["total_pages"] = $total_pages;
    $response["error"] = false;
    $response["arts"] = array();

    while ($art = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["id_art"] = $art["id_art"];
        $tmp["created_at"] = $art["created_at"];
        $tmp["price"] = $art["price"];
        $tmp["title"] = $art["title"];
        $tmp["body"] = $art["body"];
        $tmp["img_p"] = $art["img_p"];
        $tmp["des"] = $art["des"];
        $tmp["vis"] = $art["vis"];
        $tmp["coments"] = $art["coments"];
        $tmp["prior"] = $art["prior"];
        $tmp["coin"] = $art["coin"];
        $tmp["is_prem"] = $art["is_prem"];
        $tmp["is_top"] = $art["is_top"];
        $tmp["id_dep"] = $art["id_dep"];
        $tmp["id_cat"] = $art["id_cat"];
        $tmp["id_prov"] = $art["id_prov"];
        $tmp["id_user"] = $art["id_user"];

        array_push($response["arts"], $tmp);

    }

    echoResponse(200, $response);

});

$app->post('/srchport', function () use ($app) {

    verifyRequiredParams(array('word', 'id_prov'));
    $response = array();
    $db = new DbHandler();
    $word = $app->request()->post('word');
    $id_prov = $app->request()->post('id_prov');


    //BUSCAR EN DEP SIN PRECIOS
    //numero de filas por pagina

    $rows_per_page = 20;

    //pagina actual
    $current_page = (int)$app->request()->post("page");

    //numero total de recursos
    //$num_rows = $db->get_count_for_dep($dep);
    $num_rows = $db->get_count_search_port($id_prov, $word);

    //cantidad de paginas basadas en el total de recursos
    $total_pages = ceil($num_rows / $rows_per_page);

    //rango de resultados basados en la pagina actual (los listados comienzan en zero)
    $offset = (int)($current_page) * $rows_per_page;

    $result = $db->get_search_port($id_prov, $word, $offset, $rows_per_page);


    $response["total_rows"] = $num_rows;
    $response["total_pages"] = $total_pages;
    $response["error"] = false;
    $response["arts"] = array();

    while ($art = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["id_art"] = $art["id_art"];
        $tmp["created_at"] = $art["created_at"];
        $tmp["price"] = $art["price"];
        $tmp["title"] = $art["title"];
        $tmp["body"] = $art["body"];
        $tmp["img_p"] = $art["img_p"];
        $tmp["des"] = $art["des"];
        $tmp["vis"] = $art["vis"];
        $tmp["coments"] = $art["coments"];
        $tmp["prior"] = $art["prior"];
        $tmp["coin"] = $art["coin"];
        $tmp["is_prem"] = $art["is_prem"];
        $tmp["is_top"] = $art["is_top"];
        $tmp["id_dep"] = $art["id_dep"];
        $tmp["id_cat"] = $art["id_cat"];
        $tmp["id_prov"] = $art["id_prov"];
        $tmp["id_user"] = $art["id_user"];

        array_push($response["arts"], $tmp);

    }

    echoResponse(200, $response);

});


//DELETE
$app->post('/deletart', function () use ($app) {


    verifyRequiredParams(array('id_art', 'id_user'));

    global $user_id;

    $db = new DbHandler();
    $response = array();


    $id_art = $app->request()->post("id_art");
    $id_user = $app->request()->post("id_user");

    $img_p = $db->get_img_p_by_id_art($id_art);

    $path_to_delete = PRINC_PATH_IMAGES . $img_p;

    $other_imgs = $db->check_imgs_art($id_art);

    $imgs = 0;

    if ($other_imgs) {

        $imgs = $db->get_paths_by_id_art($id_art);

    }

    $result = $db->delete_art($id_art);

    if ($result) {

        unlink(realpath($path_to_delete));

        if ($other_imgs) {

            while ($img = $imgs->fetch_assoc()) {

                $path2_to_delete = PRINC_PATH_IMAGES . $img["path"];

                unlink(realpath($path2_to_delete));

            }
        }

        $db->update_cant_arts_user($id_user);

        $response["error"] = false;
        $response["message"] = "Artículo eliminado correctamente";

        echoResponse(200, $response);

    } else {

        $response["error"] = true;
        $response["message"] = "Ocurrió un error de red. Intenta más tarde.";
        echoResponse(200, $response);

    }


});

$app->post('/delcoment', function () use ($app) {

    verifyRequiredParams(array('id_user', 'id_coment', 'id_art'));
    $db = new DbHandler();
    $response = array();
    $id_user = $app->request()->delete('id_user');
    $id_coment = $app->request()->delete('id_coment');
    $id_art = $app->request()->delete('id_art');

    $result = $db->delete_coment($id_user, $id_coment);

    if ($result) {

        $response["error"] = false;
        $response["message"] = "¡ Se ha eliminado el comentario !";
        $db->update_cant_coments($id_art);

    } else {

        $response["error"] = true;
        $response["message"] = "Se ha producido un error, intenta mas tarde..";
    }
    echoResponse(200, $response);
});

$app->post('/delalllistuser', function () use ($app) {

    verifyRequiredParams(array('id_user'));
    $db = new DbHandler();
    $response = array();
    $id_user = $app->request()->post('id_user');

    $result = $db->delete_all_lista_by_user($id_user);

    if ($result) {


        $response["error"] = false;
        $response["message"] = "Lista de deseos eliminada correctamente";

    } else {


        $response["error"] = true;
        $response["message"] = "Se ha producido un error, intenta mas tarde";

    }

    echoResponse(200, $response);

});

$app->post('/deldes', function () use ($app) {
    // check for required params

    verifyRequiredParams(array('id_user', 'id_art'));

    $response = array();
    $id_art = $app->request()->post('id_art');
    $id_user = $app->request()->post('id_user');

    global $user_id;
    $db = new DbHandler();

    // creating new task
    $result = $db->delete_deseo_lista($id_user, $id_art);

    if ($result) {
        // task deleted successfully
        $db->delete_deseo_est($id_art);
        $response["error"] = false;
        $response["message"] = "Se ha eliminado de su lista de deseos";
    } else {
        // task failed to delete
        $response["error"] = true;
        $response["message"] = "Ha ocurrido un error. Intenta mas tarde";
    }

    echoResponse(200, $response);

});

//CHECK
$app->post('/verifmov', function () use ($app) {

    verifyRequiredParams(array('mov'));
    global $user_id;
    $response = array();
    $db = new DbHandler();

    $mov = $app->request()->post('mov');

    $result = $db->check_mov_usuario($mov);

    //validateEmail($email);

    if ($result == true) {

        $response["error"] = true;

    } else {

        $response["error"] = false;

    }


    echoResponse(200, $response);

});

$app->post('/verifcorreo', function () use ($app) {

    verifyRequiredParams(array('email'));
    global $user_id;
    $response = array();
    $db = new DbHandler();

    $email = $app->request()->post('email');


    $result = $db->check_email_usuario($email);


    //validateEmail($email);

    if ($result == true) {
        $response["error"] = true;
    } else {
        $response["error"] = false;
    }


    echoResponse(200, $response);

});

$app->post('/verifpass_state', function () use ($app) {

    verifyRequiredParams(array('id_user'));
    global $user_id;
    $response = array();
    $db = new DbHandler();

    $id_user = $app->request()->post('id_user');


    $result = $db->get_get_password_for_verif($id_user);


    //validateEmail($email);

    if ($result != null) {

        $response["has_pass"] = true;

    } else {

        $response["has_pass"] = false;

    }

    echoResponse(200, $response);

});

$app->post('/isdes', function () use ($app) {

    verifyRequiredParams(array('id_user', 'id_art'));

    $response = array();
    $db = new DbHandler();

    $id_user = $app->request()->post('id_user');
    $id_art = $app->request()->post('id_art');


    $es_deseo = $db->check_deseo($id_user, $id_art);

    $db->add_visita($id_art);

    if ($es_deseo == true) {

        $response["is_des"] = true;
        $response["message"] = "no deseado";


    } else {

        $response["is_des"] = false;
        $response["message"] = "deseado";

    }

    echoResponse(200, $response);

});

$app->post('/isart_users_des', function () use ($app) {

    verifyRequiredParams(array('id_art'));

    $response = array();
    $db = new DbHandler();

    //$id_user = $app->request()->post('id_user');
    $id_art = $app->request()->post('id_art');

    $count = $db->get_count_for_art_des($id_art);
    //$db->add_visita($id_art);

    if ($count > 15) {

        $response["error"] = false;
        $response["count"] = $count;

    } else {

        $response["error"] = true;

    }

    echoResponse(200, $response);

});

function get_name_folder($id_dep)
{

    switch ($id_dep) {

        case 1:
            return "Moda";
        case 2:
            return "Estilo";
        case 3:
            return "Tecn";
        case 4:
            return "Comp";
        case 5:
            return "Hogar";
        case 6:
            return "Vehic";
        case 7:
            return "Dep";
        case 8:
            return "Otros";
        default:
            return "First";

    }


}


function clean_title($art_id, $title)
{

    if ($art_id != NULL) {

        $db = new DbHandler();


        $words = array("ganga", "Ganga", "Super precio", "Súper precio", "súper precio", "super precio", "!!!!",
            "servicio de", "@@", "¡¡¡", "$$$", "atencionnnn", "Super oferta", "Súper oferta", "súper oferta",
            "super oferta", "**", ".....", "#1", "~", "---", "ñoo", "Ñoo", "Ññoo", "__•__", "_•_", "••", "//", "eee",
            "aaa", "ooo", "¥", "©", "˚", "™", "®", "¢", "£", "¤", "GANGA", "súper ganga", "SUPER GANGA", "SÚPER GANGA",
            "esto es lo mejor", "@", "oferton", "ofertón", "Oferton", "Ofertón", "se renta", "se alquila",
            "alquilo", "rento", "ofertazo", "Ofertazo", "superoferta", "Superoferta", "súperoferta", "Súperoferta",
            "que barato", "Que barato", "qué barato", "Qué barato", "baratisimo", "baratísimo", "sss", "SSS");


        foreach ($words as $word) {

            if (strpos($title, $word) !== false || stripos($title, $word) !== false) {

                $db->update_prior_art($art_id, 300);

                break;

            }

        }


        $exploded = multiexplode(array(",", ".", "|", ":", " ", "•", "_", "~", "-"), $title);

        //$flag = true;
        $cont = (int)0;

        foreach ($exploded as $word) {

            if ($word != '' && $word != '.' && $word != ',' && $word != '•' && $word != '_' && $word != '~'
                && $word != '-' && !is_int($word)
            ) {

                if (ctype_upper($word)) {

                    //$flag = false;

                    $cont += 1;

                    //break;

                }

            }

        }

        //Si hay mas de 4 mayusculas lo cambio a minuscula

        if ($cont > 0) {

            $new_title = ucfirst(strtolower($title));

            $result = $db->update_title_to_lowwer_case($art_id, $new_title);

            echo $result;

        }

        /*foreach ($exploded as $word) {

            if ($word != '' && $word != '.' && $word != ',' && $word != '•' && $word != '_' && $word != '~'
                && $word != '-' && !is_int($word)
            ) {

                if (!ctype_upper($word)) {

                    $flag = false;

                    break;

                }

            }

        }

        //si hay al menos una minuscula lo cambia
        if ($flag) {

            $new_title = ucfirst(strtolower($title));

            $result = $db->update_title_to_lowwer_case($art_id, $new_title);

            echo $result;

        }*/

    }

}

function multiexplode($delimiters, $string)
{

    $ready = str_replace($delimiters, $delimiters[0], $string);
    $launch = explode($delimiters[0], $ready);
    return $launch;
}

function get_date_from_timestamp($created_at)
{

    $timestamp = strtotime($created_at);

    return date('Y-m-d', $timestamp);

}

function gestionar_mov($mov)
{

    // if (strlen($mov) == 8) {
    if (strlen($mov) == 8 && substr($mov, 0, 1) == "5") {


        return "+53" . $mov;

    }

    if (substr($mov, 0, 3) == '+53') {

        return $mov;

    }

    if (strlen($mov) == 10 && substr($mov, 0, 1) != '+' && substr($mov, 0, 2) == "53") {

        return "+" . $mov;

    }

    return $mov;

}

/**
 * Verifying required params posted or not
 */
function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response["error"] = true;
        //$response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        $response["message"] = 'Required field(s) is missing or empty';
        echoResponse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email)
{
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Tu correo electrónico no tiene un formato válido. Intenta otra vez';
        //estaba originalmente en 400 el code
        echoResponse(200, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoResponse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
